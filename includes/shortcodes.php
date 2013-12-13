<?php
/**
 * Delightful Downloads Shortcodes
 *
 * @package     Delightful Downloads
 * @subpackage  Shortcodes
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Download Shortcode.
 *
 * Outputs a single download based on user defined attributes.
 *
 * @since   1.0
 * @param   array $atts Shortcode attributes
 * @return  string Formatted single download
 */
function dedo_shortcode_ddownload( $atts ) {
	global $dedo_options;

	// Attributes
	extract( shortcode_atts(
		array(
			'id' 	=> '',
			'text'	=> $dedo_options['default_text'],
			'style'	=> $dedo_options['default_style'],
			'button'=> '',
			'color'	=> '', // Deprecated
			'class'	=> ''
		), $atts, 'ddownload' )
	);

	// Validate download id
	if ( $id == '' || !dedo_download_valid( $id ) ) {
		return '<strong>' . __( 'Invalid download ID.', 'delightful-downloads' ) . '</strong>';
	}

	// Check style against registered styles
	$registered_styles = dedo_get_shortcode_styles();

	if ( array_key_exists( $style, $registered_styles ) ) {
		$style_format = $registered_styles[ $style ]['format'];
	}
	else {
		return '<strong>' . __( 'Invalid style attribute.', 'delightful-downloads' ) . '</strong>';
	}

	// Check for deprecated color att
	if ( !empty( $color ) && empty( $button ) ) {
		$button = $color;
	}

	// Check button against registered buttons
	if ( $style == 'button' ) {
		$button = ( empty( $button ) ) ? $dedo_options['default_button'] : $button;
		$registered_buttons = dedo_get_shortcode_buttons();

		if ( array_key_exists( $button, $registered_buttons ) ) {
			$button_class = $registered_buttons[ $button ]['class'];
		}
		else {
			return '<strong>' . __( 'Invalid button attribute.', 'delightful-downloads' ) . '</strong>';
		}
	}

	// Generate correct class and add user defined
	$classes = 'ddownload-' . $style;
	$classes .= ( isset( $button_class ) ) ? ' ' . $button_class : '';
	$classes .= ( !empty( $class ) ) ? ' ' . $class : '';

	// Replace text and class att
	$replace = array(
		'%text%'	=> $text,
		'%class%'	=> $classes
	);
	
	foreach ( $replace as $key => $value ) {
 		$style_format = str_replace( $key, $value, $style_format );
 	}

	// Search and replace wildcards
	$output = dedo_search_replace_wildcards( $style_format, $id );

	return apply_filters( 'dedo_shortcode_ddownload', $output );
}
add_shortcode( 'ddownload', 'dedo_shortcode_ddownload' );

/**
 * Download Count Shortcode
 *
 * Outputs the number of times a download has been downloaded.
 *
 * @since   1.0
 * @param   array $atts Shortcode attributes
 * @return  string Download count
 */
function dedo_shortcode_ddownload_count( $atts ) {
	
	// Attributes
	extract( shortcode_atts(
		array(
			'id' 		=> '',
			'format'	=> true
		), $atts, 'ddownload_count' )
	);

	// Validate download id
	if ( $id == '' || !dedo_download_valid( $id ) ) {
		return '<strong>' . __( 'Invalid download ID.', 'delightful-downloads' ) . '</strong>';
	}

	// Supply correct boolean for format
	$format = ( in_array( $format, array( 'true', 'yes' ) ) ) ? true : false;

	// Get downloads and format
	$downloads = get_post_meta( $id, '_dedo_file_count', true );
	$output = ( $format == true ) ? dedo_format_number( $downloads ) : $downloads;

	return apply_filters( 'dedo_shortcode_ddownload_count', $output );
}
add_shortcode( 'ddownload_count', 'dedo_shortcode_ddownload_count' );

/**
 * Download Filesize Shortcode
 *
 * Output the filesize of a download.
 *
 * @since   1.0
 * @param   array $atts Shortcode attributes
 * @return  string Download filesize
 */
