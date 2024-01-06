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
		'labels'        => apply_filters( 'dedo_ddownload_labels', $labels ),
		'public'        => true,
		// 'rewrite'       => array( 'slug' => 'ddl' ),
		'has_archive'	=> true,
		'show_ui'       => true,
		'show_in_menu'  => true,
		'menu_icon'     => 'dashicons-download',
		'capability_type' => apply_filters( 'dedo_ddownload_cap', 'post' ),
		'supports'      => apply_filters( 'dedo_ddownload_supports', array( 'title', 'editor', 'thumbnail' ) ),
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
		'onedaypass'    => __( 'One day pass:', 'delightful-downloads' ),
		'downloads'    => '<span class="dashicons dashicons-download" title="' . __( 'Downloads', 'delightful-downloads' ) . '"></span>',
		'members_only' => '<span class="dashicons dashicons-businessperson" title="' . __( 'Members Only', 'delightful-downloads' ) . '"></span>',
		'open_browser' => '<span class="dashicons dashicons-portfolio" title="' . __( 'Open in Browser', 'delightful-downloads' ) . '"></span>',
		'date'         => __( 'Date', 'delightful-downloads' ),
	);

	// If Quicklinks is enabled add to columns array
	if ( $dedo_options['download_quicklink'] ) {
		$columns_quicklink = array(
			'quicklink'    => __( 'Quick Link', 'delightful-downloads' ),
		);

		// Splice and insert after shortcode column
		$spliced = array_splice( $columns, 4 );
		$columns = array_merge( $columns, $columns_quicklink, $spliced );
	}
  	
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
		$file_path = dedo_get_abs_path($file_url);
		if (isset($_GET['aktion'])) {
		  if ( current_user_can('administrator') && $_GET['aktion'] == 'dedodelete' && $_GET['post'] == $post_id ) {
			  wp_delete_file( $file_path );
			  wp_redirect( admin_url( "edit.php?post_type=dedo_download") );
		  }	
		} 
		$file_url = dedo_get_file_name( $file_url );
		echo ( ! $file_url ) ? '<span class="blank">--</span>' : '<span style="font-weight:700">'. esc_attr( $file_url ) .'</span>';
		if (file_exists($file_path)) { 
			echo '<br><a style="color:tomato;padding-top:7px" title="'.$file_path.'" onclick="return confirm(\''.__( 'really delete attached file from server?', 'delightful-downloads' ).'\');" href ="'.admin_url( "edit.php?post_type=dedo_download&post=$post_id&aktion=dedodelete").'">' . __( 'delete file', 'delightful-downloads' ) .'</a>';
		} else { echo '<br>'.__( 'file is deleted', 'delightful-downloads' ); }
	}

	// Filesize column
	if ( $column_name == 'filesize' ) {
		$file_size = get_post_meta( $post_id, '_dedo_file_size', true );
		$file_size = ( ! $file_size ) ? 0 : size_format( $file_size, 1 );
		echo ( ! $file_size ) ? '<span class="blank">--</span>' : esc_attr( $file_size );
		$file_datum = get_the_modified_date(get_option('date_format').' '.get_option('time_format'),$post_id);
		echo '<br><br><i title="modified">'.$file_datum.' '.ago(get_the_modified_date('U')).'</i>';
	}

	// Shortcode column
	if ( $column_name == 'shortcode' ) {
		echo '<input type="text" title="id=&quot;' . esc_attr( $post_id ) . '&quot;" class="copy-to-clipboard" value="[ddownload id=&quot;' . esc_attr( $post_id ) . '&quot;]" readonly>';
		echo '<p class="description" style="display: none;">' . __( 'Shortcode copied to clipboard.', 'delightful-downloads' ) . '</p>';
	}

	// QuickLink
	if ( $column_name == 'quicklink' ) {
		global $dedo_options;
		echo '<input type="text" title="'.$dedo_options['download_url'] . '=' . esc_attr( $post_id ) . '" class="copy-to-clipboard" value="' . get_site_url() . '?' . $text = $dedo_options['download_url'] . '=' . esc_attr( $post_id ) . '" readonly>';
		echo '<p class="description" style="display: none;">' . __( 'Quicklink copied to clipboard.', 'delightful-downloads' ) . '</p>';
	}
	
	// One day pass column
	if ( $column_name == 'onedaypass' ) {
		$datetime = new DateTime('now');
		$datetime2 = new DateTime('tomorrow');
		$hashwert = md5( intval($post_id) + intval($datetime->format('Ymd')) );
		$hashwertmorgen = md5( intval($post_id) + intval($datetime2->format('Ymd')) );
		echo '<input type="text" title="für '.$datetime->format('d.m.Y').' heute&#10;'.$hashwert.'" class="copy-to-clipboard" style="direction:rtl;cursor:pointer" value="' . get_site_url() . '?sdownload=' . esc_attr( $post_id ) .  '&code='. $hashwert . '" readonly>';
		echo '<input type="text" title="für '.$datetime2->format('d.m.Y').' morgen&#10;'.$hashwertmorgen.'" class="copy-to-clipboard" style="direction:rtl;cursor:pointer" value="' . get_site_url() . '?sdownload=' . esc_attr( $post_id ) .  '&code='. $hashwertmorgen . '" readonly>';
		echo '<p class="description" style="display: none;">' . __( 'One day pass copied to clipboard.', 'delightful-downloads' ) . '</p>';
	}
	
	// Count column
	if ( $column_name == 'downloads' ) {
		$count = get_post_meta( $post_id, '_dedo_file_count', true );
		$count = ( ! $count ) ? 0 : number_format_i18n( $count );
		$scount = get_post_meta( $post_id, '_dedo_oneday_count', true );
		$scount = ( ! $scount ) ? 0 : number_format_i18n( $scount );
		echo esc_attr( $count ) . '<br><br><span title="onedaypass">' . esc_attr( $scount ) .'</span>';
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