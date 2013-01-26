<?php
/**
 * @package Process Download
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Process download and send file to user  - http://www.richnetapps.com/php-download-script-with-resume-option/
 *
 * @return void
 */
function dedo_download_process() {
	global $dedo_options;
	
	// Check for file download
	if( isset( $_GET['ddownload'] ) ) {
		$download_id = (int) $_GET['ddownload'];
		
		// Check user has download permissions
		if( dedo_download_permission() ) {
			
			// Check valid download
			if( dedo_download_valid( $download_id ) ) {
				// Grab download info
				$download_url = get_post_meta( $download_id, '_dedo_file_url', true );
				$download_path = str_replace( dedo_root_url(), dedo_root_dir(), $download_url );
				$download_count = get_post_meta( $download_id, '_dedo_file_count', true );
				
				// Check actual file exists
				if( file_exists( $download_path ) ) {
					// Update count and log on non-admins only
					if( !current_user_can( 'administrator' ) ) {
						// Update download count
						update_post_meta( $download_id, '_dedo_file_count', ++$download_count );
					
						// Add log post type
						if( $download_log = wp_insert_post( array( 'post_type' => 'dedo_log', 'post_author' => get_current_user_id() ) ) ) {
							// Add meta data if log sucessfully created
							update_post_meta( $download_log, '_dedo_log_download', $download_id );
							update_post_meta( $download_log, '_dedo_log_ip', dedo_download_ip() );
						}
					}
					
					// Disable gzip compression
					if( function_exists( 'apache_setenv' ) ) @apache_setenv( 'no-gzip', 1 );
					@ini_set( 'zlib.output_compression', 'Off' );

					// Close sessions, which can sometimes cause buffering errors??
					@session_write_close();
					
					// Disable nested buffering.... 3 hours of head scratching!!
					for( $i = 0; $i < ob_get_level(); $i++ ) { @ob_end_clean(); }
					
					// Disable max_execution_time
					@set_time_limit( 0 );
					
					// Set headers
					header( 'Pragma: public' );
					header( 'Expires: -1' );
					header( 'Cache-Control: public, must-revalidate, post-check=0, pre-check=0' );
					header( 'Content-Disposition: attachment; filename="' . basename( $download_path ) . '";' );
					header( 'Content-Type: ' . dedo_download_mime( $download_path ) );
					header( 'Content-Length: ' . filesize( $download_path ) );
					
					// Download file
					@dedo_download_chunked( $download_path );
					exit;
				}
				else {
					// File not found, log error and display message
					dedo_log_error( __( 'File does not exist: ' ) . $download_path );
					wp_die( __( 'File does not exist!', 'delightful-downloads' ) );
				}			
			}
			else {
				// Provided ID is not a valid download, log and display error
				dedo_log_error( __( 'Invalid download: ' ) . $download_id );
				wp_die( __( 'Invalid download.', 'delightful-downloads' ) );
			}
		}
		else {
			// User does not have permission to access file. Log error and attempt to redirect to a page, else display message.
			dedo_log_error( __( 'User does not have permission to access file.' ) . $download_id );
			
			$members_redirect = $dedo_options['members_redirect'];
			
			if( $location = get_permalink( $members_redirect ) ) {
				// Valid page, send the user... Now!
				wp_redirect( $location );
				exit;
			}
			else {
				// Invalid page provided, show error message
				wp_die( __( 'Please login to download this file!', 'delightful-downloads' ) );	
			}
		}
	}
	else {
		return false;
	}		
}
add_action( 'init', 'dedo_download_process', 100 );