function dedo_shortcode_ddownload_filesize( $atts ) {
	
	// Attributes
	extract( shortcode_atts(
		array(
			'id' 		=> '',
			'format'	=> true
		), $atts, 'ddownload_filesize' )
	);

	// Validate download id
	if ( $id == '' || !dedo_download_valid( $id ) ) {
		return '<strong>' . __( 'Invalid download ID.', 'delightful-downloads' ) . '</strong>';
	}

	// Supply correct boolean for format
	$format = ( in_array( $format, array( 'true', 'yes' ) ) ) ? true : false;

	// Get filesize and format
	$filesize = get_post_meta( $id, '_dedo_file_size', true );
	$output = ( $format == true ) ? dedo_format_filesize( $filesize ) : $filesize;

	return apply_filters( 'dedo_shortcode_ddownload_filesize', $output );
}
add_shortcode( 'ddownload_filesize', 'dedo_shortcode_ddownload_filesize' );
add_shortcode( 'ddownload_size', 'dedo_shortcode_ddownload_filesize' ); // Deprecated

/**
 * Downloads List Shortcode
 *
 * Displays a list of downloads based on user defined attributes.
 *
 * @since   1.3
 * @param   array $atts Shortcode attributes
 * @return  string Downloads list
 */
function dedo_shortcode_ddownload_list( $atts ) {
	global $dedo_options;

	// Attributes
	extract( shortcode_atts(
		array(
			'limit' 				=> 0,
			'orderby'				=> 'title',
			'order'					=> 'ASC',
			'categories'			=> '',
			'tags'					=> '',
			'exclude_categories'	=> '',
			'exclude_tags'			=> '',
			'relation'				=> 'AND',
			'style'					=> $dedo_options['default_list'],
			'cache'					=> true,
		), $atts, 'ddownload_list' ) 
	);

	// Default query args
	$query_args = array(
		'post_type'		=> 'dedo_download',
		'post_status'	=> 'publish',
	);

	// Validate and set limit
	$limit = abs( intval( $limit ) );
	$query_args['posts_per_page'] = ( $limit == 0 ) ? -1 : $limit;

	// Validate and set orderby
	if ( !in_array( strtolower( $orderby ), array( 'title', 'date', 'count', 'filesize', 'random' ) ) ) {
		return '<strong>' . __( 'Invalid orderby attribute.', 'delightful-downloads' ) . '</strong>';
	}
	else {
		switch ( $orderby ) {
			case 'title':
				$query_args['orderby'] = 'title';
				break;
			case 'date':
				$query_args['orderby'] = 'date';
				break;
			case 'count':
				$query_args['meta_key'] = '_dedo_file_count';
				$query_args['orderby'] = 'meta_value_num';
				break;
			case 'filesize':
				$query_args['meta_key'] = '_dedo_file_size';
				$query_args['orderby'] = 'meta_value_num';
				break;
			case 'random':
				$query_args['orderby'] = 'rand';
				break;
		}
	}

	// Validate and set order
	if ( !in_array( strtoupper( $order ), array( 'ASC', 'DESC' ) ) ) {
		return '<strong>' . __( 'Invalid order attribute.', 'delightful-downloads' ) . '</strong>';
	}
	else {
		$query_args['order'] = strtoupper( $order);
	}

	// Validate relation
	if ( !in_array( strtoupper( $relation ), array( 'AND', 'OR' ) ) ) {
		return '<strong>' . __( 'Invalid relation attribute.', 'delightful-downloads' ) . '</strong>';
	}

	// Validate and set categories/tags
	if ( !empty( $categories ) || !empty( $tags ) || !empty( $exclude_categories ) || !empty( $exclude_tags ) ) {
		$query_args['tax_query'] = array(
			'relation' => $relation,
		);

		if ( !empty( $categories ) ) {
			$categories_array = explode( ',' , $categories );
			$categories_array = array_map( 'trim', $categories_array );

			$query_args['tax_query'][] = array(
				'taxonomy'	=> 'ddownload_category',
				'field'		=> 'slug',
				'terms'		=> $categories_array,
			);
		}

		if ( !empty( $tags ) ) {
			$tags_array = explode( ',' , $tags );
			$tags_array = array_map( 'trim', $tags_array );

			$query_args['tax_query'][] = array(
				'taxonomy'	=> 'ddownload_tag',
				'field'		=> 'slug',
				'terms'		=> $tags_array,
			);
		}

		if ( !empty( $exclude_categories ) ) {
			$exclude_categories_array = explode( ',' , $exclude_categories );
			$exclude_categories_array = array_map( 'trim', $exclude_categories_array );

			$query_args['tax_query'][] = array(
				'taxonomy'	=> 'ddownload_category',
				'field'		=> 'slug',
				'terms'		=> $exclude_categories_array,
				'operator'	=> 'NOT IN',
			);
		}

		if ( !empty( $exclude_tags ) ) {
			$exclude_tags_array = explode( ',' , $exclude_tags );
			$exclude_tags_array = array_map( 'trim', $exclude_tags_array );

			$query_args['tax_query'][] = array(
				'taxonomy'	=> 'ddownload_category',
				'field'		=> 'slug',
				'terms'		=> $exclude_tags_array,
				'operator'	=> 'NOT IN',
			);
		}
	}

	// Check style against registered styles
	$registered_styles = dedo_get_shortcode_lists();

	if ( array_key_exists( $style, $registered_styles ) ) {
		$style_format = $registered_styles[ $style ]['format'];
	}
	else {
		return '<strong>' . __( 'Invalid style attribute.', 'delightful-downloads' ) . '</strong>';
	}

	// Supply correct boolean for cache
	$cache = ( in_array( $cache, array( 'true', 'yes' ) ) ) ? true : false;

	// Cache duration
	$cache_duration = $dedo_options['cache_duration'] * 60;

	// Generate list identifier for cache
	$identifier = md5( $limit . $orderby . $order . $categories . $tags . $exclude_categories . $exclude_tags . $relation . $style );
	$identifier = substr( 'delightful-downloads-list-' . $identifier, 0, 45 );

	// Check for cached data else run query
	if ( ( $output = get_transient( $identifier ) ) === false || $cache === false ) {
		$downloads_list = new WP_Query( $query_args );

		// Begin output
		if ( $downloads_list->have_posts() ) {
			ob_start();
			echo '<ul class="ddownloads_list">';
			
			while ( $downloads_list->have_posts() ) {
				$downloads_list->the_post();
				echo '<li>' . dedo_search_replace_wildcards( $style_format, get_the_ID() ) . '</li>';
			}
			
			echo '</ul>';
			$output = ob_get_clean();
		}
		else {
			return '<strong>' . __( 'No downloads found.', 'delightful-downloads' ) . '</strong>';
		}
		wp_reset_postdata();

		// Store results
		if ( $cache_duration > 0 ) {
			set_transient( $identifier, $output, $cache_duration );
		}
	}

	return apply_filters( 'dedo_shortcode_ddownload_list', $output );
}
add_shortcode( 'ddownload_list', 'dedo_shortcode_ddownload_list' );

