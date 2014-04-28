<?php
/**
 * Delightful Downloads Post Types
 *
 * @package     Delightful Downloads
 * @subpackage  Includes/Post Types
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Download Post Type
 *
 * @since  1.0
 */
function dedo_download_post_type() {
	
	$labels = array( 
		'name' 					=> __( 'Downloads', 'delightful-downloads' ),
		'singular_name' 		=> __( 'Download', 'delightful-downloads' ),
		'add_new' 				=> __( 'Add New', 'delightful-downloads' ),
		'add_new_item' 			=> __( 'Add New Download', 'delightful-downloads' ),
		'edit_item' 			=> __( 'Edit Download', 'delightful-downloads' ),
		'new_item' 				=> __( 'New Download', 'delightful-downloads' ),
		'all_items' 			=> __( 'All Downloads', 'delightful-downloads' ),
		'view_item' 			=> __( 'View Download', 'delightful-downloads' ),
		'search_items' 			=> __( 'Search Downloads', 'delightful-downloads' ),
		'not_found' 			=> __( 'No downloads found', 'delightful-downloads' ),
		'not_found_in_trash' 	=> __( 'No downloads found in Trash', 'delightful-downloads' ), 
		'parent_item_colon' 	=> '',
		'menu_name' 			=> __( 'Downloads', 'delightful-downloads' ) 
	); 

	$args = array(
		'labels' 			=> apply_filters( 'dedo_ddownload_labels', $labels ),
		'public' 			=> false,
		'show_ui' 			=> true, 
		'show_in_menu' 		=> true, 
		'capability_type' 	=> apply_filters( 'dedo_ddownload_cap', 'post' ), 
		'supports' 			=> apply_filters( 'dedo_ddownload_supports', array( 'title' ) )
	); 

	register_post_type( 'dedo_download', $args );

}
add_action( 'init', 'dedo_download_post_type' );

/**
 * Register Log Post Type
 *
 * @since  1.0
 */
function dedo_log_post_type() {
	$args = array(
	'labels' 			=> array( 
		'name' 					=> __( 'Download Logs', 'delightful-downloads' ),
		'singular_name' 		=> __( 'Log', 'delightful-downloads' ),
		'add_new' 				=> __( 'Add New', 'delightful-downloads' ),
		'add_new_item' 			=> __( 'Add New Log', 'delightful-downloads' ),
		'edit_item' 			=> __( 'Edit Log', 'delightful-downloads' ),
		'new_item' 				=> __( 'New Log', 'delightful-downloads' ),
		'all_items' 			=> __( 'Logs', 'delightful-downloads' ),
		'view_item' 			=> __( 'View Log', 'delightful-downloads' ),
		'search_items' 			=> __( 'Search Logs', 'delightful-downloads' ),
		'not_found' 			=> __( 'No logs found', 'delightful-downloads' ),
		'not_found_in_trash'	=> __( 'No logs found in Trash', 'delightful-downloads' ), 
		'parent_item_colon' 	=> '',
		'menu_name' 			=> __( 'Logs', 'delightful-downloads' ) 
						),
	'public' 			=> false,
	'show_ui' 			=> true, 
	'show_in_menu' 		=> 'edit.php?post_type=dedo_download', 
	'capability_type' 	=> apply_filters( 'dedo_cap_type_logs', 'page' ), 
	'supports' 			=> array()
  ); 

  register_post_type( 'dedo_log', $args );
}
add_action( 'init', 'dedo_log_post_type' );

/**
 * Download Post Type Column Headings
 *
 * @since  1.0
 */
function dedo_download_column_headings( $columns ) {
	global $dedo_options;

	$columns = array(
		'cb' 				=> '<input type="checkbox" />',
		'title' 			=> __( 'Title', 'delightful-downloads' ),
		'file'				=> __( 'File', 'delightful-downloads' ),
		'filesize'			=> __( 'File Size', 'delightful-downloads' ),
		'shortcode' 		=> __( 'Shortcode', 'delightful-downloads' ),
		'downloads' 		=> __( 'Downloads', 'delightful-downloads' ),
		'date' 				=> __( 'Date', 'delightful-downloads' )
	);

	// If taxonomies is enabled add to columns array
	if ( $dedo_options['enable_taxonomies'] ) {
		
		$columns_taxonomies = array(
			'taxonomy-ddownload_category' 	=> __( 'Categories', 'delightful-downloads' ),
			'taxonomy-ddownload_tag'		=> __( 'Tags', 'delightful-downloads' ),
		);

		// Splice and insert after shortcode column
		$spliced = array_splice( $columns, 4 );
		$columns = array_merge( $columns, $columns_taxonomies, $spliced );
	}

	return $columns;
}
add_filter( 'manage_dedo_download_posts_columns', 'dedo_download_column_headings' );

/**
 * Download Post Type Column Contents
 *
 * @since  1.0
 */
