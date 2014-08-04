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

	// Supply options for popular downloads dropdown
	wp_localize_script( 'dedo-admin-js-global', 'DEDODashboardOptions', array(
		'ajaxURL'		=> admin_url( 'admin-ajax.php', isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://' ),
		'nonce'			=> wp_create_nonce( 'dedo_dashboard' ),
		'errorText'		=> __( 'Download statistics could not be retrieved.', 'delightful-downloads' ),
		'noResultsText'	=> __( 'There are no popular downloads yet!', 'delightful-downloads' )
	) );

	?>

	<div id="ddownload-count">
		<ul>
			<li id="ddownload-count-1" style="opacity: 0">
				<span class="count">0</span>
				<span class="label"><?php _e( 'Last 24 Hours', 'delightful-downloads' ); ?></span>
			</li>
			<li id="ddownload-count-7" style="opacity: 0">
				<span class="count">0</span>
				<span class="label"><?php _e( 'Last 7 Days', 'delightful-downloads' ); ?></span>
			</li>
			<li id="ddownload-count-30" style="opacity: 0">
				<span class="count">0</span>
				<span class="label"><?php _e( 'Last 30 Days', 'delightful-downloads' ); ?></span>
			</li>
			<li id="ddownload-count-0" style="opacity: 0">
				<span class="count">0</span>
				<span class="label"><?php _e( 'All Time', 'delightful-downloads' ); ?></span>
			</li>
		</ul>
	</div>
	<div id="ddownload-popular">
		<h4><?php _e( 'Popular Downloads', 'delightful-downloads' ); ?></h4>
		
		<?php

		$popular_downloads = $dedo_statistics->get_popular_downloads( array( 'limit' => 5, 'cache' => false ) );

		if ( !empty( $popular_downloads ) ) {

			echo '<ol id="popular-downloads">';

			foreach ( $popular_downloads as $key => $value ) {
				echo '<li>';
				echo '<a href="' . get_edit_post_link( $value['ID'] ) . '"><span class="position">' . ( $key + 1 ) . '.</span>' . $value['title'] . ' <span class="count">' . number_format_i18n( $value['downloads'] ) . '</span></a>';
				echo '</li>';
			}

			echo '</ol>';
		}
		else {

			echo '<p>' . __( 'There are no popular downloads yet!', 'delightful-downloads' ) . '</p>';
		}

		?>
		
		<div class="sub">
			<select id="popular-downloads-dropdown">
				<option value="1"><?php _e( 'Last 24 Hours', 'delightful-downloads' ); ?></option>
				<option value="7"><?php _e( 'Last 7 Days', 'delightful-downloads' ); ?></option>
				<option value="30"><?php _e( 'Last 30 Days', 'delightful-downloads' ); ?></option>
				<option value="0" selected="selected"><?php _e( 'All Time', 'delightful-downloads' ); ?></option>
			</select>
			<span class="spinner"></span>
			<p class="error" style="display: none"></p>
		</div>
	</div>
	
	<?php
}

/**
 * Count Downloads Ajax
 *
 * @since  1.4
*/
function dedo_count_downloads_ajax() {

	global $dedo_statistics;

	// Check for nonce and permission
	if ( !check_ajax_referer( 'dedo_dashboard', 'nonce', false ) || !current_user_can( apply_filters( 'dedo_cap_dashboard', 'edit_pages' ) ) ) {
		
		echo json_encode( array(
			'status'	=> 'error',
			'content'	=> __( 'Failed security check!', 'delightful-downloads' )
		) );

		die();
	}

	// Get counts
	$result = array(
		'ddownload-count-1' 	=> number_format_i18n( $dedo_statistics->count_downloads( array( 'days' => 1, 'cache' => false ) ) ),
		'ddownload-count-7' 	=> number_format_i18n( $dedo_statistics->count_downloads( array( 'days' => 7, 'cache' => false ) ) ),
		'ddownload-count-30'	=> number_format_i18n( $dedo_statistics->count_downloads( array( 'days' => 30, 'cache' => false ) ) ),
		'ddownload-count-0'		=> number_format_i18n( $dedo_statistics->count_downloads( array( 'days' => 0, 'cache' => false ) ) )
	);

	// Return success and data
	echo json_encode( array (
		'status'	=> 'success',
		'content'	=> $result
	) );

	die();
}
add_action( 'wp_ajax_dedo_count_downloads', 'dedo_count_downloads_ajax' );

/**
 * Popular Downloads Ajax
 *
 * @since  1.4
*/
function dedo_popular_downloads_ajax() {

	global $dedo_statistics;

	// Check for nonce and permission
	if ( !check_ajax_referer( 'dedo_dashboard', 'nonce', false ) || !current_user_can( apply_filters( 'dedo_cap_dashboard', 'edit_pages' ) ) ) {
		
		echo json_encode( array(
			'status'	=> 'error',
			'content'	=> __( 'Failed security check!', 'delightful-downloads' )
		) );

		die();
	}

	// Get days from request
	$days = absint( $_REQUEST['days'] );

	// Get popular downloads
	$result = $dedo_statistics->get_popular_downloads( array( 'days' => $days, 'limit' => 5, 'cache' => false ) );

	// Add download URL to array of results
	foreach ( $result as $key => $value ) {

		$result[$key]['url'] = ( !empty( $result[$key]['title'] ) ) ? get_edit_post_link( $value['ID'] ) : admin_url( 'edit.php?post_type=dedo_download' );
		$result[$key]['title'] = ( !empty( $result[$key]['title'] ) ) ? $result[$key]['title'] : __( 'Unknown', 'delightful-downloads' );
		$result[$key]['downloads'] = number_format_i18n( $value['downloads'] );
	}

	// Return success and data
	echo json_encode( array (
		'status'	=> 'success',
		'content'	=> $result
	) );

	die();
}
add_action( 'wp_ajax_dedo_popular_downloads', 'dedo_popular_downloads_ajax' );