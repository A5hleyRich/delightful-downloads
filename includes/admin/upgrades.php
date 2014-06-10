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
 * Admin Notices
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