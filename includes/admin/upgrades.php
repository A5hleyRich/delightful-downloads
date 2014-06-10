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
	global $dedo_statistics;

	$version = get_option( 'delightful-downloads-version' );

	/**
	 * Version 1.4
	 *
	 * Add custom database structure for download statistics and
	 * check for legacy logs.
	*/
	if ( version_compare( $version, '1.4', '<' ) ) {
		
		global $wpdb;

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
 * 1.4 Admin Notices
 *
 * @since  1.4
 */
function dedo_upgrade_notices() {

	// Only show on statistics page
	if ( isset( $_GET['page'] ) && 'dedo_statistics' == $_GET['page'] ) {

		// Only show if we have legacy logs		
		if ( !$legacy_logs = get_option( 'delightful-downloads-legacy-logs' ) ) {
			return;
		}

		// Output ajax url object
		wp_localize_script( 'dedo-admin-tools-js', 'dedo_admin_tools_migrate', array(
			'ajaxurl'		=> admin_url( 'admin-ajax.php', isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://' ),
			'action'		=> 'dedo_migrate_logs',
			'nonce'			=> wp_create_nonce( 'dedo_migrate_logs' ),
			'migrate_text'	=> __( 'Migrate', 'delightful-downloads' ),
			'stop_text' 	=> __( 'Stop', 'delightful-downloads' ),
			'error_text' 	=> __( 'The migration could not start due to an error.', 'delightful-downloads' )
		) );

		?>
		<div class="error">
			<p><?php echo sprintf( __( 'You have %s logs from an older version of Delightful Downloads.', 'delightful-downloads' ), '<strong id="dedo_migrate_count">' .  $legacy_logs . '</strong>' ); ?></p>
			<p>
				<input type="button" id="dedo_migrate_button" name="dedo_migrate" value="<?php _e( 'Migrate', 'delightful-downloads' ); ?>" class="button button-primary"/>
				<span class="spinner" style="float: none"></span>
			</p>
			<noscript>
				<p class="description"><?php _e( 'JavaScript must be enabled to migrate legacy logs.', 'delightful-downloads' ); ?></p>
			</noscript>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'dedo_upgrade_notices' );

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
			'content'	=> 'Failed security check!'
		) );

		die();
	}

	// Get name of new statistics table
	$statistics_table = $wpdb->prefix . 'ddownload_statistics';

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
				INSERT INTO $statistics_table (post_id, date, user_id, user_ip, user_agent)
				VALUES (%d, %s, %d, %s, %s)
			",
			$log['download_id'],
			$log['date'],
			$log['user'],
			$log['user_ip'],
			$log['user_agent'] );

			if ( $wpdb->query( $sql ) ) {
				// Remove legacy log
				wp_delete_post( $log['log_id'], true );

				// Reduce counter
				$total_logs--;
			}
		}

		// Update legacy log flag
		update_option( 'delightful-downloads-legacy-logs', $total_logs );
	}
	else {
		// All logs remove. Delete flag
		delete_option( 'delightful-downloads-legacy-logs' );
	}

	// Return success
	echo json_encode( array (
		'status'	=> 'success',
		'content'	=> $total_logs
	) );

	die();
}
add_action( 'wp_ajax_dedo_migrate_logs', 'dedo_migrate_logs_ajax' );