<?php
/**
 * Delightful Downloads Page Statistics
 *
 * @package     Delightful Downloads
 * @subpackage  Admin/Page Statistics
 * @since       1.4
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Statistics Page
 *
 * @since  1.4
 */
function dedo_register_page_statistics() {
	
	global $dedo_statistics_page;

	$dedo_statistics_page = add_submenu_page( 'edit.php?post_type=dedo_download', __( 'Download Logs', 'delightful-downloads' ), __( 'Logs', 'delightful-downloads' ), 'manage_options', 'dedo_statistics', 'dedo_render_page_statistics' );

	// Hook for screen options dropdown
	add_action( "load-$dedo_statistics_page", 'dedo_statistics_screen_options' );
}
add_action( 'admin_menu', 'dedo_register_page_statistics', 20 );

/**
 * Render Statistics Page
 *
 * @since  1.4
 */
function dedo_render_page_statistics() {
	
	?>
	<div class="wrap">
		<h1><?php _e( 'Download Logs', 'delightful-downloads' ); ?>
			<a href="#dedo-stats-export" class="add-new-h2 dedo-modal-action"><?php _e( 'Export', 'delightful-downloads' ); ?></a>
			<a href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=dedo_download&page=dedo_statistics&action=empty_logs' ), 'dedo_empty_logs', 'dedo_empty_logs_nonce' ); ?>" class="add-new-h2 dedo_confirm_action" data-confirm="<?php _e( 'You are about to permanently delete the download logs.', 'delightful-downloads' ); ?>"><?php _e( 'Delete', 'delightful-downloads' ); ?></a>
		</h1>

		<div id="dedo-settings-main">	
			<?php do_action( 'ddownload_statistics_header' ); ?>
			
			<?php $table = new DEDO_List_table(); ?>
			<?php $table->display(); ?>

			<?php do_action( 'ddownload_statistics_footer' ); ?>
		</div>
	</div>
	<?php
}

/**
 * Render Export Logs Modal
 *
 * @since  1.5
 */
function dedo_render_export_modal() {
	// Ensure only added on statistics screen	
	$screen = get_current_screen();

	if ( 'dedo_download_page_dedo_statistics' !== $screen->id ) {
		return;
	}

	?>

	<div id="dedo-stats-export" class="dedo-modal" style="display: none; width: 400px; left: 50%; margin-left: -200px;">
		<a href="#" class="dedo-modal-close" title="Close"><span class="media-modal-icon"></span></a>
		<div class="media-modal-content">
			<h1><?php _e( 'Export Logs', 'delightful-downloads' ); ?></h1>
			<p><?php _e( 'Export log entries to a CSV file. Please select a date range, or leave blank to export all:', 'delightful-downloads' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'edit.php?post_type=dedo_download&page=dedo_statistics&action=export' ); ?>">
				<p class="left">
					<label for="dedo_start_date"><?php _e( 'Start Date', 'delightful-downloads' ); ?></label>
					<input name="dedo_start_date" id="dedo_start_date" type="date" ?>
				</p>
				<p class="right">
					<label for="dedo_end_date"><?php _e( 'End Date', 'delightful-downloads' ); ?></label>
					<input name="dedo_end_date" id="dedo_end_date" type="date" ?>
				</p>
				<p>
					<?php wp_nonce_field( 'dedo_export_stats','dedo_export_stats_nonce' ); ?>
					<input type="submit" value="<?php _e( 'Export', 'delightful-downloads' ); ?>" class="button button-primary"/>
				</p>
			</form>
		</div>
	</div>

	<?php

}
add_action( 'admin_footer', 'dedo_render_export_modal' );

/**
 * Statistics Page Actions
 *
 * @since  1.4
 */
function dedo_statistics_actions() {

	//Only perform on statistics page
	if ( isset( $_GET['page'] ) && 'dedo_statistics' == $_GET['page'] ) {

		// Export statistics
		if( isset( $_GET['action'] ) && 'export' == $_GET['action'] ) {
			dedo_statistics_actions_export();	
		}

		// Empty statistics
		if( isset( $_GET['action'] ) && 'empty_logs' == $_GET['action'] ) {
			dedo_statistics_actions_empty();
		}
	}
}
add_action( 'init', 'dedo_statistics_actions', 0 );

/**
 * Statistics Page Action Export
 *
 * @since  1.5
 */
