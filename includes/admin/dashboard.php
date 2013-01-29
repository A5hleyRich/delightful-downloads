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
	wp_add_dashboard_widget( 'dedo_dashboard_downloads', 'Delightful Downloads ' . __( 'Statistics', 'delightful-downloads' ), 'dedo_dashboard_downloads_widget' );
}
add_action( 'wp_dashboard_setup', 'dedo_register_dashboard_widgets' );

/**
 * Downloads Dashboard Widget
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/
function dedo_dashboard_downloads_widget() {
	?>
	<div class="table table_today">
		<p class="sub"><?php _e( 'Today', 'delightful-downloads' ); ?></p>
		<table>
			<tbody>
				<tr>
					<td class="b"><a href="edit.php?post_type=dedo_log"><?php echo dedo_get_total_count( 1 ); ?></a></td>
					<td class="last t"><a href="edit.php?post_type=dedo_download"><?php _e( 'Downloads', 'delightful-downloads' ); ?></a></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="table table_alltime">
		<p class="sub"><?php _e( 'All Time', 'delightful-downloads' ); ?></p>
		<table>
			<tbody>
				<tr>
					<td class="b"><a href="edit.php?post_type=dedo_log"><?php echo dedo_get_total_count( 0 ); ?></a></td>
					<td class="last t"><a href="edit.php?post_type=dedo_download"><?php _e( 'Downloads', 'delightful-downloads' ); ?></a></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="table table_last7">
		<p class="sub"><?php _e( 'Last 7 Days', 'delightful-downloads' ); ?></p>
		<table>
			<tbody>
				<tr>
					<td class="b"><a href="edit.php?post_type=dedo_log"><?php echo dedo_get_total_count( 7 ); ?></a></td>
					<td class="last t"><a href="edit.php?post_type=dedo_download"><?php _e( 'Downloads', 'delightful-downloads' ); ?></a></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="table table_last30">
		<p class="sub"><?php _e( 'Last 30 Days', 'delightful-downloads' ); ?></p>
		<table>
			<tbody>
				<tr>
					<td class="b"><a href="edit.php?post_type=dedo_log"><?php echo dedo_get_total_count( 30 ); ?></a></td>
					<td class="last t"><a href="edit.php?post_type=dedo_download"><?php _e( 'Downloads', 'delightful-downloads' ); ?></a></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="dedo-version">
		<p><?php _e( 'You are using' ); ?> <strong>Delightful Downloads <?php echo DEDO_VERSION; ?></strong>.</p>
	</div>
	<?php
}