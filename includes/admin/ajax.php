<?php
/**
 * @package Ajax
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Process Ajax upload file
 *
 * @return void
 */
function dedo_download_upload_ajax() {
	check_ajax_referer( 'dedo_download_upload' );
	
	// Set upload dir
	add_filter( 'upload_dir', 'dedo_set_upload_dir' );
	
	// Upload the file
	$file = wp_handle_upload( $_FILES['async-upload'], array( 'test_form'=> true, 'action' => 'dedo_download_upload' ) );
	
	// Check for success
	if( $file ) {
		// Post ID
		$post_id = $_REQUEST['post_id'];
	
		// Add/update post meta
		update_post_meta( $post_id, '_dedo_file_url', $file['url'] );
		update_post_meta( $post_id, '_dedo_file_size', $_FILES['async-upload']['size'] );
	
		// Echo success response
		echo $file['url'];
		die();
	}	
	else {
		// Echo error message
		echo $file['error'];
		die();
	}
}
add_action( 'wp_ajax_dedo_download_upload', 'dedo_download_upload_ajax' );