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
add_action( 'admin_menu', 'dedo_register_page_settings', 30 );

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
		$callback = 'dedo_settings_' . $key . '_field';

		if ( 'licenses' === $value['tab'] ) {
			$callback = array( $value['class'], 'render_license_field' );
		}

		add_settings_field(
			$key,
			$value['name'],
			$callback,
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
	$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general'; 
	?>

	<div class="wrap">
		
		<h1><?php _e( 'Download Settings', 'delightful-downloads' ); ?>
			<a href="#dedo-settings-import" class="add-new-h2 dedo-modal-action"><?php _e( 'Import', 'delightful-downloads' ); ?></a>
			<a href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings&action=export' ), 'dedo_export_settings', 'dedo_export_settings_nonce' ) ?>" class="add-new-h2"><?php _e( 'Export', 'delightful-downloads' ); ?></a>
			<a href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings&action=reset_defaults' ), 'dedo_reset_settings', 'dedo_reset_settings_nonce' ) ?>" class="add-new-h2 dedo_confirm_action" data-confirm="<?php _e( 'You are about to reset the download settings.', 'delightful-downloads' ); ?>"><?php _e( 'Reset Defaults', 'delightful-downloads' ); ?></a>
		</h1>
		
		<?php if ( isset( $_GET['settings-updated'] ) ) {
			echo '<div class="notice updated is-dismissible"><p>' . __( 'Settings updated successfully.', 'delightful-downloads' ) . '</p></div>';
		} ?>

		<h2 id="dedo-settings-tabs" class="nav-tab-wrapper">
			
			<?php // Generate tabs
			
			foreach ( $registered_tabs as $key => $value ) {
				
				echo '<a href="#dedo-settings-tab-' . $key . '" class="nav-tab ' . ( $active_tab == $key ? 'nav-tab-active' : '' ) . '">' . $value . '</a>';
   	 		} ?>

		</h2>

		<div id="dedo-settings-main" <?php echo ( !apply_filters( 'dedo_admin_sidebar', true ) ) ? 'style="float: none; width: 100%; padding-right: 0;"' : ''; ?>>	

			<form action="options.php" method="post">	
				<?php // Setup fields
				settings_fields( 'dedo_settings' );

				// Display correct fields
				foreach ( $registered_tabs as $key => $value ) {
					$active_class = ( $key === $active_tab ) ? 'active' : '';
					?>

					<section id="dedo-settings-tab-<?php echo $key; ?>" class="dedo-settings-tab <?php echo $active_class; ?>" style="<?php echo ( '' === $active_class ) ? 'display: none;' : ''; ?>">
						<?php 

						if ( 'support' === $key ) {
							dedo_render_part_support();
						}
						else {
							do_settings_sections( 'dedo_settings_' . $key );
						}

						?>
					</section>

					<?php
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

		<?php $current_user = wp_get_current_user(); ?>

		<div id="dedo-settings-sidebar">
			<div class="pro-version">
				<h4><?php _e( 'Add-ons?', 'delightful-downloads' ); ?></h4>
				<p><?php _e( "I'm working on a few premium add-ons to enhance Delightful Downloads:", 'delightful-downloads' ); ?></p>
				<ul>
					<li>
						<strike><?php _e( '<strong>Customizer</strong> - Button and list styles editor', 'delightful-downloads' ); ?></strike>
						<a href="https://delightfuldownloads.com/add-ons/customizer/?utm_source=WordPress&utm_medium=Plugin&utm_content=Customizer&utm_campaign=Addons%20Page"><?php _e( 'Available', 'delightful-downloads' ); ?></a>
					</li>
					<li><?php _e( '<strong>MailChimp</strong> - Subscribe to download', 'delightful-downloads' ); ?></li>
					<li><?php _e( '<strong>Twitter</strong> - Tweet to download', 'delightful-downloads' ); ?></li>
				</ul>
				<form method="post" action="http://ashleyrich.us5.list-manage.com/subscribe/post" target="_blank">
					<input type="hidden" name="u" value="ace6f39e2bb7270b9ca7a21bc">
					<input type="hidden" name="id" value="003e1f6906">
					<label for="MERGE0">Email:</label>
					<input type="email" name="MERGE0" id="MERGE0" class="regular-text" value="<?php echo $current_user->user_email; ?>">
					<label for="MERGE1">First Name:</label>
					<input type="text" name="MERGE1" id="MERGE1" class="regular-text" value="<?php echo $current_user->user_firstname; ?>">
					<label for="MERGE2">Last Name:</label>
					<input type="text" name="MERGE2" id="MERGE2" class="regular-text" value="<?php echo $current_user->user_lastname; ?>">
					<button class="button button-primary"><?php _e( 'Keep me informed', 'delightful-downloads' ); ?></button>
				</form>
				<small><?php _e( 'I will not use your email for anything else and you can unsubscribe at anytime.' ); ?></small>
			</div>

			<h4><?php _e( 'Help and Support', 'delightful-downloads' ); ?></h4>
			<p><?php printf( __( 'Having issues? Check out the %sdocumentation%s. If you can\'t find a solution, please raise an issue on the %ssupport forums%s.', 'delightful-downloads' ), '<a href="https://delightfuldownloads.com/documentation/">', '</a>', '<a href="https://wordpress.org/support/plugin/delightful-downloads">', '</a>' ); ?></p>
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
	?>
	
	<label for="enable_taxonomies_true"><input name="delightful-downloads[enable_taxonomies]" id="enable_taxonomies_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="enable_taxonomies_false"><input name="delightful-downloads[enable_taxonomies]" id="enable_taxonomies_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Allow downloads to be tagged or categorised.', 'delightful-downloads' ); ?></p>
	<?php
}

/**
 * Render members only field
 *
 * @since  1.3
 */
function dedo_settings_members_only_field() {
	global $dedo_options;
	$checked = absint( $dedo_options['members_only'] );
	?>

	<label for="members_only_true"><input name="delightful-downloads[members_only]" id="members_only_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="members_only_false"><input name="delightful-downloads[members_only]" id="members_only_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Allow only logged in users to download files. This can be overridden on a per-download basis.', 'delightful-downloads' ); ?></p>
	<?php
	// Default selected item
	$selected = $dedo_options['members_only_redirect'];
	
	// Output select input
	$args = array(
		'name'						=> 'delightful-downloads[members_only_redirect]',
		'selected'					=> $selected,
		'show_option_none'			=> __( 'No Page (Generic Error)', 'delightful-downloads' ),
		'option_none_value'			=> 0
	); ?>
	
	<div class="dedo-sub-option">
		<?php wp_dropdown_pages( $args ); ?>
		<p class="description"><?php _e( 'The page to redirect non-members. If no page is selected, a generic error message will be displayed. This can be overridden on a per-download basis.', 'delightful-downloads' ); ?></p>

	</div>
	<?php
}

/**
 * Render open in browser field
 *
 * @since  1.5
 */
function dedo_settings_open_browser_field() {
	global $dedo_options;
	$checked = absint( $dedo_options['open_browser'] );
	?>

	<label for="open_browser_true"><input name="delightful-downloads[open_browser]" id="open_browser_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="open_browser_false"><input name="delightful-downloads[open_browser]" id="open_browser_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Attempt to open files in the browser window. This can be overridden on a per-download basis. For files located within the Delightful Downloads upload directory, set folder protection to \'No\', which can be found under the advanced tab.', 'delightful-downloads' ); ?></p>
	<?php
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
	echo '<p class="description">' . __( 'User agents to block from downloading files. One per line.', 'delightful-downloads' ) . '</p>';
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
	echo '<p class="description">' . sprintf( __( 'The default text to display, when using the %s shortcode. This can be overridden on a per-download basis.', 'delightful-downloads' ), '<code>[ddownload]</code>' );
}

