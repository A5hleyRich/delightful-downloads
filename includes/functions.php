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
 * Check for valid download
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
function dedo_download_filename( $path = '' ) {
	// Strip path, leave filename and extension
	$file = explode( '/', $path );
	
	return end( $file );
}

/**
 * Convert file URL to absolute address
 *
 * @since  1.2.1
 */
function dedo_url_to_absolute( $url ) {
	
	// Get URL of WordPress core files.
	$root_url = site_url();

	// Check for trailing slash and add it if required
	if( preg_match( '/[a-zA-Z0-9\_\-]$/', $root_url ) ) {
		$root_url .= '/';
	}

	return str_replace( $root_url, ABSPATH, $url );
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
 * Return various upload dirs/urls for Delightful Downloads.
 *
 * @since  1.3
 */
function dedo_get_upload_dir( $return = '', $upload_dir = '' ) {
    $upload_dir = ( $upload_dir == '' ? wp_upload_dir() : $upload_dir );

    $upload_dir['path'] = $upload_dir['basedir'] . '/delightful-downloads' . $upload_dir['subdir'];
    $upload_dir['url'] = $upload_dir['baseurl'] . '/delightful-downloads' . $upload_dir['subdir'];
    $upload_dir['dedo_basedir'] = $upload_dir['basedir'] . '/delightful-downloads';
    $upload_dir['dedo_baseurl'] = $upload_dir['baseurl'] . '/delightful-downloads';

    switch( $return ) {
        default:
            return $upload_dir;
            break;
        case 'path':
            return $upload_dir['path'];
            break;
        case 'url':
            return $upload_dir['url'];
            break;
        case 'subdir':
            return $upload_dir['subdir'];
            break;
        case 'basedir':
            return $upload_dir['basedir'];
            break;
        case 'baseurl':
            return $upload_dir['baseurl'];
            break;
        case 'dedo_basedir':
            return $upload_dir['dedo_basedir'];
            break;
        case 'dedo_baseurl':
            return $upload_dir['dedo_baseurl'];
            break;
    }
}

/**
 * Set the upload dir for Delightful Downloads.
 *
 * @since  1.2.1
 */
function dedo_set_upload_dir( $upload_dir ) {

    return dedo_get_upload_dir( '', $upload_dir );
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
 * Returns download count for a single file
 *
 * @since  1.2.2
 */
function dedo_get_single_count( $id, $format = true, $cache = true ) {
	global $dedo_options;

	$cache_duration = $dedo_options['cache_duration'] * 60;
	
	// Check for cached data and set transient
	if( ( $count = get_transient( 'delightful-downloads-count-file-' . $id ) ) === false || $cache === false ) {
		$count = get_post_meta( $id, '_dedo_file_count', true );
		
		if( $cache_duration > 0 ) {
			set_transient( 'delightful-downloads-count-file-' . $id, $count, $cache_duration );
		}
	}

	// Format number with commas
	if( $format === true ) {
		return number_format( $count, 0, '', ',' );
	}
	else {
		return $count;	
	}
}

/**
 * Returns total download count of all files
 *
 * @return int
 */
function dedo_get_total_count( $days = 0, $format = true, $cache = true ) {
	global $wpdb, $dedo_options;
	
	$cache_duration = $dedo_options['cache_duration'] * 60;
	$current_time = current_time( 'mysql' );

	// Set correct SQL query
	if( $days > 0 ) {
		$sql = "SELECT COUNT(*) 
				FROM `$wpdb->posts`
				WHERE `post_type`  = 'dedo_log'
				AND DATE_SUB( '$current_time', INTERVAL $days DAY ) <= `post_date`";
	}
	else {
				$sql = "SELECT SUM(`meta_value`) FROM `$wpdb->postmeta` WHERE `meta_key` = '_dedo_file_count'";
	}

	// Check for cached data and set transient
	if( ( $count = get_transient( 'delightful-downloads-total-count-days-' . $days ) ) === false || $cache === false ) {
		$count = $wpdb->get_var( $sql );
		
		if( $cache_duration > 0 ) {
			set_transient( 'delightful-downloads-total-count-days-' . $days, $count, $cache_duration );
		}
	}
	
	// Format number with commas
	if( $format === true ) {
		return number_format( $count, 0, '', ',' );
	}
	else {
		return $count;	
	}
}

/**
 * Basic crawler detection
 *
 * @since  1.2.1
 */
function dedo_detect_crawler() {

	$crawlers = array( 'Googlebot', 'bingbot', 'msnbot', 'yahoo! slurp', 'ask jeeves', 'jeeves', 'teoma', '80legs' );

	if( in_array( $_SERVER['HTTP_USER_AGENT'], $crawlers ) ) {
		return true;
	}
	return false;
}