<?php
/**
 * Upgrades
 *
 * @package  	Delightful Downloads
 * @author   	Ashley Rich
 * @copyright   Copyright (c) 2014, Ashley Rich
 * @since    	1.4
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Check for Upgrades
 *
 * @since  1.4
 */
function dedo_check_upgrades() {
	$version = get_option( 'delightful-downloads-version' );

	if ( version_compare( $version, '1.4', '<' ) ) {
		dedo_upgrade_1_4();
	}

	if ( version_compare( $version, '1.5', '<' ) ) {
		dedo_upgrade_1_5();
	}

	// Update version numbers
	if ( $version !== DEDO_VERSION ) {
		
		// Previous version installed, save prior version to db
		if ( false !== $version ) {
			update_option( 'delightful-downloads-prior-version', $version );
		}
	
		update_option( 'delightful-downloads-version', DEDO_VERSION );
	}

}
add_action( 'plugins_loaded', 'dedo_check_upgrades' );

/**
 * Version 1.4
 *
 * Add custom database structure for download statistics and
 * check for legacy logs.
 *
 * @since  1.4
 */
function dedo_upgrade_1_4() {
	global $dedo_statistics, $wpdb, $dedo_notices;

	// Setup new table structure
	$dedo_statistics->setup_table();

	// Check for legacy logs
	$sql = $wpdb->prepare( "
		SELECT COUNT(ID) FROM $wpdb->posts
		WHERE post_type = %s
	",
	'dedo_log' );

	$result = $wpdb->get_var( $sql );

	// Add flag to options table
	if ( $result > 0 ) {
		add_option( 'delightful-downloads-legacy-logs', $result );
	}

	// Add new option for admin notices
	add_option( 'delightful-downloads-notices', array() );

	// Add upgrade notice
	$message = __( 'Delightful Downloads updated to version 1.4.', 'delightful-downloads' );

	if ( get_option( 'delightful-downloads-legacy-logs' ) ) {
		
		$message .=  ' ' . sprintf( __( 'Please visit the %slogs screen%s to migrate your download statistics.', 'delightful-downloads' ), '<a href="' . admin_url( 'edit.php?post_type=dedo_download&page=dedo_statistics' ) . '">', '</a>' );
	}

	$dedo_notices->add( 'updated', $message );
}

/**
 * Version 1.5
 *
 * Update options with new sub-options.
 *
 * @since  1.5
 */
function dedo_upgrade_1_5() {
	global $dedo_options, $dedo_notices;

	// Convert old durations to boolean values
	foreach( array( 'grace_period' ,'auto_delete' ) as $key ) {
		$dedo_options[$key] = ( $dedo_options[$key] > 0 ) ? 1 : 0;
	}

	// Handle cache name change
	$dedo_options['cache'] = ( $dedo_options['cache_duration'] > 0 ) ? 1 : 0;

	// Update options
	$new_options = wp_parse_args( $dedo_options, dedo_get_default_options() );
	update_option( 'delightful-downloads', $new_options );

	// Add upgrade notice
	$dedo_notices->add( 'updated', __( 'Delightful Downloads updated to version 1.5.', 'delightful-downloads' ) );
}

/**
 * 1.4 Admin Notices
 *
 * @since  1.4
 */
