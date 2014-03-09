<?php
/**
 * Delightful Downloads Options
 *
 * @package     Delightful Downloads
 * @subpackage  Includes/Options
 * @since       1.3
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get Registered Tabs
 *
 * @since  1.3
 */
function dedo_get_tabs() {
	$tabs = array(
		'general'		=>	__( 'General', 'delightful-downloads' ),
		'shortcodes'	=>	__( 'Shortcodes', 'delightful-downloads' ),
		'statistics'	=>	__( 'Statistics', 'delightful-downloads' ),
		'advanced'		=>	__( 'Advanced', 'delightful-downloads' )
	);

	return $tabs;
}

/**
 * Get Registered Options
 *
 * @since  1.3
 */
function dedo_get_options() {
	$options = array(
		'enable_taxonomies'	=> array(
			'name'		=> __( 'Categories and Tags', 'delightful-downloads' ),
			'tab'		=> 'general',
			'type'		=> 'check',
			'default'	=> 1
		),
		'members_only'		=> array(
			'name'		=> __( 'Members Download', 'delightful-downloads' ),
			'tab'		=> 'general',
			'type'		=> 'check',
			'default'	=> 0
		),
		'members_redirect'	=> array(
			'name'		=> __( 'Non-Members Redirect', 'delightful-downloads' ),
			'tab'		=> 'general',
			'type'		=> 'dropdown',
			'default'	=> 0
		),
		'block_agents'	=> array(
			'name'		=> __( 'Block User Agents', 'delightful-downloads' ),
			'tab'		=> 'general',
			'type'		=> 'textarea',
			'default'	=> "Googlebot\nbingbot\nmsnbot\nyahoo! slurp\njeeves"
		),
		'default_text'		=> array(
			'name'		=> __( 'Default Text', 'delightful-downloads' ),
			'tab'		=> 'shortcodes',
			'type'		=> 'text',
			'default'	=> __( 'Download', 'delightful-downloads' )
		),
		'default_style'		=> array(
			'name'		=> __( 'Default Style', 'delightful-downloads' ),
			'tab'		=> 'shortcodes',
			'type'		=> 'dropdown',
			'default'	=> 'button'
		),
		'default_button'	=> array(
			'name'		=> __( 'Default Button Style', 'delightful-downloads' ),
			'tab'		=> 'shortcodes',
			'type'		=> 'dropdown',
			'default'	=> 'blue'
		),
		'default_list'		=> array(
			'name'		=> __( 'Default List Style', 'delightful-downloads' ),
			'tab'		=> 'shortcodes',
			'type'		=> 'dropdown',
			'default'	=> 'title'
		),
		'log_admin_downloads'	=> array(
			'name'		=> __( 'Admin Downloads', 'delightful-downloads' ),
			'tab'		=> 'statistics',
			'type'		=> 'check',
			'default'	=> 0
		),
		'enable_css'		=> array(
			'name'		=> __( 'Default CSS Styles', 'delightful-downloads' ),
			'tab'		=> 'advanced',
			'type'		=> 'check',
			'default'	=> 1,
		),
		'cache_duration'	=> array(
			'name'		=> __( 'Cache Duration', 'delightful-downloads' ),
			'tab'		=> 'advanced',
			'type'		=> 'text',
			'default'	=> 10,
		),
		'download_url'		=> array(
			'name'		=> __( 'Download Address', 'delightful-downloads' ),
			'tab'		=> 'advanced',
			'type'		=> 'text',
			'default'	=> 'ddownload',
		),
		'uninstall'		=> array(
			'name'		=> __( 'Complete Uninstall', 'delightful-downloads' ),
			'tab'		=> 'advanced',
			'type'		=> 'check',
			'default'	=> '0',
		)
	);

	return $options;
}

/**
 * Get Default Options
 *
 * @since  1.3
 */
function dedo_get_default_options() {

	// Get registered settings
	$options = dedo_get_options();

	// Loop through and find default value
	foreach ( $options as $key => $value ) {
		$default_options[$key] = $value['default'];
	}

	return $default_options;
}