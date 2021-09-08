<?php
/**
 * Delightful Downloads Cron
 *
 * @package     Delightful Downloads
 * @subpackage  Includes/Cron
 * @since       1.3
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Cron Events
 *
 * @since  1.3
 */
function dedo_cron_register() {
	
	// Daily
	if ( !wp_next_scheduled( 'dedo_cron_daily' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'daily', 'dedo_cron_daily' );
	}	

	// Weekly
	if ( !wp_next_scheduled( 'dedo_cron_weekly' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'weekly', 'dedo_cron_weekly' );
	}	
}
add_action( 'admin_init', 'dedo_cron_register' );

/**
 * Daily Events
 *
 * @since  1.4
 */
function dedo_cron_daily() {

	global $dedo_options, $dedo_statistics;

	// Delete old logs
	if ( $dedo_options['auto_delete'] == 1 ) {

		$date = $dedo_statistics->convert_days_date( $dedo_options['auto_delete_duration'] );
		$limit = apply_filters( 'dedo_cron_delete_limit', 1000 );

		$dedo_statistics->delete_logs( array( 'end_date' => $date, 'limit' => $limit ) );
	}

}
add_action( 'dedo_cron_daily', 'dedo_cron_daily' );

/**
 * Weekly Events
 *
 * @since  1.3
 */
function dedo_cron_weekly() {
	// Run folder protection
	dedo_folder_protection();
}
add_action( 'dedo_cron_weekly', 'dedo_cron_weekly' );

/**
 * Add Cron Schedules
 *
 * @since  1.3
 */
function dedo_cron_schedules( $schedules ) {
	// Adds once weekly to the existing schedules.
 	$schedules['weekly'] = array(
 		'interval' => 604800,
 		'display' => __( 'Once Weekly' )
 	);

 	return $schedules;
}
add_filter( 'cron_schedules', 'dedo_cron_schedules' );