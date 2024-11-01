<?php
/*
Plugin Name: Smallerik File Browser
Plugin URI: http://www.smallerik.com/index.php/wordpress-plugins/smallerik-file-browser
Description: This plugin enables (authorized) users to <strong>embed a file repository inside a standard Wordpress page or post</strong>. File repositories point to a specific area of the filesystem (inside or outside of the web root). The same page or post <strong>can optionally be made to display a different file repository for each user</strong>, thus obtaining a personal area for each user. Access level restrictions to actions such as upload, delete, rename, unzip, etc. can be set for each user or set of users of the repository.
Version: 1.1
Author: Enrico Sandoli
Author URI: http://www.smallerik.com/
License: GPLv2 or later
*/

/*
 * Copyright 2012-2014 Enrico Sandoli (email: wordpress@smallerik.com)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Publi License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You can find copies of the GNU General Public License on
 * http://www.gnu.org/licenses/ or you can write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110.1301 USA
*/

define('WPFIB_DEVELOPER_SITE',		'http://www.smallerik.com/index.php/wordpress-plugins/smallerik-file-browser');

define('DS',						DIRECTORY_SEPARATOR);

define('REPOTYPE_UNSPECIFIED',				1);
define('REPOTYPE_STANDARD',					2);
define('REPOTYPE_USERBOUND',				3);
define('REPOTYPE_GROUPBOUND',				4);

define('USERBOUND_PARAMETER_ID',			1);
define('USERBOUND_PARAMETER_LOGIN',			2);
define('USERBOUND_PARAMETER_NAME',			3);
define('USERBOUND_PARAMETER_ID_LOGIN',		4);
define('USERBOUND_PARAMETER_ID_NAME',		5);
define('USERBOUND_PARAMETER_ID_LOGIN_NAME',	6);

define('CAN_DISPLAY_REPO',					1);
define('CAN_DOWNLOAD',						2);
define('CAN_UPLOAD',						3);
define('CAN_DELETE_FOLDERS',				4);
define('CAN_DELETE_FILES',					5);
define('CAN_RESTORE_FILES',					6);
define('CAN_CREATE_FOLDERS',				7);
define('CAN_RENAME_FILES',					8);
define('CAN_RENAME_FOLDERS',				9);
define('CAN_UNZIP_FILES',					10);

define('ACTION_NEEDS_RELOAD',				0);
define('ACTION_NEEDS_NOT_RELOAD',			1);

define('ACTION_TEXT_TYPE_NONE',				0);
define('ACTION_TEXT_TYPE_SUCCESS',			1);
define('ACTION_TEXT_TYPE_WARNING',			2);
define('ACTION_TEXT_TYPE_ERROR',			3);

define('SORT_BY_NAME',				1);
define('SORT_BY_SIZE',				2);
define('SORT_BY_CHANGED',			3);

define('SORT_ASCENDING',			1);
define('SORT_DESCENDING',			2);

define('LINK_THROUGH_SCRIPT',		0);
define('LINK_DIRECT_SAME_WINDOW',	1);
define('LINK_DIRECT_NEW_WINDOW',	2);

define('UPLOAD_HTML',				1);
define('UPLOAD_SWF',				2);

define('ACTIONS_BOX_IS_OPEN',		1);
define('ACTIONS_BOX_IS_CLOSED',		2);


// administrative settings
include_once plugin_dir_path(__FILE__).'includes/options.php';

register_activation_hook(__FILE__, 'wpfib_add_defaults_callback');

// global variables
$wpfib_version_number = "1.1";

// Enable output character encoding conversion only for this page ***
// http://it2.php.net/manual/en/mbstring.http.php
// Set HTTP output character encoding to SJIS
//mb_http_output('SJIS');

// Start buffering and specify "mb_output_handler" as
// callback function
//ob_start('mb_output_handler');
// *******************************************************************

if (get_magic_quotes_gpc())
{
    $_POST      = array_map( 'stripslashes_deep', $_POST );
    $_GET       = array_map( 'stripslashes_deep', $_GET );
    $_COOKIE    = array_map( 'stripslashes_deep', $_COOKIE );
    $_REQUEST   = array_map( 'stripslashes_deep', $_REQUEST );
}

// for language translations
load_plugin_textdomain('wpfib', false, 'wpfib/languages');

// this is the filter function used to remove the wpfib_art key from the URL
//function wpfib_remove_art_get_var($location, $status)
//{
//	if(isset($_GET['wpfib_art']))
//	{
//		$location = remove_query_arg('wpfib_art', $location);
//	}
//	return $location;
//}

// add main shortcode
add_shortcode('wpfib', 'wpfib_shortcode_handler');

// start output buffering (needed to get cookies to work)
ob_start();

// main shortcode handler: must return a string that will replace the shortcode
function wpfib_shortcode_handler($atts, $content = null)
{
	global $wpfib_options;
	global $wpfib_baselink;
	global $wpfib_imgdirNavigation;

	$wpfib_baselink = site_url().DS."?p=".get_the_ID();
	
	// init text
	$text = "";
	$success_text = "";
	$warning_text = "";
	$error_text = "";
	
	// initialise all options parameters
	wpfib_init_options($atts);

	// load CSS and JS
	
	// set jQuery
	wp_enqueue_script('jquery');
	

	// this cookie script must be enqueued after lightbox
	wp_enqueue_script('cookie', plugin_dir_url(__FILE__).'js/jquery.cookie.js');

	
	// write local css and js
	$text .= "<style>".wpfib_do_css()."</style>";
	$text .= "<script>".wpfib_do_js()."</script>";
	
	// get the current user info
	wpfib_get_userdata();
	
	// check if the post/page is ok for displaying a repository (single post or list of them)
//	if (!wpfib_page_or_post_check_passed($error_text))
//	{
//		return $text.$error_text;
//	}

	// check if the post/page was written by a trusted author
	if (!wpfib_trusted_author_check_passed($error_text))
	{
		return $text.$error_text;
	}

	// set/get the repository path variables
	$repo_abspath = "";
	if (!wpfib_get_repository_path($repo_abspath, $error_text))
	{
		return $text.$error_text;
	}

	// once we have the repo path (it might be a userbound repo) define access rights
	wpfib_get_access_rights();

	// check if the current user has enough permissions to display the repository
	if (!wpfib_can_display_repo($error_text))
	{
		return $text.$error_text;
	}

	// validate (and get) the current location curdir_relpath: this is the path of the current location, relative to the repo_abspath
	$curdir_relpath = "";
	if (!wpfib_get_current_location($curdir_relpath, $repo_abspath, $error_text))
	{
		return $text.$error_text;
	}

	// detect requests for cookie and reload accordingly if needed
	if (wpfib_needs_to_reload_with_cookie($curdir_relpath))
	{
		wp_redirect($wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath));
	}

	// manage user actions will call separate functions for each individual action
	// this function also manages all redirects and error/warning/success boxes display
	wpfib_actions_handler($text, $repo_abspath, $curdir_relpath);
		
	// display repository will call separate functions to render each div section of the repository
	if (!wpfib_display_repository($text, $repo_abspath, $curdir_relpath, $error_text))
	{
		return $text.$error_text;
	}
	
	// if all goes well, return the text that will replace the shortcode
	
	// we echo the html (it's still buffered), then get the contents from the buffer, clean it, end it and return the content
	//echo $text;
	//$content = ob_get_contents();
    //ob_end_clean();
    //return $content;
	
	return $text;
}

function wpfib_init_options(&$atts)
{
	global $wpfib_options;
	global $wpfib_imgdirNavigation;
	global $wpfib_imgdirExtensions;
	global $wpfib_imgdirExtensionsPath;
	
	global $wpfib_default_file_chmod;
	global $wpfib_default_dir_chmod;
	
	$wpfib_imgdirNavigation = plugin_dir_url(__FILE__).'media/smallerik/navigationIcons/';
	$wpfib_imgdirExtensions = plugin_dir_url(__FILE__).'media/smallerik/extensionsIcons/';
	$wpfib_imgdirExtensionsPath = plugin_dir_path(__FILE__).'media/smallerik/extensionsIcons/';
	
	// get the backend values of the various parameters
	$general_options =		get_option('wpfib_general_options');
	$levels_options =		get_option('wpfib_levels_options');
	$permissions_options =	get_option('wpfib_permissions_options');
 	$display_options =		get_option('wpfib_display_options');
 	$looks_options =		get_option('wpfib_looks_options');
 	$advanced_options =		get_option('wpfib_advanced_options');
	
	// the defaults array includes all options that can be either defined only in a
	// command options or backend options which may be overriden by command options
	$defaults = array(
		
		// defaults for possible command options (no backend equivalents)
		'abspath' =>					'',
		'relpath' =>					'',
		'title' =>						'',
		
		// defaults from general options
		'repo' =>						$general_options['repo'],

		// defaults from permissions options
		'def_visitor_level' =>			$permissions_options['def_visitor_level'],
		'def_visitor_level_strict' =>	$permissions_options['def_visitor_level_strict'],
		'def_registered_level' =>		$permissions_options['def_registered_level'],
		'def_userbound_level' =>		$permissions_options['def_userbound_level'],
		'def_userbound_level_strict' => $permissions_options['def_userbound_level_strict'],

		// defaults from display options
		'type_of_link_to_files' =>		$display_options['type_of_link_to_files'],
		'display_navigation' =>			$display_options['display_navigation'],
		'display_file_filter' =>		$display_options['display_file_filter'],
		'file_filter_width' =>			$display_options['file_filter_width'],

		'display_file_ext' =>			$display_options['display_file_ext'],
		'display_filesize' =>			$display_options['display_filesize'],
		'filesize_separator' =>			$display_options['filesize_separator'],
		'display_filedate' =>			$display_options['display_filedate'],
		'date_format' =>				$display_options['date_format'],
		'display_filetime' =>			$display_options['display_filetime'],
		'display_seconds' =>			$display_options['display_seconds'],
		
		'sort_by' =>					$display_options['sort_by'],
		'sort_as' =>					$display_options['sort_as'],
		'sort_nat'=>					$display_options['sort_nat'],
		
		// default from looks options	(general - apply to all boxes)
		'table_width' =>				$looks_options['table_width'],
		'border_radius' =>				$looks_options['border_radius'],
		'use_box_shadow' =>				$looks_options['use_box_shadow'],
		'box_shadow_width' =>			$looks_options['box_shadow_width'],
		'box_shadow_blur' =>			$looks_options['box_shadow_blur'],
		'box_shadow_color' =>			$looks_options['box_shadow_color'],
		
		'use_thumb_shadow' =>			$looks_options['use_thumb_shadow'],
		'thumb_shadow_width' =>			$looks_options['thumb_shadow_width'],
		'thumb_shadow_blur' =>			$looks_options['thumb_shadow_blur'],
		'thumb_shadow_color' =>			$looks_options['thumb_shadow_color'],
		
		'use_default_font_size' =>		$looks_options['use_default_font_size'],
		'font_size' =>					$looks_options['font_size'],

		'box_distance' =>				$looks_options['box_distance'],

		// ----------------------------	(file/folder list params)
		'header_bgcolor' =>				$looks_options['header_bgcolor'],

		'icon_width' =>					$looks_options['icon_width'],
		'icon_padding' =>				$looks_options['icon_padding'],
		'thumbsize'=>					$looks_options['thumbsize'],

		'min_row_height' =>				$looks_options['min_row_height'],
		'highlighted_color' =>			$looks_options['highlighted_color'],
		'oddrows_color' =>				$looks_options['oddrows_color'],
		'evenrows_color' =>				$looks_options['evenrows_color'],

		'line_bgcolor' =>				$looks_options['line_bgcolor'],
		'line_height' =>				$looks_options['line_height'],

		// ----------------------------	(boxes colors and outlines)
		'framebox_bgcolor' =>			$looks_options['framebox_bgcolor'],
		'framebox_border' =>			$looks_options['framebox_border'],
		'framebox_linetype' =>			$looks_options['framebox_linetype'],
		'framebox_linecolor' =>			$looks_options['framebox_linecolor'],
		
		'errorbox_bgcolor' =>			$looks_options['errorbox_bgcolor'],
		'errorbox_border' =>			$looks_options['errorbox_border'],
		'errorbox_linetype' =>			$looks_options['errorbox_linetype'],
		'errorbox_linecolor' =>			$looks_options['errorbox_linecolor'],
		
		'successbox_bgcolor' =>			$looks_options['successbox_bgcolor'],
		'successbox_border' =>			$looks_options['successbox_border'],
		'successbox_linetype' =>		$looks_options['successbox_linetype'],
		'successbox_linecolor' =>		$looks_options['successbox_linecolor'],
		
		// ----------------------------	(input styles)
		'inputbox_bgcolor' =>			$looks_options['inputbox_bgcolor'],
		'inputbox_border' =>			$looks_options['inputbox_border'],
		'inputbox_linetype' =>			$looks_options['inputbox_linetype'],//	echo "<br />WPFIB_DEFAULTS ----------------------------<br />";
//	print_r($defaults);
//	echo "<br />WPFIB_ATTS ----------------------------<br />";
//	print_r($atts);
//	echo "<br />WPFIB_OPTIONS ----------------------------<br />";
//	print_r($wpfib_options);

		'inputbox_linecolor' =>			$looks_options['inputbox_linecolor'],
	);

	// defaults from level options: we add non-empty level names to the defaults array
	for ($level = 0; $level < WPFIB_LEVELS; $level++)
	{
		if (!empty($levels_options['level_'.($level + 1).'_name']))
			$defaults[$levels_options['level_'.($level + 1).'_name']] = $permissions_options['level_'.($level + 1).'_users'];
	}
	
	// create global options with shortcode attributes (and defaults)
	$wpfib_options = shortcode_atts($defaults, $atts);
	
	if ($advanced_options['DEBUG_enabled'])
	{
		echo "<br />WPFIB_DEFAULTS ----------------------------<br />";
		print_r($defaults);
		echo "<br />WPFIB_ATTS ----------------------------<br />";
		print_r($atts);
		echo "<br />WPFIB_OPTIONS ----------------------------<br />";
		print_r($wpfib_options);
	}
	
	// we now create the global options we don't allow front-end users to override and merge them to the global options array
	$wpfib_options_protected = array(
		
		// defaults from general options
		'default_path' =>					$general_options['default_path'],
		'is_path_relative' =>				$general_options['is_path_relative'],
		'default_path_override_enabled' =>	$general_options['default_path_override_enabled'],//false,
		
		// defaults from permissions options
		'trusted_authors' =>				$permissions_options['trusted_authors'],
		'allow_unzip' =>					$permissions_options['allow_unzip'],
		'allow_file_archiving' =>			$permissions_options['allow_file_archiving'],
	
		// defaults from display options
		'hidden_files' =>					$display_options['hidden_files'],
		'hidden_folders' =>					$display_options['hidden_folders'],

		// defaults from advanced options
		'userbound_dir_prefix' =>			$advanced_options['userbound_dir_prefix'],
		'userbound_dir_params' =>			$advanced_options['userbound_dir_params'],
		'userbound_dir_suffix' =>			$advanced_options['userbound_dir_suffix'],
		
		'default_string_encoding' =>		$advanced_options['default_string_encoding'],
		
		'default_file_chmod' =>				$advanced_options['default_file_chmod'],
		'default_dir_chmod' =>				$advanced_options['default_dir_chmod'],
		
		'DEBUG_enabled' =>					$advanced_options['DEBUG_enabled'],
		
	);
	
	$wpfib_options = array_merge($wpfib_options, $wpfib_options_protected);
	
	// do required processing of some default values and store the results in new global variables
	$wpfib_default_file_chmod = '0'.ltrim($wpfib_options['default_file_chmod'], "0");
	$wpfib_default_file_chmod = octdec($wpfib_default_file_chmod);    // convert octal mode to decimal
	$wpfib_default_dir_chmod = '0'.ltrim($wpfib_options['default_dir_chmod'], "0");
	$wpfib_default_dir_chmod = octdec($wpfib_default_dir_chmod);    // convert octal mode to decimal
}

// TODO see if we need to use this
function wpfib_stripslashes()
{
	// remove magic quotes if needed
	if (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc())
	{
    	function wpfib_stripslashes_deep($value)
    	{
			$value = is_array($value) ?  array_map('wpfib_stripslashes_deep', $value) : stripslashes($value);
			return $value;
		}

		$_POST = array_map('wpfib_stripslashes_deep', $_POST);
		$_GET = array_map('wpfib_stripslashes_deep', $_GET);
		$_COOKIE = array_map('wpfib_stripslashes_deep', $_COOKIE);
		$_REQUEST = array_map('wpfib_stripslashes_deep', $_REQUEST);
	}
}

