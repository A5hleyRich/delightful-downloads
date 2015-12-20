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
	 		'format'			=> '<a href="%url%" title="%title%" rel="nofollow" class="%class%">%title%</a>'
	 	),
	 	'title_date'		=> array(
	 		'name'				=> __( 'Title (Date)', 'delightful-downloads' ),
	 		'format'			=> '<a href="%url%" title="%title% (%date%)" rel="nofollow" class="%class%">%title% (%date%)</a>'
	 	),
	 	'title_count'		=> array(
	 		'name'				=> __( 'Title (Count)', 'delightful-downloads' ),
	 		'format'			=> '<a href="%url%" title="%title% (Downloads: %count%)" rel="nofollow" class="%class%">%title% (Downloads: %count%)</a>'
	 	),
	 	'title_filesize'	=> array(
	 		'name'				=> __( 'Title (File size)', 'delightful-downloads' ),
	 		'format'			=> '<a href="%url%" title="%title% (%filesize%)" rel="nofollow" class="%class%">%title% (%filesize%)</a>'
	 	),
	 	'title_ext_filesize'=> array(
	 		'name'				=> __( 'Title (Extension, File size)', 'delightful-downloads' ),
	 		'format'			=> '<a href="%url%" title="%title% (%ext%, %filesize%)" rel="nofollow" class="%class%">%title% (%ext%, %filesize%)</a>'
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
function dedo_download_permission( $options ) {
	global $dedo_options;

	// First check per-download settings, else revert to global setting
	$members_only = ( isset( $options['members_only'] ) ) ? $options['members_only'] : $dedo_options['members_only'];

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

	if ( ! $user_agents ) {
		return true;
	}

	foreach ( $user_agents as $user_agent ) {
		$current_agent = trim( strtolower( $current_agent ) );
		$user_agent    = trim( strtolower( $user_agent ) );

		if ( empty( $current_agent ) || empty( $user_agent ) ) {
			return true;
		}

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

	$crawlers = $dedo_options['block_agents'];

	if ( empty( $crawlers ) ) {
		return array();
	}

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
 * @param string $return
 * @param string $upload_dir
 *
 * @return string
 */
function dedo_get_upload_dir( $return = '', $upload_dir = '' ) {
	global $dedo_options;

	$upload_dir = ( $upload_dir === '' ? wp_upload_dir() : $upload_dir );
	$directory  = $dedo_options['upload_directory'];

	$upload_dir['path']         = trailingslashit( $upload_dir['basedir'] ) . $directory . $upload_dir['subdir'];
	$upload_dir['url']          = trailingslashit( $upload_dir['baseurl'] ) . $directory . $upload_dir['subdir'];
	$upload_dir['dedo_basedir'] = trailingslashit( $upload_dir['basedir'] ) . $directory;
	$upload_dir['dedo_baseurl'] = trailingslashit( $upload_dir['baseurl'] ) . $directory;

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
function dedo_folder_protection( $folder_protection = '' ) {
	global $dedo_options;

	// Allow custom options to be passed, set to save options if not
	$folder_protection = ( '' === $folder_protection ) ? $dedo_options['folder_protection'] : $folder_protection;

	// Get delightful downloads upload base path
	$upload_dir = dedo_get_upload_dir( 'dedo_basedir' );

	// Create upload dir if needed, return on fail. Causes fatal error on activation otherwise
	if ( !wp_mkdir_p( $upload_dir ) ) {
		return;
	}

	// Add htaccess protection if enabled, else delete it
	if ( 1 == $folder_protection ) {
		if ( !file_exists( $upload_dir . '/.htaccess' ) && wp_is_writable( $upload_dir ) ) {
			$content = "Options -Indexes\n";
			$content .= "deny from all";

			@file_put_contents( $upload_dir . '/.htaccess', $content );
		}
	}
	else {
		if ( file_exists( $upload_dir . '/.htaccess' ) && wp_is_writable( $upload_dir ) ) {
			@unlink( $upload_dir . '/.htaccess' );
		}
	}

	// Check for root index.php
	if ( !file_exists( $upload_dir . '/index.php' ) && wp_is_writable( $upload_dir ) ) {
		@file_put_contents( $upload_dir . '/index.php', '<?php' . PHP_EOL . '// You shall not pass!' );
	}

	// Check subdirs for index.php
	$subdirs = dedo_folder_scan( $upload_dir );

	foreach ( $subdirs as $subdir ) {
		if ( !file_exists( $subdir . '/index.php' ) && wp_is_writable( $subdir ) ) {
			@file_put_contents( $subdir . '/index.php', '<?php' . PHP_EOL . '// You shall not pass!' );
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
 * Get Downloads Filesize
 *
 * Returns the total filesize of all files.
 *
 * @since   1.3
 */
function dedo_get_filesize( $download_id = false ) {
	global $wpdb;

	$sql = $wpdb->prepare( "
		SELECT SUM( meta_value )
		FROM $wpdb->postmeta
		WHERE meta_key = %s
	", 
	'_dedo_file_size' );

	if ( $download_id ) {

		$sql .= $wpdb->prepare( " AND post_id = %d", $download_id );
	}

	$return = $wpdb->get_var( $sql );

	return ( NULL !== $return ) ? $return : 0;
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
		OR option_name LIKE %s
		OR option_name LIKE %s
		", 
		'\_transient\_delightful-downloads%%', 
		'\_transient\_timeout\_delightful-downloads%%',
		'\_transient\_dedo_%%',
		'\_transient\_timeout\_dedo_%%' );

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
		
		$file = $requested_file;
	}

	// Falls within wp_content
	else if ( strpos( $requested_file, WP_CONTENT_URL ) !== false ) {
		$file_path = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $requested_file );
		
		$file = realpath( $file_path );
	}

	// Falls in multisite
	else if ( is_multisite() && !is_main_site() && strpos( $requested_file, network_site_url() ) !== false ) {
		$site_url = trailingslashit( site_url() );
		$file_path = str_replace( $site_url, ABSPATH, $requested_file );

		$site_url = trailingslashit( network_site_url() );
		$file_path = str_replace( $site_url, ABSPATH, $file_path );

		$file = realpath( $file_path );
	}

	// Falls within WordPress directory structure
	else if ( strpos( $requested_file, site_url() ) !== false ) {
		$site_url = trailingslashit( site_url() );
		$file_path = str_replace( $site_url, ABSPATH, $requested_file );

		$file = realpath( $file_path );
	}

	// Falls outside WordPress structure but within document root.
	else if ( strpos( $requested_file, site_url() ) && file_exists( $_SERVER['DOCUMENT_ROOT'] . $parsed_file['path'] ) ) {
		$file_path = $_SERVER['DOCUMENT_ROOT'] . $parsed_file['path'];
		
		$file = realpath( $file_path );
	}

	// Checks file exists
	if ( isset( $file ) && is_file( $file ) ) {
		return $file;
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
 * Get File Status
 *
 * Checks whether a file is accessible, either locally or remotely.
 *
 * @since   1.5
 *
 * @param string $url File path/url of filename.
 * @return boolean/array.
 */
function dedo_get_file_status( $url ) {
	// Check locally
	if( $file = dedo_get_abs_path( $url ) ) {
		$type = 'local';
		$size = @filesize( $file );
	}
	else {
		$response = @get_headers( $url, 1 );

		if ( ( false === $response || 'HTTP/1.1 404 Not Found' == $response[0] || 'HTTP/1.1 403 Forbidden' == $response[0] ) || !isset( $response['Content-Length'] ) ) {		
			return false;
		}
		else {
			$type = 'remote';
			$size = $response['Content-Length'];
		}
	}

	return array(
		'type'	=> $type,
		'size'	=> $size
	);
}

/**
 * Get File Icon
 *
 * Return the correct file icon for a file type.
 *
 * @since   1.5
 *
 * @param string $file url/path.
 * @param boolen $url return full icon url.
 * @return string.
 */
function dedo_get_file_icon( $file, $url = true ) {
	$ext = dedo_get_file_ext( $file );
	
	switch( $ext ) {
		case 'aac': 	$icon = 'aac.png'; break;
		case 'ai': 		$icon = 'ai.png'; break;
		case 'aiff':	$icon = 'aiff.png'; break;
		case 'avi':		$icon = 'avi.png'; break;
		case 'bmp':		$icon = 'bmp.png'; break;
		case 'c':		$icon = 'c.png'; break;
		case 'cpp':		$icon = 'cpp.png'; break;
		case 'css':		$icon = 'css.png'; break;
		case 'dat':		$icon = 'dat.png'; break;
		case 'dmg':		$icon = 'dmg.png'; break;
		case 'doc':		$icon = 'doc.png'; break;
		case 'dotx':	$icon = 'dotx.png'; break;
		case 'dwg':		$icon = 'dwg.png'; break;
		case 'dxf':		$icon = 'dxf.png'; break;
		case 'eps':		$icon = 'eps.png'; break;
		case 'exe':		$icon = 'exe.png'; break;
		case 'flv':		$icon = 'flv.png'; break;
		case 'gif':		$icon = 'gif.png'; break;
		case 'h':		$icon = 'h.png'; break;
		case 'hpp':		$icon = 'hpp.png'; break;
		case 'html':	$icon = 'html.png'; break;
		case 'ics':		$icon = 'ics.png'; break;
		case 'iso':		$icon = 'iso.png'; break;
		case 'java':	$icon = 'java.png'; break;
		case 'jpg':		$icon = 'jpg.png'; break;
		case 'js':		$icon = 'js.png'; break;
		case 'key':		$icon = 'key.png'; break;
		case 'less':	$icon = 'less.png'; break;
		case 'mid':		$icon = 'mid.png'; break;
		case 'mp3':		$icon = 'mp3.png'; break;
		case 'mp4':		$icon = 'mp4.png'; break;
		case 'mpg':		$icon = 'mpg.png'; break;
		case 'odf':		$icon = 'odf.png'; break;
		case 'ods':		$icon = 'ods.png'; break;
		case 'odt':		$icon = 'odt.png'; break;
		case 'otp':		$icon = 'otp.png'; break;
		case 'ots':		$icon = 'ots.png'; break;
		case 'ott':		$icon = 'ott.png'; break;
		case 'pdf':		$icon = 'pdf.png'; break;
		case 'php':		$icon = 'php.png'; break;
		case 'png':		$icon = 'png.png'; break;
		case 'ppt':		$icon = 'ppt.png'; break;
		case 'psd':		$icon = 'psd.png'; break;
		case 'py':		$icon = 'py.png'; break;
		case 'qt':		$icon = 'qt.png'; break;
		case 'rar':		$icon = 'rar.png'; break;
		case 'rb':		$icon = 'rb.png'; break;
		case 'rtf':		$icon = 'rtf.png'; break;
		case 'sass':	$icon = 'sass.png'; break;
		case 'scss':	$icon = 'scss.png'; break;
		case 'sql':		$icon = 'sql.png'; break;
		case 'tga':		$icon = 'tga.png'; break;
		case 'tgz':		$icon = 'tgz.png'; break;
		case 'tiff':	$icon = 'tiff.png'; break;
		case 'txt':		$icon = 'txt.png'; break;
		case 'wav':		$icon = 'wav.png'; break;
		case 'xls':		$icon = 'xls.png'; break;
		case 'xlsx':	$icon = 'xlsx.png'; break;
		case 'xml':		$icon = 'xml.png'; break;
		case 'yml':		$icon = 'yml.png'; break;
		case 'zip':		$icon = 'zip.png'; break;
		default:		$icon = '_blank.png'; break;
	}

	if ( $url ) {
		return DEDO_PLUGIN_URL . 'assets/icons/' . $icon;
	}
	else {
		return $icon;
	}
}