/**
 * Render default style field
 *
 * @since  1.3
 */
function dedo_settings_default_style_field() {
	global $dedo_options;

	$styles        = dedo_get_shortcode_styles();
	$default_style = $dedo_options['default_style'];
	$disabled      = empty( $styles ) ? 'disabled' : '';

	echo '<select name="delightful-downloads[default_style]" ' . $disabled . '>';

	if ( ! empty( $styles ) ) {
		foreach ( $styles as $key => $value ) {
			$selected = ( $default_style == $key ? ' selected="selected"' : '' );
			echo '<option value="' . $key . '" ' . $selected . '>' . $value['name'] . '</option>';
		}
	} else {
		echo '<option>' . __( 'No styles registered', 'delightful-downloads' ) . '</option>';
	}

	echo '</select>';
	echo '<p class="description">' . sprintf( __( 'The default output style, when using the %s shortcode. This can be overridden on a per-download basis.', 'delightful-downloads' ), '<code>[ddownload]</code>' );
}

/**
 * Render default button field
 *
 * @since  1.3
 */
function dedo_settings_default_button_field() {
	global $dedo_options;

	$colors        = dedo_get_shortcode_buttons();
	$default_color = $dedo_options['default_button'];
	$disabled      = empty( $colors ) ? 'disabled' : '';

	echo '<select name="delightful-downloads[default_button]" ' . $disabled . '>';

	if ( ! empty( $colors ) ) {
		foreach ( $colors as $key => $value ) {
			$selected = ( $default_color == $key ? ' selected="selected"' : '' );
			echo '<option value="' . $key . '" ' . $selected . '>' . $value['name'] . '</option>';
		}
	} else {
		echo '<option>' . __( 'No button styles registered', 'delightful-downloads' ) . '</option>';
	}

	echo '</select>';
	echo '<p class="description">' . sprintf( __( 'The default button style, when using the %s shortcode. This can be overridden on a per-download basis.', 'delightful-downloads' ), '<code>[ddownload]</code>' );
}

