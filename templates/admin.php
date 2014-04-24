<?php
/**
 * 
 * EPS 301 Redirects.
 * 
 * Admin.php
 * 
 * Outputs the admin page - and includes the tabs.
 * 
 * 
 *
 *
 * @package    EPS 301 Redirects
 * @author     Shawn Wernig ( shawn@eggplantstudios.ca )
 * @version    2.1.0
 */
 
 
global $wp_rewrite; 

?>

<div class="wrap">
        <header id="eps-header">
        <div id="icon-eggplant">&nbsp;</div>
        <h2 class="eps-title"><?php echo self::$page_title; ?> <?php echo get_option( 'eps_redirects_version' ); ?></h2>         
        </header>
        
        <?php
        if( !isset($wp_rewrite->permalink_structure) || empty($wp_rewrite->permalink_structure) ) {
            echo '<div class="error clear"><div class="eps-padding">';
                echo '<strong>WARNING:</strong> EPS 301 Redirects requires that a permalink structure is set. The Default Wordpress permalink structure is not compatible. Please update the <a href="options-permalink.php" title="Permalinks">Permalink Structure</a>.</div>';
            echo '</div></div>';
        }
        ?>
        <div id="eps-tabgroup">
        
            <div id="eps-tab-nav">
                <a href="#eps-redirect-redirects" class="active eps-tab-nav-item">Redirects</a>
            </div>
            
            <div id="eps-tabs">
                    <?php include ( EPS_REDIRECT_PATH . 'templates/admin.redirects.php'  ); ?>
            </div>
        
        </div>
        
        <div id="donate-box" class="eps-panel">
            <div class="eps-padding">
            <p>Comments, questions, bugs and feature requests can be sent to: <a href="mailto:plugins@eggplantstudios.ca">plugins@eggplantstudios.ca</a></p>
            <hr>
            <h3>Please consider donating</h3>
            <p>Your donations help support future versions EPS 301 Redirects.</p>
            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
                <p>
                    <input type="hidden" name="cmd" value="_s-xclick">
                    <input type="hidden" name="hosted_button_id" value="2WC9XYFX49CSQ">
                    <input class="button button-secondary" type="submit" name="submit" value="Donate">
                </p>
            </form>
            </div>
        </div>
        <div class="left">
        <div class="eps-panel eps-margin-top">
            <form method="post" action="" class="eps-padding" enctype="multipart/form-data">
                <?php wp_nonce_field('eps_redirect_nonce', 'eps_redirect_nonce_submit'); ?>
                <input accept="csv" type="file" name="eps_redirect_upload_file" value="">
                <input type="submit" name="eps_redirect_upload" id="submit" class="button button-secondary" value="Upload CSV"/>
                <br><small class="eps-grey-text">Supply Columns: <strong>Status</strong> (301,302,inactive), <strong>Request URL</strong>, <strong>Redirect To</strong> (ID or URL).</small>
            </form>
        </div>
        
        <div class="eps-panel left eps-margin-top">
            <form method="post" action="" class="eps-padding">
                <?php wp_nonce_field('eps_redirect_nonce', 'eps_redirect_nonce_submit');   ?>
                <input type="submit" name="eps_redirect_refresh" id="submit" class="button button-secondary" value="Refresh Cache"/>
                <br><small class="eps-grey-text">Refresh the cache if the dropdowns are out of date.</small>
            </form>
        </div>
        </div>
    
        
</div>
    
    
    
    
