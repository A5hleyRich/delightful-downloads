<?php
/**
 * Delightful Downloads Dashboard
 *
 * @package     Delightful Downloads
 * @subpackage  Admin/Dashboard
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Count Downloads Ajax
 *
 * @since  1.4
*/
function dedo_count_downloads_ajax() {

	global $dedo_statistics;

	// Check for nonce and permission
	if ( !check_ajax_referer( 'dedo_dashboard', 'nonce', false ) || !current_user_can( apply_filters( 'dedo_cap_dashboard', 'edit_pages' ) ) ) {
		echo json_encode( array(
			'status'	=> 'error',
			'content'	=> __( 'Failed security check!', 'delightful-downloads' )
		) );
		die();
	}

	// Get counts
	$result = array(
		'ddownload-count-1' 	=> number_format_i18n( $dedo_statistics->count_downloads( array( 'days' => 1, 'cache' => false ) ) ),
		'ddownload-count-7' 	=> number_format_i18n( $dedo_statistics->count_downloads( array( 'days' => 7, 'cache' => false ) ) ),
		'ddownload-count-30'	=> number_format_i18n( $dedo_statistics->count_downloads( array( 'days' => 30, 'cache' => false ) ) ),
		'ddownload-count-0'		=> number_format_i18n( $dedo_statistics->count_downloads( array( 'days' => 0, 'cache' => false ) ) )
	);

	// Return success and data
	echo json_encode( array (
		'status'	=> 'success',
		'content'	=> $result
	) );

	die();
}
add_action( 'wp_ajax_dedo_count_downloads', 'dedo_count_downloads_ajax' );

/**
 * Popular Downloads Ajax
 *
 * @since  1.4
*/
function dedo_popular_downloads_ajax() {

	global $dedo_statistics;

	// Check for nonce and permission
	if ( !check_ajax_referer( 'dedo_dashboard', 'nonce', false ) || !current_user_can( apply_filters( 'dedo_cap_dashboard', 'edit_pages' ) ) ) {
		
		echo json_encode( array(
			'status'	=> 'error',
			'content'	=> __( 'Failed security check!', 'delightful-downloads' )
		) );

		die();
	}

	// Get days from request
	$days = absint( $_REQUEST['days'] );

	// Get popular downloads
	$result = $dedo_statistics->get_popular_downloads( array( 'days' => $days, 'limit' => 5, 'cache' => false ) );

	// Add download URL to array of results
	foreach ( $result as $key => $value ) {

		$result[$key]['url'] = ( !empty( $result[$key]['title'] ) ) ? get_edit_post_link( $value['ID'] ) : admin_url( 'edit.php?post_type=dedo_download' );
		$result[$key]['title'] = ( !empty( $result[$key]['title'] ) ) ? $result[$key]['title'] : __( 'Unknown', 'delightful-downloads' );
		$result[$key]['downloads'] = number_format_i18n( $value['downloads'] );
	}

	// Return success and data
	echo json_encode( array (
		'status'	=> 'success',
		'content'	=> $result
	) );

	die();
}
add_action( 'wp_ajax_dedo_popular_downloads', 'dedo_popular_downloads_ajax' );