<?php
/**
 * @package Scripts
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register scripts and styles
 *
 * @param string $page current page
 *
 * @return bool
 */
function dedo_enqueue_scripts( $page ) {
	global $dedo_options;
	
	// Register frontend CSS
	wp_register_style( 'dedo-css', DEDO_PLUGIN_URL . 'includes/css/delightful-downloads.css', '', '1.0', 'all' );
	
	// Enqueue frontend CSS if option is enabled
	if( $dedo_options['enable_css'] ) {
		wp_enqueue_style( 'dedo-css' );
	}
}
add_action( 'wp_enqueue_scripts', 'dedo_enqueue_scripts' );

/**
 * Register admin scripts and style
 *
 * @param string $page current page
 *
 * @return bool
 */
function dedo_admin_enqueue_scripts( $page ) {
	// Register scripts
	wp_register_script( 'dedo-admin-js-post', DEDO_PLUGIN_URL . 'includes/js/admin-post.js', array( 'jquery', 'jquery-ui-selectable' ), '1.0', true );
	wp_register_script( 'dedo-admin-js-post-download', DEDO_PLUGIN_URL . 'includes/js/admin-post-download.js', array( 'jquery', 'plupload-all' ), '1.0', true );
	wp_register_script( 'jqueryFileTree', DEDO_PLUGIN_URL . 'includes/js/jqueryFileTree/jqueryFileTree.js', array( 'jquery' ), '1.01', true );
	
	// Enqueue on all admin pages
	wp_enqueue_style( 'dedo-admin-css', DEDO_PLUGIN_URL . 'includes/css/delightful-downloads-admin.css' );
	
	// Enqueue on dedo_download post add/edit screen
	if( in_array( $page, array( 'post.php', 'post-new.php', 'post-edit.php' ) ) && get_post_type() == 'dedo_download' ) {
		wp_enqueue_script( 'plupload-all' );
		wp_enqueue_script( 'dedo-admin-js-post-download' );
		wp_enqueue_script( 'jqueryFileTree' );
		wp_enqueue_style( 'jqueryFileTree-css', DEDO_PLUGIN_URL . 'includes/js/jqueryFileTree/jqueryFileTree.css' );
	}
	
	// Enqueue on all other add/edit screen
	if( in_array( $page, array( 'post.php', 'post-new.php', 'post-edit.php', 'page.php' ) ) && get_post_type() != 'dedo_download' ) {
		wp_enqueue_script( 'dedo-admin-js-post' );
	}
}
add_action( 'admin_enqueue_scripts', 'dedo_admin_enqueue_scripts' );