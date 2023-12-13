<?php
/**
 * Delightful Downloads Process Download
 *
 * @package     Delightful Downloads
 * @subpackage  Includes/Process Downloads and secure one day pass downloads
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Process Download
 *
 * Validate download and send file to user
 * http://www.richnetapps.com/php-download-script-with-resume-option/
 *
 * @since 1.0
 */

// Secure download one day pass
function dedo_onedaypass_process( $download_id ) {
	global $dedo_options;

	// Check valid download
	if ( ! dedo_download_valid( $download_id ) ) {
		do_action( 'ddownload_download_invalid', $download_id );
		wp_die( __( 'Invalid download.', 'delightful-downloads' ) );
	}
	// Get file meta
	$download_url = get_post_meta( $download_id, '_dedo_file_url', true );
	$options      = get_post_meta( $download_id, '_dedo_file_options', true );

	// Disable max_execution_time
	set_time_limit( 0 );

	// Hook before download starts
	do_action( 'ddownload_download_before', $download_id );
	
    // Onedaypass prüfen
	$datetime = new DateTime('now');
	$hashwert = md5( intval($download_id) + intval($datetime->format('Ymd')) );
	if ( file_exists(dedo_get_abs_path( $download_url ) ) && $_GET['code'] == $hashwert ) { // if it match it is legit
		// $path = ABSPATH.'wp-content/uploads/delightful-downloads/2019/software.zip'; // the file made available for download via this PHP file
		$path = dedo_get_abs_path( $download_url );
		$mm_type="application/octet-stream"; // modify accordingly to the file type of $path, but in most cases no need to do so
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Type: " . $mm_type);
		header("Content-Length: " .(string)(filesize($path)) );
		header('Content-Disposition: attachment; filename="'.basename($path).'"');
		header("Content-Transfer-Encoding: binary\n");
		readfile($path); // outputs the content of the file
		// Hook when download complete
		do_action( 'ddownload_download_complete', $download_id );
		exit();		  
	} else {
		wp_die( __( 'download not found or onedaypass invalid or expired.', 'delightful-downloads' ) ); // not legit
	}  

}


function dedo_download_process( $download_id ) {
	global $dedo_options;

	// Check valid download
	if ( ! dedo_download_valid( $download_id ) ) {
		do_action( 'ddownload_download_invalid', $download_id );
		wp_die( __( 'Invalid download.', 'delightful-downloads' ) );
	}

	// Check blocked user agents
	if ( ! dedo_download_blocked( $_SERVER['HTTP_USER_AGENT'] ) ) {
		do_action( 'ddownload_download_blocked', $download_id );
		wp_die( __( 'You are blocked from downloading this file!', 'delightful-downloads' ) );
	}

	if ( apply_filters( 'dedo_abort_download', false, $download_id ) ) {
		return;
	}

	// Get file meta
	$download_url = get_post_meta( $download_id, '_dedo_file_url', true );
	$options      = get_post_meta( $download_id, '_dedo_file_options', true );

	// Check for members only
	if ( ! dedo_download_permission( $options ) ) {
		do_action( 'ddownload_download_permission', $download_id );

		// Get redirect location
		$location = ( isset( $options['members_only_redirect'] ) ) ? $options['members_only_redirect'] : $dedo_options['members_only_redirect'];

		// Try to redirect
		if ( $location = get_permalink( $location ) ) {
			wp_redirect( $location );
			exit();
		} else {
			// Invalid page provided, show error message
			wp_die( __( 'Please login to download this file!', 'delightful-downloads' ) );
		}
	}

	// Password protected
	if ( post_password_required( $download_id ) ) {
		wp_die( get_the_password_form( $download_id ), __( 'Password Required', 'delightful-downloads' ) );
	}

	// Empty file urls not allowed
	if ( '' === $download_url ) {
		wp_die( __( 'You must attach a file to this download.', 'delightful-downloads' ) );
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

	/**
	 * Output Buffering
	 *
	 * The majority of servers work when clearing output buffering.
	 * If you get corrupt or blank downloads try the following:
	 *
	 * Disable by adding the following, to your theme's functions.php file:
	 *
	 * add_filter( 'dedo_clear_output_buffers', '__return_false' );
	 *
	 */
	if ( apply_filters( 'dedo_clear_output_buffers', true ) ) {
		do {
			@ob_end_clean();
		} while ( ob_get_level() > 0 );
	}

	// Disable max_execution_time
	set_time_limit( 0 );

	// Hook before download starts
	do_action( 'ddownload_download_before', $download_id );

	// Open in browser
	$open_browser = ( isset( $options['open_browser'] ) ) ? $options['open_browser'] : $dedo_options['open_browser'];

	if ( $open_browser ) {
		header( "Location: $download_url" );
		exit();
	}

	// Convert to path
	if ( $download_path = dedo_get_abs_path( $download_url ) ) {
		// Try to open file, else display server error
		if ( ! $file = @fopen( $download_path, 'rb' ) ) {
			// Server error
			wp_die( __( 'Server error, file cannot be opened!', 'delightful-downloads' ) );
		}

		// Set headers
		nocache_headers();
		header( "X-Robots-Tag: noindex, nofollow", true );
		header( "Content-Type: " . dedo_download_mime( $download_path ) );
		header( "Content-Description: File Transfer" );
		header( "Content-Disposition: attachment; filename=\"" . basename( $download_path ) . "\";" );
		header( "Content-Transfer-Encoding: binary" );
		header( "Content-Length: " . @filesize( $download_path ) ); // filesize causes blank downloads on Windows servers

		// Output file in chuncks
		while ( ! feof( $file ) ) {

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
	} else {
		// No disoverable path, redirect to file
		header( "Location: $download_url" );
		exit();
	}
}

/**
 * Init handle download.
 */
function dedo_init_handle_download() {
	global $dedo_options;

	if ( isset( $_GET[ $dedo_options['download_url'] ] ) ) {
		dedo_download_process( absint( $_GET[ $dedo_options['download_url'] ] ) );
	}
	if ( isset( $_GET[ 'sdownload' ] ) ) {
		dedo_onedaypass_process( absint( $_GET[ 'sdownload' ] ) );
	}
}
add_action( 'init', 'dedo_init_handle_download', 4 );