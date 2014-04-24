<?php
/**
 * 
 * EPS 301 Redirects.
 * 
 * Admin.redirects.php
 * 
 * Outputs the redirects table.
 * 
 * 
 *
 *
 * @package    EPS 301 Redirects
 * @author     Shawn Wernig ( shawn@eggplantstudios.ca )
 * @version    2.1.0
 */
?>

<div id="eps-redirect-redirects-pane" class="eps-tab group">
    <table id="eps-redirect-entries" class="eps-table eps-table-striped">
        <tr>
            <th>Request URL</th>
            <th>Redirect To</th>
            <th class="redirect-hits">Hits</th>
            <th class="redirect-actions">Actions</th>
        </tr>
        
        <tr id="eps-redirect-add" style="display:none"><td colspan="4"><a href="#" id="eps-redirect-new"><span>+</span></a></td></tr>

        <?php echo self::get_inline_edit_entry(); ?>
        
        <?php
        echo self::do_entries();
        ?>
    </table>    
</div>
