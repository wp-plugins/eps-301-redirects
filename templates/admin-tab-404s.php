<?php
/**
 *
 * The 404 Tab.
 *
 * The main admin area for the 404 tab.
 *
 * @package    EPS 301 Redirects
 * @author     Shawn Wernig ( shawn@eggplantstudios.ca )
 */
?>

<div class="wrap">
    <?php do_action('eps_redirects_admin_head'); ?>

    <table id="eps-redirect-entries" class="eps-table eps-table-striped">
        <tr>
            <th colspan="2">Request URL</th>
            <th class="redirect-hits">Hits</th>
            <th class="redirect-actions">Actions</th>
        </tr>
        <?php
        echo EPS_Redirects_Pro::list_404s();
        ?>
    </table>


    <div class="right">
        <?php do_action('eps_redirects_panels_right'); ?>
    </div>
    <div class="left">
        <?php do_action('eps_redirects_panels_left'); ?>
    </div>
</div>
    
    
    
    
