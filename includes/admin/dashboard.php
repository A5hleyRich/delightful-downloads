<?php
/**
 * @package Dashboard
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Add to Right Now Widget
 *
 * @return void
 */
function dedo_dashboard_right_now() {
	$total_files = wp_count_posts( 'dedo_download' );
	
	echo '<tr>';
	echo '<td class="first b b-tags"><a href="edit.php?post_type=dedo_download">' . $total_files->publish . '</a></td>';
	echo '<td class="t tags"><a href="edit.php?post_type=dedo_download">' . __( 'Downloads', 'delightful-downloads' ) . '</a></td>';
	echo '</tr>';
}
add_action( 'right_now_content_table_end' , 'dedo_dashboard_right_now' );

/**
 * Register dashboard widgets
 *
 * @return void
 */
function dedo_register_dashboard_widgets() {
	wp_add_dashboard_widget( 'dedo_dashboard_downloads', __( 'Download Stats', 'delightful-downloads' ), 'dedo_dashboard_downloads_widget' );
}
//add_action( 'wp_dashboard_setup', 'dedo_register_dashboard_widgets' );

/**
 * Downloads Dashboard Widget
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/
function dedo_dashboard_downloads_widget() {
	echo 'Content to follow...';
}