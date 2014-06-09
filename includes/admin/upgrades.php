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
	 * Add custom database structure for download statistics.
	*/
	if ( version_compare( $version, '1.4', '<' ) ) {
		$dedo_statistics->setup_table();
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