<?php
/**
 *
 * The Redirects Tab.
 *
 * The main admin area for the redirects tab.
 *
 * @package    EPS 301 Redirects
 * @author     Shawn Wernig ( shawn@eggplantstudios.ca )
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>


<div class="wrap">
    <?php do_action('eps_redirects_admin_head'); ?>

    <table id="eps-redirect-entries" class="eps-table eps-table-striped">
        <tr>
            <th>Request URL</th>
            <th>Redirect To</th>
            <th class="redirect-hits">Hits</th>
            <th class="redirect-actions">Actions</th>
        </tr>

        <tr id="eps-redirect-add" style="display:none"><td colspan="4"><a href="#" id="eps-redirect-new"><span>+</span></a></td></tr>

        <?php
        echo EPS_Redirects::get_inline_edit_entry();
        echo EPS_Redirects::list_redirects();
        ?>
    </table>


    <div class="right">
        <?php do_action('eps_redirects_panels_right'); ?>
    </div>
    <div class="left">
        <?php do_action('eps_redirects_panels_left'); ?>
    </div>
</div>
    
    
    
    
