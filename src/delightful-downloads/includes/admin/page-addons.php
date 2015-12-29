<?php
/**
 * Delightful Downloads Page Addons
 *
 * @package     Delightful Downloads
 * @subpackage  Admin/Page Addons
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Addons Page
 */
function dedo_register_page_addons() {
	add_submenu_page( 'edit.php?post_type=dedo_download', __( 'Download Add-Ons', 'delightful-downloads' ), __( 'Add-Ons', 'delightful-downloads' ), 'manage_options', 'dedo_addons', 'dedo_render_page_addons' );
}
add_action( 'admin_menu', 'dedo_register_page_addons', 40 );

/**
 * Render page addons
 */
function dedo_render_page_addons() {
	if ( false === ( $addons = get_site_transient( 'dedo_addons' ) ) ) {
		$response = wp_remote_get( trailingslashit( DELIGHTFUL_DOWNLOADS_API ) . 'wp-json/add-ons/v1/all/' );
		$response = wp_remote_retrieve_body( $response );
		$addons   = json_decode( $response );

		if ( is_array( $addons ) && isset( $addons[0]->title ) ) {
			set_site_transient( 'dedo_addons', $addons, HOUR_IN_SECONDS );
		}
	}

	Delightful_Downloads()->render_view( 'addons', array( 'addons' => $addons ) );
}