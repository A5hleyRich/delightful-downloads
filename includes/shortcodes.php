<?php
/**
 * Delightful Downloads Shortcodes
 *
 * @package     Delightful Downloads
 * @subpackage  Includes/Shortcodes
 * @since       1.1
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Download Shortcode.
 * Outputs a single download based on user defined attributes.
 * @param array $atts
 * @return string
 */
function dedo_shortcode_ddownload( $atts ) {
	/**
	 * Use get option again so that WPML can filter default text
	 * for translation, get_option() is cached so should not
	 * affect performance.
	 */
	global $dedo_default_options;
	$dedo_options = wp_parse_args( get_option( 'delightful-downloads' ), $dedo_default_options );

	// filetype skript laden
	wp_enqueue_style( 'filetype-style' );

	// Attributes
	extract( shortcode_atts(
		array(
			'id' 	=> '',
			'text'	=> $dedo_options['default_text'],
			'style'	=> $dedo_options['default_style'],
			'button'=> '',
			'class'	=> ''
		), $atts, 'ddownload' )
	);

	// Validate download id
	if ( $id == '' || !dedo_download_valid( $id ) ) {
		
		return __( 'Invalid download ID.', 'delightful-downloads' );
	}

	// Check style against registered styles
	$registered_styles = dedo_get_shortcode_styles();

	if ( array_key_exists( $style, $registered_styles ) ) {
		
		$style_format = $registered_styles[ $style ]['format'];
	}
	else {
		
		return __( 'Invalid style attribute.', 'delightful-downloads' );
	}

	// Check button against registered buttons
	if ( $style == 'button' ) {
		
		$button = ( empty( $button ) ) ? $dedo_options['default_button'] : $button;
		$registered_buttons = dedo_get_shortcode_buttons();

		if ( array_key_exists( $button, $registered_buttons ) ) {
			
			$button_class = $registered_buttons[ $button ]['class'];
		}
		else {
			
			return __( 'Invalid button attribute.', 'delightful-downloads' );
		}
	}

	// Generate correct class and add user defined
	$classes = 'ddownload-' . $style; // Output style
	$classes .= ( isset( $button_class ) ) ? ' ' . $button_class : ''; // Button style
	$classes .= ' id-' . $id; // Download id
	$classes .= ' ext-' . dedo_get_file_ext( get_post_meta( $id, '_dedo_file_url', true ) ); // File extension
	$classes .= ( !empty( $class ) ) ? ' ' . $class : ''; // User defined

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

	return apply_filters( 'dedo_shortcode_ddownload', $output, $id, $atts, $classes );
}
add_shortcode( 'ddownload', 'dedo_shortcode_ddownload' );

/**
 * Downloads List Shortcode
 *
 * Displays a list of downloads based on user defined attributes.
 *
 * @since   1.3
 */
