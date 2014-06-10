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
	 *	Get Hidden Columns
	 *
	 * @access public
	 * @since 1.4
	 * @return array
	 */
	public function get_hidden_columns() {

		return array();
	}

	/**
	 *	Get Sortable Columns
	 *
	 * @access public
	 * @since 1.4
	 * @return array
	 */
	public function get_sortable_columns() {

		// $sortable = array(
		// 	'download',
		// 	'user',
		// 	'ip_address',
		// 	'user_agent',
		// 	'date'
		// );

		// return $sortable;
	}

	/**
	 *	Prepare Items
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function prepare_items() {
		
		global $wpdb;
		
		// Setup columns
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		// Column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Get logs
		$sql = $wpdb->prepare( "
			SELECT * FROM $wpdb->ddownload_statistics 
			ORDER BY date DESC
		" );

		$this->items = $wpdb->get_results( $sql );

		// Count logs
		$sql = $wpdb->prepare( "
			SELECT COUNT(ID) FROM $wpdb->ddownload_statistics
			WHERE status = 'success'
		" );
		
		$total_logs = $wpdb->get_var( $sql );

		// Posts per page
		$per_page = 20;

		// Pagination
		$this->set_pagination_args( array(
			'total_items' => $total_logs,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_logs / $per_page )
		) );
	}

	/**
	 *	Column Default
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case 'download':
				
				return '<a href="' . get_edit_post_link( $item->post_id ) . '">' . get_the_title( $item->post_id ) . '</a>';
				break;

			case 'user':
				
				$user = get_user_by( 'id', $item->user_id );

				if ( false === $user ) {
					return __( 'Non-member', 'delightful-downloads' );
				}
				else {
					$output = '<a href="' . get_edit_user_link( $user->ID ) . '">' . $user->display_name . '</a>';
					$output .= '<br>' . $user->user_email;
					return $output;
				}
				break;

			case 'ip_address':
				
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

	public function no_items() {
		_e( 'No download logs found.', 'delightful-downloads' );
	}
}