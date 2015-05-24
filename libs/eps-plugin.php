<?php
/**
 * EPS Plugin
 *
 * @author Shawn Wernig, Eggplant Studios, www.eggplantstudios.ca
 * @version 1.0.0
 * @copyright 2015 Eggplant Studios
 * @package EPS Boilerplate
 */

if( ! class_exists('EPS_Plugin') )
{
    class EPS_Plugin {

        protected $config = array(
            'version' => '',
            'option_slug' => '',
            'page_slug' => '',
            'page_title' => '',
            'url' => '',
            'path' => ''
        );

        protected $resources = array(
            'css' => array(
                'admin.css'
            ),
            'js' => array(
                'admin.js'
            )
        );


        protected $tables = array();

        protected $dependencies = array();

        protected $options;

        public $name = '';


        /**
         *
         * Constructor
         *
         * Add some actions.
         *
         */
        public function __construct(){
            $this->config['url'] = plugins_url() . $this->config['directory'] . '/';
            $this->config['path'] = EPS_REDIRECT_PATH . $this->config['directory'] . '/';

            if( class_exists('EPS_Plugin_Options') )
                $this->settings = new EPS_Plugin_Options( $this );

            register_activation_hook(	__FILE__, array($this, '_activation'));
            register_deactivation_hook(	__FILE__, array($this, '_deactivation'));
            if ( !self::is_current_version() )  self::update_self();
            add_action('init',                  array($this, 'plugin_resources'));
        }


        public function resolve_dependencies()
        {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            foreach( $this->dependencies as $name => $path_to_plugin )
            {
                if ( ! is_plugin_active( $path_to_plugin ) )
                {
                    echo $name . ' IS NOT INSTALLED!';
                }
            }
        }

        public static function plugin_resources()
        {

        }

        private function resource_path( $path, $resource )
        {
            return strtolower(
                $this->config['url']
                . $path . '/'
                . $resource );
        }

        private function resource_name( $resource )
        {
            return strtolower( $this->name . '_' . key( $resource ) );
        }

        /**
         *
         *
         * Activation and Deactivation Handlers.
         *
         * @return nothing
         * @author epstudios
         */
        public function activation_error() {
            file_put_contents($this->config('path'). '/error_activation.html', ob_get_contents());
        }

        public function _activation() {
            if ( !self::is_current_version() )  self::update_self();
        }

        public function _deactivation() {}

        public static function is_current_version()
        {
            return version_compare( self::current_version(), EPS_REDIRECT_VERSION, '=') ? true : false; // TODO decouple
        }
        public static function current_version()
        {
            return get_option( 'eps_redirects_version' ); // TODO decouple
        }
        public static function set_current_version( $version )
        {
            update_option( 'eps_redirects_version', $version );
        }
        /**
         *
         * CHECK VERSION
         *
         * This function will check the current version and do any fixes required
         *
         * @return string - version number.
         * @author epstudios
         *
         */
        public function update_self() {
            $this->set_current_version(  $this->config['version'] );
            return $this->config['version'];
        }

        public function config($name)
        {
            return ( isset($this->config[ $name ]) ) ? $this->config[ $name ] : false;
        }

        /**
         *
         * CREATE TABLES
         *
         * Creates the new database architecture
         *
         * TODO This could be more elegant - and check for syntax errors too.
         *
         * @return nothing
         * @author epstudios
         *
         */
        protected function _create_tables()
        {
            global $wpdb;

            $sql = '';

            foreach( $this->tables as $name => $data )
            {
                $sql .= sprintf("CREATE TABLE `%s` (\n", $wpdb->prefix . $name );

                foreach($data['columns'] as $name => $attr )
                {
                    $sql .= sprintf( "`%s` %s, \n", $name, $attr );
                }

                $sql .= "PRIMARY KEY (`ID`), \n";

                if( isset($data['foreign_keys']) && !empty($data['foreign_keys']) )
                {
                    foreach( $data['foreign_keys'] as $name => $reference )
                    {
                        $sql .= sprintf( "FOREIGN KEY (`%s`) REFERENCES %s%s ON DELETE CASCADE ON UPDATE CASCADE, \n", $name, $wpdb->prefix, $reference );
                    }
                }

                $sql = substr($sql, 0, -3);
                $sql .= "\n";
                $sql .= ");\n\n";
            }

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }
    }

}

?>