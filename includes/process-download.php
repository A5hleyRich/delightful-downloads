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
				$download_path = dedo_url_to_absolute( $download_url );
				$download_count = get_post_meta( $download_id, '_dedo_file_count', true );
				
				// Check file exists
				if( file_exists( $download_path ) ) {
					// Try to open file, else display server error
					if( $file = @fopen( $download_path, 'rb' ) ) {

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

						// Disable php notices, can cause corrupt downloads
						@ini_set( 'error_reporting', 0 );
						
						// Disable gzip compression
						@apache_setenv( 'no-gzip', 1 );
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
						//header( 'Accept-Ranges: bytes' );

						/*
						// Check for resumable download
						if( isset( $_SERVER['HTTP_RANGE'] ) ) {
							list( $param, $range ) = explode( '=', $_SERVER['HTTP_RANGE'] );
							// Check for bytes
							if( strtolower( trim( $param ) ) != 'bytes' ) {
								header( 'HTTP/1.1 416 Requested Range Not Satisfiable' );
								exit();
							}
							// Get bytes from first range
							$range = explode( ',', $range, 2 );
							list( $start, $end ) = explode( '-', $range );
							
							// Set correct ranges
							$start = ( empty( $start ) || $end < $start ? 0 : );

							$seek_end   = (empty($seek_end)) ? ($file_size - 1) : min(abs(intval($seek_end)),($file_size - 1));
							$seek_start = (empty($seek_start) || $seek_end < abs(intval($seek_start))) ? 0 : max(abs(intval($seek_start)),0);

							// Fast forward to requested bytes
							fseek( $file, $start );
						}
						*/

						// Output file in chuncks
						while( !feof( $file ) ) {
							print @fread( $file, 1024 * 1024 );
							flush();

							// Check conection, if lost close file and end loop
							if( connection_status() != 0 ) {
								@fclose( $file );
								exit();
							}
						}

						// Reached end of file, close it. Job done!
						@fclose( $file );
						exit();
					}
					else {
						// Server error
						wp_die( __( 'File cannot be opened!', 'delightful-downloads' ) );
					}
				}
				else {
					// File not found, display message
					wp_die( __( 'File does not exist!', 'delightful-downloads' ) );
				}		
			}
			else {
				// Provided ID is not a valid download, display error
				wp_die( __( 'Invalid download.', 'delightful-downloads' ) );
			}
		}
		else {
			// User does not have permission to access file. Attempt to redirect to a page, else display message.
			
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