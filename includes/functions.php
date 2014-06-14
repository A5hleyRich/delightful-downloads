<?php
/**
 * Delightful Downloads Functions
 *
 * @package     Delightful Downloads
 * @subpackage  Includes/Functions
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode Styles
 *
 * @since  1.0
 */
function dedo_get_shortcode_styles() {
	
	$styles = array(
	 	'button'		=> array(
	 		'name'			=> __( 'Button', 'delightful-downloads' ),
	 		'format'		=> '<a href="%url%" title="%text%" rel="nofollow" class="%class%">%text%</a>'
	 	),
	 	'link'			=> array(
	 		'name'			=> __( 'Link', 'delightful-downloads' ),
	 		'format'		=> '<a href="%url%" title="%text%" rel="nofollow" class="%class%">%text%</a>'
	 	),
	 	'plain_text'	=> array(
	 		'name'			=> __( 'Plain Text', 'delightful-downloads' ),
	 		'format'		=> '%url%'
	 	)
	);

	return apply_filters( 'dedo_get_styles', $styles );
}

/**
 * Shortcode Buttons
 *
 * @since  1.3
 */
function dedo_get_shortcode_buttons() {
	
	$buttons =  array(
		'black'		=> array(
			'name'		=> __( 'Black', 'delightful-downloads' ),
			'class'		=> 'button-black'
		),
		'blue'		=> array(
			'name'		=> __( 'Blue', 'delightful-downloads' ),
			'class'		=> 'button-blue'
		),
		'grey'		=> array(
			'name'		=> __( 'Grey', 'delightful-downloads' ),
			'class'		=> 'button-grey'
		),
		'green'		=> array(
			'name'		=> __( 'Green', 'delightful-downloads' ),
			'class'		=> 'button-green'
		),
		'purple'	=> array(
			'name'		=> __( 'Purple', 'delightful-downloads' ),
			'class'		=> 'button-purple'
		),
		'red'		=> array(
			'name'		=> __( 'Red', 'delightful-downloads' ),
			'class'		=> 'button-red'
		),
		'yellow'	=> array(
			'name'		=> __( 'Yellow', 'delightful-downloads' ),
			'class'		=> 'button-yellow'
		)
	);

	return apply_filters( 'dedo_get_buttons', $buttons );
}

/**
 * Returns List Styles
 *
 * @since  1.3
 */
function dedo_get_shortcode_lists() {
	
	$lists = array(
	 	'title'				=> array(
	 		'name'				=> __( 'Title', 'delightful-downloads' ),
	 		'format'			=> '<a href="%url%" title="%title%" rel="nofollow">%title%</a>'
	 	),
	 	'title_date'		=> array(
	 		'name'				=> __( 'Title (Date)', 'delightful-downloads' ),
	 		'format'			=> '<a href="%url%" title="%title% (%date%)" rel="nofollow">%title% (%date%)</a>'
	 	),
	 	'title_count'		=> array(
	 		'name'				=> __( 'Title (Count)', 'delightful-downloads' ),
	 		'format'			=> '<a href="%url%" title="%title% (Downloads: %count%)" rel="nofollow">%title% (Downloads: %count%)</a>'
	 	),
	 	'title_filesize'	=> array(
	 		'name'				=> __( 'Title (File size)', 'delightful-downloads' ),
	 		'format'			=> '<a href="%url%" title="%title% (%filesize%)" rel="nofollow">%title% (%filesize%)</a>'
	 	),
	 	'title_ext_filesize'=> array(
	 		'name'				=> __( 'Title (Extension, File size)', 'delightful-downloads' ),
	 		'format'			=> '<a href="%url%" title="%title% (%ext%, %filesize%)" rel="nofollow">%title% (%ext%, %filesize%)</a>'
	 	)
	);

	return apply_filters( 'dedo_get_lists', $lists );
}

/**
 * Replace Wildcards
 *
 * @since  1.3
 */
 function dedo_search_replace_wildcards( $string, $id ) {

 	// id
 	if ( strpos( $string, '%id%' ) !== false ) {
 		$string = str_replace( '%id%', $id, $string );
 	}

 	// url
 	if ( strpos( $string, '%url%' ) !== false ) {
 		$value = dedo_download_link( $id );
 		$string = str_replace( '%url%', $value, $string );
 	}

 	// title
 	if ( strpos( $string, '%title%' ) !== false ) {
 		$value = get_the_title( $id );
 		$string = str_replace( '%title%', $value, $string );
 	}

 	// date
 	if ( strpos( $string, '%date%' ) !== false ) {
 		$value = get_the_date( apply_filters( 'dedo_shortcode_date_format', '' ) );
 		$string = str_replace( '%date%', $value, $string );
 	}

 	// filesize
 	if ( strpos( $string, '%filesize%' ) !== false ) {
 		$value = size_format( get_post_meta( $id, '_dedo_file_size', true ), 1 );
 		$string = str_replace( '%filesize%', $value, $string );
 	}

 	// downloads
 	if ( strpos( $string, '%count%' ) !== false ) {
 		$value = number_format_i18n( get_post_meta( $id, '_dedo_file_count', true ) );
 		$string = str_replace( '%count%', $value, $string );
 	}

 	// file name
 	if ( strpos( $string, '%filename%' ) !== false ) {
 		$value = dedo_get_file_name( get_post_meta( $id, '_dedo_file_url', true ) );
 		$string = str_replace( '%filename%', $value, $string );
 	}

 	// file extension
 	if ( strpos( $string, '%ext%' ) !== false ) {
 		$value = strtoupper( dedo_get_file_ext( get_post_meta( $id, '_dedo_file_url', true ) ) );
 		$string = str_replace( '%ext%', $value, $string );
 	}

 	 // file mime
 	if ( strpos( $string, '%mime%' ) !== false ) {
 		$value = dedo_get_file_mime( get_post_meta( $id, '_dedo_file_url', true ) );
 		$string = str_replace( '%mime%', $value, $string );
 	}

 	return apply_filters( 'dedo_search_replace_wildcards', $string, $id );
 }

