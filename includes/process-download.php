<?php
/**
 * Delightful Downloads Process Download
 *
 * @package     Delightful Downloads
 * @subpackage  Includes/Process Downloads
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Process Download
 *
 * Validate download and send file to user 
 * http://www.richnetapps.com/php-download-script-with-resume-option/
 *
 * @since 1.0
 */
function dedo_download_process() {
	global $dedo_options;
	
	// Get download id
	if ( isset( $_GET[$dedo_options['download_url']] ) ) {
		
		// Ensure only positive int, else could be fishy!
		$download_id = absint( $_GET[$dedo_options['download_url']] );
	}
	
	// Check for file download
	if ( isset( $download_id ) ) {

		// Check user has download permissions
		if ( !dedo_download_permission() ) {
			
			// Get redirect page if set, else display error instead
			$members_redirect = $dedo_options['members_redirect'];
			
			if ( $location = get_permalink( $members_redirect ) ) {
				
				wp_redirect( $location );
				exit();
			}
			else {
				
				// Invalid page provided, show error message
				wp_die( __( 'Please login to download this file!', 'delightful-downloads' ) );	
			}
		}

		// Check if user is blocked
		if ( !dedo_download_blocked( $_SERVER['HTTP_USER_AGENT'] ) ) {
			
			do_action( 'ddownload_download_blocked', $download_id );

			// User blocked, show error message
			wp_die( __( 'You are blocked from downloading this file!', 'delightful-downloads' ) );
		}

		// Check valid download
		if ( !dedo_download_valid( $download_id ) ) {
			
			do_action( 'ddownload_download_invalid', $download_id );

			// Provided ID is not a valid download, display error
			wp_die( __( 'Invalid download.', 'delightful-downloads' ) );
		}

		// Grab download info
		$download_url = get_post_meta( $download_id, '_dedo_file_url', true );
		$download_path = dedo_url_to_absolute( $download_url );
			
		// Check file exists
		if ( !file_exists( $download_path ) ) {
			
			// File not found, display message
			wp_die( __( 'File does not exist!', 'delightful-downloads' ) );
		}

		// Try to open file, else display server error
		if ( !$file = @fopen( $download_path, 'rb' ) ) {
			
			// Server error
			wp_die( __( 'Server error, file cannot be opened!', 'delightful-downloads' ) );
		}

		// Stop page caching. Cause conflicts with WP Super Cache
		define( 'DONOTCACHEPAGE', true );	

		// Disable php notices, can cause corrupt downloads
		@ini_set( 'display_errors', 0 );
		
		// Disable compression
		if ( function_exists( 'apache_setenv' ) ) {
			@apache_setenv( 'no-gzip', 1 );
		}

		@ini_set( 'zlib.output_compression', 'Off' );

		// Close sessions, which can sometimes cause buffering errors??
		@session_write_close();
		
		// Disable nested buffering.... 3 hours of head scratching!!
		for ( $i = 0; $i < ob_get_level(); $i++ ) { @ob_end_clean(); }
		
		// Disable max_execution_time
		set_time_limit( 0 );

		// Set headers
		header( 'Pragma: public' );
		header( 'Expires: -1' );
		header( 'Cache-Control: public, must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-Disposition: attachment; filename="' . basename( $download_path ) . '"' );
		header( 'Content-Type: ' . dedo_download_mime( $download_path ) );
		header( 'Content-Length: ' . filesize( $download_path ) );

		// Hook before download starts
		do_action( 'ddownload_download_before', $download_id );

		// Output file in chuncks
		while ( !feof( $file ) ) {
			
			print fread( $file, 1024 * 1024 );
			flush();

			// Check conection, if lost close file and end loop
			if ( connection_status() != 0 ) {
				
				fclose( $file );
				exit();
			}
		}

		// Reached end of file, close it. Job done!
		fclose( $file );

		// Hook when download complete
		do_action( 'ddownload_download_complete', $download_id );
		
		// Done! Exit
		exit();
	}
}
add_action( 'init', 'dedo_download_process', 0 );

// Add log once download complete.
add_action( 'ddownload_download_complete', 'dedo_download_log', 10, 1 );