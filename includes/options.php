<?php
/*
 * Smallerik File Browser plugin - File containing the administration settings management
*/

define('WPFIB_LEVELS',			10);
define('WPFIB_FUNCTIONS',		10);

// add a menu for the options page
add_action('admin_menu', 'wpfib_create_menu');

// register and define the settings
add_action('admin_init', 'wpfib_admin_init');

// function to establish default values
function wpfib_add_defaults_callback()
{
//	echo "INSIDE ACTIVATION CALLBACK<br />";
	// defaults for general options
	$general_options = get_option('wpfib_general_options');
	
//    if(($general_options['reset_defaults_on_activation'] == 'on') || (!is_array($general_options)))
    if($general_options['reset_defaults_on_activation'] || !is_array($general_options))
	{
		// defaults for GENERAL OPTIONS
		$general_options_defaults = array(
		
			'reset_defaults_on_activation' => false,
			
			'repo' => '',
			'default_path' => 'wp-file-browser-top',
			'is_path_relative' => true,
			'default_path_override_enabled' => false
		);
		update_option('wpfib_general_options', $general_options_defaults);
		
		// defaults for LEVELS OPTIONS
		$levels_options_defaults = array(

			'level_1_name' => 'Guest_users_level',
			'level_2_name' => 'Registered_users_level',
			'level_3_name' => 'Special_users_level',
			'level_4_name' => 'Admin_users_level',
			'function_1_level_1' => true,
			'function_2_level_1' => false,
			'function_3_level_1' => false,
			'function_4_level_1' => false,
			'function_5_level_1' => false,
			'function_6_level_1' => false,
			'function_7_level_1' => false,
			'function_8_level_1' => false,
			'function_9_level_1' => false,
			'function_10_level_1' => false,
				
			'function_1_level_2' => true,
			'function_2_level_2' => true,
			'function_3_level_2' => false,
			'function_4_level_2' => false,
			'function_5_level_2' => false,
			'function_6_level_2' => false,
			'function_7_level_2' => false,
			'function_8_level_2' => false,
			'function_9_level_2' => false,
			'function_10_level_2' => false,
				
			'function_1_level_3' => true,
			'function_2_level_3' => true,
			'function_3_level_3' => true,
			'function_4_level_3' => false,
			'function_5_level_3' => false,
			'function_6_level_3' => true,
			'function_7_level_3' => true,
			'function_8_level_3' => true,
			'function_9_level_3' => true,
			'function_10_level_3' => true,
				
			'function_1_level_4' => true,
			'function_2_level_4' => true,
			'function_3_level_4' => true,
			'function_4_level_4' => true,
			'function_5_level_4' => true,
			'function_6_level_4' => true,
			'function_7_level_4' => true,
			'function_8_level_4' => true,
			'function_9_level_4' => true,
			'function_10_level_4' => true,
		);
		update_option('wpfib_levels_options', $levels_options_defaults);

		// defaults for PERMISSIONS OPTIONS
		$permissions_options_defaults = array(

			'level_1_users' => '',
			'level_2_users' => '',
			'level_3_users' => '',
			'level_4_users' => '1',
				
			'trusted_authors' => '1',

			// the following first, third and fourth values are the values of the first four 'level_n_name' keys of the wpfib_levels_options defaults
			'def_visitor_level' => 'Guest_users_level', // these names depend on the level names set above
			'def_visitor_level_strict' => true, // this forces visitor level to apply to userbound repos for visitors
			'def_registered_level' => 'Registered_users_level',
			'def_userbound_level' => 'Special_users_level',
			'def_userbound_level_strict' => true, // this forces userbound level on top of custom users settings for userbound repos
			
			'allow_unzip' => true,
			'allow_file_archiving' => true,
		);
		update_option('wpfib_permissions_options', $permissions_options_defaults);
		
		// defaults for DISPLAY OPTIONS
		$display_options_defaults = array(
			
			// general
			'hidden_files' => '.htaccess .ftpquota *.php index.html .DS_Store',
			'hidden_folders' => '',
			'type_of_link_to_files' => LINK_THROUGH_SCRIPT,
			'display_navigation' => true,
			'display_file_filter' => true,
			'file_filter_width' => '220',
				
			// file extensions, size and date/time
			'display_file_ext' => true,
			'display_filesize' => true,
			'filesize_separator' => '.',
			'display_filedate' => true,
			'date_format' => 'dd_mm_yyyy_slashsep', // TODO use default blog format
			'display_filetime' => true,
			'display_seconds' => true,
				
			// sorting
			'sort_by' =>	SORT_BY_NAME,
			'sort_as' =>	SORT_ASCENDING,
			'sort_nat'=>	true
		);
		update_option('wpfib_display_options', $display_options_defaults);
		
		// defaults for LOOK & FEEL SETTINGS
		$looks_options_defaults = array(
			
			// general (apply to all boxes)
			'table_width' =>	600,
			'border_radius' => '5',
			'use_box_shadow' => true,
			'box_shadow_width' => '3',
			'box_shadow_blur' => '5',
			'box_shadow_color' => '100',
		
			'use_default_font_size' => false,
			'font_size' => '13',

			'box_distance' => '10',

			// file/folder list params
			'header_bgcolor' => 'FFF',

			'icon_width' => '36',
			'icon_padding' => '12',

			'thumbsize'=>	60,
			'use_thumb_shadow' => true,
			'thumb_shadow_width' => '2',
			'thumb_shadow_blur' => '3',
			'thumb_shadow_color' => '100',
		
			'min_row_height' => '40',
			'highlighted_color' => 'FFD',
			'oddrows_color' => 'F9F9F9',
			'evenrows_color' => 'FFF',

			'line_bgcolor' => 'CDD2D6',
			'line_height' => '1',

			// boxes colors and outlines
			'framebox_bgcolor' => 'FFF',
			'framebox_border' => '1',
			'framebox_linetype' => 'solid',
			'framebox_linecolor' => 'CDD2D6',
		
			'errorbox_bgcolor' => 'FFE4E1',
			'errorbox_border' => '1',
			'errorbox_linetype' => 'solid',
			'errorbox_linecolor' => 'F8A097',
		
			'successbox_bgcolor' => 'E7F6DC',
			'successbox_border' => '1',
			'successbox_linetype' => 'solid',
			'successbox_linecolor' => '66B42D',
		
			// input styles
			'inputbox_bgcolor' => 'FFF',
			'inputbox_border' => '1',
			'inputbox_linetype' => 'solid',
			'inputbox_linecolor' => 'CDD2D6',
		);
		update_option('wpfib_looks_options', $looks_options_defaults);

		// defaults for LOOK & FEEL SETTINGS
		$blog_charset = get_option('blog_charset');
		$advanced_options_defaults = array(
			
			'userbound_dir_prefix' => __('Personal area for user ', 'wpfib'),
			'userbound_dir_params' => USERBOUND_PARAMETER_ID_LOGIN,
			'userbound_dir_suffix' => '',
		
			'default_string_encoding' => $blog_charset,//get_option('blog_charset'),//'UTF-8',
		
			'default_file_chmod' => '0664',
			'default_dir_chmod' => '0775',
		
			'DEBUG_enabled' => false
		);
		update_option('wpfib_advanced_options', $advanced_options_defaults);
	}
}

function wpfib_create_menu()
{
//	add_options_page('WPFib - File Browser', 'WPFib', 'manage_options', 'wpfib', 'wpfib_option_page');
//	add_menu_page('Smallerik File Browser', 'Smallerik FiB', 'manage_options', 'wpfib-menu', 'wpfib_general_page_callback', 
//			plugins_url('folder16.png', __FILE__));
	// we use 'div' in place of icon image as per http://wpengineer.com/475/top-level-menu-in-wordpress-27/
	// so that we use a sprite in css file enqueued at the end of this function
	add_menu_page('Smallerik File Browser', 'Smallerik FiB', 'manage_options', 'wpfib-menu', 'wpfib_general_page_callback', 'div');
	
	// note: the first submenu's slug is the same as the menu slug. This stops the top menu from creating an automatic submenu
	add_submenu_page('wpfib-menu', 'Smallerik File Browser - General Settings', 'General Options', 'manage_options', 'wpfib-menu', 'wpfib_general_page_callback');
	add_submenu_page('wpfib-menu', 'Smallerik File Browser - Access Level Settings', 'Access Levels', 'manage_options', 'wpfib-submenu-levels', 'wpfib_levels_page_callback');
	add_submenu_page('wpfib-menu', 'Smallerik File Browser - Permission Settings', 'Permissions', 'manage_options', 'wpfib-submenu-permissions', 'wpfib_permissions_page_callback');
	add_submenu_page('wpfib-menu', 'Smallerik File Browser - Display Options', 'Display Options', 'manage_options', 'wpfib-submenu-display', 'wpfib_display_page_callback');
	add_submenu_page('wpfib-menu', 'Smallerik File Browser - Look & Feel Settings', 'Look & Feel', 'manage_options', 'wpfib-submenu-looks', 'wpfib_looks_page_callback');
	add_submenu_page('wpfib-menu', 'Smallerik File Browser - Advanced Settings', 'Advanced Options', 'manage_options', 'wpfib-submenu-advanced', 'wpfib_advanced_page_callback');
	add_submenu_page('wpfib-menu', 'Smallerik File Browser - QuickStart', '&gt;&nbsp;<strong>QuickStart</strong>', 'manage_options', 'wpfib-submenu-about', 'wpfib_about_page_callback');
	
	wp_enqueue_style( 'wpfib_css', plugins_url( $path = '/wpfib/css/menu_icon_style.css'), array() );	
}

// this function blocks access to non-trusted authors accessing the backend parameters
function wpfib_user_unauthorized(&$text)
{
	$permissions_options = get_option('wpfib_permissions_options');
	if (empty($permissions_options['trusted_authors']))
	{
		return false; // means user authorized
	}
	
	// get trusted authors list
	$trusted_authors_list = preg_split("/[\s,]+/", $permissions_options['trusted_authors']);

	// check user data
	$user_data = get_userdata(get_current_user_id());
	
	if (in_array($user_data->ID, $trusted_authors_list) || in_array($user_data->user_login, $trusted_authors_list))
	{
		return false;
	}
	else
	{
		$text = "<br /><br /><h2 style='color:#FF8800'>You are not allowed to edit these settings</h2>"
				."<p>You should be listed in the Permissions -&gt; <strong>Trusted Authors</strong> option to access this functionality</p>"
				."<br /><p>Current settings = [".$permissions_options['trusted_authors']."]</p>";
		return true;
	}
}

// this function is used by wpfib_permissions_options_validate_callback() to avoid locking the current user out of
// the trusted authors list (which would also lock the current user out of the wpfib backend parameters altogether)
function wpfib_is_current_user_locked_out_of_trusted_authors_list($trusted_authors)
{
	if (!strlen($trusted_authors))
	{
		return false; // means OK
	}
	
	// get trusted authors list
	$trusted_authors_list = preg_split("/[\s,]+/", $trusted_authors);

	// check user data
	$user_data = get_userdata(get_current_user_id());
	
	if (in_array($user_data->ID, $trusted_authors_list) || in_array($user_data->user_login, $trusted_authors_list))
	{
		return false;
	}
	else
	{
		return true;
	}
}

