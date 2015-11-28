<?php
/**
 * Notices
 *
 * @package  	Delightful Downloads
 * @author   	Ashley Rich
 * @copyright   Copyright (c) 2014, Ashley Rich
 * @since    	1.4
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class DEDO_Notices {

	/**
	 * Notices
	 *
	 * @var array
	 * @access private
	 * @since 1.4
	 */
	private $notices = array();

	/**
	 * Init Notices
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function __construct() {

		// Get notices
		add_action( 'plugins_loaded', array( $this, 'get' ) );

		// Display notices
		add_action( 'admin_notices', array( $this, 'display' ) );
	}

	/**
	 * Get
	 *
	 * Get notices from option and unserialize.
	 *
	 * @access public
	 * @since 1.4
	 * @return array/boolean (array on success, false on failure)
	 */
	public function get() {

		$notices = get_option( 'delightful-downloads-notices' );

		if ( false !== $notices ) {

			$this->notices = $notices;
		}
	}

	/**
	 * Add
	 *
	 * Add a new notice to notice array.
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function add( $type, $message ) {

		$value = array(
			'type'		=> $type,
			'message'	=> $message
		);

		array_push( $this->notices, $value );

		update_option( 'delightful-downloads-notices', $this->notices );
	}

	/**
	 * Display
	 *
	 * Display admin notices.
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function display() {

		if ( !empty( $this->notices ) ) {
			
			foreach ( $this->notices as $key => $notice ) {
			
				// Display to user
				echo '<div class="notice ' . $notice['type'] . ' is-dismissible"><p>' . $notice['message'] . '</p></div>';

				// Remove from stored notices
				unset( $this->notices[$key] );
			}

			// Update option, option kept so as to auto load on each admin request
			update_option( 'delightful-downloads-notices', $this->notices );
		}
	}

}

// Initiate admin notices
$GLOBALS['dedo_notices'] = new DEDO_Notices();