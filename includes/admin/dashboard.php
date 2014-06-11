<?php
/**
 * Delightful Downloads Dashboard
 *
 * @package     Delightful Downloads
 * @subpackage  Admin/Dashboard
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register dashboard widgets
 *
 * @since  1.0
 */
function dedo_register_dashboard_widgets() {
	
	if ( current_user_can( apply_filters( 'dedo_cap_dashboard', 'edit_pages' ) ) ) {
		
		wp_add_dashboard_widget( 'dedo_dashboard_downloads', __( 'Download Statistics', 'delightful-downloads' ), 'dedo_dashboard_downloads_widget' );
	}
}
add_action( 'wp_dashboard_setup', 'dedo_register_dashboard_widgets' );

/**
 * Downloads Dashboard Widget
 *
 * @since  1.0
*/
function dedo_dashboard_downloads_widget() {
	
	?>
	<div id="ddownload-count">
		<ul>
			<li>
				<strong>8,047</strong>
				<span><?php _e( 'Last 24 Hours', 'delightful-downloads' ); ?></span>
			</li>
			<li>
				<strong>56,012</strong>
				<span><?php _e( 'Last 7 Days', 'delightful-downloads' ); ?></span>
			</li>
			<li>
				<strong>121,349</strong>
				<span><?php _e( 'Last 30 Days', 'delightful-downloads' ); ?></span>
			</li>
			<li>
				<strong>759,567</strong>
				<span><?php _e( 'All Time', 'delightful-downloads' ); ?></span>
			</li>
		</ul>
	</div>

	<div id="ddownload-popular">
		
	</div>
	
	<?php
}