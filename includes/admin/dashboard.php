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
		wp_add_dashboard_widget( 'dedo_dashboard_downloads', 'Delightful Downloads ' . __( 'Statistics', 'delightful-downloads' ), 'dedo_dashboard_downloads_widget' );
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
	<div class="table table_today">
		<p class="sub"><?php _e( 'Last 24 Hours', 'delightful-downloads' ); ?></p>
		<table>
			<tbody>
				<tr>
					<td class="last t"><a href="edit.php?post_type=dedo_download"><?php echo number_format_i18n( dedo_get_total_count( 1 ) ) . ' ' . __( 'Downloads', 'delightful-downloads' ); ?></a></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="table table_alltime">
		<p class="sub"><?php _e( 'All Time', 'delightful-downloads' ); ?></p>
		<table>
			<tbody>
				<tr>
					<td class="last t"><a href="edit.php?post_type=dedo_download"><?php echo number_format_i18n( dedo_get_total_count( 0 ) ) . ' ' . __( 'Downloads', 'delightful-downloads' ); ?></a></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="table table_last7">
		<p class="sub"><?php _e( 'Last 7 Days', 'delightful-downloads' ); ?></p>
		<table>
			<tbody>
				<tr>
					<td class="last t"><a href="edit.php?post_type=dedo_download"><?php echo number_format_i18n( dedo_get_total_count( 7 ) ) . ' ' . __( 'Downloads', 'delightful-downloads' ); ?></a></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="table table_last30">
		<p class="sub"><?php _e( 'Last 30 Days', 'delightful-downloads' ); ?></p>
		<table>
			<tbody>
				<tr>
					<td class="last t"><a href="edit.php?post_type=dedo_download"><?php echo number_format_i18n( dedo_get_total_count( 30 ) ) . ' ' . __( 'Downloads', 'delightful-downloads' ); ?></a></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="dedo-version">
		<p><?php _e( 'You are using' ); ?> <strong>Delightful Downloads <?php echo DEDO_VERSION; ?></strong>.</p>
	</div>
	<?php
}