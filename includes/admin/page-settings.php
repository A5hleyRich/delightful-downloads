<?php
/**
 * Delightful Downloads Page Settings
 *
 * @package     Delightful Downloads
 * @subpackage  Admin/Page Settings
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Settings Page
 *
 * @since  1.3
 */
function dedo_register_page_settings() {
	add_submenu_page( 'edit.php?post_type=dedo_download', __( 'Download Settings', 'delightful-downloads' ), __( 'Settings', 'delightful-downloads' ), 'manage_options', 'dedo_settings', 'dedo_render_page_settings' );
}
add_action( 'admin_menu', 'dedo_register_page_settings', 10 );

/**
 * Register Settings Sections and Fields
 *
 * @since  1.3
 */
function dedo_register_settings() {
	
	// Get registered tabs and settings
	$registered_tabs = dedo_get_tabs();
	$registered_settings = dedo_get_options();

	// Register whitelist
	register_setting( 'dedo_settings', 'delightful-downloads', 'dedo_validate_settings' ); 

	// Register form sections
	foreach ( $registered_tabs as $key => $value ) {
		
		add_settings_section(
			'dedo_settings_' . $key,
			'',
			function_exists( 'dedo_settings_' . $key . '_section' ) ? 'dedo_settings_' . $key . '_section' : 'dedo_settings_section',
			'dedo_settings_' . $key
		);

	}
	
	// Register form fields
	foreach ( $registered_settings as $key => $value ) {
		
		add_settings_field(
			$key,
			$value['name'],
			'dedo_settings_' . $key . '_field',
			'dedo_settings_' . $value['tab'],
			'dedo_settings_' . $value['tab']
		);

	}
} 
add_action( 'admin_init', 'dedo_register_settings' );

/**
 * Render Settings Page
 *
 * @since  1.3
 */
function dedo_render_page_settings() {
	
	// Get registered tabs
	$registered_tabs = dedo_get_tabs();

	// Get current tab
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general'; ?>

	<div class="wrap">
		
		<h2><?php _e( 'Download Settings', 'delightful-downloads' ); ?>
			<a href="<?php echo admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings&action=export' ) ?>" class="add-new-h2"><?php _e( 'Export', 'delightful-downloads' ); ?></a>
			<a href="<?php echo admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings&action=reset_defaults' ) ?>" class="add-new-h2 dedo_confirm_action" data-confirm="<?php _e( 'You are about to reset the download settings.', 'delightful-downloads' ); ?>"><?php _e( 'Reset Defaults', 'delightful-downloads' ); ?></a>
		</h2>
		
		<?php if ( isset( $_GET['settings-updated'] ) ) {
				
			echo '<div class="updated"><p>' . __( 'Settings updated successfully.', 'delightful-downloads' ) . '</p></div>';
		} ?>

		<h3 id="dedo-settings-tabs" class="nav-tab-wrapper">
			
			<?php // Generate tabs
			
			foreach ( $registered_tabs as $key => $value ) {
				
				echo '<a href="#dedo-settings-tab-' . $key . '" class="nav-tab ' . ( $active_tab == $key ? 'nav-tab-active' : '' ) . '">' . $value . '</a>';
   	 		} ?>

		</h3>	

		<div id="dedo-settings-main" <?php echo ( !apply_filters( 'dedo_admin_sidebar', true ) ) ? 'style="float: none; width: 100%; padding-right: 0;"' : ''; ?>>	

			<form action="options.php" method="post">
					
				<?php // Setup fields
				
				settings_fields( 'dedo_settings' );

				// Display correct fields
				foreach ( $registered_tabs as $key => $value ) {

					$active_class = ( $key === $active_tab ) ? 'active' : '';

					echo '<section id="dedo-settings-tab-' . $key . '" class="dedo-settings-tab ' . $active_class . '">';
					
					if ( 'support' === $key ) {

						dedo_render_part_support();
					}
					else {

						do_settings_sections( 'dedo_settings_' . $key );
					}

					echo '</section>';
				}
				
				// Submit button
				submit_button(); ?>

			</form>
	
		</div>

		<?php dedo_render_part_sidebar(); ?>

	</div>
	
	<?php
}

/**
 * Render Support Section
 *
 * @since  1.5
 */