/**
 * Download Link
 *
 * Generate download link based on provided id.
 *
 * @since  1.0
 */
function dedo_download_link( $id ) {
	 global $dedo_options;
	 
	 $output = esc_html( home_url( '?' . $dedo_options['download_url'] . '=' . $id ) );
	 return apply_filters( 'dedo_download_link', $output );
}

/**
 * Check for valid download
 *
 * @since  1.0
 */
function dedo_download_valid( $download_id ) {
	$download_id = absint( $download_id );

	if ( $download = get_post( $download_id, ARRAY_A ) ) {
		
		if ( $download['post_type'] == 'dedo_download' && $download['post_status'] == 'publish' ) {
			return true;
		}
	}
	return false;
}

/**
 * Check user has permission to download file
 *
 * @since  1.0
 */
function dedo_download_permission() {
	global $dedo_options;
	
	$members_only = $dedo_options['members_only'];
	
	if ( $members_only ) {
		
		// Check user is logged in
		if ( is_user_logged_in() ) {
			return true;
		}
		else {
			return false;
		}

	}

	return true;
}

/**
 * Check if user is blocked
 *
 * @since  1.3
 */
function dedo_download_blocked( $current_agent ) {
	// Retrieve user agents
	$user_agents = dedo_get_agents();

	foreach ( $user_agents as $user_agent ) {
		
		$current_agent = trim( strtolower( $current_agent ) );
		$user_agent = trim( strtolower( $user_agent ) );

		if ( false !== strpos( $current_agent, $user_agent ) ) {
			return false;
		}	
	}

	return true;
}

/**
 * Get blocked user agents
 *
 * @since  1.3
 */
function dedo_get_agents() {
	global $dedo_options;
	
	// Get agents and explode into array
	$crawlers = $dedo_options['block_agents'];
	$crawlers = explode( "\n", $crawlers );

	return $crawlers;
}

/**
 * Get users IP Address
 *
 * @since  1.0
 */
function dedo_download_ip() {
	if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip_address = sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] );
	} 
	elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip_address = sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] );
	} 
	else {
		$ip_address = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
	}

	return $ip_address;
}

/**
 * Get file mime type based on file extension
 *
 * @since  1.0
 */
