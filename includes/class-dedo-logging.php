<?php
/**
 * Logging Class
 *
 * @package  	Delightful Downloads
 * @author   	Ashley Rich
 * @copyright   Copyright (c) 2014, Ashley Rich
 * @since    	1.4
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class DEDO_Logging {

	/**
	 *	Init Logging
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function __construct() {

		// Hooks
		add_action( 'ddownload_download_before', array( $this, 'save_success' ), 10, 1 );
	}

	/**
	 * Save Success Log
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
	 * Save Blocked Log
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function save_blocked( $download_id ) {
		
		// Hook before log
		do_action( 'ddownload_save_blocked_before', $download_id );

		$log = array(
			'status'	=> 'blocked',
			'post_id'	=> $download_id
		);

		$this->insert_log( $log );

		// Hook after log
		do_action( 'ddownload_save_blocked_after', $download_id );
	}

	/**
	 * Save Permission Log
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function save_permission( $download_id ) {
		
		// Hook before log
		do_action( 'ddownload_save_permission_before', $download_id );

		$log = array(
			'status'	=> 'permission',
			'post_id'	=> $download_id
		);

		$this->insert_log( $log );

		// Hook after log
		do_action( 'ddownload_save_permission_after', $download_id );
	}	

	/**
	 * Insert Log
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

		// Are we logging events for this user role?
		if ( false === $this->role_check( $log ) ) {
			return;
		}

		// Do we have a grace period?
		if ( true === $this->grace_period( $log ) ) {
			return;
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
			$this->prepare_ip_address( $log['ip_address'] ),
			$log['agent'] 
		);

		// Run and update count if successfull
		if ( $wpdb->query( $sql ) && 'success' === $log['status'] ) {
			
			$count = get_post_meta( $log['post_id'], '_dedo_file_count', true );
			update_post_meta( $log['post_id'], '_dedo_file_count', ++$count );
		}

		// Hook after log
		do_action( 'ddownload_insert_log_after', $log );
	}

	/**
	 * Role Check
	 *
	 * Are we logging events for this user role?
	 *
	 * @access public
	 * @since 1.4
	 * @return boolean
	 */
	public function role_check( $log ) {
		global $dedo_options;
		
		if ( current_user_can( 'administrator' ) && !$dedo_options['log_admin_downloads'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Grace Period
	 *
	 * Has a log of the same type been logged recently?
	 *
	 * @access public
	 * @since 1.4
	 * @return boolean
	 */
	public function grace_period( $log ) {
		global $wpdb, $dedo_options;

		if ( $dedo_options['grace_period'] == 1 ) {
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
			$dedo_options['grace_period_duration'],
			$this->prepare_ip_address( $log['ip_address'] ) );

			if ( $wpdb->query( $sql ) ) {
				// We have a grace period
				return true;
			}
		}

		return false;
	}

	/**
	 *
	 * @param $ip_address
	 *
	 * @return string
	 */
	public function prepare_ip_address( $ip_address ) {
		// PHP versions prior to 5.3.0 on Windows did not support the inet_pton function
		if ( ! function_exists( 'inet_pton' ) ) {
			return '';
		}

		// Some servers pass an IP range, exploding suppresses PHP Warning
		$ip_address = explode( ',', $ip_address );

		return inet_pton( $ip_address[0] );
	}

}

// Initiate the logging system
$GLOBALS['dedo_logging'] = new DEDO_Logging();