function dedo_render_part_support() {

	global $wpdb, $dedo_options;

	// Get current theme data
	$theme = wp_get_theme();

	// Get active plugins
	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	// Prior version
	$prior_version = get_option( 'delightful-downloads-prior-version' );
	?>

	<textarea id="dedo_support" readonly>

## Server Information ##

Server: <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>
PHP Version: <?php echo PHP_VERSION . "\n"; ?>
MySQL Version: <?php echo $wpdb->db_version() . "\n"; ?>

PHP Safe Mode: <?php echo ini_get( 'safe_mode' ) ? "Yes\n" : "No\n"; ?>
PHP Memory Limit: <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
PHP Time Limit: <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>
PHP Max Post Size: <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Max Upload Size: <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>


## WordPress Information ##

WordPress Version: <?php echo get_bloginfo( 'version' ) . "\n"; ?>
Multisite: <?php echo ( is_multisite() ) ? 'Yes' . "\n" : 'No' . "\n" ?>
Max Upload Size: <?php echo size_format( wp_max_upload_size(), 1 ) . "\n"; ?>

Site Address: <?php echo home_url() . "\n"; ?>
WordPress Address: <?php echo site_url() . "\n"; ?>
Download Address: <?php echo dedo_download_link( 1 ) . "\n"; ?>

<?php echo ( defined('UPLOADS') ? 'Upload Directory: ' . UPLOADS . "\n" : '' ); ?>
Directory (wp-content): <?php echo ( defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR . "\n" : '' ); ?>
URL (wp-content): <?php echo ( defined('WP_CONTENT_URL') ? WP_CONTENT_URL . "\n" : '' ); ?>

## Active Theme ## 

<?php echo $theme->Name . " " . $theme->Version . "\n"; ?>


## Active Plugins ##			

<?php 
foreach ( $plugins as $key => $value ) {
	
	if ( in_array( $key, $active_plugins ) ) {
		echo $value['Name'] . ' ' . $value['Version'] . "\n";
	}
	
}
?>


## Delightful Downloads Information ##

Version: <?php echo DEDO_VERSION . "\n"; ?>
Prior Version: <?php echo $prior_version . "\n"; ?>

<?php

foreach ( $dedo_options as $key => $value ) {
	echo $key . ": " . $value . "\n";
}

?>
	</textarea>

	<?php

}

/**
 * Render Sidebar
 *
 * @since  1.5
 */
function dedo_render_part_sidebar() {

	if ( apply_filters( 'dedo_admin_sidebar', true ) ) : ?>

		<div id="dedo-settings-sidebar">
			<h4><?php _e( 'Help and Support', 'delightful-downloads' ); ?></h4>
			<p><?php printf( __( 'Please take a moment to look at the %sdocumentation%s. If you are still having issues, please leave a %ssupport request%s.', 'delightful-downloads' ), '<a href="http://delightfulwp.com/delightful-downloads/documentation/">', '</a>', '<a href="http://delightfulwp.com/contact/?reason=support">', '</a>' ); ?></p>
			
			<h4><?php _e( 'Share the Love', 'delightful-downloads' ); ?></h4>
			<p><?php printf( __( 'Enjoy Delightful Downloads? Please consider %sdonating%s a few dollars, to help support future development. Alternatively, a %splugin review%s is always appreciated.', 'delightful-downloads' ), '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=95AQB5DP83XAU">', '</a>', '<a href="http://wordpress.org/support/view/plugin-reviews/delightful-downloads">', '</a>' ); ?></p>

			<h4><?php _e( 'About the Developer', 'delightful-downloads' ); ?></h4>
			<p><?php printf( __( 'Hey there! I\'m %sAshley Rich%s, a freelance web designer and WordPress developer based in the West Midlands, England.', 'delightful-downloads' ), '<a href="http://ashleyrich.com">', '</a>' ); ?></p>
			<p><?php printf( __( '%sTwitter%s', 'delightful-downloads' ), '<a href="//twitter.com/A5hleyRich">', '</a>' ); ?></p>
		</div>

	<?php endif;

}

/**
 * Render Settings Sections
 *
 * @since  1.3
 */
function dedo_settings_section() { return; }

/**
 * Render enable taxonomies field
 *
 * @since  1.3
 */
function dedo_settings_enable_taxonomies_field() {
	global $dedo_options;
	
	$checked = absint( $dedo_options['enable_taxonomies'] );

	echo '<label for="delightful-downloads[enable_taxonomies]">';
	echo '<input type="checkbox" name="delightful-downloads[enable_taxonomies]" id="delightful-downloads[enable_taxonomies]" value="1" ' . checked( $checked, 1, false ) . ' /> ';
	echo __( 'Enable', 'delightful-downloads' );
	echo '</label>';
	echo '<p class="description">' . __( 'Check this option to allow downloads to be tagged or categorised.', 'delightful-downloads' ) . '</p>';
}

