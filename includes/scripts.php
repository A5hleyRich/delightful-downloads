<?php
/**
 * Delightful Downloads Scripts
 *
 * @package     Delightful Downloads
 * @subpackage  Includes/Scripts
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Frontend Scripts & Styles
 *
 * @since  1.0
 */
function dedo_enqueue_scripts( $page ) {
	global $dedo_options;

	// Enqueue frontend CSS if option is enabled
	if ( ! $dedo_options['enable_css'] ) {
		return;
	}

	$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : DEDO_VERSION;
	$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	// Register frontend CSS
	$src = DEDO_PLUGIN_URL . 'assets/css/delightful-downloads' . $suffix . '.css';
	wp_enqueue_style( 'dedo-css', $src, array(), $version, 'all' );
}
add_action( 'wp_enqueue_scripts', 'dedo_enqueue_scripts' );

/**
 * Register Admin Scripts & Styles
 *
 * @since  1.0
 */
function dedo_admin_enqueue_scripts( $page ) {
	$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : DEDO_VERSION;
	$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	// Register scripts
	$src = DEDO_PLUGIN_URL . 'assets/js/dedo-admin-global' . $suffix . '.js';
	wp_register_script( 'dedo-admin-js-global', $src, array( 'jquery' ), $version, true );

	$src = DEDO_PLUGIN_URL . 'assets/js/dedo-admin-legacy-logs' . $suffix . '.js';
	wp_register_script( 'dedo-admin-js-legacy-logs', $src, array( 'jquery' ), $version, true ); // 1.4 upgrade

	$src = DEDO_PLUGIN_URL . 'assets/js/dedo-admin-media-button' . $suffix . '.js';
	wp_register_script( 'dedo-admin-js-media-button', $src, array( 'jquery', 'dedo-jqueryChosen' ), $version, true );

	$src = DEDO_PLUGIN_URL . 'assets/js/dedo-admin-post-download' . $suffix . '.js';
	wp_register_script( 'dedo-admin-js-post-download', $src, array(
		'jquery',
		'plupload-all',
		'jqueryFileTree'
	), $version, true );

	$src = DEDO_PLUGIN_URL . 'assets/vendor/jqueryFileTree/jqueryFileTree.js';
	wp_register_script( 'jqueryFileTree', $src, array( 'jquery' ), $version, true );

	$src = DEDO_PLUGIN_URL . 'assets/vendor/jqueryChosen/chosen.jquery' . $suffix . '.js';
	wp_register_script( 'dedo-jqueryChosen', $src, array( 'jquery' ), $version, true );

	$src = DEDO_PLUGIN_URL . 'assets/vendor/Vue/vue' . $suffix . '.js';
	wp_register_script( 'dedo-vue', $src, array(), '1.0.10', true );

	// Register styles
	$src = DEDO_PLUGIN_URL . 'assets/css/delightful-downloads-admin' . $suffix . '.css';
	wp_register_style( 'dedo-css-admin', $src, array(), $version, 'all' );
	$src = DEDO_PLUGIN_URL . 'assets/vendor/jqueryFileTree/jqueryFileTree.css';
	wp_register_style( 'jqueryFileTree-css', $src, array(), $version, 'all' );
	$src = DEDO_PLUGIN_URL . 'assets/vendor/jqueryChosen/chosen' . $suffix . '.css';
	wp_register_style( 'dedo-jqueryChosen-css', $src, array(), $version, 'all' );

	// Enqueue on all admin pages
	wp_enqueue_style( 'dedo-css-admin' );
	wp_enqueue_script( 'dedo-admin-js-global' );

	// JS copy to clipboard
	$src = DEDO_PLUGIN_URL . 'assets/js/copy-to-clipboard' . $suffix . '.js';
	wp_enqueue_script( 'dedo-copy-to-clipboard', $src, array(
		'jquery',
	), $version, true );

	// Enqueue on dedo_download post add/edit screen
	if ( in_array( $page, array(
			'post.php',
			'page.php',
			'post-new.php',
			'post-edit.php'
		) ) && get_post_type() == 'dedo_download'
	) {
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