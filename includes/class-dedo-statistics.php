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
	 *	Init statistics class.
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function __construct() {

		global $wpdb;

		// Add custom table to wpdb.
		$wpdb->ddownload_statistics = $wpdb->prefix . 'ddownload_statistics';

		// Hooks
		add_action( 'ddownload_download_before', array( $this, 'save_success' ), 10, 1 );

	}

	/**
	 * Setup custom database table structure.
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
	 * Save success log.
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function save_success( $download_id ) {
		// Hook before log
		do_action( 'ddownload_save_success_before', $download_id );

		$log = array(
			'status'	=> 'success',
			'post_id'	=> $download_id
		);

		$this->insert_log( $log );

		// Hook after log
		do_action( 'ddownload_save_success_after', $download_id );
	}

	/**
	 * Insert log into database.
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function insert_log( $log ) {
		global $wpdb, $dedo_options;

		// Hook before log
		do_action( 'ddownload_insert_log_before', $log );

		// Build log array
		$defaults = array(
			'post_id'	=> 0,
			'status'	=> 'success',
			'date'		=> current_time( 'mysql' ),
			'user_id'	=> get_current_user_id(),
			'ip_address'=> dedo_download_ip(),
			'agent'		=> sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] )	
		);

		$log = wp_parse_args( $log, $defaults );

		// Check if we are logging events by admins
		if ( current_user_can( 'administrator' ) && !$dedo_options['log_admin_downloads'] ) {
			return;
		}

		// Do we have a grace period?
		if ( $dedo_options['grace_period'] > 0 ) {

			// Check for recent log of same download and status within grace period
			$sql = $wpdb->prepare( "
				SELECT ID FROM $wpdb->ddownload_statistics
				WHERE status = %s
					AND post_id = %d
					AND date > DATE_SUB(%s, INTERVAL %d MINUTE)
					AND user_ip = %s
			",
			$log['status'],
			$log['post_id'],
			$log['date'],
			$dedo_options['grace_period'],
			inet_pton( $log['ip_address'] ) );

			// Query and exit if found
			if ( $wpdb->query( $sql ) ) {
				return;
			}
		}

		// Prepare sql query
		$sql = $wpdb->prepare( "
				INSERT INTO $wpdb->ddownload_statistics (status, post_id, date, user_id, user_ip, user_agent)
				VALUES (%s, %d, %s, %d, %s, %s)
			",
			$log['status'],
			$log['post_id'],
			$log['date'],
			$log['user_id'],
			inet_pton( $log['ip_address'] ),
			$log['agent'] 
		);

		// Run and update count if successfull and for success status only
		if ( $wpdb->query( $sql ) && 'success' == $log['status'] ) {
			
			$count = get_post_meta( $log['post_id'], '_dedo_file_count', true );
			update_post_meta( $log['post_id'], '_dedo_file_count', ++$count );
		}

		// Hook after log
		do_action( 'ddownload_insert_log_after', $log );
	}

	/**
	 * Empty Statistics Table.
	 *
	 * @access public
	 * @since 1.4
	 * @return boolean
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
	 * Delete Statistics Table.
	 *
	 * @access public
	 * @since 1.4
	 * @return boolean
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