/**
 * Render members only field
 *
 * @since  1.3
 */
function dedo_settings_members_only_field() {
	global $dedo_options;
	
	$checked = absint( $dedo_options['members_only'] );

	echo '<label for="delightful-downloads[members_only]">';
	echo '<input type="checkbox" name="delightful-downloads[members_only]" id="delightful-downloads[members_only]" value="1" ' . checked( $checked, 1, false ) . ' /> ';
	echo __( 'Member Only', 'delightful-downloads' );
	echo '</label>';
	echo '<p class="description">' . __( 'Check this option to allow only logged in users to download files.', 'delightful-downloads' ) . '</p>';
}

/**
 * Render members redirect field
 *
 * @since  1.3
 */
function dedo_settings_members_redirect_field() {
	global $dedo_options;
	
	// Default selected item
	$selected = $dedo_options['members_redirect'];
	
	// Output select input
	$args = array(
		'name'						=> 'delightful-downloads[members_redirect]',
		'selected'					=> $selected,
		'show_option_none'			=> 'Select...',
		'show_option_none_value'	=> 0
	);
	
	wp_dropdown_pages( $args );
	echo '<p class="description">' . __( 'Select the page that a user should be redirected to if they try to download a file when not logged in and the Members Only option is checked. If no page is selected a generic error message will be displayed.', 'delightful-downloads' ) . '</p>';
}

/**
 * Render block user agents field
 *
 * @since  1.3
 */
function dedo_settings_block_agents_field() {
	global $dedo_options;

	$agents = $dedo_options['block_agents'];

	echo '<textarea name="delightful-downloads[block_agents]" class="dedo-settings-textarea">' . esc_attr( $agents ) . '</textarea>';
	echo '<p class="description">' . __( 'Enter user agents to block from downloading files. One per line.', 'delightful-downloads' ) . '</p>';
}

/**
 * Render default text field
 *
 * @since  1.3
 */
function dedo_settings_default_text_field() {
	global $dedo_options;

	$text = $dedo_options['default_text'];

	echo '<input type="text" name="delightful-downloads[default_text]" value="' . esc_attr( $text ) . '" class="regular-text" />';
	echo '<p class="description">' . __( 'Set the default text to display on button and link style outputs. This can be overwritten on a per-download basis using the \'text\' attribute.', 'delightful-downloads' ) . ' <code>[ddownload id="123" text="Awesome Download"]</code></p>';
}

/**
 * Render default style field
 *
 * @since  1.3
 */
function dedo_settings_default_style_field() {
	global $dedo_options;

	$styles = dedo_get_shortcode_styles();
	$default_style = $dedo_options['default_style'];

	echo '<select name="delightful-downloads[default_style]">';
	foreach ( $styles as $key => $value ) {
		$selected = ( $default_style == $key ? ' selected="selected"' : '' );
		echo '<option value="' . $key . '" ' . $selected . '>' . $value['name'] . '</option>';	
	}
	echo '</select>';
	echo '<p class="description">' . __( 'Choose the default output style for downloads. This can be overwritten on a per-download basis using the \'style\' attribute.', 'delightful-downloads' ) . ' <code>[ddownload id="123" style="button"]</code></p>';
}

/**
 * Render default button field
 *
 * @since  1.3
 */
function dedo_settings_default_button_field() {
	global $dedo_options;

	$colors = dedo_get_shortcode_buttons();
	$default_color = $dedo_options['default_button'];

	echo '<select name="delightful-downloads[default_button]">';
	
	foreach ( $colors as $key => $value ) {
		$selected = ( $default_color == $key ? ' selected="selected"' : '' );
		echo '<option value="' . $key . '" ' . $selected . '>' . $value['name'] . '</option>';	
	}

	echo '</select>';
	echo '<p class="description">' . __( 'Choose the default button style. This can be overwritten on a per-download basis using the \'button\' attribute.', 'delightful-downloads' ) . ' <code>[ddownload id="123" style="button" button="blue"]</code></p>';
}

/**
 * Render default list field
 *
 * @since  1.3
 */
