<?php
/**
 * Delightful Downloads Page Settings
 *
 * @package     Delightful Downloads
 * @subpackage  Functions/Page Settings
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register settings page
 *
 * @since  1.3
 */
function dedo_register_page_settings() {
	add_submenu_page( 'edit.php?post_type=dedo_download', 'Delightful Downloads ' . __( 'Settings', 'delightful-downloads' ), __( 'Settings', 'delightful-downloads' ), 'manage_options', 'dedo_settings', 'dedo_render_page_settings' );
}
add_action( 'admin_menu', 'dedo_register_page_settings' );

/**
 * Register settings sections and fields
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
	foreach( $registered_tabs as $key => $value ) {
		add_settings_section(
			'dedo_settings_' . $key,
			'',
			function_exists( 'dedo_settings_' . $key . '_section' ) ? 'dedo_settings_' . $key . '_section' : 'dedo_settings_section',
			'dedo_settings_' . $key
		);
	}
	
	// Register form fields
	foreach( $registered_settings as $key => $value ) {
		add_settings_field(
			$key,
			$value['name'],
			'dedo_settings_' . $key . '_field',
			'dedo_settings_' . $value['tab'],
			'dedo_settings_' . $value['tab']
		);
	}

	// Check for flush rewrite flag
	if( delete_transient( 'delightful-downloads-flush-rewrite' ) ) {
		flush_rewrite_rules();
	}
} 
add_action( 'admin_init', 'dedo_register_settings' );

/**
 * Render settings page
 *
 * @since  1.3
 */
function dedo_render_page_settings() {
	
	// Get registered tabs
	$registered_tabs = dedo_get_tabs();

	// Get current tab
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general'; ?>

	<div class="wrap">
		<h2 class="nav-tab-wrapper">
		<?php 
			// Generate tabs
			foreach( $registered_tabs as $key => $value ) {
				echo '<a href="edit.php?post_type=dedo_download&page=dedo_settings&tab=' . $key . '" class="nav-tab ' . ( $active_tab == $key ? 'nav-tab-active' : '' ) . '">' . $value . '</a>';
   	 		} 
   	 	?>
		</h2>
		<div id="dedo-settings-main">	
			<?php if ( isset( $_GET['settings-updated'] ) ) {
				echo '<div class="updated"><p>' . __( 'Settings updated successfully.', 'delightful-downloads' ) . '</p></div>';
			} ?>
			<form action="options.php" method="post">
				<?php 
					// Setup fields
					settings_fields( 'dedo_settings' );

					// Display correct fields
					do_settings_sections( 'dedo_settings_' . $active_tab );
					
					submit_button();
				?>
			</form>
		</div>
	</div>
	<?php
}

/**
 * Render settings sections
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
	
	$checked = $dedo_options['enable_taxonomies'];

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
	
	$checked = $dedo_options['members_only'];

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

	echo '<textarea name="delightful-downloads[block_agents]" class="dedo-settings-textarea">' . $agents . '</textarea>';
	echo '<p class="description">' . __( 'Enter user agents to block from downloading files. One per line.', 'delightful-downloads' ) . '</p>';
}

/**
 * Render log admin downloads field
 *
 * @since  1.3
 */