/**
 * Display Total Downloads Shortcode
 * 
 * Displays the total download count of all files.
 *
 * @since   1.0
 * @param   array $atts Shortcode attributes
 * @return  string Total downloads count
 */
function dedo_shortcode_ddownload_total_count( $atts ) {
	global $dedo_options;

	// Attributes
	extract( shortcode_atts(
		array(
			'days' 		=> 0,
			'format'	=> true,
			'cache'		=> true,
		), $atts, 'ddownload_total_count' )
	);

	// Validate days
	$days = abs( intval( $days ) );

	// Supply correct boolean for format and cache
	$format = ( in_array( $format, array( 'true', 'yes' ) ) ) ? true : false;
	$cache = ( in_array( $cache, array( 'true', 'yes' ) ) ) ? true : false;

	// Cache duration
	$cache_duration = $dedo_options['cache_duration'] * 60;

	// Cache identifier
	$identifier = 'delightful-downloads-total-count-days-' . $days;

	// Check for cached data else go get it!
	if ( ( $total_count = get_transient( $identifier ) ) === false || $cache === false ) {
		$total_count = dedo_get_total_count( $days );

		// Store results
		if ( $cache_duration > 0 ) {
			set_transient( $identifier, $total_count, $cache_duration );
		}
	}

	// Format and return
	if ( $format ) {
		$total_count = dedo_format_number( $total_count );
	}

	return apply_filters( 'dedo_shortcode_ddownload_total_count', $total_count );
}
add_shortcode( 'ddownload_total_count', 'dedo_shortcode_ddownload_total_count' );