function dedo_download_column_contents( $column_name, $post_id ) {
	// File column
	if ( $column_name == 'file' ) {
		
		$path = get_post_meta( $post_id, '_dedo_file_url', true );
		echo esc_attr( basename( $path ) );
	}

	// Filesize column
	if ( $column_name == 'filesize' ) {
		
		$file_size = size_format( get_post_meta( $post_id, '_dedo_file_size', true ), 1 );
		echo esc_attr( ( !$file_size ) ? __( 'Unknown' , 'delightful-downloads' ) : $file_size );
	}
	
	// Shortcode column
	if ( $column_name == 'shortcode' ) {
		
		echo '<code>[ddownload id="' . esc_attr( $post_id ) . '"]</code>';
	}
	
	// Count column
	if ( $column_name == 'downloads' ) {
		
		$count = number_format_i18n( get_post_meta( $post_id, '_dedo_file_count', true ) );
		echo esc_attr( $count );
	}
}
add_action( 'manage_dedo_download_posts_custom_column', 'dedo_download_column_contents', 10, 2 );

/**
 * Download Post Type Sortable Filter
 *
 * @since  1.0
 */
function dedo_download_column_sortable( $columns ) {
	$columns['filesize'] = 'filesize';
	$columns['downloads'] = 'downloads';

	return $columns;
}
add_filter( 'manage_edit-dedo_download_sortable_columns', 'dedo_download_column_sortable' );

/**
 * Download Post Type Sortable Action
 *
 * @since  1.0
 */
function dedo_download_column_orderby( $query ) {
	$orderby = $query->get( 'orderby');  
  
	if ( $orderby == 'filesize' ) {  
		$query->set( 'meta_key', '_dedo_file_size' );  
		$query->set( 'orderby', 'meta_value_num' );  
	}

	if ( $orderby == 'downloads' ) {  
		$query->set( 'meta_key', '_dedo_file_count' );  
		$query->set( 'orderby', 'meta_value_num' );  
	}
}
add_action( 'pre_get_posts', 'dedo_download_column_orderby' );

/**
 * Log Post Type Column Headings
 *
 * @since  1.0
 */
function dedo_log_column_headings( $columns ) {
	return array(
		'cb' 			=> '<input type="checkbox" />',
		'ddownload' 	=> __( 'Download', 'delightful-downloads' ),
		'dedo_author' 	=> __( 'User', 'delightful-downloads' ),
		'ip'			=> __( 'IP Address', 'delightful-downloads' ),
		'agent'			=> __( 'User Agent', 'delightful-downloads' ),
		'dedo_date' 	=> __( 'Date', 'delightful-downloads' )
	);
}
add_filter( 'manage_dedo_log_posts_columns', 'dedo_log_column_headings' );

/**
 * Log Post Type Column Contents
 *
 * @since  1.0
 */
function dedo_log_column_contents( $column_name, $post_id ) {
	// Download column
	if ( $column_name == 'ddownload' ) {
		$download_id = get_post_meta( $post_id, '_dedo_log_download', true );
		echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=dedo_log&ddownload=' . $download_id )  ) . '">' . get_the_title( $download_id ) . '</a>';
	}
	
	// IP column
	if ( $column_name == 'ip' ) {
		$ip_address = get_post_meta( $post_id, '_dedo_log_ip', true );
		echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=dedo_log&ip_address=' . $ip_address  ) ) . '">' . esc_attr( $ip_address ) . '</a>';
	}

	// User Agent column
	if ( $column_name == 'agent' ) {
		$user_agent = get_post_meta( $post_id, '_dedo_log_agent', true );
		echo esc_attr( $user_agent );
	}

	// User column
	if ( $column_name == 'dedo_author' ) {
		
		if ( $author = get_the_author() ) {
			echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=dedo_log&author=' . get_the_author_meta( 'ID' ) ) ) . '">' . $author . '</a>';		}
		else {
			_e( 'Anonymous', 'delightful-downloads' );
		}

	}
	
	// Date column
	if ( $column_name == 'dedo_date' ) {
		echo human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ago<br />';
		echo get_the_time( 'Y/m/d \a\t H:i:s' );
	}
}
add_action( 'manage_dedo_log_posts_custom_column', 'dedo_log_column_contents', 10, 2 );

/**
 * Log Post Type Sortable Action
 *
 * @since  1.0
 */
function dedo_log_column_sort( $query ) {
	$ddownload = ( isset( $_GET['ddownload'] ) ? $_GET['ddownload'] : false );
	$ip_address = ( isset( $_GET['ip_address'] ) ? $_GET['ip_address'] : false );
  
	if ( $ddownload ) {  
		$query->set( 'meta_key', '_dedo_log_download' );
		$query->set( 'meta_value', $ddownload );   
	}

	if ( $ip_address ) {  
		$query->set( 'meta_key', '_dedo_log_ip' );
		$query->set( 'meta_value', $ip_address );    
	}
}
add_action( 'pre_get_posts', 'dedo_log_column_sort' );