function wpfib_get_userdata()
{
	global $wpfib_userdata;
	
	$wpfib_userdata = array();
	
	if (is_user_logged_in())
	{
		$data = get_userdata(get_current_user_id());
		
		$wpfib_userdata['user_id']			= $data->ID;
		$wpfib_userdata['user_login']		= $data->user_login;
		$wpfib_userdata['user_name']		= $data->display_name;
		$wpfib_userdata['remote_address']	= ($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : __('Unavailable', 'wpfib');
	}
	else
	{
		$wpfib_userdata['user_id']			= 0;
		$wpfib_userdata['user_login']		= 'guest';
		$wpfib_userdata['user_name']		= __('Non registered user', 'wpfib');
		$wpfib_userdata['remote_address']	= ($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : __('Unavailable', 'wpfib');
	}
}

function wpfib_get_access_rights()
{
	global $wpfib_access_rights;
	global $wpfib_options;
	global $wpfib_repotype;
	
	// if current user is a guest assign visitor's access level unless we are in a userbound repo and def_visitor_level is not strict
	if (!is_user_logged_in())
	{
		if ($wpfib_repotype == REPOTYPE_USERBOUND && !$wpfib_options['def_visitor_level_strict'])
		{
			$access_level_name = $wpfib_options['def_userbound_level'];
		}
		else
		{
			$access_level_name = $wpfib_options['def_visitor_level'];
		}
	}
		
	// otherwise (current user is registered) if the repo is userbound with def_userbound_level_strict force use of userbond acces level
	else if ($wpfib_repotype == REPOTYPE_USERBOUND && $wpfib_options['def_userbound_level_strict'])
	{
		$access_level_name = $wpfib_options['def_userbound_level'];
	}
	
	// otherwise repo is not userbound or it is but userbound level is not strict, meaning it can be overridden by custom users permissions
	else
	{
		// check if the current user ID or user name is specified in any of the user-permission lists; if it is,
		// then get the relevant access level name, otherwise use the settings for registered users;
		// because both user-permission lists and default registered users are either set in the backend
		// or overriden by the command line, we use $wpfib_options for them
		$access_level_name = $wpfib_repotype == REPOTYPE_USERBOUND ? $wpfib_options['def_userbound_level'] : $wpfib_options['def_registered_level'];
		
		$levels_options = get_option('wpfib_levels_options');
	
		for ($level = 0; $level < WPFIB_LEVELS; $level++)
		{
			if (empty($levels_options['level_'.($level + 1).'_name']))
				continue;
			
			if (wpfib_is_current_user_in_permissions_list_for_access_level($level))
			{
				$access_level_name = $levels_options['level_'.($level + 1).'_name'];
				break;
			}
		}
	}

	// once a relevant access level has been found, get the actual rights (array with individual capabilities)
	$wpfib_access_rights = wpfib_access_rights_for_level_name($access_level_name);

	if ($wpfib_options['DEBUG_enabled'])
	{
		echo "<br />Level name <strong>$access_level_name</strong> will be used<br />";
	}
}

// this function is used by wpfib_get_access_rights() to determine if the current user is in a specific user list
// (specific for an access level); the user list can be overriden
function wpfib_is_current_user_in_permissions_list_for_access_level($level)
{
	global $wpfib_userdata;
	global $wpfib_options;

	$levels_options = get_option('wpfib_levels_options');

	// we get the name from the backend options, and use this name to get the users list from $wpfib_options (which might be overriden)
	$level_name = $levels_options['level_'.($level + 1).'_name'];
	
	$users_list = preg_split("/[\s,]+/", $wpfib_options[$level_name]);
	
	if (in_array($wpfib_userdata['user_id'], $users_list) || in_array($wpfib_userdata['user_login'], $users_list))
	{
		return true;
	}
	
	return false;
}

// this function is used by wpfib_get_access_rights() to determine the access rights from the access level
function wpfib_access_rights_for_level_name($level_name)
{
	//global $wpfib_options;
	$levels_options = get_option('wpfib_levels_options');

	$access_rights = array(
		
		CAN_DISPLAY_REPO	=> false,
		CAN_DOWNLOAD		=> false,
		CAN_UPLOAD			=> false,
		CAN_DELETE_FOLDERS	=> false,
		CAN_DELETE_FILES	=> false,
		CAN_RESTORE_FILES	=> false,
		CAN_CREATE_FOLDERS	=> false,
		CAN_RENAME_FILES	=> false,
		CAN_RENAME_FOLDERS	=> false,
		CAN_UNZIP_FILES		=> false
	);

	for ($level = 0; $level < WPFIB_LEVELS; $level++)
	{
		if (empty($levels_options['level_'.($level + 1).'_name']))
			continue;

		if ($levels_options['level_'.($level + 1).'_name'] == $level_name)
		{
			$access_rights = array(
		
				CAN_DISPLAY_REPO	=> $levels_options['function_1_level_'.($level + 1)],
				CAN_DOWNLOAD		=> $levels_options['function_2_level_'.($level + 1)],
				CAN_UPLOAD			=> $levels_options['function_3_level_'.($level + 1)],
				CAN_DELETE_FOLDERS	=> $levels_options['function_4_level_'.($level + 1)],
				CAN_DELETE_FILES	=> $levels_options['function_5_level_'.($level + 1)],
				CAN_RESTORE_FILES	=> $levels_options['function_6_level_'.($level + 1)],
				CAN_CREATE_FOLDERS	=> $levels_options['function_7_level_'.($level + 1)],
				CAN_RENAME_FILES	=> $levels_options['function_8_level_'.($level + 1)],
				CAN_RENAME_FOLDERS	=> $levels_options['function_9_level_'.($level + 1)],
				CAN_UNZIP_FILES		=> $levels_options['function_10_level_'.($level + 1)]
			);
			break;
		}
	}

	return $access_rights;
}

function wpfib_can_display_repo(&$error_text)
{
	global $wpfib_access_rights;
	global $wpfib_imgdirNavigation;
	
	if ($wpfib_access_rights[CAN_DISPLAY_REPO])
	{
		return true;
	}
	else
	{
		$error_text = "<div id='JS_MAIN_DIV'><div id='JS_ERROR_DIV'><table><tr>"
					 ."<td class='alertIcon'><img src=\"".$wpfib_imgdirNavigation."warning.png\"></td><td>"
				.__('Sorry, you are not authorized to display this file repository', 'wpfib')."</td>"
					 ."</tr></table></div><br /></div>";
		return false;
	}
}

function wpfib_trusted_author_check_passed(&$error_text)
{
	global $wpfib_options;
	global $wpfib_imgdirNavigation;
	
	// an empty list means that every author is authorized
	if (empty($wpfib_options['trusted_authors']))
	{
		return true;
	}

	// get details of lastmod user
	$lastmod_author = get_the_modified_author();
	
	$lastmod_author_data = get_user_by('login', $lastmod_author);
	$lastmod_author_id = $lastmod_author_data->ID;
	
//	echo "TMA = ".$lastmod_author."<br />";
//	echo "TMA_ID = ".$lastmod_author_id."<br />";
	
	$trusted_authors_list = preg_split("/[\s,]+/", $wpfib_options['trusted_authors']);
	
	if (in_array($lastmod_author, $trusted_authors_list) || in_array($lastmod_author_id, $trusted_authors_list))
	{
		return true;
	}
	else
	{
		$msg = sprintf(__('Cannot display a file repository: this page was las modified by %s, '
					.'who is not in the Trusted Authors list of the Smallerik File Browser plugin'), $lastmod_author);

		$error_text = "<div id='JS_MAIN_DIV'><div id='JS_ERROR_DIV'><table><tr>"
						."<td class='alertIcon'><img src=\"".$wpfib_imgdirNavigation."warning.png\"></td>"
						."<td>".$msg."</td>"
						."</tr></table></div><br /></div>";
		return false;
	}
}


function wpfib_page_or_post_check_passed(&$error_text)
{
	global $wpfib_options;
	global $wpfib_imgdirNavigation;
	
	$is_singular = is_singular();
	
	echo "IS_SINGULAR[".$is_singular."]<br />";
			
	if ($is_singular)
	{
		return true;
	}
	else
	{
		$error_text = "<div id='JS_MAIN_DIV'><div id='JS_ERROR_DIV'><table><tr>"
					 ."<td class='alertIcon'><img src=\"".$wpfib_imgdirNavigation."warning.png\"></td><td>"
						.__('Cannot display a file repository: the page or post is not singular')."</td>"
					 ."</tr></table></div><br /></div>";
		return false;
	}
}

function wpfib_needs_to_reload_with_cookie($curdir_relpath)
{
	global $wpfib_baselink;

	// check if we need to set a new selected_usergroup_index cookie (used to switch between usergroups when viewing a GROUPBOUND
	// repository and the user belongs to more than one group) - we do it here as baselink is now defined
	if (isset($_GET['selected_usergroup_index']))
	{
		setcookie('selected_usergroup_index', $_GET['selected_usergroup_index'], time() + 3600 * 24 * 365);
		return true;
	}

	// set display filter cookie (request might come as a POST or GET variable
	if (isset($_POST['current_filter_list']) && strlen($_POST['current_filter_list']))
	{
		setcookie('current_filter_list', $_POST['current_filter_list']);
		return true;
	}
	else if (isset($_GET['current_filter_list']) && !strlen($_GET['current_filter_list']))
	{
		setcookie('current_filter_list', "");
		return true;
	}


	return false;
}

function wpfib_get_repository_path(&$repo_abspath, &$error_text)
{
	// ***********************************************************************************************************************
	// MANAGE REPOSITORY INFO
	// ***********************************************************************************************************************
	// get the default path parameter and check if this is meant to be expressed as an absolute path or a path relative to the root folder
	//
	// however, before doing so, check if the default path is being overriden in this particular command - this is achieved by the command option
	// abspath=<current_default_abspath> or relpath=<current_default_relative_path> - This is ONLY POSSIBLE IF the defalt path override parameter is enabled
	//
	// we can then check if a repository is found as repo=<repository>, which we take as the repository folder for this command; this folder
	// is located within the default path; the repository, which is created if it doesn't exist, may be a keyword, either USERBOUND or GROUPBOUND, to signal
	// the use of a user- or group-dependent repository (a repository whose name contains a reference to a user or a group of users)
	// 
	// note: ABSPATH is the wordpress root directory (defined at the end of wp-config.php)
	
	global $wpfib_options;
	global $wpfib_userdata;
	global $wpfib_repotype;
	global $wpfib_default_dir_chmod;
	global $wpfib_imgdirNavigation;
	
	// see if overriding default path (if enabled from the backend)
	$default_path_override_enabled = $wpfib_options['default_path_override_enabled'];

	// define the absolute path of the repository (excluding the actual repository folder, which might not be set, or be a user- or groupbound one
	if ($default_path_override_enabled && strlen($wpfib_options['relpath']))
	{
		$is_path_relative = true;
		$default_abspath = rtrim(ABSPATH, "/\\").DS.trim($wpfib_options['abspath'], "/\\");
	}
	else if ($default_path_override_enabled && strlen($wpfib_options['abspath']))
	{
		$is_path_relative = false;
		$default_abspath = rtrim($wpfib_options['abspath'], "/\\");
	}
	else
	{
		$is_path_relative = $wpfib_options['is_path_relative'];
		
		if ($is_path_relative)
		{
			$default_abspath = rtrim(ABSPATH, "/\\").DS.trim($wpfib_options['default_path'], "/\\");
		}
		else
		{
			$default_abspath = rtrim($wpfib_options['default_path'], "/\\");
		}
	}
	
	// set the repository name (excluding the path defined above)
	$repo_option = trim($wpfib_options['repo'], "/\\");
	
	if (!strlen($repo_option))
	{
		$repository = "";
		$wpfib_repotype = REPOTYPE_UNSPECIFIED;
	}
	else if (strtoupper($repo_option) == "USERBOUND")
	{
		$userbound_dir_prefix = strlen($wpfib_options['userbound_dir_prefix']) ? $wpfib_options['userbound_dir_prefix'] : "";
		$userbound_dir_suffix = strlen($wpfib_options['userbound_dir_suffix']) ? $wpfib_options['userbound_dir_suffix'] : "";
		$userbound_dir_params = is_numeric($wpfib_options['userbound_dir_params']) ? $wpfib_options['userbound_dir_params'] : USERBOUND_PARAMETER_ID_LOGIN;
		
		switch($userbound_dir_params)
		{
			case USERBOUND_PARAMETER_ID:
					$repository = $userbound_dir_prefix.$wpfib_userdata['user_id'].$userbound_dir_suffix;
					break;
			case USERBOUND_PARAMETER_LOGIN:
					$repository = $userbound_dir_prefix.$wpfib_userdata['user_login'].$userbound_dir_suffix;
					break;
			case USERBOUND_PARAMETER_NAME:
					$repository = $userbound_dir_prefix.$wpfib_userdata['user_name'].$userbound_dir_suffix;
					break;
			case USERBOUND_PARAMETER_ID_LOGIN:
					$repository = $userbound_dir_prefix."ID_".$wpfib_userdata['user_id'].".".$wpfib_userdata['user_login'].$userbound_dir_suffix;
					break;
			case USERBOUND_PARAMETER_ID_NAME:
					$repository = $userbound_dir_prefix."ID_".$wpfib_userdata['user_id'].".".$wpfib_userdata['user_name'].$userbound_dir_suffix;
					break;
			case USERBOUND_PARAMETER_ID_LOGIN_NAME:
					$repository = $userbound_dir_prefix."ID_".$wpfib_userdata['user_id'].".".$wpfib_userdata['user_login'].".".$wpfib_userdata['user_name'].$userbound_dir_suffix;
					break;
		}

		$wpfib_repotype = REPOTYPE_USERBOUND;
	}
	
	
	else
	{
		$repository = $repo_option;
		$wpfib_repotype = REPOTYPE_STANDARD;
	}


	// we now set the starting dir to be the actual initial folder of the repository (in absolute terms)
	if ($wpfib_repotype == REPOTYPE_UNSPECIFIED)
	{
		$repo_abspath = $default_abspath;
	}
	else
	{
		$repo_abspath = $default_abspath.DS.$repository;
	}

	// some debugging lines
	if ($wpfib_options['DEBUG_enabled'])
	{
		echo "<br />Default path (".($is_path_relative ? "RELATIVE" : "ABSOLUTE").") = [".$default_abspath."]<br />";
		echo "<br />Repo = [".(strlen($repository) ? $repository : "NOT FOUND")."]<br />";
		echo "<br />Repo absolute path = [".$repo_abspath."]<br /><hr />";
	}

	// sanity check on repo_abspath
	if(preg_match("/\.\.(.*)/", $repo_abspath))
	{
		$error_text  = "<div id='JS_MAIN_DIV'><div id='JS_ERROR_DIV'><table><tr>"
				."<td class='alertIcon'><img src=\"".$wpfib_imgdirNavigation."warning.png\"></td><td>"
				.__('Path to repository should not contain \'..\'')."</td>"
				."</tr></table></div><br /></div>";
		return false;
	}

	// attempt to create top level repository dir (if it does not exist)
	if (!file_exists($repo_abspath))
	{
		if (!($rc = @mkdir ($repo_abspath, $wpfib_default_dir_chmod, true)))	// we need to use recursive option TRUE
		{
			$error_text  = "<div id='JS_MAIN_DIV'><div id='JS_ERROR_DIV'><table><tr>"
					."<td class='alertIcon'><img src=\"".$wpfib_imgdirNavigation."warning.png\"></td><td>"
					.sprintf(__('Failed creating repository folder %s'), wpfib_string_encode($repo_abspath))."</td>"
					."</tr></table></div><br /></div>";
			return false;
		}
	}
	
	return true;
}


// this function will detect the current location and validate it against the repository path
function wpfib_get_current_location(&$curdir_relpath, $repo_abspath, &$error_text)
{
	global $wpfib_options;
	
	// get dir from GET
	if(isset($_GET["dir"]) && strlen($_GET["dir"])) 
	{
		$dir = wpfib_path_decode($_GET["dir"]);
	}
	
	// get dir from POST (typically from forms)
	if(isset($_POST["dir"]) && strlen($_POST["dir"])) 
	{
//		$dir = urldecode($_POST['dir']);
		$dir = wpfib_path_decode($_POST['dir']);
	}	
	
	// validate against illegal formats and set curdir_relpath: this is the path of the current location, relative to the repository path
	$dir = trim($dir, "/\\");
	if(preg_match("/\.\.(.*)/", $dir)) 
	{
		$curdir_relpath = "";
	}
	else
	{
		$curdir_relpath = $dir;
	}
	
	return true;
}

function wpfib_action_downloadfile($repo_abspath, $curdir_relpath, &$returned_text, &$text_type)
{
	$dir = $repo_abspath.DS.$curdir_relpath;
	$download_file = wpfib_path_decode($_GET['download_file']);
	$download_file_abspath = $dir.DS.$download_file;

	// check nonce
	check_admin_referer('wpfib-download_file_'.md5($download_file));

	// security check
	if (preg_match("/\.\.(.*)/", $download_file_abspath))
	{
		$returned_text = __('File name contains \'..\' - Cannot download it!');
		$text_type = ACTION_TEXT_TYPE_ERROR;
		return ACTION_NEEDS_NOT_RELOAD;
	}

	if (file_exists($download_file_abspath))
	{
		@ob_end_clean();
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header("Content-Disposition: attachment; filename=\"".$download_file."\"");
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($download_file_abspath));
		@ob_flush();
		@flush();

		// standard PHP function readfile() has documented problems with large files; readfile_chunked() is reported on php.net
		wpfib_readfile_chunked($download_file_abspath);

		
		die(); 	// stop execution of further script because we are only outputting the file
					// (see readfile() function comment by mark dated 17-Sep-2008 on php.net)
	}
	else
	{
		$returned_text = sprintf(__('Error downloading file %s'), wpfib_string_encode($download_file));
		$text_type = ACTION_TEXT_TYPE_ERROR;
	}
	
	return ACTION_NEEDS_RELOAD;
}

function wpfib_action_delfolder($repo_abspath, $curdir_relpath, &$returned_text, &$text_type)
{
	$dir = $repo_abspath.DS.$curdir_relpath;
	$delfolder = wpfib_path_decode($_GET['delfolder']);

	// check nonce
	check_admin_referer('wpfib-delete_folder_'.md5($delfolder));

	$delfolder_abspath = $dir.DS.$delfolder;

	// security check
	if (preg_match("/\.\.(.*)/", $delfolder_abspath))
	{
		$returned_text = __('Folder name contains \'..\' - Cannot delete it!');
		$text_type = ACTION_TEXT_TYPE_ERROR;
		return ACTION_NEEDS_NOT_RELOAD;
	}
	
	$rc = @rmdir ($delfolder_abspath);
					
	// Check whether directory is gone
	if(file_exists($delfolder_abspath))
	{
		$returned_text = sprintf(__('Error deleting folder %s'), $delfolder);  // NOTE: Here we need urldecode as delfolder is double encoded /ErikLtz
		$text_type = ACTION_TEXT_TYPE_ERROR;
	}
	else
	{
		$returned_text = __('Folder deleted successfully', 'wpfib');
		$text_type = ACTION_TEXT_TYPE_SUCCESS;
		
	}
	return ACTION_NEEDS_RELOAD;
}

function wpfib_action_delfile($repo_abspath, $curdir_relpath, &$returned_text, &$text_type)
{
	global $wpfib_options;
	
	$dir = $repo_abspath.DS.$curdir_relpath;
	$delfile = wpfib_path_decode($_GET['delfile']);
	
	// check nonce
	check_admin_referer('wpfib-delete_file_'.md5($delfile));

	$delfile_abspath = $dir.DS.$delfile;
	
	// security check
	if (preg_match("/\.\.(.*)/", $delfile_abspath))
	{
		$returned_text = __('File name contains \'..\' - Cannot delete it!');
		$text_type = ACTION_TEXT_TYPE_ERROR;
		return ACTION_NEEDS_NOT_RELOAD;
	}
	
	$rc = @unlink ($delfile_abspath);

	// try removing thumbnail and thumbs dir (will only work if a thumbnail for this file exists and if the thumbs dir is empty)
	$rc_thumbs = @unlink ($dir.DS."JS_THUMBS".DS.$delfile);
	$rc_thumbs = @rmdir($dir.DS."JS_THUMBS");

	// Check whether directory is gone
	if(file_exists($delfile_abspath))
	{
		$returned_text .= sprintf(__('Error deleting file %s'), $delfile);  // NOTE: Here we need urldecode as delfolder is double encoded /ErikLtz
		$text_type = ACTION_TEXT_TYPE_ERROR;
	}
	else
	{
		$returned_text = __('File deleted successfully', 'wpfib');
		$text_type = ACTION_TEXT_TYPE_SUCCESS;

		// Note : if this was the last file of an archive wpfib_display_repository() will remove the archive
		
	}
	return ACTION_NEEDS_RELOAD;
}

function wpfib_action_renfolder($repo_abspath, $curdir_relpath, &$returned_text, &$text_type)
{
	global $wpfib_options;
	
	$dir = $repo_abspath.DS.$curdir_relpath;

	$old_foldername = urldecode($_POST['old_foldername']);
	$new_foldername = urldecode($_POST['new_foldername']);
	
	// check nonce
	check_admin_referer('wpfib-rename_folder_'.$_POST['rowno']);
	
	$new_foldername = preg_replace('/\.\./', '.', $new_foldername);  // two dots become one
	$new_foldername = preg_replace('/[\\\]+/', '', $new_foldername);
	$new_foldername = preg_replace('/[\/]+/', '', $new_foldername);
	
	// using preg_replace above instead
//	$forbidden = array(".", "/", "\\");
//	for($i = 0; $i < count($forbidden); $i++)
//	{
//		$old_foldername = str_replace($forbidden[$i], "", $old_foldername);
//	}
//	for($i = 0; $i < count($forbidden); $i++)
//	{
//		$new_foldername = str_replace($forbidden[$i], "", $new_foldername);
//	}
	
	if(!@rename($dir."/".$old_foldername, $dir."/".$new_foldername))
	{
		$returned_text = sprintf(__('Failed renaming folder %s to %s', 'wpfib'), wpfib_string_encode($old_foldername), wpfib_string_encode($new_foldername));
		$text_type = ACTION_TEXT_TYPE_ERROR;
	}
	
	return ACTION_NEEDS_RELOAD;
}

function wpfib_action_renfile($repo_abspath, $curdir_relpath, &$returned_text, &$text_type)
{
	global $wpfib_options;
	
	$dir = $repo_abspath.DS.$curdir_relpath;

	$old_filename = urldecode($_POST['old_filename']);
	$new_filename = urldecode($_POST['new_filename']);

	// check nonce
	check_admin_referer('wpfib-rename_file_'.$_POST['rowno']);
	
	$new_filename = preg_replace('/\.\./', '\.', $new_filename);  // two dots become one
	$new_filename = preg_replace('/[\\\]+/', '', $new_filename);
	$new_filename = preg_replace('/[\/]+/', '', $new_filename);
	
	// using preg_replace above instead
//	$forbidden = array("/", "\\");
//	for($i = 0; $i < count($forbidden); $i++)
//	{
//		$old_filename = str_replace($forbidden[$i], "", $old_filename);
//	}
//	for($i = 0; $i < count($forbidden); $i++)
//	{
//		$new_filename = str_replace($forbidden[$i], "", $new_filename);
//	}

	if(!@rename($dir."/".$old_filename, $dir."/".$new_filename))
	{
		$returned_text = sprintf(__('Failed renaming file %s', 'wpfib'), wpfib_string_encode($old_filename));
		$text_type = ACTION_TEXT_TYPE_ERROR;
	}
	

	// try removing thumbnail of oldname file (will only work if a thumbnail for this file exists)
	$rc_thumbs = @unlink ($dir.DS."JS_THUMBS".DS.$old_filename);
	
	return ACTION_NEEDS_RELOAD;
}

function wpfib_action_newfolder($repo_abspath, $curdir_relpath, &$returned_text, &$text_type)
{
	global $wpfib_default_dir_chmod;
	
	$dir = $repo_abspath.DS.$curdir_relpath;

	// check nonce
	check_admin_referer('wpfib-create_folder_'.md5($dir));
	
	$forbidden = array(".", "/", "\\");
	for($i = 0; $i < count($forbidden); $i++)
	{
		$_POST['userdir'] = str_replace($forbidden[$i], "", $_POST['userdir']);
	}
	$tmpdir = $dir.DS.urldecode(wpfib_string_encode($_POST['userdir']));
	
	if(!@mkdir($tmpdir))
	{
		// Check for existing file with same name and choose different error message [ErikLtz]
		if(file_exists($tmpdir))
		{
			$returned_text = __('A folder with the same name already exists', 'wpfib');
		}
		else
		{
			$returned_text = __('Error creating new folder', 'wpfib');
		}
		$text_type = ACTION_TEXT_TYPE_ERROR;
	}
	else if(!@chmod($tmpdir, $wpfib_default_dir_chmod))
	{
		$returned_text = __('Error setting permissions on new folder', 'wpfib');
		$text_type = ACTION_TEXT_TYPE_ERROR;
	}
	else
	{
		$returned_text = __('New folder created successfully', 'wpfib');
		$text_type = ACTION_TEXT_TYPE_SUCCESS;
	}

	return ACTION_NEEDS_RELOAD;
}
			
// HTML upload : manage conflict
function wpfib_action_html_upload_manage_conflict($repo_abspath, $curdir_relpath, &$returned_text, &$text_type)
{
	global $wpfib_access_rights;
	global $wpfib_options;
	global $wpfib_default_file_chmod;

	$dir = $repo_abspath.DS.$curdir_relpath;
	
	// check nonce
	if (isset($_GET['keep_existing_file']))
		check_admin_referer('wpfib-keep_file_'.md5($dir));
	else if (isset($_GET['override_file']))
		check_admin_referer('wpfib-over_file_'.md5($dir));
	else if (isset($_GET['archive_file']))
		check_admin_referer('wpfib-arch_file_'.md5($dir));
	
	// moving the uploaded file (HTML upload)

	if($wpfib_access_rights[CAN_UPLOAD] && isset($_GET['keep_existing_file']) && isset($_GET['tmpfiletoupload']))
	{
		// unlink WAITING file
		@unlink(wpfib_path_decode($_GET['tmpfiletoupload'])."_WAITING");
	}
	else if($wpfib_access_rights[CAN_UPLOAD] && (isset($_GET['override_file']) || isset($_GET['archive_file'])) && isset($_GET['tmpfiletoupload']) && isset($_GET['filetoupload']))
	{
		$upload_file = wpfib_fileBaseName(wpfib_path_decode($_GET['filetoupload']));
		$upload_dir = $repo_abspath.DS.$curdir_relpath;
		$upload_file_abspath = $upload_dir.DS.$upload_file;

		if ($_GET['override_file'] == 1)
		{
			// copy WAITING file onto existing one (will then unlink WAITING tmp file)
			if(!@copy(wpfib_path_decode($_GET['tmpfiletoupload'])."_WAITING", $upload_file_abspath))
			{
				$returned_text = __('HTML upload failed move of newly uploaded file');
				$text_type = ACTION_TEXT_TYPE_ERROR;
			}
			else
			{
				@chmod($upload_file_abspath, $wpfib_default_file_chmod);

				$returned_text = __('File overriden successfully');
				$text_type = ACTION_TEXT_TYPE_SUCCESS;
			}
		}
		else if ($wpfib_options['allow_file_archiving'] && $_GET['archive_file'] == 1)
		{
			if (!is_dir($upload_dir.DS."JS_ARCHIVE") && !($rc = @mkdir ($upload_dir.DS."JS_ARCHIVE")))
			{
				$returned_text = __('Failed creating archive dir');
				$text_type = ACTION_TEXT_TYPE_ERROR;
				return ACTION_NEEDS_RELOAD;
			}

			if (strpos($upload_file, '.') === false)
			{
				$archive_file = $upload_dir.DS."JS_ARCHIVE".DS.$upload_file." (".__('Archived on')." ".date("Y-m-d H.i.s").")";
			}
			else
			{
				$archive_file = wpfib_fileWithoutExtension($upload_dir.DS."JS_ARCHIVE".DS.$upload_file)." (".__('Archived on')." ".date("Y-m-d H.i.s").").".wpfib_fileExtension($upload_file);
			}

			// copy current file into archive folder
			if(!@copy($upload_file_abspath, $archive_file))
			{
				$returned_text = __('Failed moving existing file to archive folder');
				$text_type = ACTION_TEXT_TYPE_ERROR;
				return ACTION_NEEDS_RELOAD;
			}
	       	else
			{
				// copy WAITING file onto existing one (will then unlink WAITING tmp file)
				if(!@copy(wpfib_path_decode($_GET['tmpfiletoupload'])."_WAITING", $upload_file_abspath))
				{
					$returned_text = sprintf(__('HTML upload failed move of newly uploaded file %s', 'wpfib'), $_GET['tmpfiletoupload']);
					$text_type = ACTION_TEXT_TYPE_ERROR;
				}
				else
				{
					@chmod($upload_file_abspath, $wpfib_default_file_chmod);

					$returned_text = __('Old file successfully archived before upload');
					$text_type = ACTION_TEXT_TYPE_SUCCESS;
				}
			}
		}
			
		// unlink WAITING file
		@unlink(wpfib_path_decode($_GET['tmpfiletoupload'])."_WAITING");
	}
	else
	{
		$returned_text = __('Unexpected error');
		$text_type = ACTION_TEXT_TYPE_ERROR;
	}
		
	return ACTION_NEEDS_RELOAD;
}

// HTML upload : receive file
function wpfib_action_html_upload_main_handler($repo_abspath, $curdir_relpath, &$returned_text, &$text_type)
{
	global $wpfib_access_rights;
	global $wpfib_options;
	global $wpfib_baselink;
	global $wpfib_default_file_chmod;
	
	$uploaded_tmp_file_abspath = urldecode($_FILES['userfile']['tmp_name']);
	$upload_file = wpfib_fileBaseName(urldecode(wpfib_string_encode(stripslashes($_FILES['userfile']['name']))));
	$upload_file_abspath = $repo_abspath.DS.$curdir_relpath.DS.$upload_file;
	
	$dir = $repo_abspath.DS.$curdir_relpath;

	
	// check nonce
	check_admin_referer('wpfib-upload_file_'.md5($dir));
	
	if (!$wpfib_access_rights[CAN_UPLOAD])
	{
		$returned_text = __('You are not authorized to use this function');
		$text_type = ACTION_TEXT_TYPE_ERROR;
		return ACTION_NEEDS_RELOAD;
	}
	
	if(!is_uploaded_file($uploaded_tmp_file_abspath))
	{
		$returned_text = __('HTML upload failed');
		$text_type = ACTION_TEXT_TYPE_ERROR;
		return ACTION_NEEDS_RELOAD;
	}
	else if(file_exists($upload_file_abspath))    // Check to avoid overwriting existing file /ErikLtz
	{
		// create nonced links
		$keep_file_href = $wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath)."&keep_existing_file=1&tmpfiletoupload=".wpfib_path_encode($uploaded_tmp_file_abspath);
		$over_file_href = $wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath)."&override_file=1&filetoupload=".wpfib_path_encode($upload_file)."&tmpfiletoupload=".wpfib_path_encode($uploaded_tmp_file_abspath);
		$arch_file_href = $wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath)."&archive_file=1&filetoupload=".wpfib_path_encode($upload_file)."&tmpfiletoupload=".wpfib_path_encode($uploaded_tmp_file_abspath);

		$keep_file_href_nonced = wp_nonce_url($keep_file_href, 'wpfib-keep_file_'.md5($dir));
		$over_file_href_nonced = wp_nonce_url($over_file_href, 'wpfib-over_file_'.md5($dir));
		$arch_file_href_nonced = wp_nonce_url($arch_file_href, 'wpfib-arch_file_'.md5($dir));

		$keep_link = "<a href=\"".$keep_file_href_nonced."\">";
		$over_link = "<a href=\"".$over_file_href_nonced."\">";
		$arch_link = "<a href=\"".$arch_file_href_nonced."\">";

		if ($wpfib_options['allow_file_archiving'])
		{
			$returned_text .= sprintf(__('Detected existing file; please choose if you want to<ul><li>%scancel upload</a></li><li>%soverride existing file</a></li><li>%sarchive existing file first</a></li></ul>'),
				$keep_link, $over_link, $arch_link);
		}
		else
		{
			$returned_text .= sprintf(__('Detected existing file; please choose if you want to<ul><li>%scancel upload</a></li><li>%soverride existing file</a></li></ul>'),
				$keep_link, $over_link);
		}

		// rename tmp uploaded file to WAITING one
		move_uploaded_file($uploaded_tmp_file_abspath, $uploaded_tmp_file_abspath."_WAITING");
		
		$text_type = ACTION_TEXT_TYPE_WARNING;
		return ACTION_NEEDS_NOT_RELOAD;
	}
	else if(!move_uploaded_file($uploaded_tmp_file_abspath, $upload_file_abspath))
	{
		$returned_text = __('HTML upload failed move of newly uploaded file');
		$text_type = ACTION_TEXT_TYPE_ERROR;
		return ACTION_NEEDS_RELOAD;
	}
	else
	{
		@chmod($upload_file_abspath, $wpfib_default_file_chmod);
		$returned_text = __('File uploaded successfully');
		$text_type = ACTION_TEXT_TYPE_SUCCESS;
	}
			
	return ACTION_NEEDS_RELOAD;
}