/**
 * Display Total Filesize Shortcode
 * 
 * Displays the total filesize of all files.
 *
 * @since   1.3
 * @param   array $atts Shortcode attributes
 * @return  string Total filesize
 */
function dedo_shortcode_ddownload_total_filesize( $atts ) {
	global $dedo_options;

	// Attributes
	extract( shortcode_atts(
		array(
			'format'	=> true,
			'cache'		=> true,
		), $atts, 'ddownload_total_filesize' )
	);

	// Supply correct boolean for format and cache
	$format = ( in_array( $format, array( 'true', 'yes' ) ) ) ? true : false;
	$cache = ( in_array( $cache, array( 'true', 'yes' ) ) ) ? true : false;

	// Cache duration
	$cache_duration = $dedo_options['cache_duration'] * 60;

	// Cache identifier
	$identifier = 'delightful-downloads-total-filesize';

	// Check for cached data else go get it!
	if ( ( $total_filesize = get_transient( $identifier ) ) === false || $cache === false ) {
		$total_filesize = dedo_get_total_filesize();

		// Store results
		if ( $cache_duration > 0 ) {
			set_transient( $identifier, $total_filesize, $cache_duration );
		}
	}

	// Format and return
	if ( $format ) {
		$total_filesize = dedo_format_filesize( $total_filesize );
	}

	return apply_filters( 'dedo_shortcode_ddownload_total_filesize', $total_filesize );
}
add_shortcode( 'ddownload_total_filesize', 'dedo_shortcode_ddownload_total_filesize' );

/**
 * Display Total Downloads
 * 
 * Displays the total number of downloads.
 *
 * @since   1.3
 * @param   array $atts Shortcode attributes
 * @return  string Total downloads
 */
function dedo_shortcode_ddownload_total_files( $atts ) {
	global $dedo_options;

	// Attributes
	extract( shortcode_atts(
		array(
			'format'	=> true,
			'cache'		=> true,
		), $atts, 'ddownload_total_filesize' )
	);

	// Supply correct boolean for format and cache
	$format = ( in_array( $format, array( 'true', 'yes' ) ) ) ? true : false;
	$cache = ( in_array( $cache, array( 'true', 'yes' ) ) ) ? true : false;

	// Cache duration
	$cache_duration = $dedo_options['cache_duration'] * 60;

	// Cache identifier
	$identifier = 'delightful-downloads-total-files';

	// Check for cached data else go get it!
	if ( ( $total_files = get_transient( $identifier ) ) === false || $cache === false ) {
		$total_files = dedo_get_total_files();

		// Store results
		if ( $cache_duration > 0 ) {
			set_transient( $identifier, $total_files, $cache_duration );
		}
	}

	// Format and return
	if ( $format ) {
		$total_files = dedo_format_number( $total_files );
	}

	return apply_filters( 'dedo_shortcode_ddownload_total_files', $total_files );
}
add_shortcode( 'ddownload_total_files', 'dedo_shortcode_ddownload_total_files' );

/**
 * Allow shortcodes in widgets
 *
 * @since  1.0
 */
add_filter( 'widget_text', 'do_shortcode' );