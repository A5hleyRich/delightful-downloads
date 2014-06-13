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
	
	global $dedo_statistics;

	?>
	<div id="ddownload-count">
		<ul>
			<li>
				<strong><?php echo number_format_i18n( $dedo_statistics->count_downloads( 1 ) ); ?></strong>
				<span><?php _e( 'Last 24 Hours', 'delightful-downloads' ); ?></span>
			</li>
			<li>
				<strong><?php echo number_format_i18n( $dedo_statistics->count_downloads( 7 ) ); ?></strong>
				<span><?php _e( 'Last 7 Days', 'delightful-downloads' ); ?></span>
			</li>
			<li>
				<strong><?php echo number_format_i18n( $dedo_statistics->count_downloads( 30 ) ); ?></strong>
				<span><?php _e( 'Last 30 Days', 'delightful-downloads' ); ?></span>
			</li>
			<li>
				<strong><?php echo number_format_i18n( $dedo_statistics->count_downloads() ); ?></strong>
				<span><?php _e( 'All Time', 'delightful-downloads' ); ?></span>
			</li>
		</ul>
	</div>
	<div id="ddownload-popular">
		<h4><?php _e( 'Popular Downloads', 'delightful-downloads' ); ?></h4>
		<ol>
			<li>
				<a href="#">Dolor Fermentum Malesuada<span class="count">1,896</span></a>
			</li>
			<li>
				<a href="#">Dolor Fermentum Malesuada<span class="count">1,156</span></a>
			</li>
			<li>
				<a href="#">Dolor Fermentum Malesuada<span class="count">1,001</span></a>
			</li>
			<li>
				<a href="#">Dolor Fermentum Malesuada<span class="count">863</span></a>
			</li>
			<li>
				<a href="#">Dolor Fermentum Malesuada<span class="count">501</span></a>
			</li>
		</ol>
		<div class="sub">	
			<a href="<?php echo admin_url( 'edit.php?post_type=dedo_download&page=dedo_statistics' ); ?>"><?php _e( 'View Statistics', 'delightful-downloads' ); ?></a>
			<select>
				<option><?php _e( 'Last 24 Hours', 'delightful-downloads' ); ?></option>
				<option><?php _e( 'Last 7 Days', 'delightful-downloads' ); ?></option>
				<option><?php _e( 'Last 30 Days', 'delightful-downloads' ); ?></option>
				<option><?php _e( 'All Time', 'delightful-downloads' ); ?></option>
			</select>
		</div>
	</div>
	
	<?php
}