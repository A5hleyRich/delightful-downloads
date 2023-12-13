<?php
/**
 * Cache Class
 *
 * @package  	Delightful Downloads
 * @author   	Ashley Rich
 * @copyright   Copyright (c) 2014, Ashley Rich
 * @since    	1.4
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class DEDO_Cache {

	/**
	 * Cache Enabled
	 *
	 * Is caching enabled on the settings screen?
	 *
	 * @var boolean
	 * @access private
	 * @since 1.4
	 */
	private $cache_enabled;

	/**
	 * Cache Duration
	 *
	 * How long are we caching data for?
	 *
	 * @var boolean
	 * @access private
	 * @since 1.4
	 */
	private $cache_duration;

	/**
	 * Cache Key
	 *
	 * Unique key for data.
	 *
	 * @var string
	 * @access private
	 * @since 1.4
	 */
	private $key;

	/**
	 * Cached
	 *
	 * Cached flag for data.
	 *
	 * @var boolean
	 * @access private
	 * @since 1.4
	 */
	private $cached = false;

	/**
	 * Init
	 *
	 * @since   1.4
	 *
	 * @return void
	 */
	public function __construct( $key ) {

		global $dedo_options;

		if ( $dedo_options['cache'] == 1 ) {
			$this->cache_enabled = true;
			$this->cache_duration = $dedo_options['cache_duration'] * 60;
			$this->key = $key;
		}
	}
	/**
	 * Get Cache
	 *
	 * Check for cached data in transients.
	 *
	 * @since   1.4
	 *
	 * @param string $sql Prepared SQL statement.
	 * @return mixed Mixed result or false on failure.
	 */
	public function get() {

		if ( true === $this->cache_enabled && false !== ( $data = get_transient( $this->key ) ) ) {
			// Set cached flag
			$this->cached = true;

			return $data;
		}

		return false;
	}

	/**
	 * Set Cache
	 *
	 * Cache data in transients.
	 *
	 * @since   1.4
	 *
	 * @param string $sql Prepared SQL statement.
	 * @return void
	 */
	public function set( $result ) {

		if ( true === $this->cache_enabled && false === $this->cached  ) {
			// Save transient
			set_transient( $this->key, $result, $this->cache_duration );
		}
	}

}