<?php
/*
Plugin Name: Delightful Downloads
Plugin URI: http://delightfulwp.com/delightful-downloads/
Description: A super-awesome downloads manager for WordPress.
Version: 1.5.2
Author: Delightful WP
Author URI: http://delightfulwp.com
License: GPL2

Copyright 2013  Delightful WP  (email : hello@delightfulwp.com)

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

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Delightful Downloads
 *
 * @package  Delightful Downloads
 * @since    1.3.2
*/
class Delightful_Downloads {

	/**
	 * Instance of this class.
	 *
	 * @since  1.3.2
	 */
	private static $instance = null;

	/**
	 * Initialize the plugin.
	 *
	 * @since  1.3.2
	 */
	private function __construct() {

		// Setup plugin constants
		self::setup_constants();

		// Load plugin text domain
		self::load_plugin_textdomain();

		// Setup plugin options
		self::setup_options();

		// Include plugin files
		self::includes();

		// Register activation/deactivation hooks
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Register misc hooks
		add_filter( 'plugin_action_links', array( $this, 'plugin_links' ), 10, 2 );

	}

	/**
	 * Return an instance of this class.
	 *
	 * @since  1.3.2
	 */
	public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;

	}

	/**
	 * Setup plugin constants.
	 *
	 * @since  1.3.2
	 */
	private function setup_constants() {

		if( !defined( 'DEDO_VERSION' ) ) {
			define( 'DEDO_VERSION', '1.5.2' );
		}

		if( !defined( 'DEDO_PLUGIN_URL' ) ) {
			define( 'DEDO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		if( !defined( 'DEDO_PLUGIN_DIR' ) ) {
			define( 'DEDO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );			
		}

	}

	/**
     * Load the plugin text domain.
     *
     * @since  1.3.2
     */
    private function load_plugin_textdomain() {

		load_textdomain( 'delightful-downloads', WP_LANG_DIR . '/delightful-downloads/delightful-downloads-' . get_locale() . '.mo' );
        load_plugin_textdomain( 'delightful-downloads', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );        

    }

	/**
	 * Setup plugin options.
	 *
	 * @since  1.3.2
	 */
	private function setup_options() {

		global $dedo_options, $dedo_default_options;

		// Include options file.
		include_once( DEDO_PLUGIN_DIR . 'includes/options.php' );

		// Set globals
		$dedo_default_options = dedo_get_default_options();
		$dedo_options = wp_parse_args( get_option( 'delightful-downloads' ), $dedo_default_options );

	}    

	/**
	 * Include plugin files.
	 *
	 * @since  1.3.2
	 */
	private function includes() {

		include_once( DEDO_PLUGIN_DIR . 'includes/class-dedo-cache.php' );
		include_once( DEDO_PLUGIN_DIR . 'includes/class-dedo-logging.php' );
		include_once( DEDO_PLUGIN_DIR . 'includes/class-dedo-statistics.php' );
		include_once( DEDO_PLUGIN_DIR . 'includes/cron.php' );
		include_once( DEDO_PLUGIN_DIR . 'includes/functions.php' );
		include_once( DEDO_PLUGIN_DIR . 'includes/mime-types.php' );
		include_once( DEDO_PLUGIN_DIR . 'includes/post-types.php' );
		include_once( DEDO_PLUGIN_DIR . 'includes/process-download.php' );
		include_once( DEDO_PLUGIN_DIR . 'includes/scripts.php' );
		include_once( DEDO_PLUGIN_DIR . 'includes/shortcodes.php' );
		include_once( DEDO_PLUGIN_DIR . 'includes/taxonomies.php' );
		
		if ( is_admin() ) {
			include_once( DEDO_PLUGIN_DIR . 'includes/admin/ajax.php' );
			include_once( DEDO_PLUGIN_DIR . 'includes/admin/class-dedo-list-table.php' );
			include_once( DEDO_PLUGIN_DIR . 'includes/admin/class-dedo-notices.php' );
			include_once( DEDO_PLUGIN_DIR . 'includes/admin/dashboard.php' );
			include_once( DEDO_PLUGIN_DIR . 'includes/admin/media-button.php' );
			include_once( DEDO_PLUGIN_DIR . 'includes/admin/meta-boxes.php' );
			include_once( DEDO_PLUGIN_DIR . 'includes/admin/page-settings.php' );
			include_once( DEDO_PLUGIN_DIR . 'includes/admin/page-statistics.php' );
			include_once( DEDO_PLUGIN_DIR . 'includes/admin/upgrades.php' );
		}

	}

	/**
	 * Activate plugin
	 *
	 * @since  1.3.2
	 */
	public function activate() {

		global $dedo_default_options, $dedo_statistics;
	
		// Install database table
		$dedo_statistics->setup_table();

		// Add version to database
		update_option( 'delightful-downloads-version', DEDO_VERSION );

		// Add default options to database
		update_option( 'delightful-downloads', $dedo_default_options );

		// Add option for admin notices
		update_option( 'delightful-downloads-notices', array() );

		// Run folder protection
		dedo_folder_protection();
		
	}

	/**
	 * Deactivate plugin
	 *
	 * @since  1.3.2
	 */
	public function deactivate() {

		// Clear dedo transients
		dedo_delete_all_transients();
	}

	/**
	 * Plugin Links.
	 *
	 * Add links below Delightful Downloads on the plugin screen.
	 *
	 * @since  1.3.2
	 */
	public function plugin_links( $links, $file ) {

		if ( $file == plugin_basename( __FILE__ ) ) {
			$plugin_links[] = '<a href="' . admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings' ) . '">' . __( 'Settings', 'delightful-downloads' ) . '</a>';
		
			foreach ( $plugin_links as $plugin_link ) {
				array_unshift( $links, $plugin_link );
			}
		}

		return $links;

	} 

}

$delightful_downloads = Delightful_Downloads::get_instance();