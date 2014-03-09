<?php
/**
 * Delightful Downloads Page Support
 *
 * @package     Delightful Downloads
 * @subpackage  Admin/Page Support
 * @since       1.3
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Support Page
 *
 * Hide it from the menu.
 *
 * @since  1.3
 */
function dedo_register_page_support() {
	add_submenu_page( 'edit.php?post_type=dedo_download', 'Delightful Downloads ' . __( 'Support', 'delightful-downloads' ), __( 'Support', 'delightful-downloads' ), 'manage_options', 'dedo_support', 'dedo_render_page_support' );
	remove_submenu_page( 'edit.php?post_type=dedo_download', 'dedo_support' );
}
add_action( 'admin_menu', 'dedo_register_page_support' );

/**
 * Render Support Page
 *
 * @since  1.3
 */
function dedo_render_page_support() {
	global $dedo_options;

	// Get current theme data
	$theme = wp_get_theme();

	// Get active plugins
	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );
	
	// Prior version
	$prior_version = get_option( 'delightful-downloads-prior-version' );
	?>

	<div class="wrap">
		<h2>Delightful Downloads <?php _e( 'Support', 'delightful-downloads' ); ?></h2>
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
Multisite: <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>
Max Upload Size: <?php echo dedo_format_filesize( wp_max_upload_size() ) . "\n"; ?>

Site Address: <?php echo home_url() . "\n"; ?>
WordPress Address: <?php echo site_url() . "\n"; ?>
Download Address: <?php echo dedo_download_link( 1 ) . "\n"; ?>


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
		</textarea>
	</div>
	<?php
}