function wpfib_action_restorefile($repo_abspath, $curdir_relpath, &$returned_text, &$text_type)
{
	global $wpfib_options;
	global $wpfib_default_file_chmod;
	
	$dir = $repo_abspath.DS.$curdir_relpath;
	$restorefile = wpfib_path_decode($_GET['restorefile']);
	
	// check nonce
	check_admin_referer('wpfib-restore_file_'.md5($restorefile));

	if(!@copy($dir.DS.$restorefile, wpfib_upperDirSetForwardSlashes($dir).DS.wpfib_tools_restoreArchiveFilename($restorefile)))
	{
		$returned_text .= sprintf(__('Failed restoring file %s', 'wpfib'), wpfib_string_encode($restorefile));
		$text_type = ACTION_TEXT_TYPE_ERROR;
	}
	else
	{
		$returned_text = __('File successfully unarchived');
		$text_type = ACTION_TEXT_TYPE_SUCCESS;
		@chmod(wpfib_upperDirSetForwardSlashes($dir).DS.$restorefile, $wpfib_default_file_chmod);

	}
	
	return ACTION_NEEDS_RELOAD;
}

function wpfib_action_unzipfile($repo_abspath, $curdir_relpath, &$returned_text, &$text_type)
{
	global $wpfib_options;
	global $wpfib_default_file_chmod;
	
	$dir = $repo_abspath.DS.$curdir_relpath;
	$unzipfile = wpfib_path_decode($_GET['unzipfile']);
	
	// check nonce
	check_admin_referer('wpfib-unzip_file_'.md5($unzipfile));

	$zip = new ZipArchive;
	if ($zip->open($dir.DS.$unzipfile) === TRUE)
	{
		$zip->extractTo($dir);
		$zip->close();
		$returned_text = __('File successfully extracted');
		$text_type = ACTION_TEXT_TYPE_SUCCESS;
	}
	else
	{
		$returned_text .= sprintf(__('Failed extracting file %s', 'wpfib'), wpfib_string_encode($unzipfile));
		$text_type = ACTION_TEXT_TYPE_ERROR;
	}
	
	return ACTION_NEEDS_RELOAD;
}

