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