function dedo_settings_default_list_field() {
	global $dedo_options;

	$lists = dedo_get_shortcode_lists();
	$default_list = $dedo_options['default_list'];

	echo '<select name="delightful-downloads[default_list]">';
	
	foreach ( $lists as $key => $value ) {
		$selected = ( $default_list == $key ? ' selected="selected"' : '' );
		echo '<option value="' . $key . '" ' . $selected . '>' . $value['name'] . '</option>';	
	}

	echo '</select>';
	echo '<p class="description">' . __( 'Choose the default output style for downloads lists. This can be overwritten on a per-list basis using the \'style\' attribute.', 'delightful-downloads' ) . ' <code>[ddownload_list style="title_filesize"]</code></p>';
}

/**
 * Render log admin downloads field
 *
 * @since  1.3
 */
function dedo_settings_log_admin_downloads_field() {
global $dedo_options;
	
	$checked = absint( $dedo_options['log_admin_downloads'] );

	echo '<label for="delightful-downloads[log_admin_downloads]">';
	echo '<input type="checkbox" name="delightful-downloads[log_admin_downloads]" id="delightful-downloads[log_admin_downloads]" value="1" ' . checked( $checked, 1, false ) . ' /> ';
	echo __( 'Log Downloads', 'delightful-downloads' );
	echo '</label>';
	echo '<p class="description">' . __( 'Check this option to log downloads by admins.', 'delightful-downloads' ) . '</p>';
}

/**
 * Render grace period field
 *
 * @since  1.4
 */
function dedo_settings_grace_period_field() {
	global $dedo_options;

	$grace_period = $dedo_options['grace_period'];

	echo '<input type="number" name="delightful-downloads[grace_period]" value="' . esc_attr( $grace_period ) . '" min="0" class="small-text" />';
	echo '<p class="description">' . __( 'Set the time in minutes before creating an additional log when the user tries to download the same file multiple times (0 to disable).', 'delightful-downloads' ) . '</p>';
}

/**
 * Render auto delete field
 *
 * @since  1.4
 */
function dedo_settings_auto_delete_field() {
	global $dedo_options;

	$auto_delete = $dedo_options['auto_delete'];

	echo '<input type="number" name="delightful-downloads[auto_delete]" value="' . esc_attr( $auto_delete ) . '" min="0" class="small-text" />';
	echo '<p class="description">' . __( 'Set the amount of days that logs should be kept (0 to disable). Logs older than the specified value will be deleted once a day.', 'delightful-downloads' ) . '</p>';
}

/**
 * Render enable css field
 *
 * @since  1.3
 */
function dedo_settings_enable_css_field() {
	global $dedo_options;
	
	$checked = absint( $dedo_options['enable_css'] );

	echo '<label for="delightful-downloads[enable_css]">';
	echo '<input type="checkbox" name="delightful-downloads[enable_css]" id="delightful-downloads[enable_css]" value="1" ' . checked( $checked, 1, false ) . ' /> ';
	echo __( 'Enable', 'delightful-downloads' );
	echo '</label>';
	echo '<p class="description">' . __( 'Check this option to include the Delightful Downloads stylesheet on the front-end. If this option is disabled you must manually add the button CSS classes to your theme\'s CSS file.', 'delightful-downloads' ) . '</p>';
}

/**
 * Render cache duration field
 *
 * @since  1.3
 */
function dedo_settings_cache_duration_field() {
	global $dedo_options;
	
	$cache_duration = $dedo_options['cache_duration'];

	echo '<input type="number" name="delightful-downloads[cache_duration]" value="' . esc_attr( $cache_duration ) . '" min="0" class="small-text" />';
	echo '<p class="description">' . __( sprintf( 'Set the time in minutes to cache database queries (0 to disable). This will affect how often the %s, %s and %s shortcodes update. It is not recommended to set this value to 0 as it can impede site performance.', '<code>[ddownload_count]</code>', '<code>[ddownload_total_count]</code>', '<code>[ddownload_list]</code>' ), 'delightful-downloads' ) . '</p>';
}

/**
 * Render Download Address field
 *
 * @since  1.3
 */
function dedo_settings_download_url_field() {
	global $dedo_options;

	$text = $dedo_options['download_url'];

	echo '<input type="text" name="delightful-downloads[download_url]" value="' . esc_attr( $text ) . '" class="regular-text" />';
	echo '<p class="description">' . __( 'Set the URL for download links. This should be left as the default value, unless absolutely sure that it will not cause conflicts with existing permalinks or plugins.', 'delightful-downloads' ) . ' <code>' . dedo_download_link( 123 ) . '</code></p>';
}

/**
 * Render Uninstall field
 *
 * @since  1.3.6
 */