function dedo_statistics_actions_export() {
	global $dedo_statistics, $dedo_notices;

	// Disable max_execution_time
	set_time_limit( 0 );

	// Verfiy nonce
	check_admin_referer( 'dedo_export_stats', 'dedo_export_stats_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {
		return;
	}

	// Add args to query
	$args = array();

	if ( isset( $_POST['dedo_start_date'] ) && !empty( $_POST['dedo_start_date'] ) ) {
		$args['start'] = $_POST['dedo_start_date'] . ' 00:00:00';
	}

	if ( isset( $_POST['dedo_end_date'] )  && !empty( $_POST['dedo_end_date'] ) ) {
		$args['end'] = $_POST['dedo_end_date'] . ' 23:59:59';
	}

	// Get logs
	$logs = $dedo_statistics->get_logs( $args );

	// Check we have logs before creating file
	if ( NULL == $logs ) {
		$dedo_notices->add( 'error', __( 'You do not have any logs to export in that date range.', 'delightful-downloads' ) );
		
		// Redirect page to remove action from URL
		wp_redirect( admin_url( 'edit.php?post_type=dedo_download&page=dedo_statistics' ) );
		exit();	
	}

	// Get download titles
	$downloads = get_posts( array( 'post_type' => 'dedo_download', 'posts_per_page'   => -1, ) );

	foreach( $downloads as $download ) {
		$download_title[$download->ID] =  $download->post_title;
	}

	// Get user names
	$users = get_users();

	foreach( $users as $user ) {
		$user_name[$user->ID] = $user->user_email;
	}

	// Set filename
	$filename = 'download-logs-' . date( 'Ymd' ) . '.csv';

	// Output headers so that the file is downloaded
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Expires: 0' );

	$output = fopen( 'php://output', 'w' );

	// Column headings
	fputcsv( $output, array( __( 'ID', 'delightful-downloads' ), __( 'Status', 'delightful-downloads' ), __( 'Date', 'delightful-downloads' ), __( 'Download', 'delightful-downloads' ), __( 'User', 'delightful-downloads' ), __( 'IP Address', 'delightful-downloads' ), __( 'User Agent', 'delightful-downloads' ) ) );

	// Add data
	foreach( $logs as $log ) {
		// Convert download ID to title
		$log['post_id'] = ( isset( $download_title[$log['post_id']] ) ) ? $download_title[$log['post_id']] : __( 'Unknown', 'delightful-downloads' );

		// Convert user ID to email
		$log['user_id'] = ( isset( $user_name[$log['user_id']] ) ) ? $user_name[$log['user_id']] : __( 'Non-member', 'delightful-downloads' );

		// Convert ip to human readable
        if ( ! empty( $log['user_ip'] ) ) {
            $log['user_ip'] = inet_ntop( $log['user_ip'] );
        }
		
		fputcsv( $output, $log );
	}	

	die();
}

/**
 * Statistics Page Action Empty
 *
 * @since  1.5
 */
function dedo_statistics_actions_empty() {
	global $dedo_statistics, $dedo_notices;

	// Verfiy nonce
	check_admin_referer( 'dedo_empty_logs', 'dedo_empty_logs_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {
		return;
	}

	$result = $dedo_statistics->empty_table();

	if ( false === $result ) {
		// Error
		$dedo_notices->add( 'error', __( 'Logs could not be deleted.', 'delightful-downloads' ) );
	}
	else {
		// Success
		$dedo_notices->add( 'updated', __( 'Logs deleted successfully.', 'delightful-downloads' ) );
	}

	// Redirect page to remove action from URL
	wp_redirect( admin_url( 'edit.php?post_type=dedo_download&page=dedo_statistics' ) );
	exit();
}

/**
 * Statistics Sreen Options
 *
 * @since  1.4
 */
function dedo_statistics_screen_options() {
 
	global $dedo_statistics_page;

	$screen = get_current_screen();

	if ( !is_object( $screen ) || $screen->id != $dedo_statistics_page ) {
		return;
	}

	// Per page option
	$args = array(
	    'label' => __( 'Download Logs', 'delightful-downloads' ),
	    'default' => 20,
	    'option' => 'dedo_logs_per_page'
	);
	 
	add_screen_option( 'per_page', $args );
 
}

/**
 * Statistics Save Sreen Options
 *
 * @since  1.4
 */
function dedo_statistics_save_screen_options( $status, $option, $value ) {
	
	if ( 'dedo_logs_per_page' == $option ) {
		
		return $value;
	}
}
add_filter( 'set-screen-option' , 'dedo_statistics_save_screen_options', 10, 3 );