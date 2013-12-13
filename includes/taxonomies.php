<?php
/**
 * @package Taxonomies
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register download taxonomies
 *
 * @since  1.3
 */
function dedo_download_taxonomies() {
	global $dedo_options;	

	// Register download category taxonomy
	$category_args = array(
		'labels'			=> array(
			'name'				=> __( 'Download Categories', 'delightful-downloads' ),
			'singular_name'		=> __( 'Download Category', 'delightful-downloads' ),
			'menu_name'			=> __( 'Categories', 'delightful-downloads' ),
			'all_items'			=> __( 'All Categories', 'delightful-downloads' ),
			'edit_item'			=> __( 'Edit Category', 'delightful-downloads' ),
			'view_item'			=> __( 'View Category', 'delightful-downloads' ),
			'update_item'		=> __( 'Update Category', 'delightful-downloads' ),
			'add_new_item'		=> __( 'Add New Category', 'delightful-downloads' ),
			'new_item_name'		=> __( 'New Category Name', 'delightful-downloads' ),
			'search_items'		=> __( 'Search Categories', 'delightful-downloads' ),
			'popular_items'		=> __( 'Popular Categories', 'delightful-downloads' ) ),
		'public'			=> true,
		'show_in_nav_menus'	=> false,
		'show_tag_cloud'	=> false,
		'show_admin_column'	=> true,
		'hierarchical'		=> true
	);

	// Register download tag taxonomy
	$tag_args = array(
		'labels'			=> array(
			'name'				=> __( 'Download Tags', 'delightful-downloads' ),
			'singular_name'		=> __( 'Download Tag', 'delightful-downloads' ),
			'menu_name'			=> __( 'Tags', 'delightful-downloads' ),
			'all_items'			=> __( 'All Tags', 'delightful-downloads' ),
			'edit_item'			=> __( 'Edit Tag', 'delightful-downloads' ),
			'view_item'			=> __( 'View Tag', 'delightful-downloads' ),
			'update_item'		=> __( 'Update Tag', 'delightful-downloads' ),
			'add_new_item'		=> __( 'Add New Tag', 'delightful-downloads' ),
			'new_item_name'		=> __( 'New Tag Name', 'delightful-downloads' ),
			'search_items'		=> __( 'Search Tags', 'delightful-downloads' ),
			'popular_items'		=> __( 'Popular Tags', 'delightful-downloads' ) ),
		'public'			=> true,
		'show_in_nav_menus'	=> false,
		'show_tag_cloud'	=> false,
		'show_admin_column'	=> true,
		'hierarchical'		=> false
	);

	// Only register if enabled in settings
	if( $dedo_options['enable_taxonomies'] ) {
		register_taxonomy( 'ddownload_category', array( 'dedo_download' ), $category_args );
		register_taxonomy( 'ddownload_tag', array( 'dedo_download' ), $tag_args );
	}
}
add_action( 'init', 'dedo_download_taxonomies' );