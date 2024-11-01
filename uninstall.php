<?php
/*
 * Smallerik File Browser plugin - Uninstall script
*/

if (!defined('WP_UNINSTALL_PLUGIN'))
	exit();
	
// remove all options
delete_option('wpfib_general_options');
delete_option('wpfib_levels_options');
delete_option('wpfib_permissions_options');
delete_option('wpfib_display_options');
delete_option('wpfib_looks_options');
delete_option('wpfib_advanced_options');
	
?>