/**
 * Render default list field
 *
 * @since  1.3
 */
function dedo_settings_default_list_field() {
	global $dedo_options;

	$lists        = dedo_get_shortcode_lists();
	$default_list = $dedo_options['default_list'];
	$disabled     = empty( $lists ) ? 'disabled' : '';

	echo '<select name="delightful-downloads[default_list]" ' . $disabled . '>';

	if ( ! empty( $lists ) ) {
		foreach ( $lists as $key => $value ) {
			$selected = ( $default_list == $key ? ' selected="selected"' : '' );
			echo '<option value="' . $key . '" ' . $selected . '>' . $value['name'] . '</option>';
		}
	} else {
		echo '<option>' . __( 'No list styles registered', 'delightful-downloads' ) . '</option>';
	}

	echo '</select>';
	echo '<p class="description">' . sprintf( __( 'The default output style, when using the %s shortcode. This can be overridden on a per-list basis.', 'delightful-downloads' ), '<code>[ddownload_list]</code>' );
}

/**
 * Render log admin downloads field
 *
 * @since  1.3
 */
function dedo_settings_log_admin_downloads_field() {
	global $dedo_options;
	$checked = absint( $dedo_options['log_admin_downloads'] );
	?>
	
	<label for="log_admin_downloads_true"><input name="delightful-downloads[log_admin_downloads]" id="log_admin_downloads_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="log_admin_downloads_false"><input name="delightful-downloads[log_admin_downloads]" id="log_admin_downloads_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Log events triggered by admin users.', 'delightful-downloads' ); ?></p>
	<?php
}

/**
 * Render grace period field
 *
 * @since  1.4
 */