function wpfib_actions_handler(&$text, $repo_abspath, $curdir_relpath)
{
	global $wpfib_access_rights;
	global $wpfib_baselink;
	global $wpfib_imgdirNavigation;
	global $wpfib_options;

	$returned_value = ACTION_NEEDS_NOT_RELOAD;
	$text_type = ACTION_TEXT_TYPE_NONE;
	$returned_text = "";
	
	// IF ACTION REQUEST DETECTED

	// managing file download
	if($wpfib_access_rights[CAN_DOWNLOAD] && isset($_GET['download_file']) && strlen($_GET['download_file']))
	{
		$returned_value = wpfib_action_downloadfile($repo_abspath, $curdir_relpath, $returned_text, $text_type);
	}

	// deleting a folder
	else if ($wpfib_access_rights[CAN_DELETE_FOLDERS] && isset($_GET["delfolder"]) && strlen($_GET["delfolder"]))
	{
		$returned_value = wpfib_action_delfolder($repo_abspath, $curdir_relpath, $returned_text, $text_type);
	}
	
	// deleting a file
	else if ($wpfib_access_rights[CAN_DELETE_FILES] && isset($_GET["delfile"]) && strlen($_GET["delfile"]))
	{
		$returned_value = wpfib_action_delfile($repo_abspath, $curdir_relpath, $returned_text, $text_type);
	}

	// changing name to a folder
	else if($wpfib_access_rights[CAN_RENAME_FOLDERS] && isset($_POST['old_foldername']) && strlen($_POST['old_foldername']) > 0 &&
       			 isset($_POST['new_foldername']) && strlen($_POST['new_foldername']) > 0)
	{
		$returned_value = wpfib_action_renfolder($repo_abspath, $curdir_relpath, $returned_text, $text_type);
	}
	
	// changing name to a file
	else if($wpfib_access_rights[CAN_RENAME_FILES] && isset($_POST['old_filename']) && strlen($_POST['old_filename']) > 0 &&
		       	isset($_POST['new_filename']) && strlen($_POST['new_filename']) > 0)
	{
		$returned_value = wpfib_action_renfile($repo_abspath, $curdir_relpath, $returned_text, $text_type);
	}
	
	// creating a new directory
	else if($wpfib_access_rights[CAN_CREATE_FOLDERS] && isset($_POST['userdir']) && strlen($_POST['userdir']) > 0)
	{
		$returned_value = wpfib_action_newfolder($repo_abspath, $curdir_relpath, $returned_text, $text_type);
	}

	// HTML upload : main handler
	else if($wpfib_access_rights[CAN_UPLOAD] && isset($_FILES['userfile']['name']) && strlen($_FILES['userfile']['name']) > 0)
	{
		$returned_value = wpfib_action_html_upload_main_handler($repo_abspath, $curdir_relpath, $returned_text, $text_type);
	}
	
	// HTML upload : manage conflict in case of upload of a file with the same name of an existing file
	else if($wpfib_access_rights[CAN_UPLOAD] && (isset($_GET['keep_existing_file']) || isset($_GET['override_file']) || isset($_GET['archive_file'])))
	{
		$returned_value = wpfib_action_html_upload_manage_conflict($repo_abspath, $curdir_relpath, $returned_text, $text_type);
	}
	
	// if asking to restore an archived file
	else if ($wpfib_access_rights[CAN_RESTORE_FILES] && isset($_GET["restorefile"]) && strlen($_GET["restorefile"]))
	{
		$returned_value = wpfib_action_restorefile($repo_abspath, $curdir_relpath, $returned_text, $text_type);
	}
	
	// if asking to extract a file
	else if ($wpfib_options['allow_unzip'] && $wpfib_access_rights[CAN_UNZIP_FILES] && isset($_GET["unzipfile"]) && strlen($_GET["unzipfile"]))
	{
		$returned_value = wpfib_action_unzipfile($repo_abspath, $curdir_relpath, $returned_text, $text_type);
	}
	
	// IF NO ACTION REQUEST DETECTED
	else
	{
		// if reloaded after performed action
		if ($_GET['wpfib_art'] && strlen($_GET['wpfib_art']))
		{
			$text .= wpfib_path_decode($_GET['wpfib_art']);
			return;
		}
		else
		{
			return;
		}
	}
	
	// IF HERE then process the outcome of the performed action

	$returned_text_div = "";
	switch ($text_type)
	{
		case ACTION_TEXT_TYPE_SUCCESS:
			$returned_text_div .= "<div id='JS_MAIN_DIV'><div id='JS_SUCCESS_DIV'><table><tr>"
					."<td class='alertIcon'><img src=\"".$wpfib_imgdirNavigation."success.png\" onload='makeSuccessBoxDisappear();'></td>"
					."<td>".$returned_text."</td>"
					."</tr></table></div></div>";
			break;
		case ACTION_TEXT_TYPE_WARNING:
			$returned_text_div .= "<div id='JS_MAIN_DIV'><div id='JS_ERROR_DIV'><table><tr>"
					."<td class='alertIcon'><img src=\"".$wpfib_imgdirNavigation."warning.png\"></td>"
					."<td>".$returned_text."</td>"
					."</tr></table></div></div>";
			break;
		case ACTION_TEXT_TYPE_ERROR:
			$returned_text_div .= "<div id='JS_MAIN_DIV'><div id='JS_ERROR_DIV'><table><tr>"
					."<td class='alertIcon'><img src=\"".$wpfib_imgdirNavigation."warning.png\"></td>"
					."<td>".$returned_text."</td>"
					."<td class='alertIcon'><img src=\"".$wpfib_imgdirNavigation."delete.png\" onclick='makeErrorBoxDisappear();'></td>"
					."</tr></table></div></div>";
			break;
		default:
			break;
	}
	
	switch ($returned_value)
	{
		case ACTION_NEEDS_NOT_RELOAD:
			$text .= $returned_text_div;
			$returned_text_div = "";
			break;
		case ACTION_NEEDS_RELOAD:
			if ($text_type == ACTION_TEXT_TYPE_NONE)
			{
				wp_redirect($wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath));
			}
			else
			{
				wp_redirect($wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath)
										   ."&wpfib_art=".wpfib_path_encode($returned_text_div)); // Action-Returned Text
				//add_filter('wp_redirect', 'wpfib_remove_art_get_var', 10, 2);
			}
			break;
	}
	
	return;
}

function wpfib_set_hidden_elements()
{
	global $wpfib_options;
	
	global $wpfib_hidden_folders;
	global $wpfib_hidden_files;
	global $wpfib_hidden_prefixes;
	global $wpfib_hidden_extensions;
	
	// set the array of folders that will be hidden from the list.
	$hidden_folders_parameter = $wpfib_options['hidden_folders'];
	$wpfib_hidden_folders = array();
//	$wpfib_hidden_folders = preg_split("/\s*,+\s*/", $hidden_folders_parameter.", JS_ARCHIVE, JS_THUMBS");
	$wpfib_hidden_folders = preg_split("/[\s,]+/", $hidden_folders_parameter.", JS_ARCHIVE, JS_THUMBS");

	// manage filenames and extensions that will be hidden from the list.
	$hidden_files_parameter = $wpfib_options['hidden_files'];
		
	$wpfib_hidden_extensions = array();
	//$hidden_extensions_found = preg_match_all("/\*{1}\.{1}\w+/", $hidden_files_parameter, $wpfib_hidden_extensions);	// this matches *.php but not  *.th.jpg
	$hidden_extensions_found = preg_match_all("/\*{1}\.{1}[\w\.]+/", $hidden_files_parameter, $wpfib_hidden_extensions);	// this matches *.php but also *.th.jpg

	$wpfib_hidden_prefixes = array();
	$hidden_prefixes_found = preg_match_all("/[^\s]+\*{1}/", $hidden_files_parameter, $wpfib_hidden_prefixes);

	$wpfib_hidden_files = array();
	$hidden_files_string = trim(preg_replace("/\*{1}\.{1}\w+/", "", $hidden_files_parameter));
	$hidden_files_string = trim(preg_replace("/[^s]+\*{1}/", "", $hidden_files_string));
//	$wpfib_hidden_files = preg_split("/\s*,+\s*/", $hidden_files_string);
	$wpfib_hidden_files = preg_split("/[\s,]+/", $hidden_files_string);
}

function wpfib_get_dirs_and_files($repo_abspath, $curdir_relpath, &$dirs, &$files, &$error_text)
{
	global $wpfib_options;
	
	global $wpfib_hidden_folders;
	global $wpfib_hidden_files;
	global $wpfib_hidden_prefixes;
	global $wpfib_hidden_extensions;
	
	global $wpfib_imgdirNavigation;

	// we are rendering the repository for the following dir
	$dir = $repo_abspath.DS.$curdir_relpath;
	
	// for file filtering
	$file_filter_pattern_required = (isset($_COOKIE['current_filter_list']) && strlen($_COOKIE['current_filter_list'])) ? true : false;

//	print_r($wpfib_hidden_files);
//	print_r($wpfib_hidden_folders);
//	if($open_dir = @opendir(html_entity_decode(str_replace("\\", "/", $dir."/"))))
//	if($open_dir = @opendir(html_entity_decode($dir, ENT_QUOTES, $wpfib_options['default_string_encoding'])))
	if($open_dir = @opendir($dir))
	{
		$i = 0;
		while ($it = @readdir($open_dir)) 
		{
			if($it != "." && $it != "..")
			{
				if(is_dir($dir.DS.$it))
				{
					if(!in_array($it, $wpfib_hidden_folders))
					{
						$dirs[] = wpfib_string_encode($it);
					}
				}
				//else if(!in_array($it, $wpfib_hidden_files) && !in_array("*.".$this->wpfib_fileExtension($it), $wpfib_hidden_extensions[0]))
				else if(!in_array($it, $wpfib_hidden_files))
				{
					$matched_prefix = 0;
					for ($k = 0; $k < count($wpfib_hidden_prefixes[0]); $k++)
					{
							if (!strncasecmp($wpfib_hidden_prefixes[0][$k], $it, strlen($wpfib_hidden_prefixes[0][$k]) - 1))
								$matched_prefix = 1;
					}

					$matched_extension = 0;
					for ($k = 0; $k < count($wpfib_hidden_extensions[0]); $k++)
					{
						if (!strncasecmp(strrev($wpfib_hidden_extensions[0][$k]), strrev($it), strlen($wpfib_hidden_extensions[0][$k]) - 1))
							$matched_extension = 1;
					}

					// file list filtering
					$file_filter_pattern_matched = false;
					if ($file_filter_pattern_required)
					{
//						$pattern_array = explode(";", chosen_decoding($_COOKIE['current_filter_list']));
						$pattern_array = explode(";", $_COOKIE['current_filter_list']);
						for ($k = 0; $k < count($pattern_array); $k++)
						{
							if (stristr($it, trim($pattern_array[$k])))
							{
								$file_filter_pattern_matched = true;
							}
						}
					}
					if (!$matched_prefix && !$matched_extension && (!$file_filter_pattern_required || $file_filter_pattern_matched))
					{
//						$files[$i]["name"]	= htmlspecialchars($it);
						$files[$i]["name"]	= wpfib_string_encode($it);
						$it	= $dir."/".$it;
						$files[$i]["extension"]	= wpfib_fileExtension($it);
						$files[$i]["size"]	= wpfib_fileRealSize($it);
						$files[$i]["changed"]	= filemtime($it);
						$i++;
					}
				}
			}
		}
		@closedir($open_dir);
	}
	else
	{
		$error_text = "<div id='JS_MAIN_DIV'><div id='JS_ERROR_DIV'><table><tr>"
				."<td class='alertIcon'><img src=\"".$wpfib_imgdirNavigation."warning.png\"></td><td>"
				.sprintf(__('Folder %s not found'), wpfib_string_encode($curdir_relpath))."</td>"
				."</tr></table></div><br /></div>";
		return false;
	}

	// sort files and folders
	if (isset($_GET["sort_as"]) && isset($_GET["sort_by"]))
	{
		wpfib_sort_files_and_dirs($files, $dirs, $_GET["sort_as"], $_GET["sort_by"]);
	}
	else
	{
		wpfib_sort_files_and_dirs($files, $dirs);
	}
	
	return true;
}

function wpfib_render_filter_row($curdir_relpath, &$dirs, &$files)
{
	global $wpfib_baselink;
	global $wpfib_options;
	global $wpfib_imgdirNavigation;
	
	$file_filter_pattern_required = (isset($_COOKIE['current_filter_list']) && strlen($_COOKIE['current_filter_list'])) ? true : false;

	// set the row for file filtering (above file list)
	if ($wpfib_options['display_file_filter'] && (count($dirs) || count($files)))
	{
		$clear_current_filter_list_icon = isset($_COOKIE['current_filter_list']) && strlen($_COOKIE['current_filter_list']) ?
		       	"<a href='".$wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath)."&current_filter_list='><img src='".$wpfib_imgdirNavigation."delete.png' title='".__('Clear current filter list')."'></a>" : "";

		$filter_list_tr = $file_filter_pattern_required ? "<tr class='separatingLine'><td colspan='7'></td></tr>" : "";
		$filter_list_tr .= "<tr ".($file_filter_pattern_required ? "class='row highlighted' style='border-style:solid'" : "").">"
			."<form action='' method='post'>"
			."<td colspan='7'>"
			."<table class='filterTable'>"
			."<tr>"
				."<td title=\"".__('Set filter list')."\">".__('Set filter list')."&nbsp;&nbsp;</td>"
				."<td width='".($wpfib_options['file_filter_width'] + 30 + (strlen($clear_current_filter_list_icon) ? 30 : 0))."' align='right'>"
				."<input class='long_input_field' name='current_filter_list' type='text' value=\"".(isset($_COOKIE['current_filter_list']) ? $_COOKIE['current_filter_list'] : "")."\" />"
				."</td>"
				."<td class='filterIconTick'>"
				."<input type='image' src=\"".$wpfib_imgdirNavigation."tick.png\" title=\"".__('Filter')."\" />"
				."<input type='hidden' name='dir' value=\"".wpfib_path_encode($curdir_relpath)."\">"
				."</td>"
				."<td class='filterIconDelete'>".$clear_current_filter_list_icon."</td>"
			."</tr>"
			."</table>"
			."</td>"
			."</form>"
			."</tr>";
		$filter_list_tr .= $file_filter_pattern_required ? "<tr class='separatingLine'><td colspan='7'></td></tr>"
				."<tr><td colspan='7'><img src=\"".$wpfib_imgdirNavigation."null.gif\" height=20 /></td></tr>" : "";
	}
	else if ($wpfib_options['display_file_filter'] && (!count($dirs) && !count($files)) && $file_filter_pattern_required)
	{
		// needed when deleting all files inside a filter list (issue raised by Daniel Campos)
		$clear_current_filter_list_text = "<a href='".$wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath)."&current_filter_list='>".__('Clear current filter list')."</a>";
		$filter_list_tr = "<tr class='separatingLine'><td colspan='19'></td></tr>"
				 ."<tr height='30' valign='middle'><td colspan='19' align='center'>".$clear_current_filter_list_text."</td></tr>"
				 ."<tr class='separatingLine'><td colspan='19'></td></tr>";
	}
	else
	{
		$filter_list_tr = "";
	}
	
	return $filter_list_tr;
}

// the top div hosts the navigation bar and
function wpfib_render_top_div(&$text, $repo_abspath, $curdir_relpath)
{
	global $wpfib_options;
	global $wpfib_baselink;
	global $wpfib_imgdirNavigation;

	// we are rendering the repository for the following dir
	$dir = $repo_abspath.DS.$curdir_relpath;
	
	// set the navigation links to put on top of the repository
	if (!strlen($curdir_relpath))
	{
		$current_position_links = "<a href='".$wpfib_baselink."'>".__('top level folder')."</a>";
	}
	else
	{
		$is_archive = wpfib_is_archive($curdir_relpath);
		
		if ($is_archive)
		{
			$tmpdir = wpfib_upperDirSetForwardSlashes($curdir_relpath);
			if (strlen($tmpdir))
			{
				$arr = explode("/", wpfib_makeForwardSlashes($tmpdir));
				$current_position_links = "<a href='".$wpfib_baselink."&dir=".wpfib_path_encode($tmpdir)."'>".wpfib_string_encode($arr[(count($arr) - 1)])."</a>";
			}
			else
			{
				$current_position_links = "<a href='".$wpfib_baselink."'>".__('top level folder')."</a>";
			}
		}
		else
		{
			// Use current_position to build linked list of directories in $current_position_links [ErikLtz]
			$tmpdir = $curdir_relpath;
			$arr = explode("/", wpfib_makeForwardSlashes($tmpdir));
			$current_position_links = "";

			for($i = count($arr) - 1; $i >= 0; $i--)
			{
				$current_position_links = "<a href='".$wpfib_baselink."&dir=".wpfib_path_encode($tmpdir)."'>".wpfib_string_encode($arr[$i])."</a>"
						.($i == count($arr) - 1 ? "" : "&nbsp;<img src=\"".$wpfib_imgdirNavigation."arrow_right.png\" />&nbsp;").$current_position_links;
			
			  	$tmpdir = wpfib_upperDirSetForwardSlashes($tmpdir);
			}
			
			$current_position_links = "<a href='".$wpfib_baselink."'>".__('top level folder')."</a>"
					."&nbsp;<img src=\"".$wpfib_imgdirNavigation."arrow_right.png\" />&nbsp;".$current_position_links;
		}
	}
	
    if ($wpfib_options['display_navigation'])
	{
		$browsing_text = (wpfib_is_archive($curdir_relpath) ? __('Archive folder for') : __('You are in'));
		$currentdirectory_td = "<td class='navigation'>".$browsing_text." ".$current_position_links."</td>";
	}
	else
	{
		$currentdirectory_td = "";
	}
		
    // START (and END) OF div TAG with id JS_TOP_DIV
                
	$text .= "<div id='JS_TOP_DIV'>"
			."<table>"
				."<tr valign='center'>"
					."<td colspan='2'>&nbsp;</td>"
				."</tr>"
				."<tr valign='top'>"
					.$currentdirectory_td
					."<td class='topLinks'>".$links_string."</td>"
				."</tr>"
			."</table>"
//			.($enable_usergroup_switch_links ? str_replace("TMP_BASELINK", $this->baselink, $usergroup_switch_links) : "")
			."</div>";

//	if ($enable_usergroup_switch_links)
//	{
//		$text .= "<div id='jsmallspacer'><img src=\"".$this->imgdirNavigation."null.gif\" /></div>";
//	}
	return true;
}

