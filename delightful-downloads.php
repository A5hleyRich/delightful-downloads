<?php
/*
Plugin Name: Delightful Downloads
Plugin URI: http://wordpress.org/extend/plugins/delightful-downloads/
Description: A super-awesome downloads manager for WordPress.
Version: 1.3.1.1
Author: Ashley Rich
Author URI: http://ashleyrich.com
License: GPL2

Copyright 2013  Ashley Rich  (email : hello@ashleyrich.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * @package Main
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Constants
 */
if( !defined( 'DEDO_VERSION' ) )
	define( 'DEDO_VERSION', '1.3.1.1' );

if( !defined( 'DEDO_PLUGIN_URL' ) )
	define( 'DEDO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if( !defined( 'DEDO_PLUGIN_DIR' ) )
	define( 'DEDO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Localization
 */
function dedo_localization() {
	load_textdomain( 'delightful-downloads', WP_LANG_DIR . '/delightful-downloads/delightful-downloads-' . get_locale() . '.mo' );
	load_plugin_textdomain( 'delightful-downloads', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );	
}
add_action( 'plugins_loaded', 'dedo_localization' );

/**
 * Options
 */
global $dedo_options, $dedo_default_options;

// Include options
include_once( DEDO_PLUGIN_DIR . 'includes/options.php' );

// Set globals
$dedo_default_options = dedo_get_default_options();
$dedo_options = wp_parse_args( get_option( 'delightful-downloads' ), $dedo_default_options );

/**
 * Include required plugin files
 */
include_once( DEDO_PLUGIN_DIR . 'includes/cron.php' );
include_once( DEDO_PLUGIN_DIR . 'includes/functions.php' );
include_once( DEDO_PLUGIN_DIR . 'includes/mime-types.php' );
include_once( DEDO_PLUGIN_DIR . 'includes/post-types.php' );
include_once( DEDO_PLUGIN_DIR . 'includes/process-download.php' );
include_once( DEDO_PLUGIN_DIR . 'includes/scripts.php' );
include_once( DEDO_PLUGIN_DIR . 'includes/shortcodes.php' );
include_once( DEDO_PLUGIN_DIR . 'includes/taxonomies.php' );
if( is_admin() ) {
	include_once( DEDO_PLUGIN_DIR . 'includes/admin/ajax.php' );
	include_once( DEDO_PLUGIN_DIR . 'includes/admin/dashboard.php' );
	include_once( DEDO_PLUGIN_DIR . 'includes/admin/media-button.php' );
	include_once( DEDO_PLUGIN_DIR . 'includes/admin/meta-boxes.php' );
	include_once( DEDO_PLUGIN_DIR . 'includes/admin/page-settings.php' );
	include_once( DEDO_PLUGIN_DIR . 'includes/admin/page-support.php' );
}

/**
 * On activation
 */
function dedo_activation() {
	global $dedo_default_options;
	
	// Add version and prior version to database
	if( $current_version = get_option( 'delightful-downloads-version' ) ) {
		update_option( 'delightful-downloads-prior-version', $current_version );
	}
	update_option( 'delightful-downloads-version', DEDO_VERSION );

	// Add default options to database in no options exist
	add_option( 'delightful-downloads', $dedo_default_options );

	// Run folder protection
	dedo_folder_protection();
}
register_activation_hook( __FILE__, 'dedo_activation' );

/**
 * On deactivation
 */
function dedo_deactivation() {
	// Clear transients
	dedo_delete_all_transients();
}
register_deactivation_hook( __FILE__, 'dedo_deactivation' );

/**
 * Add plugin links
 */
function dedo_plugin_links( $links, $file ) {
	if( $file == plugin_basename(__FILE__) ) {
		$plugin_links[] = '<a href="' . admin_url( 'edit.php?post_type=dedo_download&page=dedo_support' ) . '">' . __( 'Support', 'delightful-downloads' ) . '</a>';
		$plugin_links[] = '<a href="' . admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings' ) . '">' . __( 'Settings', 'delightful-downloads' ) . '</a>';
	
		foreach( $plugin_links as $plugin_link ) {
			array_unshift( $links, $plugin_link );
		}
	}

	return $links;
}
add_filter( 'plugin_action_links', 'dedo_plugin_links', 10, 2 );