function wpfib_general_page_callback()
{
	?>
	<div class='wrap'>
		<div id="icon-edit_pages" class="icon32"><img src="<?php echo plugins_url().'/wpfib/media/admin/folder32.png'; ?>" /></div>
		<!--?php screen_icon('plugins'); ?-->
		<h2>Smallerik File Browser</h2>
		<?php
			$unauthorized_text = "";
			if (wpfib_user_unauthorized($unauthorized_text))
			{
				echo $unauthorized_text."</div>";
				return;
			}
		?>
		<form action='options.php' method='post'>
			<?php settings_fields('wpfib_general_options'); ?>
			<?php do_settings_sections('wpfib-menu'); ?>
			<br />
		<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function wpfib_levels_page_callback()
{
	?>
	<div class='wrap'>
		<div id="icon-edit_pages" class="icon32"><img src="<?php echo plugins_url().'/wpfib/media/admin/folder32.png'; ?>" /></div>
		<!--?php screen_icon('plugins'); ?-->
		<h2>Smallerik File Browser</h2>
		<?php
			$unauthorized_text = "";
			if (wpfib_user_unauthorized($unauthorized_text))
			{
				echo $unauthorized_text."</div>";
				return;
			}
		?>
		<form action='options.php' method='post'>
			<?php settings_fields('wpfib_levels_options'); ?>
			<?php do_settings_sections('wpfib-submenu-levels'); ?>
			<br />
		<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function wpfib_permissions_page_callback()
{
	?>
	<div class='wrap'>
		<div id="icon-edit_pages" class="icon32"><img src="<?php echo plugins_url().'/wpfib/media/admin/folder32.png'; ?>" /></div>
		<!--?php screen_icon('plugins'); ?-->
		<h2>Smallerik File Browser</h2>
		<?php
			$unauthorized_text = "";
			if (wpfib_user_unauthorized($unauthorized_text))
			{
				echo $unauthorized_text."</div>";
				return;
			}
		?>
		<form action='options.php' method='post'>
			<?php settings_fields('wpfib_permissions_options'); ?>
			<?php do_settings_sections('wpfib-submenu-permissions'); ?>
			<br />
		<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function wpfib_display_page_callback()
{
	?>
	<div class='wrap'>
		<div id="icon-edit_pages" class="icon32"><img src="<?php echo plugins_url().'/wpfib/media/admin/folder32.png'; ?>" /></div>
		<!--?php screen_icon('plugins'); ?-->
		<h2>Smallerik File Browser</h2>
		<?php
			$unauthorized_text = "";
			if (wpfib_user_unauthorized($unauthorized_text))
			{
				echo $unauthorized_text."</div>";
				return;
			}
		?>
		<form action='options.php' method='post'>
			<?php settings_fields('wpfib_display_options'); ?>
			<?php do_settings_sections('wpfib-submenu-display'); ?>
			<br />
		<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function wpfib_looks_page_callback()
{
	?>
	<div class='wrap'>
		<div id="icon-edit_pages" class="icon32"><img src="<?php echo plugins_url().'/wpfib/media/admin/folder32.png'; ?>" /></div>
		<!--?php screen_icon('plugins'); ?-->
		<h2>Smallerik File Browser</h2>
		<?php
			$unauthorized_text = "";
			if (wpfib_user_unauthorized($unauthorized_text))
			{
				echo $unauthorized_text."</div>";
				return;
			}
		?>
		<form action='options.php' method='post'>
			<?php settings_fields('wpfib_looks_options'); ?>
			<?php do_settings_sections('wpfib-submenu-looks'); ?>
			<br />
		<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function wpfib_advanced_page_callback()
{
	?>
	<div class='wrap'>
		<div id="icon-edit_pages" class="icon32"><img src="<?php echo plugins_url().'/wpfib/media/admin/folder32.png'; ?>" /></div>
		<!--?php screen_icon('plugins'); ?-->
		<h2>Smallerik File Browser</h2>
		<?php
			$unauthorized_text = "";
			if (wpfib_user_unauthorized($unauthorized_text))
			{
				echo $unauthorized_text."</div>";
				return;
			}
		?>
		<form action='options.php' method='post'>
			<?php settings_fields('wpfib_advanced_options'); ?>
			<?php do_settings_sections('wpfib-submenu-advanced'); ?>
			<br />
		<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

function wpfib_about_page_callback()
{
	global $wpfib_version_number;
	?>
	<div class='wrap'>
		<div id="icon-edit_pages" class="icon32"><img src="<?php echo plugins_url().'/wpfib/media/admin/folder32.png'; ?>" /></div>
		<h2>Smallerik File Browser</h2>
		<!--form action='options.php' method='post'-->
			<!--?php settings_fields('wpfib_options'); ?-->
			<!--?php do_settings_sections('wpfib'); ?-->
			<br />
		<!--?php submit_button(); ?-->
		<!--/form-->
		<?php 
		$donation_upgrade_stuff = 'If you like this plugin and wish to support its development, please consider making a donation to Smallerik:<br /><br />'
						.'<div align="center">'
						.'<form action="https://www.paypal.com/cgi-bin/webscr" method="post">'
						.'<input type="hidden" name="cmd" value="_s-xclick">'
						.'<input type="hidden" name="hosted_button_id" value="Y9YQWWCLEC67G">'
						.'<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">'
						.'<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">'
						.'</form>'
						.'</div>'
						.'Alternatively, <strong>you may want to upgrade to the commercial version of the plugin. Check out the <a href="'.WPFIB_DEVELOPER_SITE.'" target="_blank">'
						.'current commercial version\'s features</a></strong>'
						.'<br /><br />';
		
		$about_text = __("<strong>The Smallerik File Browser plugin allows you to transform a normal WordPress page or post into a simple but fully functional "
			."file browser</strong>. Files and folders within specified repositories can be browsed and, depending on "
			."<a href=\"".admin_url()."admin.php?page=wpfib-submenu-permissions\">user-based access rights</a>, managed according to "
			."<a href=\"".admin_url()."admin.php?page=wpfib-submenu-levels\">customizable access levels</a>."
			."<br /><br />"
			."Once the plugin is installed and activated, <strong>all you need to do to see it in action is create a new post or page</strong> (or edit an "
			."existing one) <strong>and write inside the following command</strong> (technically, it is a shortcode):"
			."<div align='center'>"
			."<code>[wpfib]</code>"
			."</div><br />"
			."The post/page will now display the contents of the top level repository folder, as specified in the <a href=\"".admin_url()."admin.php?page=wpfib-menu\">"
			."General options</a>. "
			."<br /><br /><div align='center'><img src='".plugins_url()."/wpfib/media/admin/input-output.png' /></div><br />"
			."If you have left the default settings after activation, all files and folders of the repository will be stored inside a "
			."folder named <strong>wp-file-browser-top</strong> under your Wordpress installation. Smallerik File Browser will create this folder when first accessing "
			."the repository."
//Please notice that the repository will only be displayed when the article is viewed directly and not if this is displayed in a list of articles (such as when using a blog mode).
			."<br /><br />"
			."This shows you how easy it is to start using this plugin. However, there are a lot of features to explore. For example, <strong>if you want "
			."the same post or page to display a different repository for each user viewing it</strong>, you can add a command option specifying "
			."that the repository should be user-dependent. You achieve this by simply writing:"
			."<br /><br /><div align='center'>"
			."<code>[wpfib repo='USERBOUND']</code>"
			."</div><br />"
			."inside the post or page. This will create a separate folder for each user from the same post or page (in the filesystem, the actual directory "
			."name used, linked to the current user, is defined in the <a href=\"".admin_url()."admin.php?page=wpfib-submenu-advanced\">Advanced options</a>). "
			."As an example, this arrangement is useful if you intend to provide file access in a school website, where pupils can access their own repository "
			."where to upload homework and download corrections or other personal files. The teacher could access all areas by setting a repository to "
			."the top folder (above all personal areas). In a different context, a project team leader could share files with people involved in the project, "
			."perhaps even giving different access levels to each team member."
			."</div><br />"
			." You can easily customize the plugin behavior or appearance by modifying how to display files and folders (see the "
			."<a href=\"".admin_url()."admin.php?page=wpfib-submenu-display\">Display options</a>) or the overall look & feel of the repository "
			."(in the <a href=\"".admin_url()."admin.php?page=wpfib-submenu-looks\">Look & Feel Options</a>) parameters. "
			."<br /><br />"
			."Most parameters have an equivalent <strong>override option</strong>, so that you can <strong>modify one or more parameters' values "
			."on a specific repository</strong>. For example you can have a 'static' repository on one page and a user-dependent repository on "
			."another one. For all details on how to use individual functions based on backend parameters, including all available "
			."override options, please refer to the information displayed with each backend parameter. "
			."For more documentation please refer to the <a href='".WPFIB_DEVELOPER_SITE."' target='_blank'>developer site</a>."
			."<br /><br />"
			."Smallerik File Browser has been written by <a href='".WPFIB_DEVELOPER_SITE."' target='_blank'>Enrico Sandoli</a>. You are using version ".$wpfib_version_number.". "
			.$donation_upgrade_stuff	
			."<br />"
			."This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty "
			."of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the <a href='http://www.gnu.org/licenses' target='_blank'>GNU General Public "
			."License</a> for more details."
			."<br /><br />"
			."Copyright © 2012-2014  Enrico Sandoli", "wpfib");
		
		echo "<p>".$about_text."</p>";
		echo "</div>";
}

function wpfib_admin_init()
{
	// SECTION : GENERAL SETTINGS
	
	register_setting('wpfib_general_options', 'wpfib_general_options', 'wpfib_general_options_validate_callback');
	
	add_settings_section('wpfib_general_section', '<br />General Options', 'wpfib_general_section_text', 'wpfib-menu');
	
	add_settings_field(
		'default_path',
		__('Default path', 'wpfib'),
		'default_path_input',
		'wpfib-menu',
		'wpfib_general_section',
		array(
			'&nbsp;&nbsp;'.__('This is the default path under which all repository folders (if specified) will be located. '
				.'This parameter cannot be overriden using command options, unless the <strong>Enable default path override</strong> parameter is enabled. '
				.'In this case, this parameter is overriden with the command options <code>abspath=\'<em>full-absolute-path</em>\'</code> or <code>relpath=\'<em>path-relative-to-WP-root</em>\'</code> '
				.'for absolute or relative paths respectively. This path is never displayed or sent over the network.', 'wpfib')
		)
	);
	
	add_settings_field(
		'is_path_relative',
		__('Default path is relative to WP root', 'wpfib'),
		'is_path_relative_input',
		'wpfib-menu',
		'wpfib_general_section',
		array(
			'&nbsp;&nbsp;'.__('If checked, this parameter indicates that the above <strong>Default path</strong> is to be intended as relative to the Wordpress '
				.'root folder. If left unchecked, you\'ll need to specify the default path in absolute terms (as a full path). '
				.'Setting the default path outside of the web root altogether increases security as the files will not be reachable '
				.'using a web browser outside the scope of this plugin.', 'wpfib')
		)
	);
	
	add_settings_field(
		'default_path_override_enabled',
		__('Enable default path override', 'wpfib'),
		'default_path_override_enabled_input',
		'wpfib-menu',
		'wpfib_general_section',
		array(
			'&nbsp;&nbsp;'.__('If checked, this parameter indicates that the above <strong>Default path</strong> can be overriden from the front-end, using '
				.'the command options <code>abspath=\'<em>full-absolute-path</em>\'</code> or <code>relpath=\'<em>path-relative-to-WP-root</em>\'</code> for absolute or relative paths '
				.'respectively.', 'wpfib')
		)
	);
	
	add_settings_field(
		'repo',
		__('Repository folder', 'wpfib'),
		'repo_input',
		'wpfib-menu',
		'wpfib_general_section',
		array(
			'&nbsp;&nbsp;'.__('This is the default repository name (a folder inside the <strong>Default path</strong>).<br /><br />A typical '
				.'configuration would be to leave this parameter blank (meaning that all files will reside '
				.'inside the <strong>Default path</strong> by default), so that specific repositories would override this settings by '
				.'adding the option <code>repo=\'<em>repo-folder-name</em>\'</code> in the front-end [wpfib] command. '
				.'This allows different pages or posts to display different repositories.<br /><br />Specifying the keyword <strong>USERBOUND</strong> '
				.'(typically as a command option <code>repo=\'USERBOUND\'</code>) will display a user-dependant repository, '
				.'that is a different repository for each user from the same page. These <em>Personal repositories</em> will reside '
				.'in user-linked folders inside the <strong>Default path</strong>. These folders are named according to what specified in the '
				.'<a href="'.admin_url().'admin.php?page=wpfib-submenu-advanced">Advanced Options</a>, where you can edit these details.', 'wpfib')
		)
	);

	add_settings_field(
		'reset_defaults_on_activation',
		'<strong>'.__('Restore all defaults on activation', 'wpfib').'</strong>',
		'reset_defaults_on_activation_input',
		'wpfib-menu',
		'wpfib_general_section',
		array(
			'&nbsp;&nbsp;<strong>'.__('If checked, re-activating the plugin will restore the default values for all the backend parameters.', 'wpfib').'</strong>'
		)
	);

	// SECTION : LEVELS SETTINGS
	
	register_setting('wpfib_levels_options', 'wpfib_levels_options', 'wpfib_levels_options_validate_callback');
	
	add_settings_section('wpfib_levels_section', '<br />Access Levels Settings', 'wpfib_levels_section_text', 'wpfib-submenu-levels');

	// set function name array
	$function_names = array(
		__('Can display the repository', 'wpfib'),
		__('Can download files', 'wpfib'),
		__('Can upload files', 'wpfib'),
		__('Can delete folders', 'wpfib'),
		__('Can delete files', 'wpfib'),
		__('Can restore files', 'wpfib'),
		__('Can create folders', 'wpfib'),
		__('Can rename files', 'wpfib'),
		__('Can rename folders', 'wpfib'),
		__('Can unzip files', 'wpfib')
	);
	
	$levels_options = get_option('wpfib_levels_options');

	for ($level = 0; $level < WPFIB_LEVELS; $level++)
	{
		if ($level && empty($levels_options['level_'.$level.'_name']))
			continue;
		
		// settings for level name
		add_settings_field(
			'level_'.($level + 1).'_name',
			'<strong>'.__('Level name', 'wpfib').'</strong>',
			'level_name_input',
			'wpfib-submenu-levels',
			'wpfib_levels_section',
			array('level_'.($level + 1).'_name',
					__('This name can be used in the permissions settings and in command options (set it blank to disable it)', 'wpfib'),
					__('<span style=\'color:#ff8800\'>Enable a new access level by setting its name</span>', 'wpfib')
			)
		);
				
		for ($function = 0; $function < WPFIB_FUNCTIONS; $function++)
		{
			// settings for each piece of functionality
			add_settings_field(
				'function_'.($function + 1).'_level_'.($level + 1).'',
				'',
				'function_level_input',
				'wpfib-submenu-levels',
				'wpfib_levels_section',
				array('function_'.($function + 1).'_level_'.($level + 1), $function_names[$function], $function, $level)
			);
		}
		
	}

	// SECTION : PERMISSIONS SETTINGS
	
	register_setting('wpfib_permissions_options', 'wpfib_permissions_options', 'wpfib_permissions_options_validate_callback');
	
	add_settings_section('wpfib_permissions_section_trusted', '<br />Trusted Authors', 'wpfib_permissions_section_trusted_text', 'wpfib-submenu-permissions');
	add_settings_section('wpfib_permissions_section_default', '<br />Default Users Permissions', 'wpfib_permissions_section_default_text', 'wpfib-submenu-permissions');
	add_settings_section('wpfib_permissions_section_custom', '<br />Custom Users Permissions', 'wpfib_permissions_section_custom_text', 'wpfib-submenu-permissions');
	add_settings_section('wpfib_permissions_section_other', '<br />Other Permissions', 'wpfib_permissions_section_other_text', 'wpfib-submenu-permissions');

	add_settings_field(
		'trusted_authors',
		__('Trusted authors', 'wpfib'),
		'trusted_authors_input',
		'wpfib-submenu-permissions',
		'wpfib_permissions_section_trusted',
		array(
			__('Enter a space-separated list of user IDs or login names<br /><strong>Note you cannot remove yourself from a non-empty list</strong>', 'wpfib')
		)
	);
	
	$level_options = get_option('wpfib_levels_options');

	for ($level = 0; $level < WPFIB_LEVELS; $level++)
	{
		if (empty($level_options['level_'.($level + 1).'_name']))
			continue;
		
		// settings for level name
		add_settings_field(
			'level_'.($level + 1).'_users',
			 sprintf(__('Users on level %s', 'wpfib'), '<strong>'.$level_options['level_'.($level + 1).'_name'].'</strong>'),
			'level_users_input',
			'wpfib-submenu-permissions',
			'wpfib_permissions_section_custom',
			array('level_'.($level + 1).'_users',
				__('Enter a space-separated list of user IDs or login names', 'wpfib')
			)
		);
	}

	add_settings_field(
		'def_visitor_level',
		__('Visitors\'s level', 'wpfib'),
		'def_visitor_level_input',
		'wpfib-submenu-permissions',
		'wpfib_permissions_section_default',
		array(
			__('Set the required access level', 'wpfib')
		)
	);

	add_settings_field(
		'def_registered_level',
		__('Registered users\' level', 'wpfib'),
		'def_registered_level_input',
		'wpfib-submenu-permissions',
		'wpfib_permissions_section_default',
		array(
			__('Set the required access level', 'wpfib')
		)
	);

	add_settings_field(
		'def_userbound_level',
		__('Personal repositories\'s level', 'wpfib'),
		'def_userbound_level_input',
		'wpfib-submenu-permissions',
		'wpfib_permissions_section_default',
		array(
			__('Set the required access level for users accessing their own personal repositories', 'wpfib')
		)
	);

	add_settings_field(
		'def_userbound_level_strict',
		__('Personal repositories\'s level priority	', 'wpfib'),
		'def_userbound_level_strict_input',
		'wpfib-submenu-permissions',
		'wpfib_permissions_section_default',
		array(
			__('Apply personal repositories\'s level in preference to any custom users settings (settings below) when displaying a personal repo. Use <code>def_userbound_level_strict=\'1\'</code> (or <code>\'0\'</code>) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);

	add_settings_field(
		'def_visitor_level_strict',
		__('Visitors in a personal repository', 'wpfib'),
		'def_visitor_level_strict_input',
		'wpfib-submenu-permissions',
		'wpfib_permissions_section_default',
		array(
			__('Apply visitors\'s level to non-registered users when accessing a personal repository. Use <code>def_visitor_level_strict=\'1\'</code> (or <code>\'0\'</code>) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);

	add_settings_field(
		'allow_unzip',
		__('Allow file unzip', 'wpfib'),
		'allow_unzip_input',
		'wpfib-submenu-permissions',
		'wpfib_permissions_section_other',
		array(
			__('Allow ability to uncompress zipped files.', 'wpfib')
		)
	);

	add_settings_field(
		'allow_file_archiving',
		__('Allow file archiving', 'wpfib'),
		'allow_file_archiving_input',
		'wpfib-submenu-permissions',
		'wpfib_permissions_section_other',
		array(
			__('Allow ability to archive existing files when uploading new versions of the same files.', 'wpfib')
		)
	);

	// SECTION : DISPLAY OPTIONS
	
	register_setting('wpfib_display_options', 'wpfib_display_options');//, TODO 'wpfib_display_options_validate_callback');
	
	add_settings_section('wpfib_display_section_general', '<br />General Display Options', 'wpfib_display_section_general_text', 'wpfib-submenu-display');
	add_settings_section('wpfib_display_section_sizedate', '<br />File Extensions, Size and Date/Time', 'wpfib_display_section_sizedate_text', 'wpfib-submenu-display');
	add_settings_section('wpfib_display_section_sorting', '<br />Sorting Options', 'wpfib_display_section_sorting_text', 'wpfib-submenu-display');

	// display: general section fields
	add_settings_field(
		'hidden_files',
		__('Hidden files', 'wpfib'),
		'hidden_files_input',
		'wpfib-submenu-display',
		'wpfib_display_section_general',
		array(
			__('Enter a space-separated list of files that will not be displayed (can use *.ext)', 'wpfib')
		)
	);
	add_settings_field(
		'hidden_folders',
		__('Hidden folders', 'wpfib'),
		'hidden_folders_input',
		'wpfib-submenu-display',
		'wpfib_display_section_general',
		array(
			__('Enter a space-separated list of folders that will not be displayed in any repository', 'wpfib')
		)
	);
	add_settings_field(
		'type_of_link_to_files',
		__('Type of link to files', 'wpfib'),
		'type_of_link_to_files_input',
		'wpfib-submenu-display',
		'wpfib_display_section_general',
		array(
			__('Files are linked by default through a script: this allows repositories to be hosted outside the web root; if this is not required you may '
			.'choose to link files directly, either in the same or in a new browser window. You can override this default setting with '
			.'<code>type_of_link_to_files=\'<em>value</em>\'</code>, with <em>value</em> equal to 0, 1 or 2 for script, direct link in same '
			.'window or direct link to new browser window respectively.', 'wpfib')
		)
	);
	add_settings_field(
		'display_navigation',
		__('Display navigation bar', 'wpfib'),
		'display_navigation_input',
		'wpfib-submenu-display',
		'wpfib_display_section_general',
		array(
			__('Use <code>display_navigation=\'1\'</code> (or <code>\'0\'</code>) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'display_file_filter',
		__('Display file filter', 'wpfib'),
		'display_file_filter_input',
		'wpfib-submenu-display',
		'wpfib_display_section_general',
		array(
			__('Displays a form to filter the files displayed in the repository. Use <code>display_file_filter=\'1\'</code> (or <code>\'0\'</code>) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'file_filter_width',
		__('Width of filter input', 'wpfib'),
		'file_filter_width_input',
		'wpfib-submenu-display',
		'wpfib_display_section_general',
		array(
			__('Use <code>file_filter_width=\'<em>value</em>\'</code> with <em>value</em> in pixels (no \'px\' needed) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);

	// display: extensions, size and date section fields
	add_settings_field(
		'display_file_ext',
		__('Display file extensions', 'wpfib'),
		'display_file_ext_input',
		'wpfib-submenu-display',
		'wpfib_display_section_sizedate',
		array(
			__('Use <code>display_file_ext=\'1\'</code> (or <code>\'0\'</code>) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'display_filesize',
		__('Display file size', 'wpfib'),
		'display_filesize_input',
		'wpfib-submenu-display',
		'wpfib_display_section_sizedate',
		array(
			__('Use <code>display_filesize=\'1\'</code> (or <code>\'0\'</code>) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'filesize_separator',
		__('File size decimal separator', 'wpfib'),
		'filesize_separator_input',
		'wpfib-submenu-display',
		'wpfib_display_section_sizedate',
		array(
			__('Use <code>filesize_separator=\'<em>x</em>\'</code> (with <em>x</em> the separator character) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'display_filedate',
		__('Display file date/time', 'wpfib'),
		'display_filedate_input',
		'wpfib-submenu-display',
		'wpfib_display_section_sizedate',
		array(
			__('Use <code>display_filedate=\'1\'</code> (or <code>\'0\'</code>) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'date_format',
		__('Date format', 'wpfib'),
		'date_format_input',
		'wpfib-submenu-display',
		'wpfib_display_section_sizedate',
		array(
			__('Use <code>date_format=\'<em>value</em>\'</code> in the command line to override this option on a specific repository, '
			.'with admitted values being <code>dd_mm_yyyy_dashsep</code>, <code>dd_mm_yyyy_pointsep</code>, <code>dd_mm_yyyy_slashsep</code>, '
			.'<code>yyyy_mm_dd_dashsep</code>, <code>yyyy_mm_dd_pointsep</code>, <code>yyyy_mm_dd_slashsep</code>, <code>mm_dd_yyyy_dashsep</code>, '
			.'<code>mm_dd_yyyy_pointsep</code>, <code>mm_dd_yyyy_slashsep</code> for the respective types.', 'wpfib')
		)
	);
	add_settings_field(
		'display_filetime',
		__('Display file time', 'wpfib'),
		'display_filetime_input',
		'wpfib-submenu-display',
		'wpfib_display_section_sizedate',
		array(
			__('Use <code>display_filetime=\'1\'</code> (or <code>\'0\'</code>) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'display_seconds',
		__('Display seconds', 'wpfib'),
		'display_seconds_input',
		'wpfib-submenu-display',
		'wpfib_display_section_sizedate',
		array(
			__('Use <code>display_seconds=\'1\'</code> (or <code>\'0\'</code>) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);

	// display: sorting section fields
	add_settings_field(
		'sort_by',
		__('Default sort field', 'wpfib'),
		'sort_by_input',
		'wpfib-submenu-display',
		'wpfib_display_section_sorting',
		array(
			__('Use <code>sort_by=\'<em>value</em>\'</code> (with <em>value</em> equal to 1, 2 or 3 for sorting by file/folder name, '
			.'file size and file date/time respectively) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'sort_as',
		__('Default sort order', 'wpfib'),
		'sort_as_input',
		'wpfib-submenu-display',
		'wpfib_display_section_sorting',
		array(
			__('Use <code>sort_as=\'<em>value</em>\'</code> (with <em>value</em> equal to 1 or 2 for sorting in ascending or descending '
			.'order respectively) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'sort_nat',
		__('Enable natural sorting', 'wpfib'),
		'sort_nat_input',
		'wpfib-submenu-display',
		'wpfib_display_section_sorting',
		array(
			__('To enable sorting [1, 2, ..., 10, 11] instead of [1, 10, 11, 2, ... ]<br />Use <code>sort_nat=\'1\'</code> (or <code>\'0\'</code>) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);

	// SECTION : LOOKS OPTIONS
	
	register_setting('wpfib_looks_options', 'wpfib_looks_options');//, TODO 'wpfib_looks_options_validate_callback');
	
	add_settings_section('wpfib_looks_section_general', '<br />General Look and Feel Options', 'wpfib_looks_section_general_text', 'wpfib-submenu-looks');
	add_settings_section('wpfib_looks_section_filelist', '<br />File/Folder List Parameters', 'wpfib_looks_section_filelist_text', 'wpfib-submenu-looks');
	add_settings_section('wpfib_looks_section_boxes', '<br />Boxes Styling', 'wpfib_looks_section_boxes_text', 'wpfib-submenu-looks');
	add_settings_section('wpfib_looks_section_input', '<br />Input Fields Styles', 'wpfib_looks_section_input_text', 'wpfib-submenu-looks');

	// looks: general section fields
	add_settings_field(
		'table_width',
		__('Repository width', 'wpfib'),
		'table_width_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_general',
		array(
			__('Enter a width for the entire repository (no \'px\' needed). Use <code>table_width=\'<em>value</em>\'</code> '
			.'in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'border_radius',
		__('Box border radius', 'wpfib'),
		'border_radius_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_general',
		array(
			__('Enter a radius for the boxes used by the repository (no \'px\' needed). Leave blank for square boxes. '
			.'Use <code>border_radius=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'use_box_shadow',
		__('Use box shadow', 'wpfib'),
		'use_box_shadow_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_general',
		array(
			__('If checked, all boxes used will display a drop shadow, as defined by the following three parameters. '
			.'Use <code>use_box_shadow=\'1\'</code> (or <code>\'0\'</code>) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'box_shadow_width',
		__('Box shadow width', 'wpfib'),
		'box_shadow_width_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_general',
		array(
			__('Width of the box shadow in pixels (no \'px\' needed). Use <code>box_shadow_width=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'box_shadow_blur',
		__('Box shadow blur', 'wpfib'),
		'box_shadow_blur_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_general',
		array(
			__('Blur of the box shadow in pixels (no \'px\' needed). Use <code>box_shadow_blur=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'box_shadow_color',
		__('Box shadow lightness', 'wpfib'),
		'box_shadow_color_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_general',
		array(
			__('The lightness of the shadow, in a range 0-255 (0 is black, 255 is white). Use <code>box_shadow_color=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'use_default_font_size',
		__('Use default font size', 'wpfib'),
		'use_default_font_size_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_general',
		array(
			__('With this option checked the text size of the repository will match the one set in the template used. '
			.'Use <code>use_default_font_size=\'1\'</code> (or <code>\'0\'</code>) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'font_size',
		__('Font size', 'wpfib'),
		'font_size_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_general',
		array(
			__('The font size used in the repository, if not using the default template size (no \'px\' needed). '
			.'Use <code>font_size=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'box_distance',
		__('Distance between boxes', 'wpfib'),
		'box_distance_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_general',
		array(
			__('The distance, in pixels, between the boxes used in the repository (no \'px\' needed). '
			.'Use <code>box_distance=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);

	// looks: file/folder list section fields
	add_settings_field(
		'header_bgcolor',
		__('Header background color', 'wpfib'),
		'header_bgcolor_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_filelist',
		array(
			__('The RGB background color of the header (no \'#\' prefix needed). Use <code>header_bgcolor=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'icon_width',
		__('Icon width', 'wpfib'),
		'icon_width_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_filelist',
		array(
			__('The width of the icons representing files and folders (in pixels; no \'px\' needed). Use <code>icon_width=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'icon_padding',
		__('Icon padding', 'wpfib'),
		'icon_padding_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_filelist',
		array(
			__('The padding around the icons representing files and folders (in pixels; no \'px\' needed). Use <code>icon_padding=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'thumbsize',
		__('Thumbnail size', 'wpfib'),
		'thumbsize_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_filelist',
		array(
			__('The size of the thumbnails of image files (in pixels; no \'px\' needed). Leave empty or set to 0 (zero) for not using image thumbnails. '
			.'Use <code>icon_padding=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'use_thumb_shadow',
		__('Use thumbnail shadow', 'wpfib'),
		'use_thumb_shadow_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_filelist',
		array(
			__('If checked, all image thumbnails will display a drop shadow, as defined by the following three parameters. '
			.'Use <code>use_thumb_shadow=\'1\'</code> (or <code>\'0\'</code>) in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'thumb_shadow_width',
		__('Thumbnail shadow width', 'wpfib'),
		'thumb_shadow_width_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_filelist',
		array(
			__('Width of the thumbnail shadow in pixels (no \'px\' needed). Use <code>thumb_shadow_width=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'thumb_shadow_blur',
		__('Thumbnail shadow blur', 'wpfib'),
		'thumb_shadow_blur_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_filelist',
		array(
			__('Blur of the thumbnail shadow in pixels (no \'px\' needed). Use <code>thumb_shadow_blur=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'thumb_shadow_color',
		__('Thumbnail shadow lightness', 'wpfib'),
		'thumb_shadow_color_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_filelist',
		array(
			__('The lightness of the thumbnail shadow, in a range 0-255 (0 is black, 255 is white). Use <code>thumb_shadow_color=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'min_row_height',
		__('Minimum row height', 'wpfib'),
		'min_row_height_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_filelist',
		array(
			__('The minimum height of file/folder rows. The actual height also depends on the size of the thumbnails of image files or of the file/folder icons (in pixels; no \'px\' needed). '
			.'Use <code>min_row_height=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'highlighted_color',
		__('Highligthed line background color', 'wpfib'),
		'highlighted_color_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_filelist',
		array(
			__('The RGB background color of the file/folder rows when mouse hovering onto them (no \'#\' prefix needed). '
			.'Use <code>highlighted_color=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'oddrows_color',
		__('Background color of odd rows', 'wpfib'),
		'oddrows_color_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_filelist',
		array(
			__('The RGB background color of the odd rows in the file/folder list (no \'#\' prefix needed). '
			.'Use <code>oddrows_color=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'evenrows_color',
		__('Background color of even rows', 'wpfib'),
		'evenrows_color_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_filelist',
		array(
			__('The RGB background color of the even rows in the file/folder list (no \'#\' prefix needed). '
			.'Use <code>evenrows_color=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'line_bgcolor',
		__('Color of lines between rows', 'wpfib'),
		'line_bgcolor_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_filelist',
		array(
			__('The RGB background color of the lines between rows in the file/folder list (no \'#\' prefix needed). '
			.'Use <code>line_bgcolor=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'line_height',
		__('Color of lines between rows', 'wpfib'),
		'line_height_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_filelist',
		array(
			__('The height in pixels of the lines between rows in the file/folder list (no \'px\' needed). '
			.'Use <code>line_height=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);

	// looks: boxes colors and outlines section fields
	add_settings_field(
		'framebox_bgcolor',
		__('Main boxes background color', 'wpfib'),
		'framebox_bgcolor_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_boxes',
		array(
			__('The RGB background color of the boxes used in the repository for file/folder lists, archives, upload areas (no \'#\' prefix needed). '
			.'Use <code>framebox_bgcolor=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'framebox_border',
		__('Main boxes border width', 'wpfib'),
		'framebox_border_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_boxes',
		array(
			__('The border width in pixels of the boxes used in the repository for file/folder lists, archives, upload areas (no \'px\' needed). '
			.'Use <code>framebox_border=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'framebox_linetype',
		__('Main boxes border type', 'wpfib'),
		'framebox_linetype_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_boxes',
		array(
			__('The border type of the boxes used in the repository for file/folder lists, archives, upload areas. '
			.'Use <code>framebox_linetype=\'<em>value</em>\'</code> in the command line to override this option on a specific repository, '
			.'with admitted values being <code>solid</code>, <code>dotted</code>, <code>dashed</code>, <code>double</code>, '
			.'<code>groove</code>, <code>ridge</code>, <code>inset</code>, <code>outset</code> for the respective types.', 'wpfib')
		)
	);
	add_settings_field(
		'framebox_linecolor',
		__('Main boxes border color', 'wpfib'),
		'framebox_linecolor_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_boxes',
		array(
			__('The RGB border color of the boxes used in the repository for file/folder lists, archives, upload areas (no \'#\' prefix needed). '
			.'Use <code>framebox_linecolor=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'errorbox_bgcolor',
		__('Error boxes background color', 'wpfib'),
		'errorbox_bgcolor_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_boxes',
		array(
			__('The RGB background color of the boxes displaying errors or warnings (no \'#\' prefix needed). '
			.'Use <code>errorbox_bgcolor=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'errorbox_border',
		__('Error boxes border width', 'wpfib'),
		'errorbox_border_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_boxes',
		array(
			__('The border width in pixels of the boxes displaying errors or warnings (no \'px\' needed). '
			.'Use <code>errorbox_border=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'errorbox_linetype',
		__('Error boxes border type', 'wpfib'),
		'errorbox_linetype_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_boxes',
		array(
			__('The border type of the boxes displaying errors or warnings. '
			.'Use <code>errorbox_linetype=\'<em>value</em>\'</code> in the command line to override this option on a specific repository, '
			.'with admitted values being <code>solid</code>, <code>dotted</code>, <code>dashed</code>, <code>double</code>, '
			.'<code>groove</code>, <code>ridge</code>, <code>inset</code>, <code>outset</code> for the respective types.', 'wpfib')
		)
	);
	add_settings_field(
		'errorbox_linecolor',
		__('Error boxes border color', 'wpfib'),
		'errorbox_linecolor_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_boxes',
		array(
			__('The RGB border color of the boxes displaying errors or warnings (no \'#\' prefix needed). '
			.'Use <code>errorbox_linecolor=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'successbox_bgcolor',
		__('Success boxes background color', 'wpfib'),
		'successbox_bgcolor_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_boxes',
		array(
			__('The RGB background color of the boxes displaying successful completion of actions (no \'#\' prefix needed). '
			.'Use <code>successbox_bgcolor=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'successbox_border',
		__('Success boxes border width', 'wpfib'),
		'successbox_border_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_boxes',
		array(
			__('The border width in pixels of the boxes displaying successful completion of actions (no \'px\' needed). '
			.'Use <code>successbox_border=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'successbox_linetype',
		__('Success boxes border type', 'wpfib'),
		'successbox_linetype_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_boxes',
		array(
			__('The border type of the boxes displaying successful completion of actions. '
			.'Use <code>successbox_linetype=\'<em>value</em>\'</code> in the command line to override this option on a specific repository, '
			.'with admitted values being <code>solid</code>, <code>dotted</code>, <code>dashed</code>, <code>double</code>, '
			.'<code>groove</code>, <code>ridge</code>, <code>inset</code>, <code>outset</code> for the respective types.', 'wpfib')
		)
	);
	add_settings_field(
		'successbox_linecolor',
		__('Success boxes border color', 'wpfib'),
		'successbox_linecolor_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_boxes',
		array(
			__('The RGB border color of the boxes displaying successful completion of actions (no \'#\' prefix needed). '
			.'Use <code>successbox_linecolor=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);

	// looks: input styles section fields
	add_settings_field(
		'inputbox_bgcolor',
		__('Input boxes background color', 'wpfib'),
		'inputbox_bgcolor_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_input',
		array(
			__('The RGB background color of the input field boxes used (no \'#\' prefix needed). '
			.'Use <code>inputbox_bgcolor=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'inputbox_border',
		__('Input boxes border width', 'wpfib'),
		'inputbox_border_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_input',
		array(
			__('The border width in pixels of the input field boxes used (no \'px\' needed). '
			.'Use <code>inputbox_border=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);
	add_settings_field(
		'inputbox_linetype',
		__('Input boxes border type', 'wpfib'),
		'inputbox_linetype_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_input',
		array(
			__('The border type of the input field boxes used. '
			.'Use <code>inputbox_linetype=\'<em>value</em>\'</code> in the command line to override this option on a specific repository, '
			.'with admitted values being <code>solid</code>, <code>dotted</code>, <code>dashed</code>, <code>double</code>, '
			.'<code>groove</code>, <code>ridge</code>, <code>inset</code>, <code>outset</code> for the respective types.', 'wpfib')
		)
	);
	add_settings_field(
		'inputbox_linecolor',
		__('Input boxes border color', 'wpfib'),
		'inputbox_linecolor_input',
		'wpfib-submenu-looks',
		'wpfib_looks_section_input',
		array(
			__('The RGB border color of the input field boxes used (no \'#\' prefix needed). '
			.'Use <code>inputbox_linecolor=\'<em>value</em>\'</code> in the command line to override this option on a specific repository.', 'wpfib')
		)
	);

	// SECTION : ADVANCED OPTIONS
	
	register_setting('wpfib_advanced_options', 'wpfib_advanced_options');//, TODO 'wpfib_advanced_options_validate_callback');
	
	add_settings_section('wpfib_advanced_section', '<br />Advanced Options', 'wpfib_advanced_section_text', 'wpfib-submenu-advanced');

	add_settings_field(
		'userbound_dir_prefix',
		__('Personal area name prefix', 'wpfib'),
		'userbound_dir_prefix_input',
		'wpfib-submenu-advanced',
		'wpfib_advanced_section',
		array(
			__('Personal areas are represented by filesystem directories whose name is made up of two or three parts. This is the first part, '
			.'represented by a string of text.', 'wpfib')
		)
	);
	add_settings_field(
		'userbound_dir_params',
		__('Personal area user parameter(s)', 'wpfib'),
		'userbound_dir_params_input',
		'wpfib-submenu-advanced',
		'wpfib_advanced_section',
		array(
			__('The second part of the Personal area directory name is a user parameter or a combination of parameters.', 'wpfib')
		)
	);
	add_settings_field(
		'userbound_dir_suffix',
		__('Personal area name suffix', 'wpfib'),
		'userbound_dir_suffix_input',
		'wpfib-submenu-advanced',
		'wpfib_advanced_section',
		array(
			__('The third (optional) part of the Personal area directory name is a string of text to append to the directory name.', 'wpfib')
		)
	);
	add_settings_field(
		'default_string_encoding',
		__('Default character encoding', 'wpfib'),
		'default_string_encoding_input',
		'wpfib-submenu-advanced',
		'wpfib_advanced_section',
		array(
			__('Choose a default character encoding for the repository file and folder names.', 'wpfib')
		)
	);
	add_settings_field(
		'default_file_chmod',
		__('Default CHMOD for files', 'wpfib'),
		'default_file_chmod_input',
		'wpfib-submenu-advanced',
		'wpfib_advanced_section',
		array(
			__('Choose a default value for Unix-based permissions to use when saving files.', 'wpfib')
		)
	);
	add_settings_field(
		'default_dir_chmod',
		__('Default CHMOD for directories', 'wpfib'),
		'default_dir_chmod_input',
		'wpfib-submenu-advanced',
		'wpfib_advanced_section',
		array(
			__('Choose a default value for Unix-based permissions to use when creating directories.', 'wpfib')
		)
	);
	add_settings_field(
		'DEBUG_enabled',
		__('Enable plugin debug', 'wpfib'),
		'DEBUG_enabled_input',
		'wpfib-submenu-advanced',
		'wpfib_advanced_section',
		array(
			__('Enable debug of this plugin, by writing on screen values that may aid debugging.', 'wpfib')
		)
	);
}

// SECTION : GENERAL SETTINGS

// description of the section
function wpfib_general_section_text()
{
	echo "<p>".__('General options define parameters regulating the main path of the file repositories. '
			.'Please refer to the <strong><a href=\''.admin_url().DS.'admin.php?page=wpfib-submenu-about\'>QuickStart guide</a> '
			.'</strong>on the About page to see how to start using this plugin.', 'wpfib')."</p>";
}

// display and fill the form fields
function reset_defaults_on_activation_input($args)
{
	$options = get_option('wpfib_general_options');
	
	if($options['reset_defaults_on_activation']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='reset_defaults_on_activation' name='wpfib_general_options[reset_defaults_on_activation]' type='checkbox' />";
	
	echo "<label>".$args[0]."</label>";
}
function default_path_input($args)
{
	$options = get_option('wpfib_general_options');
	
	// echo the field
	echo "<input id='default_path' name='wpfib_general_options[default_path]' size='40' type='text' value='{$options['default_path']}' />";
	echo "<label>".$args[0]."</label>";
	
}
function is_path_relative_input($args)
{
	$options = get_option('wpfib_general_options');
	
	if($options['is_path_relative']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='is_path_relative' name='wpfib_general_options[is_path_relative]' type='checkbox' />";
	
	echo "<label>".$args[0]."</label>";
	
}
function repo_input($args)
{
	$options = get_option('wpfib_general_options');
	
	// echo the field
	echo "<input id='repo' name='wpfib_general_options[repo]' size='40' type='text' value='{$options['repo']}' />";
	echo "<label>".$args[0]."</label>";
}
function default_path_override_enabled_input($args)
{
	$options = get_option('wpfib_general_options');
	
	if($options['default_path_override_enabled']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='default_path_override_enabled' name='wpfib_general_options[default_path_override_enabled]' type='checkbox' />";
	
	echo "<label>".$args[0]."</label>";
}

// validate user input
function wpfib_general_options_validate_callback($input)
{
	$valid = array();

//	$valid['repo'] = preg_replace('/[^a-zA-Z]/', '', $input['repo']);
	$valid['reset_defaults_on_activation'] = $input['reset_defaults_on_activation'];
	$valid['default_path'] = rtrim(preg_replace('/\.\.(.*)/', '', $input['default_path']), '\\/');
	$valid['is_path_relative'] = $input['is_path_relative'];
	$valid['repo'] = trim(preg_replace('/\.\.(.*)/', '', $input['repo']), '\\/');
	$valid['default_path_override_enabled'] = $input['default_path_override_enabled'];
	
//	// some warning checks
//	if (1)//!strlen(trim($valid['default_path'])) && $valid['is_path_relative'] == '')
//	{
////		add_settings_error('options', 'wpfib_default_path_is_server_root_error', __('Warning! Leaving the default path blank and the relative path indication unchecked will get the repository to point to the server root as its top-level folder. Are you sure this is what you intend to do?', 'wpfib'), 'error');
//		add_settings_error('options', 'wpfib_default_path_is_server_root_error', 'Warning!', 'error');
//	}
	
	return $valid;
}

// SECTION : LEVELS SETTINGS

// description of the section
function wpfib_levels_section_text()
{
	echo "<p>".__('Each access level defines a set of capabilities. These levels are linked to users in the <a href=\''.admin_url().DS
		.'admin.php?page=wpfib-submenu-permissions\'>Permissions settings</a>, '
		.'thus defining what actions each user is allowed to perform on a repository. The Permissions settings define these permissions '
		.'for all repositories. However, individual repositories can override these settings using command options in the form '
		.'<code><em>level_name</em>=\'<em>&lt;space-separated list of user IDs or login names&gt;</em>\'</code> '
		.'(See <a href=\''.admin_url().DS.'admin.php?page=wpfib-submenu-permissions\'>Permissions settings</a> '
		.'for more details).<br /><br />Notice that <strong>level names should not contain spaces, quotation marks or slashes</strong> '
		.' (if found they will be removed)', 'wpfib')."</p>";
}

// for level names
function level_name_input($args)
{
	$options = get_option('wpfib_levels_options');
	
	// echo the field
	echo "<input id='".$args[0]."' name='wpfib_levels_options[".$args[0]."]' size='20' type='text' value='{$options[$args[0]]}' />";
	echo "<label>&nbsp;&nbsp;".(empty($options[$args[0]]) ? $args[2] : $args[1])."</label>";
}

// display and fill the form fields
function function_level_input($args)
{
	$options = get_option('wpfib_levels_options');
	
	// if it's the checkbox of the first function (display repository) then add script to toggle 'disabled' attribute to 
	// all other functions checkboxes (for the same level) if the first checkbox is unchecked
	if ($args[2] == 0)
	{
		echo "<script type='text/javascript'>"
			."	function toggleFunctionsCheckboxesForLevel_".($args[3] + 1)."() {\n"
			."		if (jQuery('#".$args[0]."').is(':checked')) {\n"
			."			jQuery('div.otherFunctionsForLevel_".($args[3] + 1)." :input').removeAttr('disabled');\n"
			."		}	\n"
			."		else {\n"
			."			jQuery('div.otherFunctionsForLevel_".($args[3] + 1)." :input').attr('disabled', true);\n"
			."		}	\n"
			."	}	\n"
			."</script>\n";
		
		$onchange = 'onchange="toggleFunctionsCheckboxesForLevel_'.($args[3] + 1).'();" ';
		$div_class = '';
		$disabled = '';
	}
	else
	{
		$onchange = '';
		$div_class = 'class=\'otherFunctionsForLevel_'.($args[3] + 1).'\'';
		$disabled = ($options['function_1_level_'.($args[3] + 1)] && !empty($options['level_'.($args[3] + 1).'_name'])) ? '' : 'disabled';
	}
	if($options[$args[0]] && !empty($options['level_'.($args[3] + 1).'_name'])) { $checked = ' checked="checked" '; }

	
	echo "<div ".$div_class." noid='div_".$args[0]."'><input ".$checked." ".$onchange." ".$disabled." id='".$args[0]."' name='wpfib_levels_options[".$args[0]."]' type='checkbox' />";
	echo "<label>&nbsp;&nbsp;".$args[1]."</label></div>";
}

// validation on level names (do not allow spaces or quotation marks)
function wpfib_levels_options_validate_callback($input)
{
	$valid = array();
	
	// by default we validate everything
	$valid = $input;
	
	// then we clean the level names
	for ($level = 0; $level < WPFIB_LEVELS; $level++)
	{
		if (!empty($input['level_'.($level + 1).'_name']))
			$valid['level_'.($level + 1).'_name'] = preg_replace ('/[\s\'\"\\/]*/', '', $input['level_'.($level + 1).'_name']);
	}
	
	return $valid;
}

// SECTION : PERMISSIONS SETTINGS

// description of the sections
function wpfib_permissions_section_trusted_text()
{
	echo "<p>".__('If you wish to only enable certain users, with the capability to write posts/pages, to also write repository '
			.'commands, you can define a space-separated list of user IDs or login names. These users will be <strong>the only ones allowed '
			.'to write commands (short codes) that generate repositories in post/pages</strong>.<br /><br /><strong>An empty list '
			.'means no restriction for users that can write posts and/or pages</strong>.<br /><br />Also notice that <strong>if a list is not '
			.'empty then users not explicitly listed will additionally be denied access to all backend settings</strong>. For '
			.'this reason, you are not allowed to remove yourself from a non-empty Trusted Authors list.', 'wpfib');
}
function wpfib_permissions_section_default_text()
{
	echo "<p>".__('In this subsection you define the default access levels for <strong>guests</strong> (non-registered users), '
		.'<strong>registered users</strong>, and for <strong>users accessing personal repositories</strong> (these are repositories whose '
		.'folder has been set to <strong>USERBOUND</strong> - see '
		.'<a href=\''.admin_url().DS.'admin.php?page=wpfib-menu\'>General settings</a> -&gt; Repository folder for more info). '
		.'Each access level is a set of capabilities, as defined in the <a href=\''.admin_url().DS.'admin.php?page=wpfib-submenu-levels\'>'
		.'Access levels settings</a>.'
		.'<br /><br />These parameters can be overriden by individual repositories by setting <code>def_visitor_level=\'<em>access-level-name</em>\'</code>, '
		.'<code>def_registered_level=\'<em>access-level-name</em>\'</code> and <code>def_userbound_level=\'<em>access-level-name</em>\'</code> '
		.'in the short-code command (inside a page/post) for non-registered, registered and <em>USERBOUND</em> users respectively<br /><br />'
		.'Please notice that only access levels listed in the <a href=\''.admin_url().DS.'admin.php?page=wpfib-submenu-levels\'>'
		.'Access levels settings</a> with non blank names will be used.', 'wpfib')."</p>";
}
function wpfib_permissions_section_custom_text()
{
	echo "<p>".__('In this subsection access levels can be linked to specific users, '
		.'thus defining what actions each user is allowed to perform on a repository. Here you define these permissions '
		.'for all repositories. However, individual repositories can override these settings using command options in the form '
		.'<code><em>level_name</em>=\'<em>&lt;space-separated list of user IDs or login names&gt;</em>\'</code>. '
		.'<br /><br />Please '
		.'notice that only access levels listed in the Access levels settings with non blank names can be used.', 'wpfib')."</p>";
}
function wpfib_permissions_section_other_text()
{
	echo "<p>".__('These parameters provide default abilities for all repositories; however their validity for a specific user ultimately depends on the access level that applies to that user.', 'wpfib')."</p>";
}

// for level names
function level_users_input($args)
{
	$options = get_option('wpfib_permissions_options');
	
	echo "<textarea id='".$args[0]."' name='wpfib_permissions_options[".$args[0]."]' rows='3' cols='40' type='textarea'>{$options[$args[0]]}</textarea>";
	echo "<label style='vertical-align:top'>&nbsp;&nbsp;".$args[1]."</label>";
}

// display and fill the form fields
function trusted_authors_input($args)
{
	$options = get_option('wpfib_permissions_options');
	
	echo "<textarea id='trusted_authors' name='wpfib_permissions_options[trusted_authors]' rows='3' cols='40' type='textarea'>{$options['trusted_authors']}</textarea>";
	echo "<label style='vertical-align:top'>&nbsp;&nbsp;".$args[0]."</label>";
}

// display and fill the form fields
function def_visitor_level_input($args)
{
	$levels_options = get_option('wpfib_levels_options');
	$permissions_options = get_option('wpfib_permissions_options');
	
	$items = array();
	for ($level = 0; $level < WPFIB_LEVELS; $level++)
	{
		if (empty($levels_options['level_'.($level + 1).'_name']))
			continue;
	
		$items[] = $levels_options['level_'.($level + 1).'_name'];
	}
	
	echo "<select id='def_visitor_level' name='wpfib_permissions_options[def_visitor_level]'>";
	
	foreach($items as $item)
	{
		$selected = ($permissions_options['def_visitor_level'] == $item) ? 'selected="selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
	
	echo "<label style='vertical-align:top'>&nbsp;&nbsp;".$args[0]."</label>";
}
function def_visitor_level_strict_input($args)
{
	$options = get_option('wpfib_permissions_options');
	
	if($options['def_visitor_level_strict']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='def_visitor_level_strict' name='wpfib_permissions_options[def_visitor_level_strict]' type='checkbox' />";
	echo "<label style='vertical-align:top'>&nbsp;&nbsp;".$args[0]."</label>";
}
function def_registered_level_input($args)
{
	$levels_options = get_option('wpfib_levels_options');
	$permissions_options = get_option('wpfib_permissions_options');
	
	$items = array();
	for ($level = 0; $level < WPFIB_LEVELS; $level++)
	{
		if (empty($levels_options['level_'.($level + 1).'_name']))
			continue;
	
		$items[] = $levels_options['level_'.($level + 1).'_name'];
	}
	
	echo "<select id='def_registered_level' name='wpfib_permissions_options[def_registered_level]'>";
	
	foreach($items as $item)
	{
		$selected = ($permissions_options['def_registered_level'] == $item) ? 'selected="selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
	
	echo "<label style='vertical-align:top'>&nbsp;&nbsp;".$args[0]."</label>";
}
function def_userbound_level_input($args)
{
	$levels_options = get_option('wpfib_levels_options');
	$permissions_options = get_option('wpfib_permissions_options');
	
	$items = array();
	for ($level = 0; $level < WPFIB_LEVELS; $level++)
	{
		if (empty($levels_options['level_'.($level + 1).'_name']))
			continue;
	
		$items[] = $levels_options['level_'.($level + 1).'_name'];
	}
	
	echo "<select id='def_userbound_level' name='wpfib_permissions_options[def_userbound_level]'>";
	
	foreach($items as $item)
	{
		$selected = ($permissions_options['def_userbound_level'] == $item) ? 'selected="selected"' : '';
		echo "<option value='$item' $selected>$item</option>";
	}
	echo "</select>";
	
	echo "<label style='vertical-align:top'>&nbsp;&nbsp;".$args[0]."</label>";
}
function def_userbound_level_strict_input($args)
{
	$options = get_option('wpfib_permissions_options');
	
	if($options['def_userbound_level_strict']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='def_userbound_level_strict' name='wpfib_permissions_options[def_userbound_level_strict]' type='checkbox' />";
	echo "<label style='vertical-align:top'>&nbsp;&nbsp;".$args[0]."</label>";
}
function allow_unzip_input($args)
{
	$options = get_option('wpfib_permissions_options');
	
	if($options['allow_unzip']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='allow_unzip' name='wpfib_permissions_options[allow_unzip]' type='checkbox' />";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function allow_file_archiving_input($args)
{
	$options = get_option('wpfib_permissions_options');
	
	if($options['allow_file_archiving']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='allow_file_archiving' name='wpfib_permissions_options[allow_file_archiving]' type='checkbox' />";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}

// validation on user lists :: this also blocks the current user from locking herself from the trusted users list
function wpfib_permissions_options_validate_callback($input)
{
	$valid = array();
	
	// by default we validate everything
	$valid = $input;
	
	// then we clean the trusted authors list
	$valid['trusted_authors'] = trim($input['trusted_authors']);
	
	// and the users lists for all non-blank levels
	for ($level = 0; $level < WPFIB_LEVELS; $level++)
	{
		if (!empty($input['level_'.($level + 1).'_users']))
			$valid['level_'.($level + 1).'_users'] = trim($input['level_'.($level + 1).'_users']);
	}

	// check if current user is no more in the trusted users list
	if (wpfib_is_current_user_locked_out_of_trusted_authors_list($valid['trusted_authors']))
	{
		$current_userdata = get_userdata(get_current_user_id());
		
		// add the current user to the list and display a warning
		$valid['trusted_authors'] .= ' '.$current_userdata->user_login;
		
		// TODO fix this
		add_settings_error(
				'wpfib_permissions_options',
				'trusted_authors_locked',
				__('You either have an empty Trusted Authors list, or you should be in the list, otherwise you will no longer be able to '
						.'access the Smallerik File Browser backend settings!'),
				'error'
		);
	}
	
	return $valid;
}

// SECTION : DISPLAY SETTINGS

// description of the sections
function wpfib_display_section_general_text()
{
	echo "<p>".__('These settings refer to files and folders that are not to be displayed in any repository, and provide '
			.'other options regulating what to display when clicking on a file or in general around the repository '
			.'(navigation bar and/or filter).', 'wpfib');
}
function wpfib_display_section_sizedate_text()
{
	echo "<p>".__('You can define here if and how to display file extensions as well as file size and date/time of last modification.', 'wpfib');
}
function wpfib_display_section_sorting_text()
{
	echo "<p>".__('Default options for sorting files and folders.', 'wpfib');
}

// fields
function hidden_files_input($args)
{
	$options = get_option('wpfib_display_options');
	
	echo "<textarea id='hidden_files' name='wpfib_display_options[hidden_files]' rows='3' cols='40' type='textarea'>{$options['hidden_files']}</textarea>";
	echo "<label style='vertical-align:top'>&nbsp;&nbsp;".$args[0]."</label>";
}
function hidden_folders_input($args)
{
	$options = get_option('wpfib_display_options');
	
	echo "<textarea id='hidden_folders' name='wpfib_display_options[hidden_folders]' rows='3' cols='40' type='textarea'>{$options['hidden_folders']}</textarea>";
	echo "<label style='vertical-align:top'>&nbsp;&nbsp;".$args[0]."</label>";
}
function type_of_link_to_files_input($args)
{
	$display_options = get_option('wpfib_display_options');
	
	$items = array(
		LINK_THROUGH_SCRIPT		=> __('Link through script', 'wpfib'),
		LINK_DIRECT_SAME_WINDOW	=> __('Link directly in the same window', 'wpfib'),
		LINK_DIRECT_NEW_WINDOW	=> __('Link directly in a new window', 'wpfib')
	);
	
	echo "<select id='type_of_link_to_files' name='wpfib_display_options[type_of_link_to_files]'>";

	foreach($items as $itemVal => $itemString)
	{
		$selected = ($display_options['type_of_link_to_files'] == $itemVal) ? 'selected="selected"' : '';
		echo "<option value='$itemVal' $selected>$itemString</option>";
	}
	echo "</select>";
	
	echo "<label style='vertical-align:top'><br /><br />".$args[0]."</label>";
}
function display_navigation_input($args)
{
	$options = get_option('wpfib_display_options');
	
	if($options['display_navigation']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='display_navigation' name='wpfib_display_options[display_navigation]' type='checkbox' />";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function display_file_filter_input($args)
{
	$options = get_option('wpfib_display_options');
	
	if($options['display_file_filter']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='display_file_filter' name='wpfib_display_options[display_file_filter]' type='checkbox' />";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function file_filter_width_input($args)
{
	$options = get_option('wpfib_display_options');
	
	// echo the field
	echo "<input id='file_filter_width' name='wpfib_display_options[file_filter_width]' size='5' type='text' value='{$options['file_filter_width']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}

function display_file_ext_input($args)
{
	$options = get_option('wpfib_display_options');
	
	if($options['display_file_ext']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='display_file_ext' name='wpfib_display_options[display_file_ext]' type='checkbox' />";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function display_filesize_input($args)
{
	$options = get_option('wpfib_display_options');
	
	if($options['display_filesize']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='display_filesize' name='wpfib_display_options[display_filesize]' type='checkbox' />";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function filesize_separator_input($args)
{
	$display_options = get_option('wpfib_display_options');
	
	$items = array(
		'.'	=> __('Use a dot', 'wpfib'),
		','	=> __('Use a comma', 'wpfib')
	);
	
	echo "<select id='filesize_separator' name='wpfib_display_options[filesize_separator]'>";

	foreach($items as $itemVal => $itemString)
	{
		$selected = ($display_options['filesize_separator'] == $itemVal) ? 'selected="selected"' : '';
		echo "<option value='$itemVal' $selected>$itemString</option>";
	}
	echo "</select>";
	
	echo "<label style='vertical-align:top'><br /><br />".$args[0]."</label>";
}
function display_filedate_input($args)
{
	$options = get_option('wpfib_display_options');

	// write jquery script to disable time display parameters if this is disabled
	echo "<script type='text/javascript'>"
		."	function toggleTimeDisplayParameters() {\n"
		."		if (jQuery('#display_filedate').is(':checked')) {\n"
		."			jQuery('span.timeDisplayParameters :input').removeAttr('disabled');\n"
		."		}	\n"
		."		else {\n"
		."			jQuery('span.timeDisplayParameters :input').attr('disabled', true);\n"
		."		}	\n"
		."	}	\n"
		."</script>\n";
		
	$onchange = 'onchange="toggleTimeDisplayParameters();" ';
	
	if($options['display_filedate']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." ".$onchange." id='display_filedate' name='wpfib_display_options[display_filedate]' type='checkbox' />";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function date_format_input($args)
{
	$options = get_option('wpfib_display_options');
	
	$items = array(

		'dd_mm_yyyy_dashsep'	=> 'dd-mm-yyyy',
		'dd_mm_yyyy_pointsep'	=> 'dd.mm.yyyy',
		'dd_mm_yyyy_slashsep'	=> 'dd/mm/yyyy',
		'yyyy_mm_dd_dashsep'	=> 'yyyy-mm-dd',
		'yyyy_mm_dd_pointsep'	=> 'yyyy.mm.dd',
		'yyyy_mm_dd_slashsep'	=> 'yyyy/mm/dd',
		'mm_dd_yyyy_dashsep'	=> 'mm-dd-yyyy',
		'mm_dd_yyyy_pointsep'	=> 'mm.dd.yyyy',
		'mm_dd_yyyy_slashsep'	=> 'mm/dd/yyyy'
	);

	$disabled = $options['display_filedate'] ? '' : 'disabled';
	
	echo "<span class='timeDisplayParameters'>";
	echo "<select id='date_format' name='wpfib_display_options[date_format]' ".$disabled.">";

	foreach($items as $itemVal => $itemString)
	{
		$selected = ($options['date_format'] == $itemVal) ? 'selected="selected"' : '';
		echo "<option value='$itemVal' $selected>$itemString</option>";
	}
	echo "</select>";
	echo "</span>";
	
	echo "<label style='vertical-align:top'><br /><br />".$args[0]."</label>";
}
function display_filetime_input($args)
{
	$options = get_option('wpfib_display_options');
	
	$disabled = $options['display_filedate'] ? '' : 'disabled';
	
	if($options['display_filetime']) { $checked = ' checked="checked" '; }
	
	echo "<span class='timeDisplayParameters'>";
	echo "<input ".$checked." id='display_filetime' name='wpfib_display_options[display_filetime]' type='checkbox' ".$disabled." />";
	echo "</span>";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function display_seconds_input($args)
{
	$options = get_option('wpfib_display_options');
	
	$disabled = $options['display_filedate'] ? '' : 'disabled';
	
	if($options['display_seconds']) { $checked = ' checked="checked" '; }

	echo "<span class='timeDisplayParameters'>";
	echo "<input ".$checked." id='display_seconds' name='wpfib_display_options[display_seconds]' type='checkbox' ".$disabled." />";
	echo "</span>";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}

function sort_by_input($args)
{
	$display_options = get_option('wpfib_display_options');
	
	$items = array(

		SORT_BY_NAME	=> __('Sort by file/folder name', 'wpfib'),
		SORT_BY_SIZE	=> __('Sort by file size', 'wpfib'),
		SORT_BY_CHANGED	=> __('Sort by file date/time', 'wpfib')
	);
	
	echo "<select id='sort_by' name='wpfib_display_options[sort_by]'>";

	foreach($items as $itemVal => $itemString)
	{
		$selected = ($display_options['sort_by'] == $itemVal) ? 'selected="selected"' : '';
		echo "<option value='$itemVal' $selected>$itemString</option>";
	}
	echo "</select>";
	
	echo "<label style='vertical-align:top'><br /><br />".$args[0]."</label>";
}
function sort_as_input($args)
{
	$display_options = get_option('wpfib_display_options');
	
	$items = array(

		SORT_ASCENDING	=> __('Sort in ascending order', 'wpfib'),
		SORT_DESCENDING	=> __('Sort in descending order', 'wpfib')
	);
	
	echo "<select id='sort_as' name='wpfib_display_options[sort_as]'>";

	foreach($items as $itemVal => $itemString)
	{
		$selected = ($display_options['sort_as'] == $itemVal) ? 'selected="selected"' : '';
		echo "<option value='$itemVal' $selected>$itemString</option>";
	}
	echo "</select>";
	
	echo "<label style='vertical-align:top'><br /><br />".$args[0]."</label>";
}
function sort_nat_input($args)
{
	$options = get_option('wpfib_display_options');
	
	if($options['sort_nat']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." id='sort_nat' name='wpfib_display_options[sort_nat]' type='checkbox' />";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}

// TODO add validation on display options

// SECTION : LOOK & FEEL SETTINGS

// description of the sections
function wpfib_looks_section_general_text()
{
	echo "<p>".__('General styling options for the entire repository and affecting all \'boxes\' of which the repository is made of '
			.'(file/folder list, archive, actions boxes).', 'wpfib');
}
function wpfib_looks_section_filelist_text()
{
	echo "<p>".__('Styling of elements in the file/folder lists.', 'wpfib');
}
function wpfib_looks_section_boxes_text()
{
	echo "<p>".__('Colors & backgrounds of the various types of box types used (standard, error/warning and success boxes).', 'wpfib');
}
function wpfib_looks_section_input_text()
{
	echo "<p>".__('Styles that apply to input boxes used.', 'wpfib');
}

// looks: general section fields
function table_width_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='table_width' name='wpfib_looks_options[table_width]' size='5' type='text' value='{$options['table_width']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function border_radius_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='border_radius' name='wpfib_looks_options[border_radius]' size='5' type='text' value='{$options['border_radius']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function use_box_shadow_input($args)
{
	$options = get_option('wpfib_looks_options');

	// write jquery script to disable time display parameters if this is disabled
	echo "<script type='text/javascript'>"
		."	function toggleBoxShadowParameters() {\n"
		."		if (jQuery('#use_box_shadow').is(':checked')) {\n"
		."			jQuery('span.boxShadowParameters :input').removeAttr('disabled');\n"
		."		}	\n"
		."		else {\n"
		."			jQuery('span.boxShadowParameters :input').attr('disabled', true);\n"
		."		}	\n"
		."	}	\n"
		."</script>\n";
		
	$onchange = 'onchange="toggleBoxShadowParameters();" ';
	
	if($options['use_box_shadow']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." ".$onchange." id='use_box_shadow' name='wpfib_looks_options[use_box_shadow]' type='checkbox' />";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function box_shadow_width_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	$disabled = $options['use_box_shadow'] ? '' : 'disabled';

	// echo the field
	echo "<span class='boxShadowParameters'>";
	echo "<input id='box_shadow_width' name='wpfib_looks_options[box_shadow_width]' size='5' type='text' value='{$options['box_shadow_width']}' ".$disabled." />";
	echo "</span>";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function box_shadow_blur_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	$disabled = $options['use_box_shadow'] ? '' : 'disabled';

	// echo the field
	echo "<span class='boxShadowParameters'>";
	echo "<input id='box_shadow_blur' name='wpfib_looks_options[box_shadow_blur]' size='5' type='text' value='{$options['box_shadow_blur']}' ".$disabled." />";
	echo "</span>";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function box_shadow_color_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	$disabled = $options['use_box_shadow'] ? '' : 'disabled';

	// echo the field
	echo "<span class='boxShadowParameters'>";
	echo "<input id='box_shadow_color' name='wpfib_looks_options[box_shadow_color]' size='5' type='text' value='{$options['box_shadow_color']}' ".$disabled." />";
	echo "</span>";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function use_default_font_size_input($args)
{
	$options = get_option('wpfib_looks_options');

	// write jquery script to disable time display parameters if this is disabled
	echo "<script type='text/javascript'>"
		."	function toggleFontSize() {\n"
		."		if (jQuery('#use_default_font_size').is(':checked')) {\n"
		."			jQuery('#font_size').attr('disabled', true);\n"
		."		}	\n"
		."		else {\n"
		."			jQuery('#font_size').removeAttr('disabled');\n"
		."		}	\n"
		."	}	\n"
		."</script>\n";
		
	$onchange = 'onchange="toggleFontSize();" ';
	
	if($options['use_default_font_size']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." ".$onchange." id='use_default_font_size' name='wpfib_looks_options[use_default_font_size]' type='checkbox' />";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function font_size_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	$disabled = $options['use_default_font_size'] ? 'disabled' : '';

	// echo the field
	echo "<input id='font_size' name='wpfib_looks_options[font_size]' size='5' type='text' value='{$options['font_size']}' ".$disabled." />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function box_distance_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='box_distance' name='wpfib_looks_options[box_distance]' size='5' type='text' value='{$options['box_distance']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}

// looks: file/folder list section fields
function header_bgcolor_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='header_bgcolor' name='wpfib_looks_options[header_bgcolor]' size='5' type='text' value='{$options['header_bgcolor']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function icon_width_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='icon_width' name='wpfib_looks_options[icon_width]' size='5' type='text' value='{$options['icon_width']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function icon_padding_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='icon_padding' name='wpfib_looks_options[icon_padding]' size='5' type='text' value='{$options['icon_padding']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function thumbsize_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='thumbsize' name='wpfib_looks_options[thumbsize]' size='5' type='text' value='{$options['thumbsize']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function use_thumb_shadow_input($args)
{
	$options = get_option('wpfib_looks_options');

	// write jquery script to disable time display parameters if this is disabled
	echo "<script type='text/javascript'>"
		."	function toggleThumbShadowParameters() {\n"
		."		if (jQuery('#use_thumb_shadow').is(':checked')) {\n"
		."			jQuery('span.thumbShadowParameters :input').removeAttr('disabled');\n"
		."		}	\n"
		."		else {\n"
		."			jQuery('span.thumbShadowParameters :input').attr('disabled', true);\n"
		."		}	\n"
		."	}	\n"
		."</script>\n";
		
	$onchange = 'onchange="toggleThumbShadowParameters();" ';
	
	if($options['use_thumb_shadow']) { $checked = ' checked="checked" '; }
	echo "<input ".$checked." ".$onchange." id='use_thumb_shadow' name='wpfib_looks_options[use_thumb_shadow]' type='checkbox' />";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function thumb_shadow_width_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	$disabled = $options['use_thumb_shadow'] ? '' : 'disabled';

	// echo the field
	echo "<span class='thumbShadowParameters'>";
	echo "<input id='thumb_shadow_width' name='wpfib_looks_options[thumb_shadow_width]' size='5' type='text' value='{$options['thumb_shadow_width']}' ".$disabled." />";
	echo "</span>";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function thumb_shadow_blur_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	$disabled = $options['use_thumb_shadow'] ? '' : 'disabled';

	// echo the field
	echo "<span class='thumbShadowParameters'>";
	echo "<input id='thumb_shadow_blur' name='wpfib_looks_options[thumb_shadow_blur]' size='5' type='text' value='{$options['thumb_shadow_blur']}' ".$disabled." />";
	echo "</span>";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function thumb_shadow_color_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	$disabled = $options['use_thumb_shadow'] ? '' : 'disabled';

	// echo the field
	echo "<span class='thumbShadowParameters'>";
	echo "<input id='thumb_shadow_color' name='wpfib_looks_options[thumb_shadow_color]' size='5' type='text' value='{$options['thumb_shadow_color']}' ".$disabled." />";
	echo "</span>";
	
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function min_row_height_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='min_row_height' name='wpfib_looks_options[min_row_height]' size='5' type='text' value='{$options['min_row_height']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function highlighted_color_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='highlighted_color' name='wpfib_looks_options[highlighted_color]' size='5' type='text' value='{$options['highlighted_color']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function oddrows_color_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='oddrows_color' name='wpfib_looks_options[oddrows_color]' size='5' type='text' value='{$options['oddrows_color']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function evenrows_color_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='evenrows_color' name='wpfib_looks_options[evenrows_color]' size='5' type='text' value='{$options['evenrows_color']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function line_bgcolor_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='line_bgcolor' name='wpfib_looks_options[line_bgcolor]' size='5' type='text' value='{$options['line_bgcolor']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function line_height_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='line_height' name='wpfib_looks_options[line_height]' size='5' type='text' value='{$options['line_height']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}

// looks: boxes colors and outlines section fields
function framebox_bgcolor_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='framebox_bgcolor' name='wpfib_looks_options[framebox_bgcolor]' size='5' type='text' value='{$options['framebox_bgcolor']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function framebox_border_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='framebox_border' name='wpfib_looks_options[framebox_border]' size='5' type='text' value='{$options['framebox_border']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function framebox_linetype_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	$items = array(

		'solid'		=> __('Solid line', 'wpfib'),
		'dotted'	=> __('Dotted line', 'wpfib'),
		'dashed'	=> __('Dashed line', 'wpfib'),
		'double'	=> __('Double line', 'wpfib'),
		'groove'	=> __('Grooved line', 'wpfib'),
		'ridge'		=> __('Ridged line', 'wpfib'),
		'inset'		=> __('Inset line', 'wpfib'),
		'outset'	=> __('Outset line', 'wpfib')
	);
	
	echo "<select id='framebox_linetype' name='wpfib_looks_options[framebox_linetype]'>";

	foreach($items as $itemVal => $itemString)
	{
		$selected = ($options['framebox_linetype'] == $itemVal) ? 'selected="selected"' : '';
		echo "<option value='$itemVal' $selected>$itemString</option>";
	}
	echo "</select>";
	
	echo "<label style='vertical-align:top'><br /><br />".$args[0]."</label>";
}
function framebox_linecolor_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='framebox_linecolor' name='wpfib_looks_options[framebox_linecolor]' size='5' type='text' value='{$options['framebox_linecolor']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function errorbox_bgcolor_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='errorbox_bgcolor' name='wpfib_looks_options[errorbox_bgcolor]' size='5' type='text' value='{$options['errorbox_bgcolor']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function errorbox_border_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='errorbox_border' name='wpfib_looks_options[errorbox_border]' size='5' type='text' value='{$options['errorbox_border']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function errorbox_linetype_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	$items = array(

		'solid'		=> __('Solid line', 'wpfib'),
		'dotted'	=> __('Dotted line', 'wpfib'),
		'dashed'	=> __('Dashed line', 'wpfib'),
		'double'	=> __('Double line', 'wpfib'),
		'groove'	=> __('Grooved line', 'wpfib'),
		'ridge'		=> __('Ridged line', 'wpfib'),
		'inset'		=> __('Inset line', 'wpfib'),
		'outset'	=> __('Outset line', 'wpfib')
	);
	
	echo "<select id='errorbox_linetype' name='wpfib_looks_options[errorbox_linetype]'>";

	foreach($items as $itemVal => $itemString)
	{
		$selected = ($options['errorbox_linetype'] == $itemVal) ? 'selected="selected"' : '';
		echo "<option value='$itemVal' $selected>$itemString</option>";
	}
	echo "</select>";
	
	echo "<label style='vertical-align:top'><br /><br />".$args[0]."</label>";
}
function errorbox_linecolor_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='errorbox_linecolor' name='wpfib_looks_options[errorbox_linecolor]' size='5' type='text' value='{$options['errorbox_linecolor']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function successbox_bgcolor_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='successbox_bgcolor' name='wpfib_looks_options[successbox_bgcolor]' size='5' type='text' value='{$options['successbox_bgcolor']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function successbox_border_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='successbox_border' name='wpfib_looks_options[successbox_border]' size='5' type='text' value='{$options['successbox_border']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function successbox_linetype_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	$items = array(

		'solid'		=> __('Solid line', 'wpfib'),
		'dotted'	=> __('Dotted line', 'wpfib'),
		'dashed'	=> __('Dashed line', 'wpfib'),
		'double'	=> __('Double line', 'wpfib'),
		'groove'	=> __('Grooved line', 'wpfib'),
		'ridge'		=> __('Ridged line', 'wpfib'),
		'inset'		=> __('Inset line', 'wpfib'),
		'outset'	=> __('Outset line', 'wpfib')
	);
	
	echo "<select id='successbox_linetype' name='wpfib_looks_options[successbox_linetype]'>";

	foreach($items as $itemVal => $itemString)
	{
		$selected = ($options['successbox_linetype'] == $itemVal) ? 'selected="selected"' : '';
		echo "<option value='$itemVal' $selected>$itemString</option>";
	}
	echo "</select>";
	
	echo "<label style='vertical-align:top'><br /><br />".$args[0]."</label>";
}
function successbox_linecolor_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='successbox_linecolor' name='wpfib_looks_options[successbox_linecolor]' size='5' type='text' value='{$options['successbox_linecolor']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}

// looks: input styles section fields
function inputbox_bgcolor_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='inputbox_bgcolor' name='wpfib_looks_options[inputbox_bgcolor]' size='5' type='text' value='{$options['inputbox_bgcolor']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function inputbox_border_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='inputbox_border' name='wpfib_looks_options[inputbox_border]' size='5' type='text' value='{$options['inputbox_border']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function inputbox_linetype_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	$items = array(

		'solid'		=> __('Solid line', 'wpfib'),
		'dotted'	=> __('Dotted line', 'wpfib'),
		'dashed'	=> __('Dashed line', 'wpfib'),
		'double'	=> __('Double line', 'wpfib'),
		'groove'	=> __('Grooved line', 'wpfib'),
		'ridge'		=> __('Ridged line', 'wpfib'),
		'inset'		=> __('Inset line', 'wpfib'),
		'outset'	=> __('Outset line', 'wpfib')
	);
	
	echo "<select id='inputbox_linetype' name='wpfib_looks_options[inputbox_linetype]'>";

	foreach($items as $itemVal => $itemString)
	{
		$selected = ($options['inputbox_linetype'] == $itemVal) ? 'selected="selected"' : '';
		echo "<option value='$itemVal' $selected>$itemString</option>";
	}
	echo "</select>";
	
	echo "<label style='vertical-align:top'><br /><br />".$args[0]."</label>";
}
function inputbox_linecolor_input($args)
{
	$options = get_option('wpfib_looks_options');
	
	// echo the field
	echo "<input id='inputbox_linecolor' name='wpfib_looks_options[inputbox_linecolor]' size='5' type='text' value='{$options['inputbox_linecolor']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}

// TODO add validation on looks options

// SECTION : ADVANCED SETTINGS

// description of the sections
function wpfib_advanced_section_text()
{
	echo "<p>".__('This section is meant to be used by advanced users.', 'wpfib');
}

function userbound_dir_prefix_input($args)
{
	$options = get_option('wpfib_advanced_options');
	
	// echo the field
	echo "<input id='userbound_dir_prefix' name='wpfib_advanced_options[userbound_dir_prefix]' size='25' type='text' value='{$options['userbound_dir_prefix']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function userbound_dir_params_input($args)
{
	$options = get_option('wpfib_advanced_options');
	
	$items = array(

		USERBOUND_PARAMETER_ID				=> __('User ID', 'wpfib'),
		USERBOUND_PARAMETER_LOGIN			=> __('User login name', 'wpfib'),
		USERBOUND_PARAMETER_NAME			=> __('User name', 'wpfib'),
		USERBOUND_PARAMETER_ID_LOGIN		=> __('User ID and login name', 'wpfib'),
		USERBOUND_PARAMETER_ID_NAME			=> __('User ID and name', 'wpfib'),
		USERBOUND_PARAMETER_ID_LOGIN_NAME	=> __('User ID, login name and user name', 'wpfib'),
	);
	
	echo "<select id='userbound_dir_params' name='wpfib_advanced_options[userbound_dir_params]'>";

	foreach($items as $itemVal => $itemString)
	{
		$selected = ($options['userbound_dir_params'] == $itemVal) ? 'selected="selected"' : '';
		echo "<option value='$itemVal' $selected>$itemString</option>";
	}
	echo "</select>";
	
	echo "<label style='vertical-align:top'>&nbsp;&nbsp;".$args[0]."</label>";
}
function userbound_dir_suffix_input($args)
{
	$options = get_option('wpfib_advanced_options');
	
	// echo the field
	echo "<input id='userbound_dir_suffix' name='wpfib_advanced_options[userbound_dir_suffix]' size='25' type='text' value='{$options['userbound_dir_suffix']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function default_string_encoding_input($args)
{
	$options = get_option('wpfib_advanced_options');
	
	$items = array(

		'UTF-8'			=> __('ASCII compatible multi-byte 8-bit Unicode', 'wpfib'),
		'ISO-8859-1'	=> __('Western European, Latin-1', 'wpfib'),
		'ISO-8859-5'	=> __('Little used cyrillic charset (Latin/Cyrillic)', 'wpfib'),
		'ISO-8859-15'	=> __('Western European, Latin-9. Adds the Euro sign, French and Finnish letters missing in Latin-1', 'wpfib'),
		'cp1251'		=> __('Windows-specific Cyrillic charset', 'wpfib'),
		'cp1252'		=> __('Windows specific charset for Western European', 'wpfib'),
		'KOI8-R'		=> __('Russian', 'wpfib'),
		'BIG5'			=> __('Traditional Chinese, mainly used in Taiwan', 'wpfib'),
		'GB2312'		=> __('Simplified Chinese, national standard character set', 'wpfib'),
		'BIG5-HKSCS'	=> __('Big5 with Hong Kong extensions, Traditional Chinese', 'wpfib'),
		'Shift_JIS'		=> __('Japanese', 'wpfib'),
		'EUC-JP'		=> __('Japanese', 'wpfib'),
		'MacRoman'		=> __('Charset that was used by Mac OS', 'wpfib')
	);
	
	echo "<hr />";
	echo "<select id='default_string_encoding' name='wpfib_advanced_options[default_string_encoding]'>";

	foreach($items as $itemVal => $itemString)
	{
		$selected = ($options['default_string_encoding'] == $itemVal) ? 'selected="selected"' : '';
		echo "<option value='$itemVal' $selected>".$itemVal." - ".$itemString."</option>";
	}
	echo "</select>";
	
	echo "<label style='vertical-align:top'><br /><br />".$args[0]."</label>";
}
function default_file_chmod_input($args)
{
	$options = get_option('wpfib_advanced_options');
	
	// echo the field
	echo "<hr />";
	echo "<input id='default_file_chmod' name='wpfib_advanced_options[default_file_chmod]' size='5' type='text' value='{$options['default_file_chmod']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function default_dir_chmod_input($args)
{
	$options = get_option('wpfib_advanced_options');
	
	// echo the field
	echo "<input id='default_dir_chmod' name='wpfib_advanced_options[default_dir_chmod]' size='5' type='text' value='{$options['default_dir_chmod']}' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}
function DEBUG_enabled_input($args)
{
	$options = get_option('wpfib_advanced_options');

	if($options['DEBUG_enabled']) { $checked = ' checked="checked" '; }

	echo "<hr />";
	echo "<input ".$checked." id='DEBUG_enabled' name='wpfib_advanced_options[DEBUG_enabled]' type='checkbox' />";
	echo "<label>&nbsp;&nbsp;".$args[0]."</label>";
}

?>