function wpfib_render_files_div(&$text, $repo_abspath, $curdir_relpath, &$dirs, &$files)
{
	global $wpfib_access_rights;

	global $wpfib_options;
	global $wpfib_baselink;
	global $wpfib_imgdirNavigation;
	
	// we are rendering the repository for the following dir
	$dir = $repo_abspath.DS.$curdir_relpath;
	
	// detects if we are inside the web root and determine the relative dir
	$dir_relpath = "";
	$base_url = "";
	$is_dir_in_webroot = wpfib_is_in_webroot($dir, $dir_relpath, $base_url);
	if ($wpfib_options['DEBUG_enabled'])
	{
		echo "DIR ABSPATH = ".$dir."<br />";
		echo "DIR RELPATH = ".$dir_relpath."<br />";
	}
	
	$text .= "<div id='JS_FILES_DIV'>";
	
		
	// start files/folders table with filter row and header row
	$rowColspan = 7;

	$text .= "<table>"
		.wpfib_render_filter_row($curdir_relpath, $dirs, $files)
		."<tr class='row header'>";
                
	// CELL 1 (files icon | header row)
	if(strlen($curdir_relpath))
	{
		// if there is an upper dir 
		$text .= "<td class='fileIcon'>"
                ."<a href='".$wpfib_baselink."&dir=".wpfib_path_encode(wpfib_upperDir($curdir_relpath))."'><img title=\"".__('Go to previous folder', 'wpfib')."\" src=\"".$wpfib_imgdirNavigation."upperdir.png\" border='0' /></a>"
                ."</td>";
	}
	else
	{
		$text .= "<td class='emptyTd'></td>";
	}
		
	if (!count($dirs) && !count($files))
	{
		$text .= "<td colspan='$rowColspan'>".__('This repository is empty', 'wpfib')."</td>";
	}
	else
	{
		// CELL 2 (filename | header row)
           $text .= "<td class='fileName'>"
				.wpfib_makeArrow((isset($_GET["sort_by"]) ? $_GET["sort_by"] : ""), (isset($_GET["sort_as"]) ? $_GET["sort_as"] : ""), SORT_BY_NAME, $curdir_relpath, __('File name', 'wpfib'))
				."</td>";
                       
		// CELL 3 (filesize | header row)
		if ($wpfib_options['display_filesize'] && count($files))
		{
			$text .= "<td class='fileSize'>"
				.wpfib_makeArrow((isset($_GET["sort_by"]) ? $_GET["sort_by"] : ""), (isset($_GET["sort_as"]) ? $_GET["sort_as"] : ""), SORT_BY_SIZE, $curdir_relpath, __('File size', 'wpfib'))	
				."</td>";
		}
		else
		{
			$text .= "<td class='emptyTd'></td>";
		}

		// CELL 4 (filedate | header row)
		if ($wpfib_options['display_filedate'] && count($files))
		{
			$text .= "<td class='fileChanged'>"
					.wpfib_makeArrow((isset($_GET["sort_by"]) ? $_GET["sort_by"] : ""), (isset($_GET["sort_as"]) ? $_GET["sort_as"] : ""), SORT_BY_CHANGED, $curdir_relpath, __('Last changed', 'wpfib'))
					."</td>";
		}
		else
		{
			$text .= "<td class='emptyTd'></td>";
		}
                    
        // actions CELLS are set empty
        $text .= "<td colspan='3' class='emptyTd'></td>";
	}
	$text .= "</tr>";

	// Ready to display folders and files.
	$row = 1;

	// Folders first
	if ($dirs)
	{
		$rowno = 0; // we use this to tag rows for jQuery actions
		
		foreach ($dirs as $a_dir)
		{
			$row_style = ($row ? "odd" : "even");
				
			if ($wpfib_options['line_height'])
			{
				$text .= "<tr class='separatingLine'><td colspan='$rowColspan'></td></tr>";
			}

			if($wpfib_access_rights[CAN_RENAME_FOLDERS])
			{
				// script to swap standard and edit rows
				$text .= "<script type='text/javascript'>"
						."	function toggleEditDirRowID_$rowno() {"
						."		jQuery('#stdDirRowID_$rowno').toggle();"
						."		jQuery('#editDirRowID_$rowno').toggle();"
						."	}	"
						."</script>";

				// row to edit name
				$text .= "<tr id='editDirRowID_$rowno' class='row $row_style' style='display:none'>"
						."<form action='' method='post'>"
						."<td class='fileIcon'>"
							."<img src=\"".$wpfib_imgdirNavigation."folder.png\" width='".$wpfib_options['icon_width']."' />"
						."</td>"
						."<td class='fileName'>"
							."<input name='new_foldername' type='text' value=\"".wpfib_string_encode($a_dir)."\" />"
						."</td>"
						."<td colspan='3' class='emptyTd'></td>"
						."<td class='fileAction'>"
							."<input type='image' src=\"".$wpfib_imgdirNavigation."tick.png\" title=\"".__('Rename folder', 'wpfib')."\" />"
						."</td>"
						."<td class='fileAction'>"
							."<img onclick='toggleEditDirRowID_$rowno();' src=\"".$wpfib_imgdirNavigation."delete.png\" title='".__('Cancel')."' /></td>"
							.wp_nonce_field('wpfib-rename_folder_'.$rowno)
							."<input type='hidden' name='rowno' value='$rowno' />"
							."<input type='hidden' name='old_foldername' value=\"".wpfib_string_encode($a_dir)."\" />"
							."<input type='hidden' name='dir' value=\"".wpfib_path_encode($curdir_relpath)."\">"
						."</form>"
						."</tr>";
			}
			
			// write standard row
			$get_encoded_dir = wpfib_path_encode($curdir_relpath."/".$a_dir);
				
			$text .= "<tr id='stdDirRowID_$rowno' class='row $row_style' onmouseover='this.className=\"highlighted\"' onmouseout='this.className=\"row $row_style\"'>"
						."<td class='fileIcon'>"
							."<a href='".$wpfib_baselink."&dir=".$get_encoded_dir."'>"
							."<img src=\"".$wpfib_imgdirNavigation."folder.png\" width='".$wpfib_options['icon_width']."' /></a>"
						."</td>"
						."<td class='fileName'>"
							."<a href='".$wpfib_baselink."&dir=".$get_encoded_dir."'>".$a_dir."</a>"
						."</td>"
						."<td colspan='3' class='emptyTd'></td>";
				
			// rename icon (or not)
			if($wpfib_access_rights[CAN_RENAME_FOLDERS])
			{
				$text .= "<td class='fileAction'>"
						."<span>"
						."<img src=\"".$wpfib_imgdirNavigation."rename.png\" border='0' title=\"".sprintf(__('Rename folder %s', 'wpfib'), wpfib_string_encode($a_dir))."\"  onclick='toggleEditDirRowID_$rowno();' />"
						."</span>"
						."</td>";
			}
			else
			{
				$text .= "<td class='emptyTd'></td>";
			}
				
			// delete icon and hidden row (or not)
			if($wpfib_access_rights[CAN_DELETE_FOLDERS])
			{
				$text .= "<td class='fileAction'>"
						."<form>"
						."<img id='deleteButtonDirRowID_$rowno' src=\"".$wpfib_imgdirNavigation."delete.png\" border='0' title=\"".sprintf(__('Remove folder %s', 'wpfib'), wpfib_string_encode($a_dir))."\" onclick='slideDown_deleteConfirmDirRowID_$rowno();' />"
						."</form>"
						."</td>";

				// create delete confirmation row
				$delete_link_href = $wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath)
												   ."&delfolder=".wpfib_path_encode($a_dir);
				
				$delete_link_href_nonced = wp_nonce_url($delete_link_href, 'wpfib-delete_folder_'.md5($a_dir));
				$delete_link = "<a href='".$delete_link_href_nonced."'>".__('Delete folder?')."</a>";
				$delete_hidden_row = "<tr id='deleteConfirmDirRowID_$rowno' class='row $row_style' style='display:none;height:64px;background-color:#FFD' onmouseover='this.className=\"highlighted\"' onmouseout='this.className=\"row $row_style\"'>"
						."<td style='text-align:center' colspan='$rowColspan'>".$delete_link."</td>"
						."<script type='text/javascript'>"
						."	function slideDown_deleteConfirmDirRowID_$rowno() {"
						."		jQuery('#deleteButtonDirRowID_$rowno').hide(5, function(){"
						."		jQuery('#deleteConfirmDirRowID_$rowno').show(200).delay(4000).hide(200, function(){"
						."		jQuery('#deleteButtonDirRowID_$rowno').show();});});"
						."	}	"
						."</script>"
						."</tr>";
			}
			else
			{
				$text .= "<td class='emptyTd'></td>";
				$delete_hidden_row = "";
			}
			$text .= "</tr>";
			$text .= $delete_hidden_row;
				
			$rowno++;
			$row = 1 - $row;
		}
	}

	// Now the files
	if($files)
	{
		$rowno = 0; // we use this to tag rows for jQuery actions
		$supported_zip_extensions = array("BZ2", "BZIP2", "GZ", "GZIP", "TAR", "TBZ2", "TGZ", "ZIP");

		foreach ($files as $a_file)
		{
			$row_style = ($row ? "odd" : "even");

			if ($wpfib_options['line_height'])
			{
				$text .= "<tr class='separatingLine'><td colspan='$rowColspan'></td></tr>";
			}

			// wpfib_makeThumbnail will only make a new thumbnail if required, and will return one if the right thumbnail is available
//			if ($is_dir_in_webroot && wpfib_makeThumbnail($a_file["name"], $a_file["extension"], $dir, $wpfib_options['thumbsize'], $wpfib_options['thumbsize']))
			$is_image = wpfib_makeThumbnail($a_file["name"], $a_file["extension"], $dir, $wpfib_options['thumbsize'], $wpfib_options['thumbsize']);
			if ($is_dir_in_webroot && $is_image)
			{
				$file_icon_td_begin	= "<td class='fileThumb'>";
				$file_icon_image	= "<img class='imgThumb' src=\"".wpfib_string_encode($dir_relpath."/"."JS_THUMBS"."/".$a_file["name"])."\" border='0' />";
				$file_icon_td_end	= "</td>";
			}
			else
			{
				$file_icon_td_begin	= "<td class='fileIcon'>";
				$file_icon_image	= "<img src=\"".wpfib_fileIcon($a_file["extension"])."\" width='".$wpfib_options['icon_width']."' border='0' />";
				$file_icon_td_end	= "</td>";
			}

			if($wpfib_access_rights[CAN_RENAME_FILES])
			{
				// script to swap standard and edit rows
				$text .= "<script type='text/javascript'>"
						."	function toggleEditFileRowID_$rowno() {"
						."		jQuery('#stdFileRowID_$rowno').toggle();"
						."		jQuery('#editFileRowID_$rowno').toggle();"
						."	}	"
						."</script>";

				// row to edit name
				$text .= "<tr id='editFileRowID_$rowno' class='row $row_style' style='display:none'>"
						."<form action='' method='post'>"
						.$file_icon_td_begin.$file_icon_image.$file_icon_td_end
						."<td class='fileName'>"
							."<input name='new_filename' type='text' value=\"".wpfib_string_encode($a_file["name"])."\" />"
						."</td>"
						.($wpfib_options['display_filesize'] ? "<td class='fileSize'>".wpfib_fileSizeF($a_file["size"])."</td>" : "<td class='emptyTd'></td>")
						.($wpfib_options['display_filedate'] ? "<td class='fileChanged'>".wpfib_fileChanged($a_file["changed"])."</td>" : "<td class='emptyTd'></td>")
						."<td class='emptyTd'></td>"
						."<td class='fileAction'>"
							."<input type='image' src=\"".$wpfib_imgdirNavigation."tick.png\" title=\"".__('Rename file', 'wpfib')."\" />"
						."</td>"
						."<td class='fileAction'>"
							."<img onclick='toggleEditFileRowID_$rowno();' src=\"".$wpfib_imgdirNavigation."delete.png\" title='".__('Cancel')."' /></td>"
							.wp_nonce_field('wpfib-rename_file_'.$rowno)
							."<input type='hidden' name='rowno' value='$rowno' />"
							."<input type='hidden' name='old_filename' value=\"".wpfib_string_encode($a_file["name"])."\" />"
							."<input type='hidden' name='dir' value=\"".wpfib_path_encode($curdir_relpath)."\">"
						."</form>"
						."</tr>";
			}
			
			// define file link for standard row
			if(!$wpfib_access_rights[CAN_DOWNLOAD])
			{
				// no link to file for basic access rights
				$file_link = wpfib_string_encode($a_file["name"]);
				$file_link_a_tag_begin = "";
				$file_link_a_tag_end = "";
			}
			else
			{
				
				// now uses absolute path in download file (relative path returns false to file_exists() on certain unix configurations 
				// set the <a href...> tag for the file link (depends on the linking method, direct or through the open/download box)
				if ($is_dir_in_webroot && ($wpfib_options['type_of_link_to_files'] != LINK_THROUGH_SCRIPT))
				{
					// TODO we need to save forward slashes and possibly htmlentities
					$file_link_a_tag_begin = "<a href=\"".$dir_relpath.DS.$a_file["name"]."\" "
							.($wpfib_options['type_of_link_to_files'] == LINK_DIRECT_NEW_WINDOW ? "target='_blank'" : "").">";
				}
				else
				{
					$file_link_href = $wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath)."&download_file=".wpfib_path_encode($a_file["name"]);
					$file_link_href_nonced = wp_nonce_url($file_link_href, 'wpfib-download_file_'.md5($a_file["name"]));
					$file_link_a_tag_begin = "<a href='".$file_link_href_nonced."'>";
				}
				$file_link_a_tag_end = "</a>";

				// set file name to display
				$file_name = $wpfib_options['display_file_ext'] ? wpfib_string_encode($a_file["name"]) : wpfib_fileWithoutExtension(wpfib_string_encode($a_file["name"]));

				// display normal open/download link if either outside of an archive or inside, but with no right to restore a file
				if (!wpfib_is_archive($curdir_relpath) || !$wpfib_access_rights[CAN_RESTORE_FILES])
				{
					// normal link
					$file_link = $file_link_a_tag_begin.$file_name.$file_link_a_tag_end;
				}
				else
				{
					// link in case of an archived file
					$file_link = "<br />".$file_name
							    ."<br />"
								."<img src='".$wpfib_imgdirNavigation."arrow_right.png'>&nbsp;"
							    .$file_link_a_tag_begin.__('download or open file', 'wpfib').$file_link_a_tag_end;

					if ($wpfib_access_rights[CAN_RESTORE_FILES])
					{
						$file_link .= "<br />"
									 ."<a><span id='restoreButtonFileRowID_$rowno' onclick='slideDown_restoreConfirmFileRowID_$rowno();'>"
									 ."<img src='".$wpfib_imgdirNavigation."arrow_right.png'>&nbsp;"
										.__('restore archived file', 'wpfib')
									 ."</span></a>"
									 ."<br /><br />";
				
						// create restore confirmation row
						$restore_link_href = $wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath)
															."&restorefile=".wpfib_path_encode($a_file['name']);
				
						$restore_link_href_nonced = wp_nonce_url($restore_link_href, 'wpfib-restore_file_'.md5($a_file['name']));
						$restore_link = "<a href='".$restore_link_href_nonced."'>".__('Restore file to its original folder?')."</a>";
						$restore_hidden_row = "<tr id='restoreConfirmFileRowID_$rowno' class='row $row_style' style='display:none;height:64px;background-color:#FFD' onmouseover='this.className=\"highlighted\"' onmouseout='this.className=\"row $row_style\"'>"
								."<td style='text-align:center' colspan='$rowColspan'>".$restore_link."</td>"
								."<script type='text/javascript'>"
								."	function slideDown_restoreConfirmFileRowID_$rowno() {"
								."		jQuery('#restoreButtonFileRowID_$rowno').hide(5, function(){"
								."		jQuery('#restoreConfirmFileRowID_$rowno').show(200).delay(4000).hide(200, function(){"
								."		jQuery('#restoreButtonFileRowID_$rowno').show();});});"
								."	}	"
								."</script>"
								."</tr>";
					}
					else
					{
						$file_link .= "<br /><br />";
						$restore_hidden_row = "";
					}
				}
			}
			
			// write standard row
			$text .= "<tr id='stdFileRowID_$rowno' class='row $row_style' onmouseover='this.className=\"highlighted\"' onmouseout='this.className=\"row $row_style\"'>"
					.$file_icon_td_begin.$file_link_a_tag_begin.$file_icon_image.$file_link_a_tag_end.$file_icon_td_end
					."<td class='fileName'>"
						.$file_link
					."</td>"
					.($wpfib_options['display_filesize'] ? "<td class='fileSize'>".wpfib_fileSizeF($a_file["size"])."</td>" : "<td class='emptyTd'></td>")
					.($wpfib_options['display_filedate'] ? "<td class='fileChanged'>".wpfib_fileChanged($a_file["changed"])."</td>" : "<td class='emptyTd'></td>");

			// unzip icon (or not)
			if($wpfib_access_rights[CAN_UNZIP_FILES] && $wpfib_options['allow_unzip'] && !wpfib_is_archive($curdir_relpath) && in_array(strtoupper($a_file["extension"]), $supported_zip_extensions))
			{
				$text .= "<td class='fileAction'>"
						."<form>"
						."<img id='unzipButtonFileRowID_$rowno' src=\"".$wpfib_imgdirNavigation."unzip.png\" border='0' title=\"".sprintf(__('Unzip file %s', 'wpfib'), wpfib_string_encode($a_file["name"]))."\" onclick='slideDown_unzipConfirmFileRowID_$rowno();' />"
						."</form>"
						."</td>";
				
				// create unzip confirmation row
				$unzip_link_href = $wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath)
												  ."&unzipfile=".wpfib_path_encode($a_file['name']);
				
				$unzip_link_href_nonced = wp_nonce_url($unzip_link_href, 'wpfib-unzip_file_'.md5($a_file['name']));
				$unzip_link = "<a href='".$unzip_link_href_nonced."'>".__('Unzip file?')."</a>";
				$unzip_hidden_row = "<tr id='unzipConfirmFileRowID_$rowno' class='row $row_style' style='display:none;height:64px;background-color:#FFD' onmouseover='this.className=\"highlighted\"' onmouseout='this.className=\"row $row_style\"'>"
						."<td style='text-align:center' colspan='$rowColspan'>".$unzip_link."</td>"
						."<script type='text/javascript'>"
						."	function slideDown_unzipConfirmFileRowID_$rowno() {"
						."		jQuery('#unzipButtonFileRowID_$rowno').hide(5, function(){"
						."		jQuery('#unzipConfirmFileRowID_$rowno').show(200).delay(4000).hide(200, function(){"
						."		jQuery('#unzipButtonFileRowID_$rowno').show();});});"
						."	}	"
						."</script>"
						."</tr>";
			}
			else
			{
				$text .= "<td class='emptyTd'></td>";
				$unzip_hidden_row = "";
			}

			// rename icon (or not)
			if($wpfib_access_rights[CAN_RENAME_FILES] && !wpfib_is_archive($curdir_relpath))
			{
				$text .= "<td class='fileAction'>"
						."<span>"
						."<img src=\"".$wpfib_imgdirNavigation."rename.png\" border='0' title=\"".sprintf(__('Rename file %s', 'wpfib'), wpfib_string_encode($a_file["name"]))."\" onclick='toggleEditFileRowID_$rowno();' />"
						."</span>"
						."</td>";
			}
			else
			{
				$text .= "<td class='emptyTd'></td>";
			}
			
			// delete icon and hidden row (or not)
			if($wpfib_access_rights[CAN_DELETE_FILES])
			{
				$text .= "<td class='fileAction'>"
						."<form>"
						."<img id='deleteButtonFileRowID_$rowno' src=\"".$wpfib_imgdirNavigation."delete.png\" border='0' title=\""
							.sprintf(__('Remove file %s', 'wpfib'), wpfib_string_encode($a_file["name"]))."\" onclick='slideDown_deleteConfirmFileRowID_$rowno();' />"
						."</form>"
						."</td>";
				
				// create delete confirmation row
				$delete_link_href = $wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath)
												   ."&delfile=".wpfib_path_encode($a_file['name']);
				
				$delete_link_href_nonced = wp_nonce_url($delete_link_href, 'wpfib-delete_file_'.md5($a_file['name']));
				$delete_link = "<a href='".$delete_link_href_nonced."'>".__('Delete file?')."</a>";
				$delete_hidden_row = "<tr id='deleteConfirmFileRowID_$rowno' class='row $row_style' style='display:none;height:64px;background-color:#FFD' onmouseover='this.className=\"highlighted\"' onmouseout='this.className=\"row $row_style\"'>"
						."<td style='text-align:center' colspan='$rowColspan'>".$delete_link."</td>"
						."<script type='text/javascript'>"
						."	function slideDown_deleteConfirmFileRowID_$rowno() {"
						."		jQuery('#deleteButtonFileRowID_$rowno').hide(5, function(){"
						."		jQuery('#deleteConfirmFileRowID_$rowno').show(200).delay(4000).hide(200, function(){"
						."		jQuery('#deleteButtonFileRowID_$rowno').show();});});"
						."	}	"
						."</script>"
						."</tr>";
			}
			else
			{
				$text .= "<td class='emptyTd'></td>";
				$delete_hidden_row = "";
			}
			
			$text .= "</tr>";
			$text .= $unzip_hidden_row;
			$text .= $restore_hidden_row;
			$text .= $delete_hidden_row;
				
			$rowno++;
			$row = 1 - $row;
		}
	}

	$text .= "</table>";
	$text .= "</div>";
	
	return true;
}