function dedo_upgrade_notices_1_4() {

	// Only show on statistics page
	if ( isset( $_GET['page'] ) && 'dedo_statistics' == $_GET['page'] ) {

		// Only show if we have legacy logs		
		if ( !$legacy_logs = get_option( 'delightful-downloads-legacy-logs' ) ) {
			return;
		}

		// Enqueue our migration JS
		wp_enqueue_script( 'dedo-admin-js-legacy-logs' );

		// Output ajax url object
		wp_localize_script( 'dedo-admin-js-legacy-logs', 'dedo_admin_logs_migrate', array(
			'ajaxurl'		=> admin_url( 'admin-ajax.php', isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://' ),
			'action'		=> 'dedo_migrate_logs',
			'nonce'			=> wp_create_nonce( 'dedo_migrate_logs' ),
			'migrate_text'	=> __( 'Migrate', 'delightful-downloads' ),
			'stop_text' 	=> __( 'Stop', 'delightful-downloads' ),
			'error_text' 	=> __( 'The migration could not start due to an error.', 'delightful-downloads' )
		) );

		?>
		<div id="dedo_migrate_message" class="error">
			<p><?php echo sprintf( __( 'You have %s logs from an older version of Delightful Downloads. %sPlease make a backup of your database before migrating!%s', 'delightful-downloads' ), '<strong id="dedo_migrate_count">' .  $legacy_logs . '</strong>', '<p><strong>', '</strong></p>' ); ?></p>
			<p style="overflow: hidden;">
				<input type="button" id="dedo_migrate_button" name="dedo_migrate" value="<?php _e( 'Migrate', 'delightful-downloads' ); ?>" class="button button-primary" style="float: left;" />
				<span class="spinner" style="float: left; margin-left: 10px;"></span>
			</p>
			<noscript>
				<p class="description"><?php _e( 'JavaScript must be enabled to migrate legacy logs.', 'delightful-downloads' ); ?></p>
			</noscript>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'dedo_upgrade_notices_1_4' );

/**
 * 1.4 Migrate Legacy Logs
 *
 * Migrate logs from old postmeta table to 
 * custom statistics table. Cleanup postmeta
 * afterwards.
 *
 * @since  1.4
 */
function dedo_migrate_logs_ajax() {

	global $wpdb;

	// Check for nonce and permission
	if ( !check_ajax_referer( 'dedo_migrate_logs', 'nonce', false ) || !current_user_can( 'manage_options' ) ) {
		echo json_encode( array(
			'status'	=> 'error',
			'content'	=> __( 'Failed security check!', 'delightful-downloads' )
		) );

		die();
	}

	// Disable max_execution_time
	set_time_limit( 0 );

	// Get amount of legacy logs
	$sql = $wpdb->prepare( "
		SELECT COUNT(ID) FROM $wpdb->posts
		WHERE post_type = %s
	",
	'dedo_log' );

	$total_logs = $wpdb->get_var( $sql );

	// We have old logs, lets grab them
	if ( $total_logs > 0 ) {

		// Query for the results we need in blocks of 100
		$sql = $wpdb->prepare( "
			SELECT $wpdb->posts.ID AS log_id, 
				   $wpdb->posts.post_date AS date, 
				   $wpdb->posts.post_author AS user,
				   download_id.meta_value AS download_id,
				   user_ip.meta_value AS user_ip,
				   user_agent.meta_value AS user_agent
			FROM $wpdb->posts
			LEFT JOIN $wpdb->postmeta download_id 
				ON $wpdb->posts.ID = download_id.post_id 
				AND download_id.meta_key = %s
			LEFT JOIN $wpdb->postmeta user_ip 
				ON $wpdb->posts.ID = user_ip.post_id 
				AND user_ip.meta_key = %s
			LEFT JOIN $wpdb->postmeta user_agent
				ON $wpdb->posts.ID = user_agent.post_id 
				AND user_agent.meta_key = %s
			WHERE post_type = %s 
			ORDER BY post_date ASC LIMIT %d
		",
		'_dedo_log_download',
		'_dedo_log_ip',
		'_dedo_log_agent',
		'dedo_log',
		mt_rand( 95, 105 ) );

		// Store logs
		$logs = $wpdb->get_results( $sql, ARRAY_A );

		// Loop through, move and delete
		foreach ( $logs as $log ) {

			$sql = $wpdb->prepare( "
				INSERT INTO $wpdb->ddownload_statistics (post_id, date, user_id, user_ip, user_agent)
				VALUES (%d, %s, %d, %s, %s)
			",
			$log['download_id'],
			$log['date'],
			$log['user'],
			inet_pton( $log['user_ip'] ),
			$log['user_agent'] );

			if ( $wpdb->query( $sql ) ) {
				// Remove legacy log
				wp_delete_post( $log['log_id'], true );

				// Reduce counter
				$total_logs--;
			}
		}

		// Update legacy log flag
		if ( $total_logs > 0 ) {
			update_option( 'delightful-downloads-legacy-logs', $total_logs );
		}
		else {
			delete_option( 'delightful-downloads-legacy-logs' );
		}
	}

	// Return success
	echo json_encode( array (
		'status'	=> 'success',
		'content'	=> $total_logs
	) );

	die();
}
add_action( 'wp_ajax_dedo_migrate_logs', 'dedo_migrate_logs_ajax' );