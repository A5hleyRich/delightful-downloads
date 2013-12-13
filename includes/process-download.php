<?php
/**
 * Delightful Downloads Process Download
 *
 * @package     Delightful Downloads
 * @subpackage  Functions/Process Downloads
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Process download and send file to user 
 * http://www.richnetapps.com/php-download-script-with-resume-option/
 *
 * @return  since 1.0
 */
function dedo_download_process() {
	global $dedo_options;
	
	// Get download id
	if( $dedo_options['download_url_rewrite'] ) {
		$download_id = intval( get_query_var( $dedo_options['download_url'] ) );
	}
	else {
		if( isset( $_GET[$dedo_options['download_url']] ) ) {
			$download_id = intval( $_GET[$dedo_options['download_url']] );
		}
	}
	
	// Check for file download
	if( !empty( $download_id ) ) {

		// Check user has download permissions
		if( dedo_download_permission() ) {
			// Check if user is blocked
			if( dedo_download_blocked( $_SERVER['HTTP_USER_AGENT'] ) ) {
				// Check valid download
				if( dedo_download_valid( $download_id ) ) {
					// Grab download info
					$download_url = get_post_meta( $download_id, '_dedo_file_url', true );
					$download_path = dedo_url_to_absolute( $download_url );
					
					// Check file exists
					if( file_exists( $download_path ) ) {
						// Try to open file, else display server error
						if( $file = @fopen( $download_path, 'rb' ) ) {

							// Log download
							dedo_download_log( $download_id );

							// Disable php notices, can cause corrupt downloads
							@ini_set( 'display_errors', 0 );
							
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
							header( 'Content-Disposition: attachment; filename="' . basename( $download_path ) . '"' );
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
							wp_die( __( 'Server error, file cannot be opened!', 'delightful-downloads' ) );
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
				// User blocked, show error message
				wp_die( __( 'You are blocked from downloading this file!', 'delightful-downloads' ) );
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
add_action( 'template_redirect', 'dedo_download_process' );

/**
 * Add rewrite rules
 *
 * @return  since 1.3
 */
function dedo_rewrite_rules() {
	global $dedo_options;

	// Add rewrite rule if enabled
	if( $dedo_options['download_url_rewrite'] ) {
		add_rewrite_endpoint( $dedo_options['download_url'], EP_ALL );
	}
}
add_action( 'init', 'dedo_rewrite_rules' );