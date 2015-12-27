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
	 *
	 * @return Delightful_Downloads_Customizer
	 */
	public static function get_instance( $path, $version, $class ) {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new $class();

			// Initialize the class
			self::$instance->init( $path, $version, $class );
		}

		return self::$instance;
	}

	/**
	 * Initialize the class.
	 *
	 * @param string $path
	 * @param string $version
	 * @param string $class
	 */
	protected function init( $path, $version, $class ) {
		$this->path    = plugin_dir_path( $path );
		$this->url     = plugin_dir_url( $path );
		$this->slug    = strtolower( str_replace( '_', '-', $class ) );
		$this->version = $version;

		self::$instance->textdomain();
		self::$instance->includes();
		self::$instance->loader();
		self::$instance->hooks();

		// Activation
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
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