function dedo_settings_grace_period_field() {
	global $dedo_options;
	$grace_period = $dedo_options['grace_period'];
	$duration = $dedo_options['grace_period_duration'];
	?>
	
	<label for="grace_period_toggle_true"><input name="delightful-downloads[grace_period]" id="grace_period_toggle_true" type="radio" value="1" <?php echo ( 1 == $grace_period ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="grace_period_toggle_false"><input name="delightful-downloads[grace_period]" id="grace_period_toggle_false" type="radio" value="0" <?php echo ( 0 == $grace_period ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Stop multiple logs of the same type from being saved, in quick succession.', 'delightful-downloads' ); ?></p>
	<div id="grace_period_sub" class="dedo-sub-option" style="<?php echo ( $grace_period == 1 ) ? 'display: block;' : 'display: none;';?> ">
		<input type="number" name="delightful-downloads[grace_period_duration]" value="<?php echo esc_attr( $duration ); ?>" min="1" class="small-text" />
		<p class="description"><?php _e( 'The time in minutes before creating a new log.', 'delightful-downloads' ); ?></p>
	</div>
	<?php
}

/**
 * Render auto delete field
 *
 * @since  1.4
 */
function dedo_settings_auto_delete_field() {
	global $dedo_options;
	$auto_delete = $dedo_options['auto_delete'];
	$duration = $dedo_options['auto_delete_duration'];
	?>
	
	<label for="auto_delete_toggle_true"><input name="delightful-downloads[auto_delete]" id="auto_delete_toggle_true" type="radio" value="1" <?php echo ( 1 == $auto_delete ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="auto_delete_toggle_false"><input name="delightful-downloads[auto_delete]" id="auto_delete_toggle_false" type="radio" value="0" <?php echo ( 0 == $auto_delete ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Automatically delete old logs.', 'delightful-downloads' ); ?></p>
	<div id="auto_delete_sub" class="dedo-sub-option" style="<?php echo ( $auto_delete == 1 ) ? 'display: block;' : 'display: none;';?> ">
		<input type="number" name="delightful-downloads[auto_delete_duration]" value="<?php echo esc_attr( $duration ); ?>" min="1" class="small-text" />
		<p class="description"><?php _e( 'The time in days to keep logs.', 'delightful-downloads' ); ?></p>
	</div>
	<?php
}

/**
 * Render enable css field
 *
 * @since  1.3
 */
function dedo_settings_enable_css_field() {
	global $dedo_options;
	$checked = absint( $dedo_options['enable_css'] );
	?>

	<label for="enable_css_true"><input name="delightful-downloads[enable_css]" id="enable_css_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="enable_css_false"><input name="delightful-downloads[enable_css]" id="enable_css_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Output the Delightful Downloads stylesheet on the front-end.', 'delightful-downloads' ); ?></p>
	<?php
}

/**
 * Render cache duration field
 *
 * @since  1.3
 */
function dedo_settings_cache_field() {
	global $dedo_options;
	$cache = $dedo_options['cache'];
	$duration = $dedo_options['cache_duration'];
	?>

	<label for="cache_toggle_true"><input name="delightful-downloads[cache]" id="cache_toggle_true" type="radio" value="1" <?php echo ( 1 == $cache ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="cache_toggle_false"><input name="delightful-downloads[cache]" id="cache_toggle_false" type="radio" value="0" <?php echo ( 0 == $cache ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Cache database queries that are expensive to generate.', 'delightful-downloads' ); ?></p>
	<div id="cache_sub" class="dedo-sub-option" style="<?php echo ( $cache == 1 ) ? 'display: block;' : 'display: none;';?> ">
		<input type="number" name="delightful-downloads[cache_duration]" value="<?php echo esc_attr( $duration ); ?>" min="1" class="small-text" />
		<p class="description"><?php _e( 'The time in minutes to cache queries.', 'delightful-downloads' ); ?></p>
	</div>
	<?php
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
	echo '<p class="description">' . __( 'The URL for download links.', 'delightful-downloads' ) . ' <code>' . dedo_download_link( 123 ) . '</code></p>';
}

/**
 * Render Upload Directory field
 */
function dedo_settings_upload_directory_field() {
	global $dedo_options;

	$text = $dedo_options['upload_directory'];

	echo '<input type="text" name="delightful-downloads[upload_directory]" value="' . esc_attr( $text ) . '" class="regular-text" />';
	echo '<p class="description">' . __( 'The directory to upload files.', 'delightful-downloads' ) . ' <code>' . trailingslashit( dedo_get_upload_dir( 'dedo_baseurl' ) ) . '</code></p>';
}

/**
 * Render Folder Protection field
 *
 * @since  1.5
 */
function dedo_settings_folder_protection_field() {
	global $dedo_options;
	$checked = absint( $dedo_options['folder_protection'] );
	?>

	<label for="folder_protection_true"><input name="delightful-downloads[folder_protection]" id="folder_protection_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="folder_protection_false"><input name="delightful-downloads[folder_protection]" id="folder_protection_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Stop direct access to uploaded files, within the Delightful Downloads upload directory.', 'delightful-downloads' ); ?></p>
	<?php
}

/**
 * Render Uninstall field
 *
 * @since  1.3.6
 */
function dedo_settings_uninstall_field() {
	global $dedo_options;
	$checked = absint( $dedo_options['uninstall'] );
	?>

	<label for="uninstall_true"><input name="delightful-downloads[uninstall]" id="uninstall_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="uninstall_false"><input name="delightful-downloads[uninstall]" id="uninstall_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Completely remove all data associated with Delightful Downloads, when uninstalling the plugin. All downloads, categories, tags and statistics will be removed.', 'delightful-downloads' ); ?></p>
	<?php
}

/**
 * Validate settings callback
 *
 * @since  1.3
 */
function dedo_validate_settings( $input ) {
	global $dedo_options, $dedo_default_options;

	// Registered options
	$options = dedo_get_options();

	// Ensure text fields are not blank
	foreach( $options as $key => $value ) {
		if ( 'text' !== $options[ $key ]['type'] ) {
			continue;
		}

		// None empty text fields
		if ( 'licenses' !== $options[ $key ]['tab'] && '' === trim( $input[ $key ] ) ) {
			$input[ $key ] = $dedo_default_options[ $key ];
		}
	}
	 
	// Ensure download URL does not contain illegal characters
	$input['download_url'] = strtolower( preg_replace( '/[^A-Za-z0-9\_\-]/', '', $input['download_url'] ) );

	// Ensure upload directory does not contain illegal characters
	$input['upload_directory'] = strtolower( preg_replace( '/[^A-Za-z0-9\_\-]/', '', $input['upload_directory'] ) );

	// Run folder protection if option changed
	if ( $input['folder_protection'] != $dedo_options['folder_protection'] ) {
		dedo_folder_protection( $input['folder_protection'] );
	}
	
	// Clear transients
	dedo_delete_all_transients();

	return apply_filters( 'dedo_validate_settings', $input );
}

/**
 * Render Import Modal
 *
 * @since  1.5
 */
function dedo_render_part_import() {

	// Ensure only added on settings screen	
	$screen = get_current_screen();

	if ( 'dedo_download_page_dedo_settings' !== $screen->id ) {

		return;
	}

	?>

	<div id="dedo-settings-import" class="dedo-modal" style="display: none; width: 400px; left: 50%; margin-left: -200px;">
		<a href="#" class="dedo-modal-close" title="Close"><span class="media-modal-icon"></span></a>
		<div class="media-modal-content">
			<h1><?php _e( 'Import Settings', 'delightful-downloads' ); ?></h1>
			<p><?php _e( 'Select a Delightful Downloads settings file to import:', 'delightful-downloads' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings&action=import' ); ?>">
				<p><input type="file" name="json_file"/></p>
				<p>
					<?php wp_nonce_field( 'dedo_import_settings','dedo_import_settings_nonce' ); ?>
					<input type="submit" value="<?php _e( 'Import', 'delightful-downloads' ); ?>" class="button button-primary"/>
				</p>
			</form>
		</div>
	</div>

	<?php

}
add_action( 'admin_footer', 'dedo_render_part_import' );

/**
 * Settings Page Actions
 *
 * @since  1.4
 */
function dedo_settings_actions() {

	//Only perform on settings page, when form not submitted
	if ( isset( $_GET['page'] ) && 'dedo_settings' == $_GET['page'] ) {

		// Import
		if( isset( $_GET['action'] ) && 'import' == $_GET['action'] ) {

			dedo_settings_actions_import();
		}
		// Export
		else if( isset( $_GET['action'] ) && 'export' == $_GET['action'] ) {

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
 * Settings Page Actions Import
 *
 * @since  1.5
 */
function dedo_settings_actions_import() {

	global $dedo_notices;

	// Verfiy nonce
	check_admin_referer( 'dedo_import_settings', 'dedo_import_settings_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {

		return;
	}

	// Check file is uploaded
	if ( isset( $_FILES['json_file'] ) && $_FILES['json_file']['size'] > 0 ) {

		// Check file extension
		if ( 'json' !== dedo_get_file_ext( $_FILES['json_file']['name'] ) ) {

			$dedo_notices->add( 'error', __( 'Invalid settings file.', 'delightful-downloads' ) );

			return;
		}

		// Import and display success
		$import = json_decode( file_get_contents( $_FILES['json_file']['tmp_name'] ), true );

		update_option( 'delightful-downloads', $import );

		$dedo_notices->add( 'updated', __( 'Settings have been successfully imported.', 'delightful-downloads' ) );

		// Redirect page to remove action from URL
		wp_redirect( admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings' ) );
		exit();	
	}
	else {

		$dedo_notices->add( 'error', __( 'No file uploaded.', 'delightful-downloads' ) );

		return;
	}
}

/**
 * Settings Page Actions Export
 *
 * @since  1.5
 */
function dedo_settings_actions_export() {

	global $dedo_options;

	// Verfiy nonce
	check_admin_referer( 'dedo_export_settings', 'dedo_export_settings_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {

		return;
	}

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
 * Settings Page Actions Reset
 *
 * @since  1.5
 */
function dedo_settings_actions_reset() {

	global $dedo_default_options, $dedo_notices;

	// Verfiy nonce
	check_admin_referer( 'dedo_reset_settings', 'dedo_reset_settings_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {

		return;
	}

	delete_option( 'delightful-downloads' );
	add_option( 'delightful-downloads', $dedo_default_options );

	// Add success notice
	$dedo_notices->add( 'updated', __( 'Default settings reset successfully.', 'delightful-downloads' ) );

	// Redirect page to remove action from URL
	wp_redirect( admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings' ) );
	exit();	
}