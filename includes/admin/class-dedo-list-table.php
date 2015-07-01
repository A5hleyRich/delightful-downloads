<?php
/**
 * Delightful Downloads Page Statistics
 *
 * @package     Delightful Downloads
 * @subpackage  Class/Delightful Downloads List Table
 * @since       1.4
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Check class exists
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class DEDO_List_Table extends WP_List_Table {

	/**
	 *	Init class.
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function __construct() {

		parent::__construct( array(
			'singular' => __( 'Log', 'delightful-downloads' ),  
			'plural'   => __( 'Logs', 'delightful-downloads' ), 
			'ajax'     => false
		) );

		$this->prepare_items();
	}

	/**
	 *	Get Columns
	 *
	 * @access public
	 * @since 1.4
	 * @return array
	 */
	public function get_columns() {
		
		$columns = array(
			'download'		=> __( 'Download', 'delightful-downloads' ),
			'user'			=> __( 'User', 'delightful-downloads' ),
			'ip_address'	=> __( 'IP Address', 'delightful-downloads' ),
			'user_agent'	=> __( 'User Agent', 'delightful-downloads' ),
			'dedo_date'		=> __( 'Date', 'delightful-downloads' ),
		);

		return $columns;
	}

	/**
	 *	Prepare Items
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function prepare_items() {
		
		global $wpdb, $dedo_statistics;

		// Column headers
		$this->_column_headers = array( $this->get_columns(), array(), array() );

		// Get the current user ID used to retrieve per_page from screen options
		$user = get_current_user_id();

		// Get the current admin screen
		$screen = get_current_screen();

		// Retrieve the "per_page" option
		$screen_option = $screen->get_option( 'per_page', 'option' );

		// Retrieve the value of the option stored for the current user
		$per_page = get_user_meta( $user, $screen_option, true );
		
		if ( empty ( $per_page) || $per_page < 1 ) {
			
			// Get the default value if none is set
			$per_page = $screen->get_option( 'per_page', 'default' );
		}
		
		// Get current page
		$current_page = $this->get_pagenum();

		// Count logs
		$total_logs = $dedo_statistics->count_logs( array( 'status' => 'success' ) );

		// Pagination
		$this->set_pagination_args( array(
			'total_items' => $total_logs,
			'per_page'    => $per_page
		) );

		// Get logs
		$sql = $wpdb->prepare( "
			SELECT * FROM $wpdb->ddownload_statistics 
			WHERE status = %s
			ORDER BY date DESC
			LIMIT %d OFFSET %d
		",
		'success', // WHERE status
		$per_page, // LIMIT
		( $current_page - 1 ) * $per_page ); // OFFSET

		$this->items = $wpdb->get_results( $sql );
	}

	/**
	 *	Column Default
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'download':
				$title = get_the_title( $item->post_id );

				if ( '' === $title ) {
					return __( 'Unknown', 'delightful-downloads' );
				} else {
					return '<a href="' . get_edit_post_link( $item->post_id ) . '">' . get_the_title( $item->post_id ) . '</a>';
				}
				break;
			case 'user':
				$user = get_user_by( 'id', $item->user_id );

				if ( false === $user ) {
					return __( 'Non-member', 'delightful-downloads' );
				} else {
					$output = '<a href="' . get_edit_user_link( $user->ID ) . '">' . $user->display_name . '</a>';
					$output .= '<br>' . $user->user_email;

					return $output;
				}
				break;
			case 'ip_address':
				if ( empty( $item->user_ip ) ) {
					return;
				}

				return inet_ntop( $item->user_ip );
				break;
			case 'user_agent':
				return esc_attr( $item->user_agent );
				break;
			case 'dedo_date':
				$output = human_time_diff( mysql2date( 'U', $item->date ), current_time( 'timestamp' ) ) . ' ago<br />';
				$output .= mysql2date( get_option( 'date_format' ), $item->date ) . ' at ' . mysql2date( get_option( 'time_format' ), $item->date );

				return $output;
				break;
		}
	}

	/**
	 *	No Items
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function no_items() {
		_e( 'No download logs found.', 'delightful-downloads' );
	}
}