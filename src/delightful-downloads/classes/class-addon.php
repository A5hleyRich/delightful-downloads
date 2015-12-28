<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Delightful_Downloads_Addon {
	/**
	 * Instance of this class.
	 *
	 * @since  1.3.2
	 */
	protected static $instance;

	/**
	 * @var EDD_SL_Plugin_Updater
	 */
	protected $updater;

	/**
	 * @var string
	 */
	public $file;

	/**
	 * @var string
	 */
	public $path;

	/**
	 * @var string
	 */
	public $url;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $slug;

	/**
	 * @var string
	 */
	public $version;

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * class via the `new` operator from outside of this class.
	 */
	protected function __construct() {
	}

	/**
	 * As this class is a singleton it should not be clone-able
	 */
	protected function __clone() {
	}

	/**
	 * As this class is a singleton it should not be able to be unserialized
	 */
	protected function __wakeup() {
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since  1.3.2
	 *
	 * @param string $path
	 * @param string $version
	 * @param string $class
	 * @param string $name
	 * @param string $slug
	 *
	 * @return Delightful_Downloads_Customizer
	 */
	public static function get_instance( $path, $version, $class, $name, $slug ) {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new $class();

			// Initialize the class
			self::$instance->init( $path, $version, $name, $slug );
		}

		return self::$instance;
	}

	/**
	 * Initialize the class.
	 *
	 * @param string $path
	 * @param string $version
	 * @param string $name
	 * @param string $slug
	 */
	protected function init( $path, $version, $name, $slug ) {
		$this->file    = $path;
		$this->path    = plugin_dir_path( $path );
		$this->url     = plugin_dir_url( $path );
		$this->name    = $name;
		$this->slug    = $slug;
		$this->version = $version;

		self::$instance->textdomain();
		self::$instance->includes();
		self::$instance->hooks();
	}

	/**
	 * Textdomain
	 */
	protected function textdomain() {
		$global_path = WP_LANG_DIR . sprintf( '/%1$s/%1$s-%2$s.mo', $this->slug, get_locale() );

		if ( file_exists( $global_path ) ) {
			load_textdomain( $this->slug, $global_path );
		} else {
			load_plugin_textdomain( $this->slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
	}

	/**
	 * Includes
	 */
	protected function includes() {
		require_once dirname( Delightful_Downloads()->path ) . '/classes/EDD_SL_Plugin_Updater.php';
	}

	/**
	 * Hooks
	 */
	protected function hooks() {
		// Activation
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// Actions
		add_action( 'admin_init', array( $this, 'load_updater' ) );

		// Filters
		add_filter( 'dedo_settings_options', array( $this, 'add_settings_option' ) );
		add_filter( 'dedo_validate_settings', array( $this, 'update_license' ) );
	}

	/**
	 * Load updater
	 */
	public function load_updater() {
		$this->updater = new EDD_SL_Plugin_Updater( DELIGHTFUL_DOWNLOADS_API, $this->file, array(
			'version' 	=> $this->version,
			'license' 	=> 'e48dd0ebc8ae88b79a9d7b7e0bb17243',
			'item_name' => $this->name,
			'author' 	=> 'Ashley Rich',
		) );
	}

	/**
	 * Add settings option
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function add_settings_option( $options ) {
		$options[ $this->get_option_key() ] = array(
			'name'    =>  $this->name . ' ' . __( 'License Key', 'delightful-downloads' ),
			'tab'     => 'licenses',
			'type'    => 'text',
			'default' => '',
			'class'   => $this,
		);

		return $options;
	}

	/**
	 * Update license
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	public function update_license( $input ) {
		if ( ! isset( $input[ $this->get_option_key() ] ) ) {
			// License not set
			return $input;
		}

		if ( '' !== trim( $input[ $this->get_option_key() ] ) ) {
			$response = $this->api_call( 'activate_license', $input[ $this->get_option_key() ] );

			if ( ! $response ) {
				global $dedo_notices;

				$dedo_notices->add( 'error', __( 'Your license could not be activated! Please check the license key and try again.', 'delightful-downloads' ) );
			}
		} else {
			// Deactivate site
			$this->api_call( 'deactivate_license', $this->get_option( $this->get_option_key() ) );
			unset( $input[ $this->get_option_key() ] );
		}

		return $input;
	}

	/**
	 * API call
	 *
	 * @param string $action
	 * @param string $license
	 *
	 * @return bool
	 */
	protected function api_call( $action, $license ) {
		$args = array(
			'edd_action' => $action,
			'license' 	 => $license,
			'item_name'  => $this->name,
			'url'        => home_url(),
		);

		$response = wp_remote_post( DELIGHTFUL_DOWNLOADS_API, array( 'timeout' => 15, 'sslverify' => false, 'body' => $args ) );

		error_log( $action . ': ' . print_r( $response, true ) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 'valid' !== $data->license ) {
			return false;
		}

		return true;
	}

	/**
	 * Render license field
	 */
	public function render_license_field() {
		$name  = $this->get_option_key();
		$value = $this->get_option( $name );

		include dirname( Delightful_Downloads()->path ) . '/views/license-field.php';
	}

	/**
	 * Get option
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	protected function get_option( $key, $default = '' ) {
		global $dedo_options, $dedo_default_options;

		if ( isset( $dedo_options[ $key ] ) ) {
			return $dedo_options[ $key ];
		}

		if ( isset( $dedo_default_options[ $key ] ) ) {
			return $dedo_default_options[ $key ];
		}

		return $default;
	}

	/**
	 * Get option key
	 *
	 * @return string
	 */
	protected function get_option_key() {
		return $this->slug . '-license';
	}

	/**
	 * Render view
	 *
	 * @param string $view
	 * @param array  $args
	 */
	public function render_view( $view, $args = array() ) {
		extract( $args );
		include $this->path . 'views/' . $view . '.php';
	}

}