function wpfib_render_archive_div(&$text, $repo_abspath, $curdir_relpath, &$dirs, &$files)
{
	global $wpfib_options;
	global $wpfib_baselink;
	global $wpfib_imgdirNavigation;
	
	$dir = $repo_abspath.DS.$curdir_relpath;
                
	if (is_dir(html_entity_decode($dir.DS."JS_ARCHIVE", ENT_QUOTES, $wpfib_options['default_string_encoding'])))
	{
			//$text .= "<img src=\"".$this->imgdirNavigation."null.gif\" height=10 />";

		$archiveLinkATag = "<a href='".$wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath.DS."JS_ARCHIVE")."'>";
                        
		$text .= "<div id='JS_ARCHIVE_DIV'>"
				."<table>"
				."<tr>"
				."	<td class='actionIcon'>"
				."	</td>"
                ."      <td>"
					    .$archiveLinkATag.__('View archive')."</a>"
                ."      </td>"
				."	<td class='actionIcon'>"
                        .$archiveLinkATag."<img src=\"".$wpfib_imgdirNavigation."viewArchive.png\" border='0' /></a>"
				."	</td>";

		$text .= "</tr>"
				."</table>"
				."</div>";
	}
	
	return true;
}

function wpfib_is_archive($dir_relpath)
{
	return !strcmp(wpfib_fileBaseName($dir_relpath), "JS_ARCHIVE");
}

// this function detects if the $dir_abspath variable contains a path inside the webroot
// or not, and returns a boolean, but writes into $webroot the detected web root 
function wpfib_is_in_webroot($dir_abspath, &$dir_relpath, &$base_url)
{
	global $wpfib_options;
	
	$script_relpath = dirname(getenv("SCRIPT_NAME"));
	$script_abspath = ABSPATH;
	$script_url		= home_url();
	
	// base url is the url without any installation subdir
	$base_url = substr($script_url, 0, strlen($script_url) - strlen($script_relpath));

//	echo "ABS = ".$script_abspath."<br />";
//	echo "REL = ".$script_relpath."<br />";
//	echo "URL = ".$script_url."<br />";
//	echo "bURL = ".$base_url."<br />";

	// ensure forward slashes
	$dir_abspath	= rtrim(str_replace("\\", "/", $dir_abspath), "/");
	$script_abspath	= rtrim(str_replace("\\", "/", $script_abspath), "/");
	
	$webroot = substr($script_abspath, 0, strpos($script_abspath, $script_relpath));
	if ($wpfib_options['DEBUG_enabled'])
	{
		echo "WEBROOT = ".$webroot."<br />";
	}
	
	if(!strcmp($webroot, substr($dir_abspath, 0, strlen($webroot))))
	{
		$dir_relpath = substr($dir_abspath, strlen($webroot), strlen($dir_abspath));
		return true;
	}
	else
	{
		$dir_relpath = "";
		return false;
	}
}

function wpfib_render_actions_div(&$text, $repo_abspath, $curdir_relpath)
{
	global $wpfib_access_rights;

	global $wpfib_options;
	global $wpfib_userdata;
	global $wpfib_imgdirNavigation;
	global $wpfib_baselink;
	global $wpfib_default_file_chmod;
	
	$dir = $repo_abspath.DS.$curdir_relpath;
	
	$allow_file_archiving = $wpfib_options['allow_file_archiving'];
	
	$user_has_sufficient_rights = $wpfib_access_rights[CAN_CREATE_FOLDERS] || $wpfib_access_rights[CAN_UPLOAD];

	// display the actions box under these conditions
	if ($user_has_sufficient_rights && !wpfib_is_archive($curdir_relpath))
	{
			$display_html_upload_form = true;

		// start actions div
                        
		$text .= "<div id='JS_ACTIONS_DIV'"
				.((!isset($_COOKIE['wpfib_display_actions']) || $_COOKIE['wpfib_display_actions'] == ACTIONS_BOX_IS_CLOSED) ? " style='display:none;' " : "")
				.">"
				."<table>";
                                
		// first action (new folder)
		// NOTE: action='' (or no action) must be empty for post method to work
        
		if ($wpfib_access_rights[CAN_CREATE_FOLDERS])
		{
			$text .= "<tr>"
					."	<td class='right_aligned'>"
					."		<form style='display:inline; margin: 0px; padding: 0px;' enctype='multipart/form-data' action='' method='POST'>"
						.__('Create new folder').":&nbsp;&nbsp;"
					."	</td>"
					."	<td>"
					."		<input class='long_input_field' name='userdir' type='text' />"
					."		<input name='dir' type='hidden' value='".wpfib_path_encode($curdir_relpath)."' />"
					."	</td>"
					."	<td class='actionIcon'>"
					."		<input type='image' src=\"".$wpfib_imgdirNavigation."addfolder.png\" title=\"".__('Add folder')."\" />"
							.wp_nonce_field('wpfib-create_folder_'.md5($dir))
					."		</form>"
					."	</td>"
					."</tr>";
		}

		// second action (upload)
		if ($wpfib_access_rights[CAN_UPLOAD])
		{
			$text .= "<tr>"
			."	<td class='right_aligned' colspan='".($display_html_upload_form ? 1 : 3)."'>"; // the swf upload form only has one td cell

//			$text .= "<form name='uploadForm' style='display:inline; margin: 0px; padding: 0px;' enctype='multipart/form-data' noaction='".$wpfib_baselink."&dir=".urlencode($curdir_relpath)."' method='post'>";
			$text .= "<form name='uploadForm' style='display:inline; margin: 0px; padding: 0px;' enctype='multipart/form-data' action='' method='post'>";

				$text .= __('Upload file').":&nbsp;"
					."	</td>"
					."	<td>"
					."	<input name=\"userfile\" type=\"file\" />"
					."	</td>"
					."	<td class='actionIcon'>"
					."	<input type='image' src=\"".$wpfib_imgdirNavigation."addfile.png\" title=\"".__('Upload file')."\" />"
						.wp_nonce_field('wpfib-upload_file_'.md5($dir))
					."	<input type='hidden' name='dir' value=\"".wpfib_path_encode($curdir_relpath)."\">";

			$text .= "	"//	</td>"
			//	."	</tr>"
			//	."	</table>"
				."	</form>"
				."	</td>"
				."</tr>";
		}
		
		$text .= "</table>"
				."</div>"; // end of div tag with id JS_ACTIONS_DIV

	}
		
	return true;
}

function wpfib_render_bottom_div(&$text, $repo_abspath, $curdir_relpath)
{
	global $wpfib_access_rights;
	
	global $wpfib_options;
	global $wpfib_baselink;
	global $wpfib_imgdirNavigation;
	
	global $wpfib_version_number;

	$dir = $repo_abspath.DS.$curdir_relpath;
	
	$user_has_sufficient_rights = $wpfib_access_rights[CAN_CREATE_FOLDERS] || $wpfib_access_rights[CAN_UPLOAD];

	// javascript to slide JS_ACTIONS_DIV
	$javascript .= "<script type='text/javascript'>"
					."	function toggle_actionsBox() {"
//					."alert(jQuery.cookie('wpfib_display_actions'));"
					."		jQuery('#JS_ACTIONS_DIV').toggle(200, function () {"
					."			if (jQuery('#actionsBoxToggleImg').attr('src') == '".$wpfib_imgdirNavigation."minus.png') {"
					."				jQuery('#actionsBoxToggleImg').attr('src', '".$wpfib_imgdirNavigation."plus.png');"
					."				jQuery('#actionsBoxToggleImg').attr('title', '');"
					."				jQuery.cookie('wpfib_display_actions', '".ACTIONS_BOX_IS_CLOSED."', { expires: 365 });"
					."			}"
					."			else if (jQuery('#actionsBoxToggleImg').attr('src') == '".$wpfib_imgdirNavigation."plus.png') {"
					."				jQuery('#actionsBoxToggleImg').attr('src', '".$wpfib_imgdirNavigation."minus.png');"
					."				jQuery('#actionsBoxToggleImg').attr('title', '');"
					."				jQuery.cookie('wpfib_display_actions', '".ACTIONS_BOX_IS_OPEN."', { expires: 365 });"
					."			}"
					."		});"
					."	}"
					."</script>";

	// small icon with link to site and title containing copyright and version number
//		$credits_icon = "<td class='right_aligned'><a href='http://www.smallerik.com' target='_blank'>"
//					   ."<img src=\"".$wpfib_imgdirNavigation."wpfib.png\" border='0' title=\"".sprintf(__('WP File Browser v.%s - Copyright 2012-2014 Enrico Sandoli'), $wpfib_version_number)."\" /></a>"
//					   ."</td>";
		$credits_icon = "<td class='right_aligned'>"
					   ."<img src=\"".$wpfib_imgdirNavigation."wpfib.png\" border='0' title=\"".sprintf(__('WP File Browser v.%s - Copyright 2012-2014 Enrico Sandoli'), $wpfib_version_number)."\" />"
					   ."</td>";

	// set display actions link(s): distinguish case of cookie set (the cookie is the same, so only one box is open at any one time) or not set
	if (isset($_COOKIE['wpfib_display_actions']))
	{
		// for the upload box (not allowed in archive folder)
		if (!wpfib_is_archive($dir))
		{
			if ($user_has_sufficient_rights && $_COOKIE['wpfib_display_actions'] == ACTIONS_BOX_IS_OPEN)
			{
				$upload_actions_icon = "<td class='actionIcon'>"
						."<form>"
						."<img id='actionsBoxToggleImg' src=\"".$wpfib_imgdirNavigation."minus.png\" border='0' "
						." onclick='toggle_actionsBox();' title=\"".__('Close actions box', 'wpfib')."\" />"
						."</form>"
						."</td>";
			}
			else if ($user_has_sufficient_rights)
			{
				$upload_actions_icon = "<td class='actionIcon'>"
						."<form>"
						."<img id='actionsBoxToggleImg' src=\"".$wpfib_imgdirNavigation."plus.png\" border='0' "
						." onclick='toggle_actionsBox();' title=\"".__('Open actions box')."\" />"
						."</form>"
						."</td>";
			}
			else
			{
				$upload_actions_icon = "<td class='emptyTd'></td>";
			}
		}
		else
		{
			$upload_actions_icon = "<td class='emptyTd'></td>";
		}
	}
	else
	{
		// for the upload box (not allowed in archive folder)
		if ($user_has_sufficient_rights && !wpfib_is_archive($dir))
		{
				$upload_actions_icon = "<td class='actionIcon'>"
						."<form>"
						."<img id='actionsBoxToggleImg' src=\"".$wpfib_imgdirNavigation."plus.png\" border='0' "
						." onclick='toggle_actionsBox();' title=\"".__('Open actions box', 'wpfib')."\" />"
						."</form>"
						."</td>";
		}
		else
		{
			$upload_actions_icon = "<td class='emptyTd'></td>";
		}
	}

	// Bottom line

	$text .= "<div id='JS_BOTTOM_DIV'>"
		."<table>"
		."<tr>"
		.$javascript
		.$upload_actions_icon
		."	<td>&nbsp;</td>"
		.$credits_icon
		."</tr>"
		."</table>"
		."</div>";
		
	return true;
}