function dedo_settings_uninstall_field() {
	global $dedo_options;

	$checked = absint( $dedo_options['uninstall'] );

	echo '<label for="delightful-downloads[uninstall]">';
	echo '<input type="checkbox" name="delightful-downloads[uninstall]" id="delightful-downloads[uninstall]" value="1" ' . checked( $checked, 1, false ) . ' /> ';
	echo __( 'Enable', 'delightful-downloads' );
	echo '</label>';
	echo '<p class="description">' . __( sprintf( 'Check this option to completely remove all data associated with Delightful Downloads when deleting the plugin. All downloads, categories, tags, logs and statistics will be removed. The uploaded files will remain in the %s directory.', '<code>wp-content/uploads/delightful-downloads</code>' ), 'delightful-downloads' ) . '</p>';
}

/**
 * Validate settings callback
 *
 * @since  1.3
 */
function dedo_validate_settings( $input ) {
	 global $dedo_options, $dedo_default_options;

	 // Get referer tab
	 parse_str( $_POST['_wp_http_referer'], $referer );
	 $tab = isset( $referer['tab'] ) ? $referer['tab'] : 'general';

	 // Get registered options
	 $registered_options = dedo_get_options();

	 // Create a list of textfields and checkboxes on active tab
	 foreach ( $registered_options as $key => $value ) {
	 	
	 	if ( $value['tab'] == $tab && $value['type'] == 'text' ) {
	 		$textfields[] = $key;
	 	}
	 	
	 	if ( $value['tab'] == $tab && $value['type'] == 'check' ) {
	 		$checkboxes[] = $key;
	 	}

	 }

	 // Parse so that settings not on the active tab keep their values
	 $parsed = wp_parse_args( $input, $dedo_options );
	 
	 // Save empty text fields with default options
	if ( isset( $textfields ) ) {	 
		 
		foreach ( $textfields as $textfield ) {
			$parsed[$textfield] = trim( $input[$textfield] ) == '' ? $dedo_default_options[$textfield] : trim( $input[$textfield] );		 
		}
	}
	 
	 // Save checkboxes
	if ( isset( $checkboxes ) )	{ 
		 
		 foreach ( $checkboxes as $checkbox ) {
			 
			 if ( !isset( $input[$checkbox] ) ) {
				 $parsed[$checkbox] = 0;
			 }
			 else {
				$parsed[$checkbox] = 1;	 
			 }

		 }

	}

	// Ensure values are positive ints only
	foreach ( array( 'grace_period', 'auto_delete', 'cache_duration' ) as $field ) {
		$parsed[$field] = absint( $parsed[$field] );
	}

	// Ensure download URL does not contain illegal characters
	$parsed['download_url'] = strtolower( preg_replace( '/[^A-Za-z0-9_-]/', '', $parsed['download_url'] ) );

	 // Clear transients
	dedo_delete_all_transients();

	 return $parsed;
}

/**
 * Settings Page Actions
 *
 * @since  1.4
 */
function dedo_settings_actions() {

	//Only perform on settings page, when form not submitted
	if ( isset( $_GET['page'] ) && 'dedo_settings' == $_GET['page'] ) {

		// Export
		if( isset( $_GET['action'] ) && 'export' == $_GET['action'] ) {

			dedo_settings_actions_export();
		}
		// Reset default settings
		else if( isset( $_GET['action'] ) && 'reset_defaults' == $_GET['action'] ) {
			
			dedo_settings_actions_reset();
		}

	}
}
add_action( 'init', 'dedo_settings_actions', 0 );

/**
 * Settings Page Actions Export
 *
 * @since  1.5
 */
function dedo_settings_actions_export() {

	global $dedo_options;

	// Set filename
	$filename = 'delightful-downloads-' . date( 'Ymd' ) . '.json';

	// Output headers so that the file is downloaded
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Expires: 0' );

	echo json_encode( $dedo_options );	

	die();
}

/**
 * Settings Page Actions Import
 *
 * @since  1.5
 */
function dedo_settings_actions_import() {

	
}

/**
 * Settings Page Actions Reset
 *
 * @since  1.5
 */
function dedo_settings_actions_reset() {

	global $dedo_default_options, $dedo_notices;

	delete_option( 'delightful-downloads' );
	add_option( 'delightful-downloads', $dedo_default_options );

	// Add success notice
	$dedo_notices->add( 'updated', __( 'Default settings reset successfully.', 'delightful-downloads' ) );

	// Redirect page to remove action from URL
	wp_redirect( admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings' ) );
	exit();	
}