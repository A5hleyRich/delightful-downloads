<?php
/*
Plugin Name: Delightful Downloads
Plugin URI: http://wordpress.org/extend/plugins/delightful-downloads/
Description: A super-awesome downloads manager for WordPress.
Version: 1.2
Author: Ashley Rich
Author URI: http://www.ashleyrich.com
License: GPL2

Copyright 2013  Ashley Rich  (email : ashleyrich@me.com)

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
	define( 'DEDO_VERSION', '1.2' );

if( !defined( 'DEDO_PLUGIN_URL' ) )
	define( 'DEDO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if( !defined( 'DEDO_PLUGIN_DIR' ) )
	define( 'DEDO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Localization
 */
function dedo_localization() {
	load_plugin_textdomain( 'delightful-downloads', false, DEDO_PLUGIN_DIR . 'languages/' );	
}
add_action( 'plugins_loaded', 'dedo_localization' );

/**
 * Options
 */
global $dedo_options, $dedo_default_options;

$dedo_default_options = array(
 	'members_only'		=> 0,
	'members_redirect'	=> 0,
	'enable_css'		=> 1,
	'cache_duration'	=> 10,
	'default_text'		=> __( 'Download', 'delightful-downloads' ),
	'default_style'		=> 'button',
	'default_color'		=> 'blue',
	'reset_settings'	=> 0 
);

$dedo_options = wp_parse_args( get_option( 'delightful-downloads' ), $dedo_default_options );

/**
 * Include required plugin files
 */
include_once( DEDO_PLUGIN_DIR . 'includes/functions.php' );
include_once( DEDO_PLUGIN_DIR . 'includes/post-types.php' );
include_once( DEDO_PLUGIN_DIR . 'includes/process-download.php' );
include_once( DEDO_PLUGIN_DIR . 'includes/scripts.php' );
include_once( DEDO_PLUGIN_DIR . 'includes/shortcodes.php' );
if( is_admin() ) {
	include_once( DEDO_PLUGIN_DIR . 'includes/admin/ajax.php' );
	include_once( DEDO_PLUGIN_DIR . 'includes/admin/dashboard.php' );
	include_once( DEDO_PLUGIN_DIR . 'includes/admin/media-button.php' );
	include_once( DEDO_PLUGIN_DIR . 'includes/admin/meta-boxes.php' );
	include_once( DEDO_PLUGIN_DIR . 'includes/admin/page-settings.php' );
	include_once( DEDO_PLUGIN_DIR . 'includes/admin/post-types-columns.php' );
}

/**
 * On activation
 */
function dedo_activation() {
	global $dedo_options, $dedo_default_options;
	
	if( $dedo_options['reset_settings'] ) {
		update_option( 'delightful-downloads', $dedo_default_options );
	}
	else {
		add_option( 'delightful-downloads', $dedo_default_options );
	}
	
}
register_activation_hook( __FILE__, 'dedo_activation' );