function dedo_settings_log_admin_downloads_field() {
global $dedo_options;
	
	$checked = $dedo_options['log_admin_downloads'];

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

	echo '<input type="text" name="delightful-downloads[default_text]" value="' . $text . '" class="regular-text" />';
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
	foreach( $styles as $key => $value ) {
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
	foreach( $colors as $key => $value ) {
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
	foreach( $lists as $key => $value ) {
		$selected = ( $default_list == $key ? ' selected="selected"' : '' );
		echo '<option value="' . $key . '" ' . $selected . '>' . $value['name'] . '</option>';	
	}
	echo '</select>';
	echo '<p class="description">' . __( 'Choose the default output style for downloads lists. This can be overwritten on a per-list basis using the \'style\' attribute.', 'delightful-downloads' ) . ' <code>[ddownloads_list style="title_filesize"]</code></p>';
}

/**
 * Render enable css field
 *
 * @since  1.3
 */
function dedo_settings_enable_css_field() {
	global $dedo_options;
	
	$checked = $dedo_options['enable_css'];

	echo '<label for="delightful-downloads[enable_css]">';
	echo '<input type="checkbox" name="delightful-downloads[enable_css]" value="1" ' . checked( $checked, 1, false ) . ' /> ';
	echo __( 'Enable', 'delightful-downloads' );
	echo '</label>';
	echo '<p class="description">' . __( 'Check this option to include the Delightful Downloads stylesheet on the front-end. If this option is disabled you must manually add the button CSS classes to your theme’s CSS file.', 'delightful-downloads' ) . '</p>';
}

/**
 * Render cache duration field
 *
 * @since  1.3
 */
function dedo_settings_cache_duration_field() {
	global $dedo_options;
	
	$cache_duration = $dedo_options['cache_duration'];

	echo '<input type="number" name="delightful-downloads[cache_duration]" value="' . $cache_duration . '" min="0" class="small-text" />';
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

	echo '<input type="text" name="delightful-downloads[download_url]" value="' . $text . '" class="regular-text" />';
	echo '<p class="description">' . __( 'Set the URL for download links. This should be left as the default value, unless absolutely sure that it will not cause conflicts with existing permalinks or plugins.', 'delightful-downloads' ) . ' <code>' . dedo_download_link( 123 ) . '</code></p>';
}

/**
 * Render Download Address Rewrite field
 *
 * @since  1.3
 */
function dedo_settings_download_url_rewrite_field() {
	global $dedo_options;
	
	$checked = $dedo_options['download_url_rewrite'];

	echo '<label for="delightful-downloads[download_url_rewrite]">';
	echo '<input type="checkbox" name="delightful-downloads[download_url_rewrite]" value="1" ' . checked( $checked, 1, false ) . ' /> ';
	echo __( 'Enable', 'delightful-downloads' );
	echo '</label>';
	echo '<p class="description">' . __( 'Check this option to enable pretty permalinks.', 'delightful-downloads' ) . '<code>' . home_url( '?' . $dedo_options['download_url'] . '=123' ) . '</code> becomes <code>' . home_url( $dedo_options['download_url'] . '/123' ) . '</code>.</p>';
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
	 foreach( $registered_options as $key => $value ) {
	 	if( $value['tab'] == $tab && $value['type'] == 'text' ) {
	 		$textfields[] = $key;
	 	}
	 	if( $value['tab'] == $tab && $value['type'] == 'check' ) {
	 		$checkboxes[] = $key;
	 	}
	 }

	 // Parse so that settings not on the active tab keep their values
	 $parsed = wp_parse_args( $input, $dedo_options );
	 
	 // Save empty text fields with default options
	if ( isset( $textfields ) ) {	 
		 foreach( $textfields as $textfield ) {
			$parsed[$textfield] = trim( $input[$textfield] ) == '' ? $dedo_default_options[$textfield] : trim( $input[$textfield] );		 
		 }
	}
	 
	 // Save checkboxes
	if ( isset( $checkboxes ) )	{ 
		 foreach( $checkboxes as $checkbox ) {
			 if( !isset( $input[$checkbox] ) ) {
				 $parsed[$checkbox] = 0;
			 }
			 else {
				$parsed[$checkbox] = 1;	 
			 }
		 }
	}

	 // Ensure cache duration is a positive number only
	 $parsed['cache_duration'] = abs( intval( $parsed['cache_duration'] ) );

	 // Ensure download URL does not contain illegal characters
	 $parsed['download_url'] = strtolower( preg_replace( '/[^A-Za-z0-9_-]/', '', $parsed['download_url'] ) );

	 // Set flush rewrite flag
	 set_transient( 'delightful-downloads-flush-rewrite', 'true', 60 * 60 );

	 return $parsed;
}