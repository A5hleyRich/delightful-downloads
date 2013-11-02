<?php
/**
 * @package Post Types
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register download post type
 *
 * @return void
 */
function dedo_download_post_type() {
  $args = array(
    'labels' 			=> array( 
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
	    						),
    'public' 			=> false,
    'show_ui' 			=> true, 
    'show_in_menu' 		=> true, 
    'capability_type' 	=> 'post', 
    'supports' 			=> array( 'title' )
  ); 

  register_post_type( 'dedo_download', $args );
}
add_action( 'init', 'dedo_download_post_type' );

/**
 * Register log post type
 *
 * @return void
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
    'capability_type' 	=> 'post', 
    'supports' 			=> array( 'title' )
  ); 

  register_post_type( 'dedo_log', $args );
}
add_action( 'init', 'dedo_log_post_type' );

/**
 * Download post type column headings
 *
 * @param array $columns default columns registered by WordPress
 *
 * @return void
 */
function dedo_download_column_headings( $columns ) {
	return array(
        'cb' 			=> '<input type="checkbox" />',
        'title' 		=> __( 'Title', 'delightful-downloads' ),
        'file'			=> __( 'File', 'delightful-downloads' ),
        'shortcode' 	=> __( 'Shortcode', 'delightful-downloads' ),
        'downloads' 	=> __( 'Downloads', 'delightful-downloads' ),
        'date' 			=> __( 'Date', 'delightful-downloads' )
    );
}
add_filter( 'manage_dedo_download_posts_columns', 'dedo_download_column_headings' );

/**
 * Download post type column contents
 *
 * @param array $column_name current column
 * @param int $post_id current post id provided by WordPress
 *
 * @return void
 */
function dedo_download_column_contents( $column_name, $post_id ) {
	// file column
	if( $column_name == 'file' ) {
		$path = get_post_meta( $post_id, '_dedo_file_url', true );
		echo dedo_download_filename( $path );
	}
	
	// Shortcode column
	if( $column_name == 'shortcode' ) {
		echo '[ddownload id=' . $post_id . ']';
	}
	
	// Count column
	else if( $column_name == 'downloads' ) {
		echo get_post_meta( $post_id, '_dedo_file_count', true );
	}
}
add_action( 'manage_dedo_download_posts_custom_column', 'dedo_download_column_contents', 10, 2 );

/**
 * Download post type sortable columns filter
 *
 * @param array $columns as set above
 *
 * @return void
 */
function dedo_download_column_sortable( $columns ) {
	$columns['downloads'] = 'downloads';

	return $columns;
}
add_filter( 'manage_edit-dedo_download_sortable_columns', 'dedo_download_column_sortable' );

/**
 * Download post type sortable columns action
 *
 * @param array $query
 *
 * @return void
 */
function dedo_download_column_orderby( $query ) {
	$orderby = $query->get( 'orderby');  
  
    if( $orderby == 'downloads' ) {  
        $query->set( 'meta_key', '_dedo_file_count' );  
        $query->set( 'orderby', 'meta_value_num' );  
    } 
}
add_action( 'pre_get_posts', 'dedo_download_column_orderby' );

/**
 * Log post type column headings
 *
 * @param array $columns default columns registered by WordPress
 *
 * @return void
 */
function dedo_log_column_headings( $columns ) {
	return array(
      	'cb' 			=> '<input type="checkbox" />',
        'ddownload' 	=> __( 'Download', 'delightful-downloads' ),
        'ip'			=> __( 'IP Address', 'delightful-downloads' ),
        'dedo_author' 	=> __( 'User', 'delightful-downloads' ),
        'dedo_date' 	=> __( 'Date', 'delightful-downloads' )
    );
}
add_filter( 'manage_dedo_log_posts_columns', 'dedo_log_column_headings' );

/**
 * Log post type column contents
 *
 * @param array $column_name current column
 * @param int $post_id current post id provided by WordPress
 *
 * @return void
 */
function dedo_log_column_contents( $column_name, $post_id ) {
	// Download column
	if( $column_name == 'ddownload' ) {
		$download_id = get_post_meta( $post_id, '_dedo_log_download', true );
		echo '<a href="' . admin_url( 'post.php?post=' . $download_id . '&action=edit' ) . '">' . get_the_title( $download_id ) . '</a>';
	}
	
	// IP column
	if( $column_name == 'ip' ) {
		$ip_address = get_post_meta( $post_id, '_dedo_log_ip', true );
		echo '<a href="' . admin_url( 'edit.php?post_type=dedo_log&ip_address=' . $ip_address  ) . '">' . $ip_address . '</a>';
	}

	// User column
	if( $column_name == 'dedo_author' ) {
		if( $author = get_the_author() ) {
			echo '<a href="' . admin_url( 'edit.php?post_type=dedo_log&author=' . get_the_author_meta( 'ID' )  ) . '">' . $author . '</a>';		}
		else {
			_e( 'Anonymous', 'delightful-downloads' );
		}
		
	}
	
	// Date column
	if( $column_name == 'dedo_date' ) {
		echo human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) . ' ago<br />';
		echo get_the_time( 'Y/m/d \a\t H:i:s' );
	}
}
add_action( 'manage_dedo_log_posts_custom_column', 'dedo_log_column_contents', 10, 2 );

/**
 * Log post type sortable columns action
 *
 * @param array $query
 *
 * @return void
 */
function dedo_log_column_sort( $query ) {
	$ip_address = ( isset( $_GET['ip_address'] ) ? $_GET['ip_address'] : false );  
  
    if( $ip_address ) {  
        $query->set( 'meta_key', '_dedo_log_ip' );
        $query->set( 'meta_value', $ip_address );    
    } 

}
add_action( 'pre_get_posts', 'dedo_log_column_sort' );