function dedo_download_mime( $path ) {
	// Strip path, leave filename and extension
	$file = explode( '/', $path );
	$file = strtolower( end( $file ) );
	$filetype = wp_check_filetype( $file );	
	
	return $filetype['type'];
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

    switch ( $return ) {
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
 * Protect uploads dir from direct access
 *
 * @since  1.3
 */
function dedo_folder_protection() {
	// Get delightful downloads upload base path
	$upload_dir = dedo_get_upload_dir( 'dedo_basedir' );

	// Default files
	$index = DEDO_PLUGIN_DIR . 'assets/default_files/index.php';
	$htaccess = DEDO_PLUGIN_DIR . 'assets/default_files/.htaccess';

	// Create upload dir if needed, return on fail. Causes fatal error on activation otherwise
	if ( !wp_mkdir_p( $upload_dir ) ) {
		return;
	}

	// Check for root index.php
	if ( !file_exists( $upload_dir . '/index.php' ) ) {
		@copy( $index, $upload_dir . '/index.php' );
	}

	// Check for root .htaccess
	if ( !file_exists( $upload_dir . '/.htaccess' ) ) {
		@copy( $htaccess, $upload_dir . '/.htaccess' );
	}

	// Check subdirs for index.php
	$subdirs = dedo_folder_scan( $upload_dir );

	foreach ( $subdirs as $subdir ) {
		
		if ( !file_exists( $subdir . '/index.php' ) ) {
			@copy( $index, $subdir . '/index.php' );
		}

	}
}

/**
 * Scan dir and return subdirs
 *
 * @since  1.3
 */
function dedo_folder_scan( $dir ) {
	// Check class exists
	if ( class_exists( 'RecursiveDirectoryIterator' ) ) {
		// Setup return array
		$return = array();

		$iterator = new RecursiveDirectoryIterator( $dir );

		// Loop through results and add uniques to return array
		foreach ( new RecursiveIteratorIterator( $iterator ) as $file ) {
			
			if ( !in_array( $file->getPath(), $return ) ) {	
				$return[] = $file->getPath();
			}

		}

		return $return;
	}
	
	return false;
}

/**
 * Get Total Downloads Filesize
 *
 * Returns the total filesize of all files.
 *
 * @since   1.3
 */
function dedo_get_total_filesize() {
	global $wpdb;

	$sql = $wpdb->prepare( "
		SELECT SUM( meta_value )
		FROM $wpdb->postmeta
		WHERE meta_key = %s
		", 
		'_dedo_file_size' 
	);

	$return = $wpdb->get_var( $sql );

	if ( $return == NULL ) {
		return 0;
	}

	return $return;
}

/**
 * Get Total Files
 *
 * Returns the total number of files.
 *
 * @since   1.3
 */
function dedo_get_total_files() {
	$total_files = wp_count_posts( 'dedo_download' );

	return $total_files->publish;
}

/**
 * Delete All Transients
 *
 * Deletes all transients created by Delightful Downloads
 *
 * @since   1.3
 */
function dedo_delete_all_transients() {
	global $wpdb;

	$sql = $wpdb->prepare( "
		DELETE FROM $wpdb->options
		WHERE option_name LIKE %s
		OR option_name LIKE %s
		", 
		'\_transient\_delightful-downloads%%', 
		'\_transient\_timeout\_delightful-downloads%%' );

	$wpdb->query( $sql );
}

/**
 * Get Absolute Path
 *
 * Searches various locations for download file.
 *
 * It is always recommended that the file should be within /wp-content
 * otherwise it can't be guaranteed that the file will be found.
 *
 * Also allows absolute path to store files outsite the document root.
 *
 * @since   1.3.8
 */
function dedo_get_abs_path( $requested_file ) {
	
	$parsed_file = parse_url( $requested_file );

	// Check for absolute path
	if ( ( !isset( $parsed_file['scheme'] ) || !in_array( $parsed_file['scheme'], array( 'http', 'https' ) ) ) && isset( $parsed_file['path'] ) && file_exists( $requested_file ) ) {
		
		return $requested_file;
	}

	// Falls within wp_content
	else if ( strpos( $requested_file, WP_CONTENT_URL ) !== false ) {
		$file_path = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $requested_file );
		
		return realpath( $file_path );
	}

	// Falls in multisite
	else if ( is_multisite() && !is_main_site() && strpos( $requested_file, network_site_url() ) !== false ) {
		$site_url = trailingslashit( site_url() );
		$file_path = str_replace( $site_url, ABSPATH, $requested_file );

		$site_url = trailingslashit( network_site_url() );
		$file_path = str_replace( $site_url, ABSPATH, $file_path );

		return realpath( $file_path );
	}

	// Falls within WordPress directory structure
	else if ( strpos( $requested_file, site_url() ) !== false ) {
		$site_url = trailingslashit( site_url() );
		$file_path = str_replace( $site_url, ABSPATH, $requested_file );

		return realpath( $file_path );
	}

	// Falls outside WordPress structure but within document root.
	else if ( file_exists( $_SERVER['DOCUMENT_ROOT'] . $parsed_file['path'] ) ) {
		$file_path = $_SERVER['DOCUMENT_ROOT'] . $parsed_file['path'];
		
		return realpath( $file_path );
	}

	else {

		return false;
	}
}

/**
 * Get File Name
 *
 * Strips the filename from a URL or path.
 *
 * @since   1.3.8
 *
 * @param string $path File path/url of filename.
 * @return string Value of file name with extension.
 */
function dedo_get_file_name( $path ) {

	return basename( $path );
}

/**
 * Get File Mime
 *
 * Get the file mime type from the file path using WordPress
 * built in filetype check.
 *
 * @since   1.3.8
 *
 * @param string $path File path/url of filename.
 * @return string Value of file mime.
 */
function dedo_get_file_mime( $path ) {
	
	$file = wp_check_filetype( $path );

	return $file['type'];
}

/**
 * Get File Extension
 *
 * Get the file extension from the file path using WordPress
 * built in filetype check.
 *
 * @since   1.3.8
 *
 * @param string $path File path/url of filename.
 * @return string Value of file extension.
 */
function dedo_get_file_ext( $path ) {
	
	$file = wp_check_filetype( $path );
	
	return $file['ext'];
}

/**
 * Get Cache
 *
 * Check for cached SQL result in transients.
 *
 * @since   1.4
 *
 * @param string $sql Prepared SQL statement.
 * @return mixed Mixed result or false on failure.
 */
function dedo_get_cache( $key ) {
	
	global $dedo_options;

	if ( $dedo_options['cache_duration'] > 0 ) {

		return get_transient( $key );
	}

	return false;
}

/**
 * Set Cache
 *
 * Cache SQL result in transients.
 *
 * @since   1.4
 *
 * @param string $sql Prepared SQL statement.
 * @return void
 */
function dedo_set_cache( $key, $result ) {
	
	global $dedo_options;

	if ( $dedo_options['cache_duration'] > 0 ) {

		set_transient( $key, $result, $dedo_options['cache_duration'] * 60 );
	}
}