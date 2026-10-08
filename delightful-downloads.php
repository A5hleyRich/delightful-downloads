<?php
/*
Plugin Name: Delightful Downloads
Plugin URI: https://github.com/svenbolte/delightful-downloads/
Author URI: https://github.com/svenbolte/
Author: Ashley Rich und PBMod
Description: A downloads manager for WordPress with protected files, download statistics and signed 7-day and 365-day download tickets.
Text Domain: delightful-downloads
Domain Path: /languages
License: GPL2
Version: 9.13.25
Stable tag: 9.13.25
Requires at least: 6.0
Tested up to: 7.0.2
Requires PHP: 8.4
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {	exit; }

// Load plugin textdomain.
add_action('init', function () {
    load_plugin_textdomain( 'delightful-downloads', false, dirname(plugin_basename(__FILE__)) . '/languages' );
});

//  Delightful Downloads class @package  Delightful Downloads
class Delightful_Downloads {
	// Instance of this class.
	private static $instance;
	public $path;
	public $version;
	// Protected constructor to prevent creating a new instance of the class via the `new` operator from outside of this class.
	protected function __construct() {}
	// As this class is a singleton it should not be clone-able
	protected function __clone() {}
	// As this class is a singleton it should not be able to be unserialized
	public function __wakeup(): void {}

	/**
	 * Return an instance of this class.
	 */
	public static function get_instance( $path, $version ) {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Delightful_Downloads();
			// Initialize the class
			self::$instance->init( $path, $version );
		}
		return self::$instance;
	}

	/**
	 * Initialize the class.
	 * @param string $path
	 * @param string $version
	 */
	protected function init( $path, $version ) {
		$this->path    = $path;
		$this->version = $version;
		self::$instance->constants();
		self::$instance->includes();

		// Defer options (uses translations) until init to avoid WP 6.7+ JIT warnings
		add_action( 'init', array( $this, 'late_init' ), 1 );

		// Register activation/deactivation hooks
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		// Plugin row links
		add_filter( 'plugin_action_links', array( $this, 'plugin_links' ), 10, 2 );
	}

	/**
	 * Late initialization: run after init so translations can load safely.
	 */
	public function late_init() {
		$this->options();
	}

	/**
	 * Include all the classes used by the plugin
	 */
	protected function includes() {
		require_once dirname( $this->path ) . '/includes/core.php';
		require_once dirname( $this->path ) . '/includes/folder-downloads.php';

		if ( is_admin() ) {
			require_once dirname( $this->path ) . '/includes/admin.php';
		}
	}

	/**
	 * Setup class constants
	 */
	protected function constants() {
		if ( ! defined( 'DEDO_VERSION' ) ) {
			define( 'DEDO_VERSION', $this->version );
		}
		if ( ! defined( 'DEDO_PLUGIN_URL' ) ) {
			define( 'DEDO_PLUGIN_URL', plugin_dir_url( $this->path ) );
		}
		if ( ! defined( 'DEDO_PLUGIN_DIR' ) ) {
			define( 'DEDO_PLUGIN_DIR', plugin_dir_path( $this->path ) );
		}
	}


	/**
	 * Options
	 */
	protected function options() {
		global $dedo_options, $dedo_default_options;

		// Set globals
		$dedo_default_options = dedo_get_default_options();
		$dedo_options         = wp_parse_args( get_option( 'delightful-downloads' ), $dedo_default_options );
	}

	/**
	 * Plugin Links.
	 * Add links below Delightful Downloads on the plugin screen.
	 * @param string $links
	 * @param string $file
	 * @return string
	 */
	public function plugin_links( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			$plugin_links[] = '<a href="' . esc_url( admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings' ) ) . '">' . esc_html__( 'Settings', 'delightful-downloads' ) . '</a>';

			foreach ( $plugin_links as $plugin_link ) {
				array_unshift( $links, $plugin_link );
			}
		}

		return $links;
	}

	/**
	 * Activate plugin
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
	 */
	public function deactivate() {
		// Clear dedo transients
		dedo_delete_all_transients();
	}

}

/**
 * Delightful Downloads
 * @return Delightful_Downloads
 */
function Delightful_Downloads() {
	$version = '9.13.25';
	return Delightful_Downloads::get_instance( __FILE__, $version );
}
Delightful_Downloads();