function dedo_shortcode_ddownload_list( $atts ) {
	global $dedo_options;

	// filetype skript laden
	wp_enqueue_style( 'filetype-style' );
	
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
	$limit = abs( $limit );
	$query_args['posts_per_page'] = ( 0 === $limit ) ? -1 : $limit;

	// Validate and set orderby
	if ( !in_array( strtolower( $orderby ), array( 'title', 'date', 'modified', 'count', 'filesize', 'random' ) ) ) {
		
		return __( 'Invalid orderby attribute.', 'delightful-downloads' );
	}
	else {
		switch ( $orderby ) {
			case 'title':
				$query_args['orderby'] = 'title';
				break;

			case 'date':
				$query_args['orderby'] = 'date';
				break;

			case 'modified':
				$query_args['orderby'] = 'modified';
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
		return __( 'Invalid order attribute.', 'delightful-downloads' );
	}
	else {
		$query_args['order'] = strtoupper( $order);
	}

	// Validate relation
	if ( !in_array( strtoupper( $relation ), array( 'AND', 'OR' ) ) ) {
		return __( 'Invalid relation attribute.', 'delightful-downloads' );
	}
	else {
		$relation = strtoupper( $relation );
	}

	// Validate and set categories/tags
	$tax_class = '';

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
			// Set taxonomy class
			$tax_class .= ' category-' . implode( ' category-', $categories_array );
		}

		if ( !empty( $tags ) ) {
			$tags_array = explode( ',' , $tags );
			$tags_array = array_map( 'trim', $tags_array );
			$query_args['tax_query'][] = array(
				'taxonomy'	=> 'ddownload_tag',
				'field'		=> 'slug',
				'terms'		=> $tags_array,
			);
			// Set taxonomy class
			$tax_class .= ' tag-' . implode( ' tag-', $tags_array );
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
				'taxonomy'	=> 'ddownload_tag',
				'field'		=> 'slug',
				'terms'		=> $exclude_tags_array,
				'operator'	=> 'NOT IN',
			);
		}
	}

	// Check style against registered styles
	$registered_styles = dedo_get_shortcode_lists();
	$style_class       = ' list-' . $style;

	if ( array_key_exists( $style, $registered_styles ) ) {
		$style_format = $registered_styles[ $style ]['format'];
	}
	else {
		return __( $style. 'Invalid style attribute.', 'delightful-downloads' );
	}

	// Supply correct boolean for cache
	$cache = ( in_array( $cache, array( 'true', 'yes' ) ) ) ? true : false;

	// First check for cached data
	$key = md5( $limit . $orderby . $order . $categories . $tags . $exclude_categories . $exclude_tags . $relation . $style );
	$key = substr( 'dedo_shortcode_list_' . $key, 0, 45 );
	$dedo_cache = new DEDO_Cache( $key );

	if ( true == $cache && false !== ( $cached_data = $dedo_cache->get() ) ) {
		$output = $cached_data;
	} else {

		// Run query
		$downloads_list = new WP_Query( $query_args );

		// Begin output
		if ( $downloads_list->have_posts() ) {
			ob_start();
			$filecount=0;
			$tfilesize=0;
			echo '<table style="table-layout: fixed" class="ddownloads_list' . $tax_class . $style_class . '">';
			if (!empty($categories)) echo '<thead><tr><th style="width:100%"><i class="fa fa-filter"></i> '.$categories.'</th></tr></thead><tbody>';
			while ( $downloads_list->have_posts() ) {
				$downloads_list->the_post();

				// Add classes
				$classes = 'id-' . get_the_ID(); // Download id
				$classes .= ' ext-' . dedo_get_file_ext( get_post_meta( get_the_ID(), '_dedo_file_url', true ) ); // File extension
				$new_style_format = str_replace( '%class%', $classes, $style_format );
				$filecount++;
				$tfilesize += (int) get_post_meta( get_the_ID(), '_dedo_file_size', true );
				echo '<tr><td style="position:relative;width:100%"><div style="background-color:#ffffffbb;color:#000;font-size:1.2em;font-weight:700;position:absolute;left:8px;top:6px;z-index:99999;line-height:1em">'. $filecount.'</div>' . dedo_search_replace_wildcards( $new_style_format, get_the_ID() ) . '</td></tr>';
				// Reset classes for next iteration
				unset( $classes );
				unset( $new_style_format );
			}
			
			// File Statistiken, wenn limit nicht gesetzt
			if ($limit == 0) {
				$total_files = wp_count_posts( 'dedo_download' )->publish;
				echo '<tfoot><tr><td>';
				if ((int) $filecount < (int) $total_files) {
					echo __('in list','delightful-downloads').': <b>'.$filecount.'</b> '.__('files','delightful-downloads').' / ';
					echo ' <b>' . size_format( $tfilesize, 1 ).'</b>';
				}	
				echo ' &nbsp; TOTAL: <b>'.$total_files.'</b> '.__('files','delightful-downloads').' / <b>'.size_format( dedo_get_filesize(), 1 ).'</b>';
				echo '</td></tr></tfoot>';
			}	
			echo '</table>';
			$output = ob_get_clean();
			wp_reset_postdata();

			// Save to cache
			if ( true == $cache ) {
				$dedo_cache->set( $output );
			}
		}
		else {
			return '<p>' . __( 'No downloads found.', 'delightful-downloads' ) . '</p>';
		}
	}
	return apply_filters( 'dedo_shortcode_ddownload_list', $output );
}
add_shortcode( 'ddownload_list', 'dedo_shortcode_ddownload_list' );

/**
 * Download Filesize Shortcode
 * Output the filesize of a download.
 */
