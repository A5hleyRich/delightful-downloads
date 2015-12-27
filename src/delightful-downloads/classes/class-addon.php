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

		// Load updater
		add_action( 'admin_init', array( $this, 'load_updater' ) );
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