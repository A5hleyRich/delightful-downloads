<?php
/**
 * Delightful Downloads Scripts
 *
 * @package     Delightful Downloads
 * @subpackage  Includes/Scripts
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Scripts & Styles
 *
 * @since  1.0
 */
function dedo_enqueue_scripts( $page ) {
	global $dedo_options;
	
	// Register frontend CSS
	wp_register_style( 'dedo-css', DEDO_PLUGIN_URL . 'assets/css/delightful-downloads.css', '', '1.0', 'all' );
	
	// Enqueue frontend CSS if option is enabled
	if ( $dedo_options['enable_css'] ) {
		wp_enqueue_style( 'dedo-css' );
	}
}
add_action( 'wp_enqueue_scripts', 'dedo_enqueue_scripts' );

/**
 * Register Admin Scripts & Styles
 *
 * @since  1.0
 */
function dedo_admin_enqueue_scripts( $page ) {
	// Register scripts
	wp_register_script( 'dedo-admin-js-global', DEDO_PLUGIN_URL . 'assets/js/dedo-admin-global.js', array( 'jquery' ), '1.0', true );
	wp_register_script( 'dedo-admin-js-legacy-logs', DEDO_PLUGIN_URL . 'assets/js/admin-legacy-logs.js', array( 'jquery' ), '1.0', true ); // 1.4 upgrade
	wp_register_script( 'dedo-admin-js-media-button', DEDO_PLUGIN_URL . 'assets/js/admin-media-button.js', array( 'jquery', 'jquery-ui-selectable' ), '1.0', true );
	wp_register_script( 'dedo-admin-js-post-download', DEDO_PLUGIN_URL . 'assets/js/admin-post-download.js', array( 'jquery', 'plupload-all' ), '1.0', true );
	wp_register_script( 'jqueryFileTree', DEDO_PLUGIN_URL . 'assets/js/jqueryFileTree/jqueryFileTree.js', array( 'jquery' ), '1.01', true );

	// Register styles
	wp_register_style( 'dedo-css-admin', DEDO_PLUGIN_URL . 'assets/css/delightful-downloads-admin.css', '', '1.0', 'all' );
	wp_register_style( 'jqueryFileTree-css', DEDO_PLUGIN_URL . 'assets/js/jqueryFileTree/jqueryFileTree.css', '', '1.0', 'all' );

	// Enqueue on all admin pages
	wp_enqueue_style( 'dedo-css-admin' );
	wp_enqueue_script( 'dedo-admin-js-global' );
	wp_enqueue_script( 'dedo-admin-js-media-button' );

	// Enqueue on dedo_download post add/edit screen
	if ( in_array( $page, array( 'post.php', 'post-new.php', 'post-edit.php' ) ) && get_post_type() == 'dedo_download' ) {
		wp_enqueue_script( 'dedo-admin-js-post-download' );
		wp_enqueue_script( 'jqueryFileTree' );
		wp_enqueue_style( 'jqueryFileTree-css' );
	}
}
add_action( 'admin_enqueue_scripts', 'dedo_admin_enqueue_scripts' );