function dedo_shortcode_ddownload_filesize( $atts ) {
	
	// Attributes
	extract( shortcode_atts(
		array(
			'id' 		=> false,
			'format'	=> true,
			'cache'		=> true
		), $atts, 'ddownload_filesize' )
	);

	// Supply correct boolean for format and cache
	$format = ( in_array( $format, array( 'true', 'yes' ) ) ) ? true : false;
	$cache = ( in_array( $cache, array( 'true', 'yes' ) ) ) ? true : false;

	// Check for valid download
	if ( $id !== false && !dedo_download_valid( $id ) ) {
		return __( 'Invalid download ID.', 'delightful-downloads' );
	}

	// First check for cached data
	$key = 'dedo_shortcode_filesize_id' . absint( $id );
	$dedo_cache = new DEDO_Cache( $key );
	if ( true == $cache && false !== ( $cached_data = $dedo_cache->get() ) ) {
		$filesize = $cached_data;
	}
	else {
		// No cached data, retrieve file count
		$filesize = dedo_get_filesize( $id );
	}
	// Save to cache
	if ( true == $cache ) {
		$dedo_cache->set( $filesize );
	}
	// Format number
	if ( $format ) {
		$filesize = size_format( $filesize, 1 );
	}

	return apply_filters( 'dedo_shortcode_ddownload_filesize', $filesize );
}
add_shortcode( 'ddownload_filesize', 'dedo_shortcode_ddownload_filesize' );

/**
 * Download Count Shortcode
 * Outputs the number of times a download has been downloaded.
 */
function dedo_shortcode_ddownload_count( $atts ) {
	global $dedo_statistics;
	// Attributes
	extract( shortcode_atts(
		array(
			'id' 		=> false,
			'days'		=> false,
			'format'	=> true,
			'cache'		=> true
		), $atts, 'ddownload_count' )
	);

	// Supply correct boolean for format and cache
	$format = ( in_array( $format, array( 'true', 'yes' ) ) ) ? true : false;
	$cache = ( in_array( $cache, array( 'true', 'yes' ) ) ) ? true : false;

	// Check for valid download
	if ( $id !== false && !dedo_download_valid( $id ) ) {
		return __( 'Invalid download ID.', 'delightful-downloads' );
	}

	// Get count
	$count = $dedo_statistics->count_downloads( array( 
		'download_id'	=> $id, 
		'days'			=> $days,
		'cache' 		=> $cache 
	) );

	// Format number
	if ( $format ) {
		$count = number_format_i18n( $count );
	}

	// Apply filters and return
	return apply_filters( 'dedo_shortcode_ddownload_count', $count );
}
add_shortcode( 'ddownload_count', 'dedo_shortcode_ddownload_count' );

/**
 * Display Total Downloadable Files
 * Displays the total number of downloadable files.
 * @since   1.3
 */
function dedo_shortcode_ddownload_files( $atts ) {
	global $dedo_options;

	// Attributes
	extract( shortcode_atts(
		array(
			'format'	=> true,
			'cache'		=> true
		), $atts, 'ddownload_files' )
	);

	// Supply correct boolean for format and cache
	$format = ( in_array( $format, array( 'true', 'yes' ) ) ) ? true : false;
	$cache = ( in_array( $cache, array( 'true', 'yes' ) ) ) ? true : false;

	// First check for cached data
	$key = 'dedo_shortcode_files';
	$dedo_cache = new DEDO_Cache( $key );
	if ( true == $cache && false !== ( $cached_data = $dedo_cache->get() ) ) {
		$total_files = $cached_data;
	}
	else {
		// No cached data, retrieve file count
		$total_files = wp_count_posts( 'dedo_download' );
		$total_files = $total_files->publish;
	}

	// Save to cache
	if ( true == $cache ) {
		$dedo_cache->set( $total_files );
	}
	// Format number
	if ( $format ) {
		$total_files = number_format_i18n( $total_files );
	}
	// Apply filters and return
	return apply_filters( 'dedo_shortcode_ddownload_files', $total_files );
}
add_shortcode( 'ddownload_files', 'dedo_shortcode_ddownload_files' );

/**
 * Allow shortcodes in widgets
 */
add_filter( 'widget_text', 'do_shortcode' );
