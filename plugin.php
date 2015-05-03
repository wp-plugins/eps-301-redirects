<?php
/**
 * Class EPS_Redirects_Plugin
 *
 * Inits the EPS_Redirects Plugin's core functionality and admin management.
 *
 *
 */


class EPS_Redirects_Plugin extends EPS_Plugin {

    protected $config = array(
        'version'           => EPS_REDIRECT_VERSION,
        'option_slug'       => 'eps_redirects',
        'page_slug'         => 'eps_redirects',
        'page_title'        => 'EPS Redirects',
        'menu_location'     => 'options',
        'page_permission'   => 'manage_options',
        'directory'         => 'eps-301-redirects'
    );

    protected $dependancies = array();

    protected $tables = array();

    public $name = 'EPS Redirects';

    public function __construct()
    {
        parent::__construct();

        // Template Hooks
        add_action( 'redirects_admin_tab', array($this, 'admin_tab_redirects'), 10, 1 );
        add_action( '404s_admin_tab', array($this, 'admin_tab_404s'), 10, 1 );
        add_action( 'import-export_admin_tab', array($this, 'admin_tab_import_export'), 10, 1 );
        add_action( 'eps_redirects_panels_left', array($this, 'admin_panel_cache'));
        add_action( 'eps_redirects_panels_right', array($this, 'admin_panel_donate'));
        add_action('eps_redirects_admin_head', array($this, 'admin_header_notices'));

        // Actions
        add_action('admin_init',            array($this, 'check_plugin_actions'));

    }

    /**
     *
     * update_self
     *
     * This function will check the current version and do any fixes required
     *
     * @return string - version number.
     * @author epstudios
     *
     */
    public function update_self()
    {

        $version = get_option( 'eps_redirects_version' );
        $this->_create_tables(); // Maybe create the tables

        if( version_compare($version, '2.0.0', '<')) {
            // migrate old format to new format.
            $this->_migrate_to_v2();
        }
        $this->set_current_version( EPS_REDIRECT_VERSION );
        return EPS_REDIRECT_VERSION;
    }

    /**
     *
     * _migrate_to_v2
     *
     * Will migrate the old storage method to the new tables.
     *
     * @return nothing
     * @author epstudios
     *
     */
    protected function _migrate_to_v2() {
        $redirects = get_option( self::$option_slug );

        if (empty($redirects)) return false; // No redirects to migrate.

        $new_redirects = array();

        foreach ($redirects as $from => $to ) {
            $new_redirects[] = array(
                'id'        => false,
                'url_to'    => urldecode($to),
                'url_from'  => $from,
                'type'      => 'url',
                'status'    => '301'
            );
        }

        EPS_Redirects::_save_redirects( $new_redirects );
    }

    /**
     *
     * _create_tables
     *
     * Creates the database architecture
     *
     * @return nothing
     * @author epstudios
     *
     */
    public function _create_tables()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . "redirects";

