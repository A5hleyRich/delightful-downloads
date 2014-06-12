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