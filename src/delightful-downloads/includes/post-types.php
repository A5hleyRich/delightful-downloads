<?php
/**
 * Delightful Downloads Post Types
 *
 * @package     Delightful Downloads
 * @subpackage  Includes/Post Types
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Download Post Type
 *
 * @since  1.0
 */
function dedo_download_post_type() {
	$labels = array(
		'name'               => __( 'Downloads', 'delightful-downloads' ),
		'singular_name'      => __( 'Download', 'delightful-downloads' ),
		'add_new'            => __( 'Add New', 'delightful-downloads' ),
		'add_new_item'       => __( 'Add New Download', 'delightful-downloads' ),
		'edit_item'          => __( 'Edit Download', 'delightful-downloads' ),
		'new_item'           => __( 'New Download', 'delightful-downloads' ),
		'all_items'          => __( 'All Downloads', 'delightful-downloads' ),
		'view_item'          => __( 'View Download', 'delightful-downloads' ),
		'search_items'       => __( 'Search Downloads', 'delightful-downloads' ),
		'not_found'          => __( 'No downloads found', 'delightful-downloads' ),
		'not_found_in_trash' => __( 'No downloads found in Trash', 'delightful-downloads' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Downloads', 'delightful-downloads' ),
	);

	$args = array(
		'labels'          => apply_filters( 'dedo_ddownload_labels', $labels ),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'menu_icon'       => 'dashicons-download',
		'capability_type' => apply_filters( 'dedo_ddownload_cap', 'post' ),
		'supports'        => apply_filters( 'dedo_ddownload_supports', array( 'title' ) ),
	);
	register_post_type( 'dedo_download', apply_filters( 'dedo_ddownload_args', $args ) );
}
add_action( 'init', 'dedo_download_post_type' );

/**
 * Download Post Type Column Headings
 *
 * @since  1.0
 */
function dedo_download_column_headings( $columns ) {
	global $dedo_options;

	$columns = array(
		'cb'           => '<input type="checkbox" />',
		'title'        => __( 'Title', 'delightful-downloads' ),
		'file'         => __( 'File', 'delightful-downloads' ),
		'filesize'     => __( 'File Size', 'delightful-downloads' ),
		'shortcode'    => __( 'Shortcode', 'delightful-downloads' ),
		'downloads'    => __( 'Downloads', 'delightful-downloads' ),
		'members_only' => '<span class="icon" title="' . __( 'Members Only', 'delightful-downloads' ) . '">' . __( 'Members Only', 'delightful-downloads' ) . '</span>',
		'open_browser' => '<span class="icon" title="' . __( 'Open in Browser', 'delightful-downloads' ) . '">' . __( 'Open in Browser', 'delightful-downloads' ) . '</span>',
		'date'         => __( 'Date', 'delightful-downloads' ),
	);

	// If taxonomies is enabled add to columns array
	if ( $dedo_options['enable_taxonomies'] ) {
		$columns_taxonomies = array(
			'taxonomy-ddownload_category' => __( 'Categories', 'delightful-downloads' ),
			'taxonomy-ddownload_tag'      => __( 'Tags', 'delightful-downloads' ),
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
		$file_url = get_post_meta( $post_id, '_dedo_file_url', true );
		$file_url = dedo_get_file_name( $file_url );
		echo ( ! $file_url ) ? '<span class="blank">--</span>' : esc_attr( $file_url );
	}

	// Filesize column
	if ( $column_name == 'filesize' ) {
		$file_size = get_post_meta( $post_id, '_dedo_file_size', true );
		$file_size = ( ! $file_size ) ? 0 : size_format( $file_size, 1 );
		echo ( ! $file_size ) ? '<span class="blank">--</span>' : esc_attr( $file_size );
	}

	// Shortcode column
	if ( $column_name == 'shortcode' ) {
		echo '<input type="text" class="copy-to-clipboard" value="[ddownload id=&quot;' . esc_attr( $post_id ) . '&quot;]" readonly>';
		echo '<p class="description" style="display: none;">' . __( 'Shortcode copied to clipboard.', 'delightful-downloads' ) . '</p>';
	}

	// Count column
	if ( $column_name == 'downloads' ) {
		$count = get_post_meta( $post_id, '_dedo_file_count', true );
		$count = ( ! $count ) ? 0 : number_format_i18n( $count );
		echo esc_attr( $count );
	}

	// Members only column
	if ( 'members_only' == $column_name ) {
		$file = get_post_meta( $post_id, '_dedo_file_options', true );

		if ( isset( $file['members_only'] ) ) {
			echo ( 1 == $file['members_only'] ) ? '<span class="true" title="' . __( 'Yes', 'delightful-downloads' ) . '"></span>' : '<span class="false" title="' . __( 'No', 'delightful-downloads' ) . '"></span>';
		} else {
			echo '<span class="blank" title="' . __( 'Inherit', 'delightful-downloads' ) . '">--</span>';
		}
	}

	// Open browser column
	if ( 'open_browser' == $column_name ) {
		$file = get_post_meta( $post_id, '_dedo_file_options', true );

		if ( isset( $file['open_browser'] ) ) {
			echo ( 1 == $file['open_browser'] ) ? '<span class="true" title="' . __( 'Yes', 'delightful-downloads' ) . '"></span>' : '<span class="false" title="' . __( 'No', 'delightful-downloads' ) . '"></span>';
		} else {
			echo '<span class="blank" title="' . __( 'Inherit', 'delightful-downloads' ) . '">--</span>';
		}
	}
}
add_action( 'manage_dedo_download_posts_custom_column', 'dedo_download_column_contents', 10, 2 );

/**
 * Download Post Type Sortable Filter
 *
 * @since  1.0
 */
function dedo_download_column_sortable( $columns ) {
	$columns['filesize']  = 'filesize';
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
	$orderby = $query->get( 'orderby' );

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