        $sql = "CREATE TABLE $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          url_from VARCHAR(256) DEFAULT '' NOT NULL,
          url_to VARCHAR(256) DEFAULT '' NOT NULL,
          status VARCHAR(12) DEFAULT '301' NOT NULL,
          type VARCHAR(12) DEFAULT 'url' NOT NULL,
          count mediumint(9) DEFAULT 0 NOT NULL,
          UNIQUE KEY id (id)
       );";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }





    /**
     *
     * plugin_resources
     *
     * Enqueues the resources
     *
     * @return nothing
     * @author epstudios
     *
     */
    public static function plugin_resources()
    {
        global $EPS_Redirects_Plugin;
        if( is_admin() && isset($_GET['page']) && $_GET['page'] == $EPS_Redirects_Plugin->config('page_slug') ) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('eps_redirect_script', EPS_REDIRECT_URL .'js/scripts.js');
            wp_enqueue_style('eps_redirect_styles', EPS_REDIRECT_URL .'css/eps_redirect.css');
        }
    }

    /**
     *
     * check_plugin_actions
     *
     * This function handles various POST requests.
     *
     * @return nothing
     * @author epstudios
     *
     */
    public function check_plugin_actions(){
        if( is_admin() && isset($_GET['page']) && $_GET['page'] == $this->config('page_slug') )
        {
            // Upload a CSV
            if( isset($_POST['eps_redirect_upload']) && wp_verify_nonce( $_POST['eps_redirect_nonce_submit'], 'eps_redirect_nonce') ) {
                self::_upload();
            }
            // Export a CSV
            if( isset($_POST['eps_redirect_export']) && wp_verify_nonce( $_POST['eps_redirect_nonce_submit'], 'eps_redirect_nonce') ) {
                self::export_csv();
            }

            // Refresh the Transient Cache
            if ( isset( $_POST['eps_redirect_refresh'] ) && wp_verify_nonce( $_POST['eps_redirect_nonce_submit'], 'eps_redirect_nonce') )  {
                $post_types = get_post_types(array('public'=>true), 'objects');
                foreach ($post_types as $post_type ) {
                    $options = eps_dropdown_pages( array('post_type'=>$post_type->name ) );
                    set_transient( 'post_type_cache_'.$post_type->name, $options, HOUR_IN_SECONDS );
                }
                add_action( 'admin_notices', array($this, 'admin_notice_refresh_cache') );
            }

            // Save Redirects
            if ( isset( $_POST['eps_redirect_submit'] ) && wp_verify_nonce( $_POST['eps_redirect_nonce_submit'], 'eps_redirect_nonce') ) {
                self::_save_redirects( EPS_Redirects::_parse_serial_array($_POST['redirect']) );
            }
        }
    }


    /**
     *
     * export_csv
     *
     * @return nothing
     * @author epstudios
     *
     */
    public static function export_csv()
    {
        $entries = EPS_Redirects::get_all();
        $filename = sprintf("%s-redirects.csv",
            date('Y-m-d')
        );
        if( $entries )
        {
            header('Content-disposition: attachment; filename='.$filename);
            header('Content-type: text/csv');

            foreach( $entries as $entry )
            {
                $csv = array(
                    $entry->status,
                    $entry->url_from,
                    $entry->url_to,
                    $entry->count
                );
                echo implode(',',$csv);
                echo "\n";
            }

            die();
        }

    }

    /**
     *
     * _upload
     *
     * This function handles the upload of CSV files, in accordance to the upload method specified.
     *
     * @return html string
     * @author epstudios
     *
     */
    private function _upload() {
        $new_redirects = array();
        $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');
        ini_set('auto_detect_line_endings',TRUE);

        if( !in_array($_FILES['eps_redirect_upload_file']['type'], $mimes) ) {
            add_action( 'admin_notices', array($this, 'admin_notice_bad_csv') );
            return false;
        }

        // open the file.
        if (($handle = fopen($_FILES['eps_redirect_upload_file']['tmp_name'], "r")) !== FALSE)
        {
            while (($redirect = fgetcsv($handle, 0, ",")) !== FALSE)
            {
                $redirect = array_filter($redirect);

                if( empty( $redirect ) ) continue;

                $args = count($redirect);

                if( $args > 4 || $args < 2 ) {
                    // Bad line. Too many/few arguments.
                    add_action( 'admin_notices', array($this, 'admin_notice_bad_csv_entry') );
                    continue;
                }

                $status     = (isset($redirect[0])) ? $redirect[0] : false;
                $url_from   = (isset($redirect[1])) ? $redirect[1] : false;
                $url_to     = (isset($redirect[2])) ? $redirect[2] : false;
                $count      = (isset($redirect[3])) ? $redirect[3] : false;

                switch( strtolower( $status ) ) {
                    case '404': $status = 404; break;
                    case '302': $status = 302; break;
                    case 'off':
                    case 'no':
                    case 'inactive': $status = 'inactive'; break;
                    default: $status = 301; break;
                }

                // If the user supplied a post_id, is it valid? If so, use it!
                if( $url_to && $post_id = url_to_postid( $url_to )  )
                {
                    $url_to = $post_id;
                }

                // new redirect!
                $new_redirect = array(
                    'id'        => false, // new
                    'url_from'  => $url_from,
                    'url_to'    => $url_to,
                    'type'      => ( is_numeric( $url_to ) ) ? 'post' : 'url',
                    'status'    => $status,
                    'count'     => $count
                );

                array_push($new_redirects, $new_redirect);

            }
            fclose($handle); // close file.
        }


        if( $new_redirects )
        {
            $save_redirects = array();
            foreach( $new_redirects as $redirect )
            {
                // Decide how to handle duplicates:
                switch( strtolower( $_POST['eps_redirect_upload_method'] ) )
                {
                    case 'skip':
                        if( ! EPS_Redirects::redirect_exists( $redirect ) )
                        {
                            $save_redirects[] = $redirect;
                        }
                        break;
                    case 'update':
                        if( $entry = EPS_Redirects::redirect_exists( $redirect ) )
                        {
                            $redirect['id'] = $entry->id;
                        }
                        $save_redirects[] = $redirect;
                        break;
                    case 'ignore':
                        $save_redirects[] = $redirect;
                        break;
                }
            }

            if( ! empty( $save_redirects ) )
            {
                EPS_Redirects::_save_redirects( $save_redirects );
                add_action( 'admin_notices', array($this, 'admin_notice_upload_success') );
            }
            else
            {
                add_action( 'admin_notices', array($this, 'admin_notice_upload_success_no_new') );
            }

        }
        else
        {
            add_action( 'admin_notices', array($this, 'admin_notice_upload_error') );
        }
        ini_set('auto_detect_line_endings',FALSE);
    }



    /**
     *
     * Template Hooks
     *
     * @author epstudios
     *
     */
    public static function admin_panel_cache()
    {
        include ( EPS_REDIRECT_PATH . 'templates/admin-panel-cache.php'  );
    }
    public static function admin_panel_donate()
    {
        include ( EPS_REDIRECT_PATH . 'templates/admin-panel-donate.php'  );
    }

    public static function admin_tab_redirects( $options )
    {
        include ( EPS_REDIRECT_PATH . 'templates/admin-tab-redirects.php'  );
    }
    public static function admin_tab_404s( $options )
    {
        include ( EPS_REDIRECT_PATH . 'templates/admin-tab-404s.php'  );
    }
    public static function admin_tab_import_export( $options )
    {
        include ( EPS_REDIRECT_PATH . 'templates/admin-tab-import-export.php'  );
    }

    public static function admin_header_notices()
    {
        global $wp_rewrite;
        if( !isset($wp_rewrite->permalink_structure) || empty($wp_rewrite->permalink_structure) ) {
            echo '<div class="error clear"><div class="eps-padding">';
            echo '<strong>WARNING:</strong> EPS 301 Redirects requires that a permalink structure is set. The Default Wordpress permalink structure is not compatible. Please update the <a href="options-permalink.php" title="Permalinks">Permalink Structure</a>.</div>';
            echo '</div></div>';
        }
    }



    /**
     *
     * Notices
     *
     * These functions will output a variable containing the admin ajax url for use in javascript.
     *
     * @author epstudios
     *
     */
    function admin_notice_bad_csv() {
        $this->admin_notice("WARNING: Not a valid CSV file! No new redirects have been added.", "error");
    }
    function admin_notice_upload_success() {
        $this->admin_notice("SUCCCESS: New redirects have been added.");
    }
    function admin_notice_upload_success_no_new() {
        $this->admin_notice("SUCCCESS: But no new redirects have been added. (Possibly Duplicates?)");
    }
    function admin_notice_upload_error() {
        $this->admin_notice("WARNING: Something's up. No new redirects were added, please review your CSV file.", "error");
    }
    function admin_notice_bad_csv_entry() {
        $this->admin_notice("WARNING: Encountered a bad Redirect entry in your CSV file.", "error");
    }
    function admin_notice_refresh_cache() {
        $this->admin_notice("SUCCCESS: Cache Refreshed.");
    }
    protected function admin_notice( $string, $type = "updated" ) {
        printf('<div class="%s"><p>%s</p></div>',
        $type,
        $string
        );
    }

}

// Init the plugin.
$EPS_Redirects_Plugin = new EPS_Redirects_Plugin();
?>