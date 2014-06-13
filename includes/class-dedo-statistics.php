<?php
/**
 * Statistics Class
 *
 * @package  	Delightful Downloads
 * @author   	Ashley Rich
 * @copyright   Copyright (c) 2014, Ashley Rich
 * @since    	1.4
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class DEDO_Statistics {

	/**
	 *	Init Statistics
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function __construct() {

		global $wpdb;

		// Add custom table to wpdb.
		$wpdb->ddownload_statistics = $wpdb->prefix . 'ddownload_statistics';
	}

	/**
	 * Count Downloads
	 *
	 * Count total downloads for all/single downloads/download. If a date range is set
	 * the statistics table is used. If not, the meta keys are used.
	 *
	 * @access public
	 * @since 1.4
	 * @return string
	 */
	public function count_downloads( $days = false, $download_id = false, $start_date = false, $end_date = false ) {

		global $wpdb;

		// Days set, convert to start date and pass to count_logs
		if ( $days ) {

			$start_date = $this->convert_days_date( $days );
		}

		if ( $start_date ) {

			$result = $this->count_logs( $download_id, $start_date, $end_date, 'success' );
		}
		else {

			// Set query
			$sql = $wpdb->prepare( "
				SELECT SUM(meta_value)
				FROM $wpdb->postmeta
				WHERE meta_key = %s
			",
			'_dedo_file_count' );

			// Append download id
			if ( $download_id ) {

				$sql .= $wpdb->prepare( " AND post_id = %d", $download_id );
			}

			$result = $wpdb->get_var( $sql );
		}

		return ( $result === NULL ) ? 0 : $result;
	}

	/**
	 * Count Logs
	 *
	 * Count logs from statistics table.
	 *
	 * @access public
	 * @since 1.4
	 * @return string
	 */
	public function count_logs( $download_id = false, $start_date = false, $end_date = false, $status = false ) {

		global $wpdb;

		// Set main SQL query
		$sql = $wpdb->prepare( "
			SELECT COUNT(ID)
			FROM $wpdb->ddownload_statistics
		" );

		// Append where clause for status
		if ( $status ) {

			$sql .= $wpdb->prepare( " WHERE status = %s", $status );
		}
		else {

			$sql .= $wpdb->prepare( " WHERE 1 = %d", 1 );
		}

		// Append download id
		if ( $download_id ) {

			$sql .= $wpdb->prepare( " AND post_id = %d", $download_id );
		}

		// Append start date
		if ( $start_date ) {

			$sql .= $wpdb->prepare( " AND date >= %s", $start_date );
		}

		// Append end date
		if ( $end_date ) {

			$sql .= $wpdb->prepare( " AND date <= %s", $end_date );
		}

		$result = $wpdb->get_var( $sql );
		
		return ( $result === NULL ) ? 0 : $result;
	}

	/**
	 * Convert Days Date
	 *
	 * Converts number of days into current date - days.
	 *
	 * @access public
	 * @since 1.4
	 * @return string
	 */
	public function convert_days_date( $days ) {

		$now = current_time( 'timestamp' );
		$timestamp = strtotime( '-' . $days . ' days', $now );

		return date( 'Y-m-d H:i:s', $timestamp );
	}	

	/**
	 * Setup Statistics Table
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function setup_table() {
		
		global $wpdb;

		$sql = "
			CREATE TABLE $wpdb->ddownload_statistics (
				ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				status varchar(10) NOT NULL DEFAULT 'success',
				date datetime NOT NULL,
				post_id bigint(20) unsigned NOT NULL,
				user_id bigint(20) unsigned NOT NULL DEFAULT '0',
				user_ip varbinary(16) NOT NULL,
				user_agent varchar(255) NOT NULL,
			PRIMARY KEY  (ID),
			KEY status (status),
			KEY date (date),
			KEY post_id (post_id),
			KEY user_ip (user_ip)
			) DEFAULT CHARSET=$wpdb->charset;
		";

		// Include our database function and run
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );
	}

	/**
	 * Empty Statistics Table
	 *
	 * @access public
	 * @since 1.4
	 * @return int/boolean (rows affected or false on error)
	 */
	public function empty_table() {
		
		global $wpdb;

		// Only admins allowed to empty table
		if ( !current_user_can( 'administrator' ) ) {
			return;
		}

		$sql = "TRUNCATE TABLE $wpdb->ddownload_statistics";

		return $wpdb->query( $sql );
	}

	/**
	 * Delete Statistics Table
	 *
	 * @access public
	 * @since 1.4
	 * @return int/boolean (rows affected or false on error)
	 */
	public function delete_table() {
		
		global $wpdb;

		// Only admins allowed to remove table
		if ( !current_user_can( 'administrator' ) ) {
			return;
		}

		$sql = "DROP TABLE IF EXISTS $wpdb->ddownload_statistics";

		return $wpdb->query( $sql );
	}	

}

// Initiate the logging system
$GLOBALS['dedo_statistics'] = new DEDO_Statistics();



global $dedo_statistics;

// echo $dedo_statistics->count_downloads( 7 );