<?php
/**
 * @package Shortcodes
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Allow shortcodes in widgets
 */
add_filter( 'widget_text', 'do_shortcode' );

/**
 * Output download link in various formats
 *
 * @param array atts shortcode options
 *
 * @return string
 */
function dedo_shortcode_download( $atts ) {
	global $dedo_options;
	
	extract( 
		shortcode_atts( array(
			'id' 	=> '',
			'text'	=> $dedo_options['default_text'],
			'alt'	=> __( 'Download File', 'delightful-downloads' ),
			'style'	=> $dedo_options['default_style'],
			'color'	=> $dedo_options['default_color'],	
			'class'	=> ''
		), $atts )
	);
	
	// Check for required id
	if( $id == '' ) {
		$output = '<strong>' . __( 'No download ID provided.', 'delightful-downloads' ) . '</strong>';
	}
	else {
		// Check id exists
		if( dedo_download_valid( $id ) ) {
			// Valid id, grab the URL
			$download_link = dedo_download_link( $id );
			
			// Add class if set
			if ( $class !== '' ) {
				$class = ' ' . $class;
			}
			
			// Check for dynamic title
			if( $text == '%title%' ) {
				$text = get_the_title( $id );
			}
			
			// Get style type
			switch( $style ) {
				case 'button':
					$output = '<a href="' . $download_link . '" class="download-button button-' . $color . $class . '"  title="' . $alt . '">' . $text . '</a>';
					break;
				case 'link':
					$output = '<a href="' . $download_link . '" class="download-link' . $class . '" title="' . $alt . '">' . $text . '</a>';
					break;
				case 'text':
				default:
					$output = $download_link;
			}
		}
		else {
			$output ='<strong>' . __( 'Invalid download ID.', 'delightful-downloads' ) . '</strong>';
		}	
	}			
	return $output;
}
add_shortcode( 'ddownload', 'dedo_shortcode_download' );

/**
 * Output download count 
 *
 * @param array atts shortcode options
 *
 * @return string
 */
function dedo_shortcode_download_count( $atts ) {
	extract( 
		shortcode_atts( array(
			'id' 	=> '',
		), $atts )
	);
	
	// Check for required id
	if( $id == '' ) {
		$output = '<strong>' . __( 'No download ID provided.', 'delightful-downloads' ) . '</strong>';
	}
	else {
		// Check id exists
		if( dedo_download_valid( $id ) ) {
			// Get download post meta
			$output = get_post_meta( $id, '_dedo_file_count', true );
		}
		else {
			$output ='<strong>' . __( 'Invalid download ID.', 'delightful-downloads' ) . '</strong>';
		}
	}
	return $output;
}
add_shortcode( 'ddownload_count', 'dedo_shortcode_download_count' );

/**
 * Output human readable download filesize
 *
 * @param array atts shortcode options
 *
 * @return string
 */
function dedo_shortcode_download_size( $atts ) {
	extract( 
		shortcode_atts( array(
			'id' 	=> '',
		), $atts )
	);
	
	// Check for required id
	if( $id == '' ) {
		$output = '<strong>' . __( 'No download ID provided.', 'delightful-downloads' ) . '</strong>';
	}
	else {
		// Check id exists
		if( dedo_download_valid( $id ) ) {
			
			// Get download post meta
			$output = dedo_human_filesize( get_post_meta( $id, '_dedo_file_size', true ) );
		}
		else {
			$output ='<strong>' . __( 'Invalid download ID.', 'delightful-downloads' ) . '</strong>';
		}
	}
	return $output;
}
add_shortcode( 'ddownload_size', 'dedo_shortcode_download_size' );

/**
 * Displays total download count of all files
 *
 * @return string
 */
function dedo_shortcode_total_count( $atts ) {
	extract( 
		shortcode_atts( array(
			'days' 		=> 0,
			'format'	=> true,
			'cache'		=> true
		), $atts )
	);
	
	// Convert to int
	$days = (int) $days;
	
	// Supply correct boolean for format
	if( in_array( $format, array( 'true', 'yes' ) ) ) {
		$format = true;
	}
	else {
		$format = false;
	}
	
	// Supply correct boolean for cache
	if( in_array( $cache, array( 'true', 'yes' ) ) ) {
		$cache = true;
	}
	else {
		$cache = false;
	}
	
	return dedo_get_total_count( $days, $format, $cache );
}
add_shortcode( 'ddownload_total_count', 'dedo_shortcode_total_count' );