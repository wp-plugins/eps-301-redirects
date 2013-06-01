<?php
/**
 * 
 * EPS OPENGRAPH
 * 
 * 
 * 
 * This plugin creates opengrah data for pages, posts and custom post types.
 * 
 * PHP version 5
 *
 *
 * @package    EPS 301 Redirects
 * @author     Shawn Wernig ( shawn@eggplantstudios.ca )
 * @version    1.3.4
 */
$settings = get_option( 'eps_redirect_settings' );
global $wp_rewrite; 

?>

<div class="wrap">
        <header id="eps-header">
        <div id="icon-eggplant">&nbsp;</div>
        <h2 class="eps-title"><?php echo self::$page_title; ?></h2>         
        </header>
        
        <?php
        if( !isset($wp_rewrite->permalink_structure) || empty($wp_rewrite->permalink_structure) ) {
            echo '<div class="error clear"><div class="padding">';
                echo '<strong>WARNING:</strong> EPS 301 Redirects requires that a permalink structure is set. The Default Wordpress permalink structure is not compatible. Please update the <a href="options-permalink.php" title="Permalinks">Permalink Structure</a>.</div>';
            echo '</div></div>';
        }
        ?>
        <div id="eps-tabgroup">
        
            <div id="eps-tab-nav">
                <a href="#eps-redirect-redirects" class="active eps-tab-nav-item">Redirects</a>
                <a href="#eps-redirect-settings" class="eps-tab-nav-item">Settings</a>
            </div>
            
            <div id="eps-tabs">
                    <?php include ( EPS_REDIRECT_PATH . 'templates/admin.redirects.php'  ); ?>
                    <?php include ( EPS_REDIRECT_PATH . 'templates/admin.settings.php'  ); ?>
            </div>
        
        </div>

        
</div>
    
    
    
    
