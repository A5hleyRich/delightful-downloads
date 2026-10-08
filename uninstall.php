<?php
/**
 * Delightful Downloads Uninstall
 *
 * @package     Delightful Downloads
 * @subpackage  Uninstall
 * @since       1.3
*/

// Exit if accessed directly
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Include Delightful Downloads
include_once( 'delightful-downloads.php' ); 

// Globals
global $wpdb, $dedo_options, $dedo_statistics;

// Clear transients
dedo_delete_all_transients();

// Check for complete uninstall
if ( $dedo_options['uninstall'] ) {

	// Disable max_execution_time, can take a while with legacy logs
	set_time_limit( 0 );

	// Delete post types
	$post_types = array(
		'dedo_download',
		'dedo_log' // Deprecated
	);

	foreach ( $post_types as $post_type ) {
		$posts = get_posts( array( 'post_type' => $post_type, 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) );

		if ( !empty( $posts ) ) {
			
			foreach ( $posts as $post ) {
				wp_delete_post( $post, true );
			}
			
		}
	}

	/**
	 * Delete taxonomies
	 *
	 * We need to call the register taxonomies function before
	 * we can remove the taxonomies.
	*/
	if ( function_exists( 'dedo_download_taxonomies' ) ) {
		dedo_download_taxonomies();
	}

	$taxonomies = array(
		'ddownload_category',
		'ddownload_tag'
	);

	foreach ( $taxonomies as $taxonomy ) {
		
		$sql = "SELECT $wpdb->terms.term_id
				FROM $wpdb->terms
				INNER JOIN $wpdb->term_taxonomy ON $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id
				WHERE $wpdb->term_taxonomy.taxonomy = '$taxonomy'";

		$terms = $wpdb->get_results( $sql );

		if ( !empty( $terms ) ) {
			
			foreach ($terms as $term ) {
				@wp_delete_term( $term->term_id, $taxonomy );
			}

		}
	}

	// Delete statistics table
	$dedo_statistics->delete_table();

	// Delete options
	delete_option( 'delightful-downloads' );
	delete_option( 'delightful-downloads-version' );
	delete_option( 'delightful-downloads-prior-version' );
	delete_option( 'delightful-downloads-notices' );

}