// all html code created by the plugin is wrapped around a div with id JS_MAIN_DIV
// the rest is contained in 4 div tags whose ids are JS_TOP_DIV, JS_FILES_DIV, JS_ACTIONS_DIV, JS_BOTTOM_DIV
function wpfib_display_repository(&$text, $repo_abspath, $curdir_relpath, &$error_text)
{
	global $wpfib_options;
	global $wpfib_baselink;
	global $wpfib_imgdirNavigation;

	// we are rendering the repository for the following dir
	$dir = $repo_abspath.DS.$curdir_relpath;
	
	// set the hidden elements (if any)
	wpfib_set_hidden_elements();
	
	// get the sorted arrays of files and folders
	$dirs = array();
	$files = array();
	if (!wpfib_get_dirs_and_files($repo_abspath, $curdir_relpath, $dirs, $files, $error_text))
	{
		return false;
	}
	
	// if we are inside an archive and there are no files, remove the archive folder and reload the parent folder
	if (!count($files) && wpfib_is_archive($curdir_relpath))
	{
		@rmdir($dir);
					
		// check if the current directory (an archive) is gone
		if(file_exists($dir))
		{
			$error_text = "<div id='JS_MAIN_DIV'><div id='JS_ERROR_DIV'><table><tr>"
					."<td class='alertIcon'><img src=\"".$wpfib_imgdirNavigation."warning.png\"></td><td>"
					.__('Could not remove empty archive folder')."</td>"
					."</tr></table></div><br /></div>";
			return false;
		}
		else
       	{
			wp_redirect($wpfib_baselink."&dir=".wpfib_path_encode(wpfib_upperDirSetForwardSlashes($curdir_relpath)));
		}
	}

	// START MAIN DIV
	$text .= "<div id='JS_MAIN_DIV'>";
	
	// display a repo title
	if (strlen($wpfib_options['title']))
	{
		$text .= "<br /><h2>".$wpfib_options['title']."</h2>";
	}

	
    // render JS_TOP_DIV
	if (!wpfib_render_top_div($text, $repo_abspath, $curdir_relpath))
	{
		return false;
	}

    // render JS_FILES_DIV
	if (!wpfib_render_files_div($text, $repo_abspath, $curdir_relpath, $dirs, $files))
	{
		return false;
	}

	// render JS_ARCHIVE_DIV
	if (!wpfib_render_archive_div($text, $repo_abspath, $curdir_relpath, $dirs, $files))
	{
		return false;
	}

	// render JS_ACTIONS_DIV
	if (!wpfib_render_actions_div($text, $repo_abspath, $curdir_relpath))
	{
		return false;
	}
	
	// render JS_BOTTOM_DIV
	if (!wpfib_render_bottom_div($text, $repo_abspath, $curdir_relpath))
	{
		return false;
	}
	
	// END MAIN DIV
    $text .= "</div>";

	return true;
}

	// ***********************************************************************************************************************
	// Javascript and Cascading Style Sheets used locally, and other functions
	// ***********************************************************************************************************************

	function wpfib_do_js()
	{
		global $wpfib_baselink;
		
		$js = "";

		
		$js .= 	 "		"
				."	function makeErrorBoxDisappear() {"
				."		jQuery('#JS_ERROR_DIV').slideUp('slow');"
				."	}	"
				."		"
				."	function makeSuccessBoxDisappear() {"
				."		jQuery('#JS_SUCCESS_DIV').delay(3000).slideUp('slow');"
				."	}	"
				."		";
		
		
		return ($js);
	}

	function wpfib_do_css()
	{
		global $wpfib_options;
		global $wpfib_imgdirNavigation;
		
		$css = ""
                        
            // main div (default values)
                        
 			."#JS_MAIN_DIV "
			."{"
            ."	background-color:transparent;" // we set this as transparent (default value)
			."	width:".$wpfib_options['table_width']."px;"
            ."	padding:0px;"
			."	border:0px;"
			."	margin: 0 auto 0 auto;"; // this will center the content inside the main div
		//	."	font-family: Verdana;"

		if (!$wpfib_options['use_default_font_size'])
		{
			$css .= "	font-size:".$wpfib_options['font_size']."px;";
		}   
		$css .= "}"
                        
            // main div tables
                        
            ."#JS_MAIN_DIV table "
			."{"
            ."	background-color:transparent;" // we set this as transparent (default value)
			."	width:100%;"
            ."	padding:0px;"
			."	border:0px !important;"
            ." 	border-spacing:0px;"
			."	margin:0px;"
            ."	cellspacing:0px !important;"
            ."	cellpadding:0px !important;"
            ."}	"

            // for all table rows
                        
			."#JS_MAIN_DIV tr {"
            ."	background-color:transparent;" // we set this as transparent (default value)
            ."	background:url('".$wpfib_imgdirNavigation."null.gif');"
			."	height:".($wpfib_options['min_row_height'])."px;"
            ."	padding:0px;"
			."	border:0px;"
            ."	margin:0px;"
			."}	"

            ."#JS_MAIN_DIV tr.separatingLine {"
			."	background-color:#".$wpfib_options['line_bgcolor'].";"
			."	height:".$wpfib_options['line_height']."px;"
			."}	"

			// for all table cells
                        
			."#JS_MAIN_DIV td {"
            ."	background-color:transparent;" // we set this as transparent (default value)
			."	padding:0px;"
			."	border: 0px;"
			."	margin: 0px;"
			."	text-align:left;"
            ."	vertical-align:middle;"
			."}	"

			."#JS_MAIN_DIV td.right_aligned {"
//			."	width: 100px;"
			."	text-align:right;"
			."}	"

			."#JS_MAIN_DIV td.emptyTd {"
			."	width: 0px;"
			."}	"

			."#JS_MAIN_DIV td.actionIcon {"
			."	width: 25px;"
			."	text-align:center;"
			."}	"
                        
			."#JS_MAIN_DIV td.jsmallicon_log {"
            ."	vertical-align:top;"
			."	text-align:center;"
			."	width:30px;"
			."	padding:".$wpfib_options['icon_padding']."px;"
			."}	"

            // for all input fields
                        
			."#JS_MAIN_DIV input {"
			."	background-color:#FFFFFF;"
			."	border:0px;"
			."}	"

			."#JS_MAIN_DIV input[type=text], #JS_MAIN_DIV input[type=file], #JS_MAIN_DIV select {"
			."	background-color:#".$wpfib_options['inputbox_bgcolor'].";"
			."	border: ".$wpfib_options['inputbox_border']."px; border-style: ".$wpfib_options['inputbox_linetype']."; border-color: #".$wpfib_options['inputbox_linecolor'].";"
			."}	"

			."#JS_MAIN_DIV input[type=image] {"
			."	background-color:transparent;"
			."	border: 0px;"
			."}	"

            ."#JS_MAIN_DIV input[type=text].long_input_field {"
            ."	width:99%;"
            ."}"

			."#JS_MAIN_DIV a { background-image:none; } " // removes background (extra icons) when using some nasty templates (ref. Clay Hess)
            
            // for JS_TOP_DIV elements
                        
			."#JS_TOP_DIV {"
			."	width:".($wpfib_options['table_width'] - 10)."px;"
			."	padding:5px;"
			."	border:0px;"
			."	margin: 0px;"
			."}	"

			."#JS_TOP_DIV tr {"
			."	height:10px;"
			."}	"
 
			."#JS_TOP_DIV td.navigation {"
			."	text-align:left;"
			."}	"

			."#JS_TOP_DIV td.topLinks {"
			."	text-align:right;"
			."}	"

            // for JS_FILES_DIV elements
                        
			."#JS_FILES_DIV {"
			."	width:".($wpfib_options['table_width'] - 10 - 2 * $wpfib_options['framebox_border'])."px;"
			."	background-color:#".$wpfib_options['framebox_bgcolor'].";"
			."	text-align:left;"
			."	margin: 0;"
			."	padding:5px;"
			."	border: ".$wpfib_options['framebox_border']."px; border-style: ".$wpfib_options['framebox_linetype']."; border-color: #".$wpfib_options['framebox_linecolor'].";";
                
            $css .=  " border-radius:".$wpfib_options['border_radius']."px;";
                
            if ($wpfib_options['use_box_shadow'])
            {
				// for Firefox
				$css .= "-moz-box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);"
				// for Safari and Chrome
				."-webkit-box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);"
				// W3C specs
				."box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);";
			}
			
			$css .= "}  "

			."#JS_FILES_DIV tr.groupSwitch {"
			."	height:25px;"
			."}	"
                
			."#JS_FILES_DIV tr.highlighted {"
			."	background-color:#".$wpfib_options['highlighted_color'].";"
			."}	"

			."#JS_FILES_DIV tr.row.header {"
			."	background-color:#".$wpfib_options['header_bgcolor'].";"
			."}	"

			."#JS_FILES_DIV tr.row.odd {"
			."	background-color:#".$wpfib_options['oddrows_color'].";"
			."}	"

			."#JS_FILES_DIV tr.row.even {"
			."	background-color:#".$wpfib_options['evenrows_color'].";"
			."}	"

			."#JS_FILES_DIV td.groupSwitchIcon {"
			."	text-align:center;"
			."	width:40px;" // we don't put 0 here as Safari seems to take it as 'no fixed width'
			."	padding:5px;"
			."}	"

			."#JS_FILES_DIV td.fileIcon {"
			."	text-align:center;"
			."	width:".$wpfib_options['icon_width']."px;" // we don't put 0 here as Safari seems to take it as 'no fixed width'
			."	padding:".$wpfib_options['icon_padding']."px;"
			."}	"

			."#JS_FILES_DIV td.fileThumb {"
			."	padding:".$wpfib_options['icon_padding']."px;"
			."	width:".$wpfib_options['thumbsize']."px;"
			."}	"

			."#JS_FILES_DIV td.fileName {"
			."	width:auto;" // this is needed on IE when displaying only folders, to force names to take most of the space, squeezing other icons to their respective size!
			."}	"

			."#JS_FILES_DIV td.fileSize {"
//			."	width: 100px;"
			."	text-align:right;"
			."}	"

			."#JS_FILES_DIV td.fileChanged {"
			."	width: 130px;"
			."	text-align:center;"
			."}	"
                        
			."#JS_FILES_DIV td.fileAction {"
			."	width: 25px;"
			."	text-align:center;"
			."}	"
                        
			."#JS_FILES_DIV img.imgThumb {"
			."	border:1px; border-style:solid; border-color:#".$wpfib_options['framebox_linecolor'].";";
            if ($wpfib_options['use_thumb_shadow'])
            {
				// for Firefox
				$css .= "-moz-box-shadow: ".$wpfib_options['thumb_shadow_width']."px ".$wpfib_options['thumb_shadow_width']."px ".$wpfib_options['thumb_shadow_blur']."px rgba(".$wpfib_options['thumb_shadow_color'].",".$wpfib_options['thumb_shadow_color'].",".$wpfib_options['thumb_shadow_color'].",0.8);"
				// for Safari and Chrome
				."-webkit-box-shadow: ".$wpfib_options['thumb_shadow_width']."px ".$wpfib_options['thumb_shadow_width']."px ".$wpfib_options['thumb_shadow_blur']."px rgba(".$wpfib_options['thumb_shadow_color'].",".$wpfib_options['thumb_shadow_color'].",".$wpfib_options['thumb_shadow_color'].",0.8);"
				// W3C specs
				."box-shadow: ".$wpfib_options['thumb_shadow_width']."px ".$wpfib_options['thumb_shadow_width']."px ".$wpfib_options['thumb_shadow_blur']."px rgba(".$wpfib_options['thumb_shadow_color'].",".$wpfib_options['thumb_shadow_color'].",".$wpfib_options['thumb_shadow_color'].",0.8);";
			}
			$css .= "} "
					
            // filter table

			."#JS_FILES_DIV table.filterTable tr {"
            ."	align:right;"
			."}	"

            ."#JS_FILES_DIV table.filterTable td {"
			."	text-align:right;"
			."}	"

			."#JS_FILES_DIV td.filterIconTick {"
			."	text-align:center;"
			."	width:25px;"
            ."	padding:0px 0px 0px 5px;"
			."}	"

			."#JS_FILES_DIV td.filterIconDelete {"
			."	text-align:center;"
			."	width:25px;"
            ."	padding:0px 10px 0px 0px;"
			."}	"

			."#JS_FILES_DIV td.filterIconTick input[type=image] {"
            ."	padding:0px;"
			."}	"

            // for JS_ARCHIVE_DIV elements
                        
			."#JS_ARCHIVE_DIV {"
			."	width:".($wpfib_options['table_width'] - 10 - 2 * $wpfib_options['framebox_border'])."px;"
			."	background-color:#".$wpfib_options['framebox_bgcolor'].";"
			."	text-align:left;"
			."	margin: ".$wpfib_options['box_distance']."px 0px 0px 0px;"
			."	padding:5px;"
			."	border: ".$wpfib_options['framebox_border']."px; border-style: ".$wpfib_options['framebox_linetype']."; border-color: #".$wpfib_options['framebox_linecolor'].";";
                
            $css .=  " border-radius:".$wpfib_options['border_radius']."px;";
                
            if ($wpfib_options['use_box_shadow'])
            {
				// for Firefox
				$css .= "-moz-box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);"
				// for Safari and Chrome
				."-webkit-box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);"
				// W3C specs
				."box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);";
			}
			
            $css .= "}  "

            // for JS_ACTIONS_DIV
                        
			."#JS_ACTIONS_DIV {"
			."	width:".($wpfib_options['table_width'] - 10 - 2 * $wpfib_options['framebox_border'])."px;"
			."	background-color:#".$wpfib_options['framebox_bgcolor'].";"
			."	text-align:left;"
			."	margin: ".$wpfib_options['box_distance']."px 0px 0px 0px;"
			."	padding:5px;"
			."	border: ".$wpfib_options['framebox_border']."px; border-style: ".$wpfib_options['framebox_linetype']."; border-color: #".$wpfib_options['framebox_linecolor'].";";
                
            $css .=  " border-radius:".$wpfib_options['border_radius']."px;";
                
            if ($wpfib_options['use_box_shadow'])
            {
				// for Firefox
				$css .= "-moz-box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);"
				// for Safari and Chrome
				."-webkit-box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);"
				// W3C specs
				."box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);";
			}
			
            $css .= "}  "

			."#JS_ACTIONS_DIV td.actionIcon {"
			."	text-align:center;"
			."	width:25px;"
            ."	padding:0px 5px 0px 5px;"
			."}	"

			."#JS_ACTIONS_DIV td.actionIcon input[type=image] {"
            ."	padding:0px;"
			."}	"

            // for JS_BOTTOM_DIV

			."#JS_BOTTOM_DIV {"
			."	width:".$wpfib_options['table_width']."px;"
			."	margin:0px;"
			."	padding:0px;"
			."	border:0px;"
			."}	"

            // for JS_ERROR_DIV
                       
            ."#JS_ERROR_DIV {"
			."	width:".($wpfib_options['table_width'] - 10 - 2 * $wpfib_options['errorbox_border'])."px;"
			."	background-color:#".$wpfib_options['errorbox_bgcolor'].";"
			."	text-align:left;"
			."	margin: 30px 0px 0px 0px;"
			."	padding:5px;"
			."	border: ".$wpfib_options['errorbox_border']."px; border-style: ".$wpfib_options['errorbox_linetype']."; border-color: #".$wpfib_options['errorbox_linecolor'].";";
                
            $css .=  " border-radius:".$wpfib_options['border_radius']."px;";
                
            if ($wpfib_options['use_box_shadow'])
            {
				// for Firefox
				$css .= "-moz-box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);"
				// for Safari and Chrome
				."-webkit-box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);"
				// W3C specs
				."box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);";
			}
			
            $css .= "}  "

			."#JS_SUCCESS_DIV {"
			."	width:".($wpfib_options['table_width'] - 10 - 2 * $wpfib_options['successbox_border'])."px;"
			."	background-color:#".$wpfib_options['successbox_bgcolor'].";"
			."	text-align:left;"
			."	margin: 30px 0px 0px 0px;"
			."	padding:5px;"
			."	border: ".$wpfib_options['successbox_border']."px; border-style: ".$wpfib_options['successbox_linetype']."; border-color: #".$wpfib_options['successbox_linecolor'].";";
                
            $css .=  " border-radius:".$wpfib_options['border_radius']."px;";
                
            if ($wpfib_options['use_box_shadow'])
            {
				// for Firefox
				$css .= "-moz-box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);"
				// for Safari and Chrome
				."-webkit-box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);"
				// W3C specs
				."box-shadow: ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_width']."px ".$wpfib_options['box_shadow_blur']."px rgba(".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",".$wpfib_options['box_shadow_color'].",0.8);";
			}
			
            $css .= "}  "

			."#JS_ERROR_DIV td.alertIcon, #JS_SUCCESS_DIV td.alertIcon {"
            ."      vertical-align:middle;"
			."	text-align:center;"
			."	width:60px;"
			."}	"

            // TODO remove this css
			."#jsmallspacer {"
			."	width:100%"
			."	margin:0px;"
			."	padding:5px;"
			."	margin: 0px;"
			."}	";

		
		$css .= "	-moz-border-radius-topleft : ".$wpfib_options['border_radius']."px;"
			."	-webkit-border-top-left-radius : ".$wpfib_options['border_radius']."px;"
			."	-moz-border-radius-topright : ".$wpfib_options['border_radius']."px;"
			."	-webkit-border-top-right-radius : ".$wpfib_options['border_radius']."px;"
			."	-moz-border-radius-bottomleft : ".$wpfib_options['border_radius']."px;"
			."	-webkit-border-bottom-left-radius : ".$wpfib_options['border_radius']."px;"
			."	-moz-border-radius-bottomright : ".$wpfib_options['border_radius']."px;"
			."	-webkit-border-bottom-right-radius : ".$wpfib_options['border_radius']."px;";

		$css .= "}	"

			."#upload input[disabled] {"
			."	border: ".$wpfib_options['inputbox_border']."px; border-style: ".$wpfib_options['inputbox_linetype']."; border-color: #".$wpfib_options['inputbox_linecolor'].";"
       		."}	"

			;

		return $css;
	}

	function wpfib_sort_files_and_dirs(&$files, &$dirs, $sort_as = 0, $sort_by = 0)
	{
		global $wpfib_options;
		global $wpfib_cur_sort_by;
		global $wpfib_cur_sort_as;
				
		if($files || $dirs)
		{
			if($sort_by && $sort_as)
			{
				if (($sort_by == SORT_BY_NAME) && ($sort_as != SORT_ASCENDING))
				{
					@usort($dirs, "wpfib_dirname_cmp_desc");
					@usort($files, "wpfib_filename_cmp_desc");

					$wpfib_cur_sort_by = SORT_BY_NAME;
					$wpfib_cur_sort_as = SORT_DESCENDING;
				}
				elseif(($sort_by == SORT_BY_NAME) && ($sort_as == SORT_ASCENDING))
				{
					@usort($dirs, "wpfib_dirname_cmp_asc");
					@usort($files, "wpfib_filename_cmp_asc");

					$wpfib_cur_sort_by = SORT_BY_NAME;
					$wpfib_cur_sort_as = SORT_ASCENDING;
				}
				elseif(($sort_by == SORT_BY_SIZE) && ($sort_as != SORT_ASCENDING) && $files)
				{
					@usort($files, "wpfib_size_cmp_desc");

					$wpfib_cur_sort_by = SORT_BY_SIZE;
					$wpfib_cur_sort_as = SORT_DESCENDING;
				}
				elseif(($sort_by == SORT_BY_SIZE) && ($sort_as == SORT_ASCENDING) && $files)
				{
					@usort($files, "wpfib_size_cmp_asc");

					$wpfib_cur_sort_by = SORT_BY_SIZE;
					$wpfib_cur_sort_as = SORT_ASCENDING;
				}
				elseif(($sort_by == SORT_BY_CHANGED) && ($sort_as != SORT_ASCENDING) && $files)
				{
					@usort($files, "wpfib_changed_cmp_desc");

					$wpfib_cur_sort_by = SORT_BY_CHANGED;
					$wpfib_cur_sort_as = SORT_DESCENDING;
				}
				elseif(($sort_by == SORT_BY_CHANGED) && ($sort_as == SORT_ASCENDING) && $files)
				{
					@usort($files, "wpfib_changed_cmp_asc");

					$wpfib_cur_sort_by = SORT_BY_CHANGED;
					$wpfib_cur_sort_as = SORT_ASCENDING;
				}
			}
			else
			{
				// default sort by name
				if ($wpfib_options['sort_by'] == SORT_BY_NAME)
				{
					if ($wpfib_options['sort_as'] == SORT_DESCENDING)
					{
						@usort($dirs, "wpfib_dirname_cmp_desc");
						@usort($files, "wpfib_filename_cmp_desc");

						$wpfib_cur_sort_by = SORT_BY_NAME;
						$wpfib_cur_sort_as = SORT_DESCENDING;
					}
					else
					{
						@usort($dirs, "wpfib_dirname_cmp_asc");
						@usort($files, "wpfib_filename_cmp_asc");

						$wpfib_cur_sort_by = SORT_BY_NAME;
						$wpfib_cur_sort_as = SORT_ASCENDING;
					}
				}

				// default sort by size
				if ($wpfib_options['sort_by'] == SORT_BY_SIZE)
				{
					if ($wpfib_options['sort_as'] == SORT_DESCENDING)
					{
						@usort($dirs, "wpfib_dirname_cmp_asc");
						@usort($files, "wpfib_size_cmp_desc");

						$wpfib_cur_sort_by = "size";
						$wpfib_cur_sort_as = "desc";
					}
					else
					{
						@usort($dirs, "wpfib_dirname_cmp_asc");
						@usort($files, "wpfib_size_cmp_asc");

						$wpfib_cur_sort_by = "size";
						$wpfib_cur_sort_as = "asc";
					}
				}

				// default sort by changed
				if ($wpfib_options['sort_by'] == SORT_BY_CHANGED)
				{
					if ($wpfib_options['sort_as'] == SORT_DESCENDING)
					{
						@usort($dirs, "wpfib_dirname_cmp_asc");
						@usort($files, "wpfib_changed_cmp_desc");

						$wpfib_cur_sort_by = "changed";
						$wpfib_cur_sort_as = "desc";
					}
					else
					{
						@usort($dirs, "wpfib_dirname_cmp_asc");
						@usort($files, "wpfib_changed_cmp_asc");

						$wpfib_cur_sort_by = "changed";
						$wpfib_cur_sort_as = "asc";
					}
				}
			}
		}
	}

		//
	// Format the file size
	//
	function wpfib_fileSizeF($size) 
	{
		global $wpfib_options;
		
		$sizes = Array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
		$y = $sizes[0];
		for ($i = 1; (($i < count($sizes)) && ($size >= 1024)); $i++) 
		{
			$size = $size / 1024;
			$y  = $sizes[$i];
		}

		// Erik: Adjusted number format
		$dec = max(0, (3 - strlen(round($size))));
		return number_format($size, $dec, $wpfib_options['filesize_separator'], " ")." ".$y;
		// Old code:
		//return round($size, 2)." ".$y;
	}
	function wpfib_fileRealSize($file)
	{
		$sizeInBytes = filesize($file);
		//
		// If filesize() fails (with larger files), try to get the size from unix command line.
		if ($sizeInBytes === false) {
			$sizeInBytes = @exec("ls -l '$file' | awk '{print $5}'");
		}
		else
			return $sizeInBytes;
	}
	//
	// Return file extension (the string after the last dot.
	// NOTE: THIS FUNCTION IS REPLICATED IN UPLOAD.PHP
	//
	function wpfib_fileExtension($file)
	{
		$a = explode(".", $file);
		$b = count($a);
		return $a[$b-1];
	}

	// Return file without extension (the string before the last dot.
	// NOTE: THIS FUNCTION IS REPLICATED IN UPLOAD.PHP
	//
	function wpfib_fileWithoutExtension($file)
	{
		$a = explode(".", $file);
		$b = count($a);
		$c = $a[0];
		for ($i = 1; $i < $b - 1; $i++)
		{
			$c .= ".".$a[$i];
		}
		return $c;
	}

	//
	// Formatting the changing time
	//
	function wpfib_fileChanged($time)
	{
		global $wpfib_options;
		
		if (!$wpfib_options['display_filetime'])
		{
			$timeformat = "";
		}
		else if ($wpfib_options['display_seconds'])
		{
			$timeformat = " H:i:s";
		}
		else {
			$timeformat = " H:i";
		}

		switch ($wpfib_options['date_format'])
		{
		case 'dd_mm_yyyy_dashsep':
			return date("d-m-Y".$timeformat, $time);
		case 'dd_mm_yyyy_pointsep':
			return date("d.m.Y".$timeformat, $time);
		case 'dd_mm_yyyy_slashsep':
			return date("d/m/Y".$timeformat, $time);
		case 'yyyy_mm_dd_dashsep':
			return date("Y-m-d".$timeformat, $time);
		case 'yyyy_mm_dd_pointsep':
			return date("Y.m.d".$timeformat, $time);
		case 'yyyy_mm_dd_slashsep':
			return date("Y/m/d".$timeformat, $time);
		case 'mm_dd_yyyy_dashsep':
			return date("m-d-Y".$timeformat, $time);
		case 'mm_dd_yyyy_pointsep':
			return date("m.d.Y".$timeformat, $time);
		case 'mm_dd_yyyy_slashsep':
			return date("m/d/Y".$timeformat, $time);
		}
	}
	
	//
	// Find the icon for the extension
	//
	function wpfib_fileIcon($l)
	{
		global $wpfib_imgdirExtensions;
		global $wpfib_imgdirExtensionsPath;

		$l = strtolower($l);
	
		if (file_exists($wpfib_imgdirExtensionsPath.$l.".png"))
		{
			return $wpfib_imgdirExtensions.$l.".png";
		}
		else
		{
			return $wpfib_imgdirExtensions."unknown.png";
		}
	}

	//
	// Generates the sorting arrows
	//
	function wpfib_makeArrow($sort_by, $sort_as, $type, $curdir_relpath, $text)
	{
		global $wpfib_options;
		global $wpfib_baselink;
		
		global $wpfib_cur_sort_by;
		global $wpfib_cur_sort_as;
		
		global $wpfib_imgdirNavigation;
		
		// set icons
		$sort_icon    = $wpfib_cur_sort_by == $type ? ($wpfib_cur_sort_as == SORT_DESCENDING ? "arrow_down.png" : "arrow_up.png") : "null.gif"; 

		// set links (with relevant icons)
		if(($sort_by == $type || $wpfib_cur_sort_by == $type) && ($sort_as == SORT_DESCENDING || $wpfib_cur_sort_as == SORT_DESCENDING))
		{
			return "<a href=\"".$wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath)."&sort_by=".$type."&sort_as=".SORT_ASCENDING."\" title=\""
				.__('Set ascending order', 'wpfib')."\"> $text <img style=\"border:0;\" src=\"".$wpfib_imgdirNavigation.$sort_icon."\" /></a>";
		}
		else
		{
			return "<a href=\"".$wpfib_baselink."&dir=".wpfib_path_encode($curdir_relpath)."&sort_by=".$type."&sort_as=".SORT_DESCENDING."\" title=\""
				.__('Set descending order', 'wpfib')."\"> $text <img style=\"border:0;\" src=\"".$wpfib_imgdirNavigation.$sort_icon."\" /></a>";
		}
	}

	//
	// Functions that help sort the files
	//
	function wpfib_dirname_cmp_asc($a, $b)
	{
		global $wpfib_options;
		return $wpfib_options['default_sort_nat'] ? strnatcasecmp($a, $b) : strcasecmp($a, $b);
	}

	function wpfib_filename_cmp_asc($a, $b)
	{
		global $wpfib_options;
		return $wpfib_options['default_sort_nat'] ? strnatcasecmp($a["name"], $b["name"]) : strcasecmp($a["name"], $b["name"]);
	}

	function wpfib_size_cmp_asc($a, $b)
	{
		return ($a["size"] - $b["size"]);
	}

	function wpfib_changed_cmp_asc($a, $b)
	{
		return ($a["changed"] - $b["changed"]);
	}

	function wpfib_dirname_cmp_desc($b, $a)
	{
		global $wpfib_options;
		return $wpfib_options['default_sort_nat'] ? strnatcasecmp($a, $b) : strcasecmp($a, $b);
	}

	function wpfib_filename_cmp_desc($b, $a)
	{
		global $wpfib_options;
		return $wpfib_options['default_sort_nat'] ? strnatcasecmp($a["name"], $b["name"]) : strcasecmp($a["name"], $b["name"]);
	}

	function wpfib_size_cmp_desc($b, $a)
	{
		return ($a["size"] - $b["size"]);
	}

	function wpfib_changed_cmp_desc($b, $a)
	{
		return ($a["changed"] - $b["changed"]);
	}

	//
	// Find the directory one level up
	//
	function wpfib_upperDir($dir)
	{
		// Simpler implementation of upperDir method /ErikLtz
		$arr = explode(DS, $dir);
		unset($arr[count($arr) - 1]);
		return implode(DS, $arr);
		
//		$chops = explode(DS, $dir);
//		$num = count($chops);
//		$chops2 = array();
//		for($i = 0; $i < $num - 1; $i++)
//		{
//			$chops2[$i] = $chops[$i];
//		}
//		$dir2 = implode(DS, $chops2);
//		return $dir2;
	}

	function wpfib_upperDirSetForwardSlashes($dir)
	{
		// same as upperDir, but sets alla directory separators to forward slashes
		$arr = explode("/", wpfib_makeForwardSlashes($dir));
		unset($arr[count($arr) - 1]);
		return implode("/", $arr);
	}
		
	// Return last part in directory chain (built in basename depends on locale and having an utf8 locale may
        // return wrong characters when they really are iso8859-1)
	// [ErikLtz]
 

	function wpfib_fileBaseName($dir)
	{
		//$arr = explode(DS, $dir);
		$arr = explode("/", wpfib_makeForwardSlashes($dir));
		return $arr[count($arr) - 1];
	}

	// returns urlencoded string, but preserving forward slashes (UNUSED)

