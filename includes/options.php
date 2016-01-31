<?php
/**
 * Delightful Downloads Options
 *
 * @package     Delightful Downloads
 * @subpackage  Includes/Options
 * @since       1.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Registered Tabs
 *
 * @since  1.3
 */
function dedo_get_tabs() {
	$tabs = array(
		'general'    => __( 'General', 'delightful-downloads' ),
		'shortcodes' => __( 'Shortcodes', 'delightful-downloads' ),
		'statistics' => __( 'Statistics', 'delightful-downloads' ),
		'advanced'   => __( 'Advanced', 'delightful-downloads' ),
	);

	$options = dedo_get_options();

	foreach ( $options as $option ) {
		if ( 'licenses' === $option['tab'] ) {
			$tabs['licenses'] = __( 'Licenses', 'delightful-downloads' );
			break;
		}
	}

	$tabs['support'] = __( 'Support', 'delightful-downloads' );

	return apply_filters( 'dedo_settings_tabs', $tabs );
}

/**
 * Get Registered Options
 *
 * @since  1.3
 */
function dedo_get_options() {
	$options = array(
		'enable_taxonomies'   => array(
			'name'    => __( 'Categories and Tags', 'delightful-downloads' ),
			'tab'     => 'general',
			'type'    => 'radio',
			'default' => 1,
		),
		'members_only'        => array(
			'name'       => __( 'Members Only', 'delightful-downloads' ),
			'tab'        => 'general',
			'type'       => 'radio',
			'default'    => 0,
			'sub_option' => array(
				'redirect' => 0,
			),
		),
		'open_browser'        => array(
			'name'    => __( 'Open in Browser', 'delightful-downloads' ),
			'tab'     => 'general',
			'type'    => 'radio',
			'default' => 0,
		),
		'block_agents'        => array(
			'name'    => __( 'Block User Agents', 'delightful-downloads' ),
			'tab'     => 'general',
			'type'    => 'textarea',
			'default' => "Googlebot\nbingbot\nmsnbot\nyahoo! slurp\njeeves",
		),
		'default_text'        => array(
			'name'    => __( 'Default Text', 'delightful-downloads' ),
			'tab'     => 'shortcodes',
			'type'    => 'text',
			'default' => __( 'Download', 'delightful-downloads' ),
		),
		'default_style'       => array(
			'name'    => __( 'Default Style', 'delightful-downloads' ),
			'tab'     => 'shortcodes',
			'type'    => 'dropdown',
			'default' => 'button',
		),
		'default_button'      => array(
			'name'    => __( 'Default Button Style', 'delightful-downloads' ),
			'tab'     => 'shortcodes',
			'type'    => 'dropdown',
			'default' => 'blue',
		),
		'default_list'        => array(
			'name'    => __( 'Default List Style', 'delightful-downloads' ),
			'tab'     => 'shortcodes',
			'type'    => 'dropdown',
			'default' => 'title',
		),
		'log_admin_downloads' => array(
			'name'    => __( 'Admin Events', 'delightful-downloads' ),
			'tab'     => 'statistics',
			'type'    => 'radio',
			'default' => 1,
		),
		'grace_period'        => array(
			'name'       => __( 'Grace Period', 'delightful-downloads' ),
			'tab'        => 'statistics',
			'type'       => 'text',
			'default'    => 1,
			'sub_option' => array(
				'duration' => 3,
			),
		),
		'auto_delete'         => array(
			'name'       => __( 'Auto Delete', 'delightful-downloads' ),
			'tab'        => 'statistics',
			'type'       => 'text',
			'default'    => 0,
			'sub_option' => array(
				'duration' => 90,
			),
		),
		'enable_css'          => array(
			'name'    => __( 'Output CSS', 'delightful-downloads' ),
			'tab'     => 'advanced',
			'type'    => 'radio',
			'default' => 1,
		),
		'cache'               => array(
			'name'       => __( 'Cache', 'delightful-downloads' ),
			'tab'        => 'advanced',
			'type'       => 'text',
			'default'    => 1,
			'sub_option' => array(
				'duration' => 15,
			),
		),
		'download_url'        => array(
			'name'    => __( 'Download Address', 'delightful-downloads' ),
			'tab'     => 'advanced',
			'type'    => 'text',
			'default' => 'ddownload',
		),
		'upload_directory'    => array(
			'name'    => __( 'Upload Directory', 'delightful-downloads' ),
			'tab'     => 'advanced',
			'type'    => 'text',
			'default' => 'delightful-downloads',
		),
		'folder_protection'   => array(
			'name'    => __( 'Folder Protection', 'delightful-downloads' ),
			'tab'     => 'advanced',
			'type'    => 'radio',
			'default' => 1,
		),
		'uninstall'           => array(
			'name'    => __( 'Complete Uninstall', 'delightful-downloads' ),
			'tab'     => 'advanced',
			'type'    => 'radio',
			'default' => 0,
		),
	);

	return apply_filters( 'dedo_settings_options', $options );
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
		$default_options[ $key ] = $value['default'];

		// Add sub options
		if ( isset( $value['sub_option'] ) ) {
			foreach ( $value['sub_option'] as $key2 => $value2 ) {
				$default_options[ $key . '_' . $key2 ] = $value2;
			}
		}
	}

	return $default_options;
}