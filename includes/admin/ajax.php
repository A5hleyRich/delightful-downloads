<?php
/**
 * Delightful Downloads Ajax
 *
 * @package     Delightful Downloads
 * @subpackage  Admin/Ajax
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Process Ajax upload file
 *
 * @since  1.0
 */
function dedo_download_upload_ajax() {
	
	if ( !check_ajax_referer( 'dedo_download_upload', false, false ) ) {

		// Echo error message
		die( '{ "jsonrpc" : "2.0", "error" : {"code": 500, "message": "' . __( 'Failed security checks!', 'delightful-downloads' ) . '" } }' );
	}

	// Set upload dir
	add_filter( 'upload_dir', 'dedo_set_upload_dir' );
	
	// Upload the file
	$file = wp_handle_upload( $_FILES['async-upload'], array( 'test_form'=> true, 'action' => 'dedo_download_upload' ) );
	
	// Check for success
	if ( isset( $file['url'] ) ) {
		// Post ID
		$post_id = $_REQUEST['post_id'];
	
		// Add/update post meta
		update_post_meta( $post_id, '_dedo_file_url', $file['url'] );
		update_post_meta( $post_id, '_dedo_file_size', $_FILES['async-upload']['size'] );
	
		// Echo success response
		die( '{"jsonrpc" : "2.0", "file" : {"url": "' . $file['url'] . '"}}' );
	}	
	else {
		// Echo error message
		die( '{"jsonrpc" : "2.0", "error" : {"code": 500, "message": "' . $file['error'] . '"}, "details" : "' . $file['error'] . '"}' );
	}
}
add_action( 'wp_ajax_dedo_download_upload', 'dedo_download_upload_ajax' );