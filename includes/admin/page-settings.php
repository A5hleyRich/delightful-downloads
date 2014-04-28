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
	add_submenu_page( 'edit.php?post_type=dedo_download', 'Delightful Downloads ' . __( 'Settings', 'delightful-downloads' ), __( 'Settings', 'delightful-downloads' ), 'manage_options', 'dedo_settings', 'dedo_render_page_settings' );
}
add_action( 'admin_menu', 'dedo_register_page_settings' );

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
		<h2><?php _e( 'Download Settings', 'delightful-downloads' ); ?></h2>
		<h3 class="nav-tab-wrapper">
		<?php 
			// Generate tabs
			foreach ( $registered_tabs as $key => $value ) {
				echo '<a href="edit.php?post_type=dedo_download&page=dedo_settings&tab=' . $key . '" class="nav-tab ' . ( $active_tab == $key ? 'nav-tab-active' : '' ) . '">' . $value . '</a>';
   	 		} 
   	 	?>
		</h3>
		<div id="dedo-settings-main">	
			<?php if ( isset( $_GET['settings-updated'] ) ) {
				echo '<div class="updated"><p>' . __( 'Settings updated successfully.', 'delightful-downloads' ) . '</p></div>';
			} ?>

			<?php

			if ( 'support' == $active_tab ) {

global $dedo_options;

// Get current theme data
$theme = wp_get_theme();

// Get active plugins
$plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

// Prior version
$prior_version = get_option( 'delightful-downloads-prior-version' );
?>

<p><?php _e( 'Please include the following information when requesting <a href="http://wordpress.org/support/plugin/delightful-downloads">support</a>.', 'delightful-downloads' ); ?></p>
<textarea id="dedo_support" readonly>

## Server Information ##

Server: <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>
PHP Version: <?php echo PHP_VERSION . "\n"; ?>
MySQL Version: <?php echo mysql_get_server_info() . "\n"; ?>

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
	echo $key . ": " . str_replace( "\n", "\t", $value ) . "\n";
}

?>
</textarea> <?php

			}
			else { ?>

				<form action="options.php" method="post">
					<?php 
						// Setup fields
						settings_fields( 'dedo_settings' );

						// Display correct fields
						do_settings_sections( 'dedo_settings_' . $active_tab );
						
						submit_button();

			} ?>

				</form>
			
		</div>
	</div>
	<?php
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
	echo '<input type="checkbox" name="delightful-downloads[enable_taxonomies]" id="delightful-downloads-enable-taxonomies" value="1" ' . checked( $checked, 1, false ) . ' /> ';
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
	echo '<input type="checkbox" name="delightful-downloads[members_only]" id="delightful-downloads-members-only" value="1" ' . checked( $checked, 1, false ) . ' /> ';
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
 * Render log admin downloads field
 *
 * @since  1.3
 */
function dedo_settings_log_admin_downloads_field() {
global $dedo_options;
	
	$checked = absint( $dedo_options['log_admin_downloads'] );

	echo '<label for="delightful-downloads[log_admin_downloads]">';
	echo '<input type="checkbox" name="delightful-downloads[log_admin_downloads]" id="delightful-downloads-log-admin-downloads" value="1" ' . checked( $checked, 1, false ) . ' /> ';
	echo __( 'Log Downloads', 'delightful-downloads' );
	echo '</label>';
	echo '<p class="description">' . __( 'Check this option to log downloads by admins.', 'delightful-downloads' ) . '</p>';
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
 * Render enable css field
 *
 * @since  1.3
 */
function dedo_settings_enable_css_field() {
	global $dedo_options;
	
	$checked = absint( $dedo_options['enable_css'] );

	echo '<label for="delightful-downloads[enable_css]">';
	echo '<input type="checkbox" name="delightful-downloads[enable_css]" value="1" ' . checked( $checked, 1, false ) . ' /> ';
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
	echo '<p class="description">' . __( sprintf( 'Set the time in minutes to cache database queries. This will affect how often the %s, %s and %s shortcodes update. It is not recommended to set this value to 0 as it can impede site performance.', '<code>[ddownload_count]</code>', '<code>[ddownload_total_count]</code>', '<code>[ddownload_list]</code>' ), 'delightful-downloads' ) . '</p>';
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

	echo '<label for="delightful-downloads[unistall]">';
	echo '<input type="checkbox" name="delightful-downloads[uninstall]" value="1" ' . checked( $checked, 1, false ) . ' /> ';
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

	 // Ensure cache duration is a positive number only
	 $parsed['cache_duration'] = absint( $parsed['cache_duration'] );

	 // Ensure download URL does not contain illegal characters
	 $parsed['download_url'] = strtolower( preg_replace( '/[^A-Za-z0-9_-]/', '', $parsed['download_url'] ) );

	 // Clear transients
	dedo_delete_all_transients();

	 return $parsed;
}