//	function urlEncodePreserveForwardSlashes($url)
//	{
//		return str_replace("%2F", "/", urlencode($url));
//	}
	
	// STRING ENCODING FUNCTIONS **********************************

	function wpfib_base64_url_encode($input)
	{
		return strtr(base64_encode($input), '+/=', '-_,');
    }

	function wpfib_base64_url_decode($input) 
	{
		return base64_decode(strtr($input, '-_,', '+/='));
    }
	
	function wpfib_path_encode($path)
	{
		$path = wpfib_base64_url_encode($path);
		
		return $path;
	}
	
	// decode a GET variable
	function wpfib_path_decode($path)
	{
		global $wpfib_options;
		
		$path = wpfib_base64_url_decode($path);
		
		// we also html entity decode
		$path = html_entity_decode($path, ENT_QUOTES, $wpfib_options['default_string_encoding']);
		return $path;
	}
	
	// function to turn a generically-encoded string into one encoded for display (default encoding should be UTF-8)
	function wpfib_string_encode($string)
	{
		global $wpfib_options;
		
		$encoding = mb_detect_encoding($string);

		if ($encoding != $wpfib_options['default_string_encoding'])
		{
			$string = mb_convert_encoding($string, $wpfib_options['default_string_encoding'], $encoding);
		}
		return $string;
	}

	// END OF STRING ENCODING FUNCTIONS ****************************

	// this function is reported in readfile() php.net page to bypass readfile() documented problems with large files
	function wpfib_readfile_chunked($filename, $retbytes = true) { 
	
		$chunksize = 1 * (1024 * 1024); // how many bytes per chunk 
		$buffer = ''; 
		$counter = 0; 
     
		$handle = fopen($filename, 'rb'); 
		if ($handle === FALSE)
		{ 
			return FALSE; 
		} 
	
		while (!feof($handle))
		{ 
			$buffer = fread($handle, $chunksize); 
			echo $buffer; 
			@ob_flush(); 
			@flush(); 

			if ($retbytes)
			{ 
				$counter += strlen($buffer); 
			} 
		}

		$status = fclose($handle); 
	
		if ($retbytes && $status)
		{
			return $counter; // return num. bytes delivered like readfile() does. 
		}

		return $status; 
	} 


	function wpfib_tools_restoreArchiveFilename($filename)
	{
		return preg_replace("/\s\(".__('Archived on')."\s\d{4}\-\d{2}\-\d{2}\s\d{2}\.\d{2}\.\d{2}\)/", "", $filename); 
	}

	function wpfib_makeForwardSlashes($url)
	{
		return str_replace("\\", "/", $url);
	}

	// this function is used to encrypt the absolute path when transferring it to upload.php as post
	// NOTE: THE CORRESPONDING DECRYPT FUNCTION IS IN UPLOAD.PHP
	//
	function wpfib_encrypt($string, $key) { 
		
		$result = ''; 
		
		for ($i = 0; $i < strlen($string); $i++) {

			$char = substr($string, $i, 1); 
			$keychar = substr($key, ($i % strlen($key)) - 1, 1); 
			$char = chr(ord($char) + ord($keychar)); 
			
			$result .= $char; 
		}

		return base64_encode($result); 
	}

	// function based on CroppedThumbnail() by seifer at loveletslive dot com and also on class by satanas147 at gmail dot com (php.net on imagecopyresampled)
	function wpfib_makeThumbnail($imgfile, $imgfile_ext, $dir, $thumbnail_width, $thumbnail_height)
	{
		global $wpfib_options;

		// check if at least GD version 1.8 is installed, otherwise do not create a thumbnail (return 0)
		if ($wpfib_options['DEBUG_enabled'])
		{
			//var_dump(gd_info()); // DEBUG option

			echo "DEFINED ? ".defined(GD_MAJOR_VERSION)."<br />";

			echo "<br />MAJ_VER = [".(@defined('GD_MAJOR_VERSION') ? GD_MAJOR_VERSION : 0)."]<br />";
			echo "MIN_VER = [".(@defined('GD_MINOR_VERSION') ? GD_MINOR_VERSION : 0)."]<br /><br />";
			echo "FILE = [".$dir.DS.$imgfile."]<br />";
			
		}
		if (!@defined('GD_MAJOR_VERSION') || GD_MAJOR_VERSION < 2 || (GD_MAJOR_VERSION == 1 && GD_MINOR_VERSION < 8))
		{
			return 0;
		}

		// if size is 0 return 0 (it means do not make a thumbnail)
		if (!$wpfib_options['thumbsize'] || !strcmp(wpfib_fileBaseName($dir), "JS_THUMBS"))
		{
			// remove all existing thumbnails if the current thumbsize is zero
			wpfib_remove_thumbnail_files($dir);
			return 0;
		}

		// also return 0 if thumbs folder cannot be created
		if (!is_dir($dir.DS."JS_THUMBS") && !($rc = @mkdir ($dir.DS."JS_THUMBS")))
		{
			if ($wpfib_options['DEBUG_enabled'])
	       	{
				echo "<br />[".$dir.DS."JS_THUMBS] cannot be created<br /><br />";
			}
			return 0;
		}

		// and also if extension is not available - update this part when new extensions are introdced
		$available_extensions = array("JPG", "JPEG", "GIF", "PNG");

		if(!in_array(strtoupper($imgfile_ext), $available_extensions))
		{
			return 0;
		}

		// if thumbnail file exists, do some checks before returning 1 (it means use current thumbnail)
		if (file_exists($dir.DS."JS_THUMBS".DS.$imgfile))
		{
			// check if thumbnail is newer than file and the image size is the same as the requested size (in this case return 1 as we don't need to make a new thumb)
			list($curthumbwidth, $curthumbheight) = getimagesize($dir.DS."JS_THUMBS".DS.$imgfile);
			if ((filemtime($dir.DS."JS_THUMBS".DS.$imgfile) >= filemtime($dir.DS.$imgfile)) && $thumbnail_width == $curthumbwidth && $thumbnail_height == $curthumbheight)
			{
				return 1;
			}
		}

		//getting the image dimensions
		list($width_orig, $height_orig) = getimagesize($dir.DS.$imgfile);

		// switch based on image type
		switch(strtoupper($imgfile_ext))
		{
			case "JPEG":
			case "JPG":
				$image_resource = imagecreatefromjpeg($dir.DS.$imgfile);
				break;

			case "GIF":
				$image_resource = imagecreatefromgif($dir.DS.$imgfile);
				break;

			case "PNG":
				$image_resource = imagecreatefrompng($dir.DS.$imgfile);
				break;
		}
		$ratio_orig = $width_orig / $height_orig;
    
		if ($thumbnail_width / $thumbnail_height > $ratio_orig)
		{
			$new_height = $thumbnail_width / $ratio_orig;
			$new_width = $thumbnail_width;
		}
		else
		{
			$new_width = $thumbnail_height * $ratio_orig;
			$new_height = $thumbnail_height;
		}
    
		$x_mid = $new_width / 2;  //horizontal middle
		$y_mid = $new_height / 2; //vertical middle
    
		$process = imagecreatetruecolor(round($new_width), round($new_height)); 

		imagecopyresampled($process, $image_resource, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
		$thumb = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
		imagecopyresampled($thumb, $process, 0, 0, ($x_mid-($thumbnail_width/2)), ($y_mid-($thumbnail_height/2)), $thumbnail_width, $thumbnail_height, $thumbnail_width, $thumbnail_height);

		imagejpeg($thumb, $dir.DS."JS_THUMBS".DS.$imgfile);

		imagedestroy($process);
		imagedestroy($image_resource);

		return 1;
	}

	function wpfib_remove_thumbnail_files($dir) {

		if (is_dir($dir.DS."JS_THUMBS"))
		{
			if ($dh = opendir($dir.DS."JS_THUMBS"))
			{
				while (($file = readdir($dh)) !== false)
				{
					if ($file == '.' || $file == '..')
					{
						continue;
					}
					@unlink($dir.DS."JS_THUMBS".DS.$file);
				}
				closedir($dh);
			}
			@rmdir($dir.DS."JS_THUMBS");
		}
	}

?>
