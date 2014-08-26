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
 * Register Frontend Scripts & Styles
 *
 * @since  1.0
 */
function dedo_enqueue_scripts( $page ) {
	// Register frontend CSS
	wp_register_style( 'dedo-css', DEDO_PLUGIN_URL . 'assets/css/delightful-downloads.css', '', DEDO_VERSION, 'all' );
}
add_action( 'wp_enqueue_scripts', 'dedo_enqueue_scripts' );

/**
 * Register Admin Scripts & Styles
 *
 * @since  1.0
 */
function dedo_admin_enqueue_scripts( $page ) {
	// Register scripts
	wp_register_script( 'dedo-admin-js-global', DEDO_PLUGIN_URL . 'assets/js/dedo-admin-global.js', array( 'jquery' ), DEDO_VERSION, true );
	wp_register_script( 'dedo-admin-js-legacy-logs', DEDO_PLUGIN_URL . 'assets/js/dedo-admin-legacy-logs.js', array( 'jquery' ), DEDO_VERSION, true ); // 1.4 upgrade
	wp_register_script( 'dedo-admin-js-media-button', DEDO_PLUGIN_URL . 'assets/js/dedo-admin-media-button.js', array( 'jquery', 'dedo-jqueryChosen' ), DEDO_VERSION, true );
	wp_register_script( 'dedo-admin-js-post-download', DEDO_PLUGIN_URL . 'assets/js/dedo-admin-post-download.js', array( 'jquery', 'plupload-all', 'jqueryFileTree' ), DEDO_VERSION, true );
	wp_register_script( 'jqueryFileTree', DEDO_PLUGIN_URL . 'assets/js/jqueryFileTree/jqueryFileTree.js', array( 'jquery' ), DEDO_VERSION, true );
	wp_register_script( 'dedo-jqueryChosen', DEDO_PLUGIN_URL . 'assets/js/jqueryChosen/chosen.jquery.min.js', array( 'jquery' ), DEDO_VERSION, true );

	// Register styles
	wp_register_style( 'dedo-css-admin', DEDO_PLUGIN_URL . 'assets/css/delightful-downloads-admin.css', '', DEDO_VERSION, 'all' );
	wp_register_style( 'jqueryFileTree-css', DEDO_PLUGIN_URL . 'assets/js/jqueryFileTree/jqueryFileTree.css', '', DEDO_VERSION, 'all' );
	wp_register_style( 'dedo-jqueryChosen-css', DEDO_PLUGIN_URL . 'assets/js/jqueryChosen/chosen.min.css', '', DEDO_VERSION, 'all' );

	// Enqueue on all admin pages
	wp_enqueue_style( 'dedo-css-admin' );
	wp_enqueue_script( 'dedo-admin-js-global' );

	// Enqueue on dedo_download post add/edit screen
	if ( in_array( $page, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) && get_post_type() == 'dedo_download' ) {
		wp_enqueue_script( 'dedo-admin-js-post-download' );
		wp_enqueue_style( 'jqueryFileTree-css' );
	}

	// Enqueue on all post/edit screen
	if ( in_array( $page, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
		wp_enqueue_script( 'dedo-admin-js-media-button' );
		wp_enqueue_style( 'dedo-jqueryChosen-css' );
	}
}
add_action( 'admin_enqueue_scripts', 'dedo_admin_enqueue_scripts' );