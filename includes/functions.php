<?php
/**
 * @package Functions
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Generate download link based on id
 *
 * @param int id dedo_download post type id
 *
 * @return bool
 */
function dedo_download_link( $id ) {
	 return home_url( '?ddownload=' . $id );
}

/**
 * Check user has permission to download file
 *
 * @return bool
 */
function dedo_download_permission() {
	global $dedo_options;
	
	$members_only = $dedo_options['members_only'];
	
	if( $members_only ) {
		// Check user is logged in
		if( is_user_logged_in() ) {
			return true;
		}
		else {
			return false;
		}
	}
	
	return true;
}

/**
 * Get users IP Address
 *
 * @return string
 */
function dedo_download_ip() {
	if( isset( $_SERVER[ 'REMOTE_ADDR' ] ) ) {
		return $_SERVER[ 'REMOTE_ADDR' ];
	}
	else {
		return '0.0.0.0';
	}
}
 
/**
 * Check user has permission to download file
 *
 * @param int $download_id id of download to check is valid
 *
 * @return bool
 */
function dedo_download_valid( $download_id ) {
	if( $download = get_post( $download_id, ARRAY_A ) ) {
		if( $download['post_type'] == 'dedo_download' && $download['post_status'] == 'publish' ) {
			return true;
		}
	}
	return false;
}

/**
 * Get file mime type based on file extension
 *
 * @param string $file filename including extension
 *
 * @return string
 */
function dedo_download_mime( $path ) {
	// Strip path, leave filename and extension
	$file = strtolower( end( explode( '/', $path ) ) );
	$filetype = wp_check_filetype( $file );	
	
	return $filetype['type'];
}

/**
 * Get file name from path
 *
 * @param string $path path to file
 *
 * @return string
 */
function dedo_download_filename( $path ) {
	// Strip path, leave filename and extension
	$file = strtolower( end( explode( '/', $path ) ) );
	
	return $file;
}

/**
 * Send file in chunks - http://codeigniter.com/wiki/Download_helper_for_large_files/
 *
 * @param string $file absolute path to file
 * @param    boolean   return bytes of file
 *
 * @return void
 */
function dedo_download_chunked( $file, $retbytes = true ) {
	$chunksize = 1 * ( 1024 * 1024 );
	$buffer = '';
	$cnt = 0;

	$handle = fopen( $file, 'r' );
	if( $handle === false ) return false;

	while( !feof( $handle ) ) {
	   $buffer = fread( $handle, $chunksize );
	   echo $buffer;
	   ob_flush();
	   flush();

	   if ( $retbytes ) $cnt += strlen( $buffer );
	}

	$status = fclose( $handle );

	if( $retbytes AND $status ) return $cnt;

	return $status;
}

/**
 * Convert bytes to human readable format
 *
 * @param int $bytes file size in bytes
 *
 * @return string
 */
function dedo_human_filesize( $bytes ) {
	//Check a number was sent
    if( !empty( $bytes ) ) {

        //Set text sizes
        $s = array( 'Bytes', 'KB', 'MB', 'GB', 'TB', 'PB' );
        $e = floor( log( $bytes ) / log( 1024 ) );

        //Create output to 1 decimal place and return complete output
        $output = sprintf( '%.1f '.$s[$e], ( $bytes / pow( 1024, floor( $e ) ) ) );
        return $output;
    }
}

/**
 * Sets upload dir as used by wp_handle_upload
 *
 * @param array $upload_dir upload directy
 *
 * @return array
 */
function dedo_set_upload_dir( $upload_dir ) {
	$upload_dir['subdir'] = '/delightful-downloads' . $upload_dir['subdir'];
	$upload_dir['path'] = $upload_dir['basedir'] . $upload_dir['subdir'];
	$upload_dir['url']	= $upload_dir['baseurl'] . $upload_dir['subdir'];
	
	return $upload_dir;
}

/**
 * Logs an error to text file
 *
 * @param string $message error message
 *
 * @return void
 */
function dedo_log_error( $message ) {
	$file = DEDO_PLUGIN_DIR . 'logs/errors.txt';
	$ip_address = dedo_download_ip();
	$date = date( 'D M d H:i:s Y' );
	
	$output = "[" . $date . "] [client " . $ip_address . "] " . $message . "\n";
	
	@file_put_contents( $file, $output, FILE_APPEND | LOCK_EX );
}

/**
 * Returns default options
 *
 * @return array
 */
function dedo_get_default_options() {
	return array(
	 	'members_only'		=> 0,
		'members_redirect'	=> 0,
		'enable_css'		=> 1,
		'default_text'		=> __( 'Download', 'delightful-downloads' ),
		'default_style'		=> 'button',
		'default_color'		=> 'blue',
		'reset_settings'	=> 0 
	);
}

/**
 * Returns shortcode styles
 *
 * @return array
 */
function dedo_get_shortcode_styles() {
	return array(
	 	'button'	=> __( 'Button', 'delightful-downloads' ),
		'link'		=> __( 'Link', 'delightful-downloads' ),
		'text'		=> __( 'Text', 'delightful-downloads' ) 
	);
}
 
/**
 * Returns shortcode colors
 *
 * @return array
 */
function dedo_get_shortcode_colors() {
	return array(
		'black'		=> __( 'Black', 'delightful-downloads' ),
	 	'blue'		=> __( 'Blue', 'delightful-downloads' ),
	 	'grey'		=> __( 'Grey', 'delightful-downloads' ),
	 	'green'		=> __( 'Green', 'delightful-downloads' ),
	 	'purple'	=> __( 'Purple', 'delightful-downloads' ),
	 	'red'		=> __( 'Red', 'delightful-downloads' ),
	 	'yellow'	=> __( 'Yellow', 'delightful-downloads' )
	);
}
 
/**
 * Returns total download count of all files
 *
 * @return int
 */
function dedo_get_total_count() {
	global $wpdb;
	
	$sql = "SELECT SUM(`meta_value`) FROM `$wpdb->postmeta` WHERE `meta_key`='_dedo_file_count'";
	$query = $wpdb->get_var( $sql );
	
	return $query;
}