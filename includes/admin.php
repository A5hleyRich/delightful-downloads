<?php
/**
 * Delightful Downloads Admin Bundle
 * Merged for simpler maintenance.
 */

/* ===== BEGIN includes/admin/ajax.php ===== */
/**
 * Delightful Downloads Ajax
 *
 * @package     Delightful Downloads
 * @subpackage  Admin/Ajax
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Process Ajax upload file
 *
 * @since  1.0
 */
function dedo_download_upload_ajax() {
	
	if ( !check_ajax_referer( 'dedo_download_upload', false, false ) ) {

		// Echo error message
		die( '{ "jsonrpc" : "2.0", "error" : {"code": 500, "message": "' . __( 'Failed security checks!', 'delightful-downloads' ) . '" } }' );
	}

	// Set upload dir
	add_filter( 'upload_dir', 'dedo_set_upload_dir' );
	
	// Upload the file
	$file = wp_handle_upload( $_FILES['async-upload'], array( 'test_form'=> true, 'action' => 'dedo_download_upload' ) );
	
	// Check for success
	if ( isset( $file['url'] ) ) {
		// Post ID
		$post_id = $_REQUEST['post_id'];
	
		// Add/update post meta
		update_post_meta( $post_id, '_dedo_file_url', $file['url'] );
		update_post_meta( $post_id, '_dedo_file_size', $_FILES['async-upload']['size'] );
	
		// Echo success response
		die( '{"jsonrpc" : "2.0", "file" : {"url": "' . $file['url'] . '"}}' );
	}	
	else {
		// Echo error message
		die( '{"jsonrpc" : "2.0", "error" : {"code": 500, "message": "' . $file['error'] . '"}, "details" : "' . $file['error'] . '"}' );
	}
}
add_action( 'wp_ajax_dedo_download_upload', 'dedo_download_upload_ajax' );

/**
 * File browser Ajax endpoint
 *
 * @since  1.7
 */
function dedo_file_browser_ajax() {
	if ( ! check_ajax_referer( 'dedo_file_browser', 'nonce', false ) || ! current_user_can( apply_filters( 'dedo_cap_add_new', 'edit_pages' ) ) ) {
		wp_send_json_error( array( 'message' => __( 'Failed security check!', 'delightful-downloads' ) ), 403 );
	}

	$root = wp_normalize_path( trailingslashit( dedo_get_upload_dir( 'basedir' ) ) );
	$relative = isset( $_REQUEST['dir'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['dir'] ) ) : '';
	$relative = ltrim( wp_normalize_path( $relative ), '/' );
	$relative = str_replace( array( '../', '..\\' ), '', $relative );

	$current = wp_normalize_path( trailingslashit( $root . $relative ) );

	if ( 0 !== strpos( $current, $root ) || ! is_dir( $current ) || ! is_readable( $current ) ) {
		wp_send_json_error( array( 'message' => __( 'Directory not available.', 'delightful-downloads' ) ), 400 );
	}

	$directories = array();
	$files = array();
	$entries = @scandir( $current );

	if ( false === $entries ) {
		wp_send_json_error( array( 'message' => __( 'Directory could not be read.', 'delightful-downloads' ) ), 500 );
	}

	foreach ( $entries as $entry ) {
		if ( '.' === $entry || '..' === $entry ) {
			continue;
		}

		$full_path = wp_normalize_path( $current . $entry );
		if ( is_dir( $full_path ) ) {
			$directories[] = array(
				'name' => $entry,
				'path' => ltrim( $relative . $entry . '/', '/' ),
			);
			continue;
		}

		if ( is_file( $full_path ) ) {
			$files[] = array(
				'name' => $entry,
				'path' => ltrim( $relative . $entry, '/' ),
				'url'  => trailingslashit( dedo_get_upload_dir( 'baseurl' ) ) . ltrim( str_replace( '\\', '/', $relative . $entry ), '/' ),
			);
		}
	}

	usort( $directories, function( $a, $b ) {
		return strnatcasecmp( $a['name'], $b['name'] );
	} );
	usort( $files, function( $a, $b ) {
		return strnatcasecmp( $a['name'], $b['name'] );
	} );

	$parent = '';
	if ( '' !== $relative ) {
		$parent = dirname( untrailingslashit( $relative ) );
		$parent = '.' === $parent ? '' : trailingslashit( $parent );
	}

	wp_send_json_success( array(
		'current' => $relative,
		'parent' => $parent,
		'directories' => $directories,
		'files' => $files,
	) );
}
add_action( 'wp_ajax_dedo_file_browser', 'dedo_file_browser_ajax' );

/* ===== END includes/admin/ajax.php ===== */

/* ===== BEGIN includes/admin/class-dedo-list-table.php ===== */
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


	public function search_box( $text, $input_id ) { ?>
		<form method="GET"><p class="search-box">
		<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
		<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
 		<?php echo '<input type="hidden" name="post_type" value="' . esc_attr( $_REQUEST['post_type'] ) . '" />'; ?>
 		<?php echo '<input type="hidden" name="page" value="' . esc_attr( $_REQUEST['page'] ) . '" />'; ?>
  		<?php if ( ! empty( $_REQUEST['paged'] ) ) echo '<input type="hidden" name="paged" value="' . esc_attr( $_REQUEST['paged'] ) . '" />'; ?>
 		<?php if ( ! empty( $_REQUEST['orderby'] ) ) echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />'; ?>
 		<?php if ( ! empty( $_REQUEST['order'] ) ) echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />'; ?>
		<?php submit_button( __( 'Search Downloads', 'delightful-downloads' ), 'button', false, false, array('id' => 'search-submit') ); ?>
			</p></form>
	<?php }
	
	
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
	
	function get_sortable_columns() {
    $sortable_columns = array(
        'dedo_date'     => array('date',true),     //true means it's already sorted
        'download'     => array('post_id',false), 
        'user_id'    => array('user_id',false),
        'user_agent'  => array('user_agent',false),
    );
        return $sortable_columns;
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

		// get sortable columns
		$sortable = $this->get_sortable_columns();
		
		// Column headers
		$this->_column_headers = array( $this->get_columns(), array(), $sortable );

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

		// Get logs sorted
		  $orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'date'; //If no sort, default to title
		  $order = (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'desc'; //If no order, default to asc

		// Search
		if( ! empty( $_REQUEST['s'] ) ){
	        $search = esc_sql( $_REQUEST['s'] );
    	    $sqlsearch .= " AND user_agent LIKE '%{$search}%'";
    	} else $sqlsearch='';
		// search box
		$this->search_box('Search', 'search');
		
		$sql = $wpdb->prepare( "
			SELECT * FROM $wpdb->ddownload_statistics 
			WHERE status = %s ".$sqlsearch." 
			ORDER BY $orderby $order 
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
					return '<a href="' . get_edit_post_link( $item->post_id ) . '">' . get_the_title( $item->post_id ) . '</a> #' . $item->post_id;
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
				if ( empty( $item->user_ip ) ) return;
				$ip = inet_ntop( $item->user_ip );
				if ( false === $ip ) return '';

				$flagge = '';
				// Direkte Chartscodes-Helper-API; keine IPFlag-Klasse und kein Shortcode-Fallback.
				if ( function_exists( 'pb_chartscodes_flag_for_ip' ) ) {
					$flagge = pb_chartscodes_flag_for_ip( $ip, true );
				}

				return esc_html( $ip ) . ( $flagge !== '' ? '<br>' . $flagge : '' );
				break;
			case 'user_agent':
				return esc_attr( $item->user_agent );
				break;
			case 'dedo_date':
				$output = dd_shared_ago( mysql2date( 'U', $item->date ) ) . '<br>';
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

/* ===== END includes/admin/class-dedo-list-table.php ===== */

/* ===== BEGIN includes/admin/class-dedo-notices.php ===== */
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

/* ===== END includes/admin/class-dedo-notices.php ===== */

/* ===== BEGIN includes/admin/dashboard.php ===== */
/**
 * Delightful Downloads Dashboard
 *
 * @package     Delightful Downloads
 * @subpackage  Admin/Dashboard
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register dashboard widgets
 *
 * @since  1.0
 */
function dedo_register_dashboard_widgets() {
	
	if ( current_user_can( apply_filters( 'dedo_cap_dashboard', 'edit_pages' ) ) ) {
		wp_add_dashboard_widget( 'dedo_dashboard_downloads', __( 'Download Statistics', 'delightful-downloads' ), 'dedo_dashboard_downloads_widget' );
	}
}
add_action( 'wp_dashboard_setup', 'dedo_register_dashboard_widgets' );

/**
 * Downloads Dashboard Widget
 *
 * @since  1.0
*/
function dedo_dashboard_downloads_widget() {
	global $dedo_statistics,$wp;
	// Supply options for popular downloads dropdown
	wp_localize_script( 'dedo-admin-js-global', 'DEDODashboardOptions', array(
		'ajaxURL'		=> admin_url( 'admin-ajax.php', isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://' ),
		'nonce'			=> wp_create_nonce( 'dedo_dashboard' ),
		'errorText'		=> __( 'Download statistics could not be retrieved.', 'delightful-downloads' ),
		'noResultsText'	=> __( 'There are no popular downloads yet!', 'delightful-downloads' )
	) );
	// get totals of onedaypass downloads
	global $wpdb;
	$totalcost = $wpdb->get_col("SELECT SUM(pm.meta_value) FROM {$wpdb->postmeta} pm
                             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                             WHERE pm.meta_key = '_dedo_oneday_count' 
                             AND p.post_status = 'publish' 
                             AND p.post_type = 'dedo_download'");
	// Get count
	$totalfiles = wp_count_posts( 'dedo_download' )->publish;
	$filesize = dedo_get_filesize();
	?>
	<div id="ddownload-count">
		<ul>
			<li id="ddownload-count-1" style="opacity: 0">
				<span class="count">0</span>
				<span class="label"><?php _e( 'Last 24 Hours', 'delightful-downloads' ); ?></span>
			</li>
			<li id="ddownload-count-7" style="opacity: 0">
				<span class="count">0</span>
				<span class="label"><?php _e( 'Last 7 Days', 'delightful-downloads' ); ?></span>
			</li>
			<li id="ddownload-count-30" style="opacity: 0">
				<span class="count">0</span>
				<span class="label"><?php _e( 'Last 30 Days', 'delightful-downloads' ); ?></span>
			</li>
			<li id="ddownload-count-0" style="opacity: 0">
				<span class="count">0</span>
				<span class="label"><?php _e( 'All Time', 'delightful-downloads' ); ?></span>
			</li>
			<li id="ddownload-oneday" style="opacity: 0">
				<span class="count"><?php echo $totalcost[0]; ?></span>
				<span class="label"><?php _e( 'Onedaypass Downloads', 'delightful-downloads' ); ?></span>
			</li>
			<li id="ddownload-oneday" style="opacity: 0">
				<span class="count"><?php echo size_format( $filesize, 1 ); ?></span>
				<span class="label"><?php _e( 'total size', 'delightful-downloads' ); ?></span>
			</li>
			<li id="ddownload-oneday" style="opacity: 0">
				<span class="count"><?php echo $totalfiles; ?></span>
				<span class="label"><?php _e( 'total files', 'delightful-downloads' ); ?></span>
			</li>
		</ul>
	</div>
	<div id="ddownload-popular">
		<h4><?php _e( 'Popular Downloads', 'delightful-downloads' ); ?></h4>
		<?php
		$popular_downloads = $dedo_statistics->get_popular_downloads( array( 'limit' => 10, 'cache' => false ) );
		if ( !empty( $popular_downloads ) ) {
			echo '<ol id="popular-downloads">';
			foreach ( $popular_downloads as $key => $value ) {
				echo '<li>';
				echo '<a href="' . get_edit_post_link( $value['ID'] ) . '"><span class="position">' . ( $key + 1 ) . '.</span>' . $value['title'] . ' <span class="count">' . number_format_i18n( $value['downloads'] ) . '</span></a>';
				echo '</li>';
			}
			echo '</ol>';
		}
		else {
			echo '<p>' . __( 'There are no popular downloads yet!', 'delightful-downloads' ) . '</p>';
		}
		?>
		<div class="sub">
			<select id="popular-downloads-dropdown">
				<option value="1"><?php _e( 'Last 24 Hours', 'delightful-downloads' ); ?></option>
				<option value="7"><?php _e( 'Last 7 Days', 'delightful-downloads' ); ?></option>
				<option value="30"><?php _e( 'Last 30 Days', 'delightful-downloads' ); ?></option>
				<option value="0" selected="selected"><?php _e( 'All Time', 'delightful-downloads' ); ?></option>
			</select>
			<span class="spinner"></span>
			<p class="error" style="display: none"></p>
		</div>
	</div>
	<?php
}

/**
 * Count Downloads Ajax
 *
 * @since  1.4
*/
function dedo_count_downloads_ajax() {

	global $dedo_statistics;

	// Check for nonce and permission
	if ( !check_ajax_referer( 'dedo_dashboard', 'nonce', false ) || !current_user_can( apply_filters( 'dedo_cap_dashboard', 'edit_pages' ) ) ) {
		echo json_encode( array(
			'status'	=> 'error',
			'content'	=> __( 'Failed security check!', 'delightful-downloads' )
		) );
		die();
	}

	// Get counts
	$result = array(
		'ddownload-count-1' 	=> number_format_i18n( $dedo_statistics->count_downloads( array( 'days' => 1, 'cache' => false ) ) ),
		'ddownload-count-7' 	=> number_format_i18n( $dedo_statistics->count_downloads( array( 'days' => 7, 'cache' => false ) ) ),
		'ddownload-count-30'	=> number_format_i18n( $dedo_statistics->count_downloads( array( 'days' => 30, 'cache' => false ) ) ),
		'ddownload-count-0'		=> number_format_i18n( $dedo_statistics->count_downloads( array( 'days' => 0, 'cache' => false ) ) )
	);

	// Return success and data
	echo json_encode( array (
		'status'	=> 'success',
		'content'	=> $result
	) );

	die();
}
add_action( 'wp_ajax_dedo_count_downloads', 'dedo_count_downloads_ajax' );

/**
 * Popular Downloads Ajax
 *
 * @since  1.4
*/
function dedo_popular_downloads_ajax() {

	global $dedo_statistics;

	// Check for nonce and permission
	if ( !check_ajax_referer( 'dedo_dashboard', 'nonce', false ) || !current_user_can( apply_filters( 'dedo_cap_dashboard', 'edit_pages' ) ) ) {
		
		echo json_encode( array(
			'status'	=> 'error',
			'content'	=> __( 'Failed security check!', 'delightful-downloads' )
		) );

		die();
	}

	// Get days from request
	$days = absint( $_REQUEST['days'] );

	// Get popular downloads
	$result = $dedo_statistics->get_popular_downloads( array( 'days' => $days, 'limit' => 5, 'cache' => false ) );

	// Add download URL to array of results
	foreach ( $result as $key => $value ) {

		$result[$key]['url'] = ( !empty( $result[$key]['title'] ) ) ? get_edit_post_link( $value['ID'] ) : admin_url( 'edit.php?post_type=dedo_download' );
		$result[$key]['title'] = ( !empty( $result[$key]['title'] ) ) ? $result[$key]['title'] : __( 'Unknown', 'delightful-downloads' );
		$result[$key]['downloads'] = number_format_i18n( $value['downloads'] );
	}

	// Return success and data
	echo json_encode( array (
		'status'	=> 'success',
		'content'	=> $result
	) );

	die();
}
add_action( 'wp_ajax_dedo_popular_downloads', 'dedo_popular_downloads_ajax' );

/* ===== END includes/admin/dashboard.php ===== */

/* ===== BEGIN includes/admin/media-button.php ===== */
/**
 * Delightful Downloads Media Button
 *
 * @package     Delightful Downloads
 * @subpackage  Admin/Media Button
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Display Media Button
 * @since  1.0
 */
function dedo_media_button( $context ) {
	global $pagenow;

	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) { 
		printf( '<a href="#dedo-shortcode-modal" id="dedo-media-button" class="button dedo-modal-action add-download" data-editor="content" title="Add Download">' . '<span class="wp-media-buttons-icon dashicons dashicons-art"></span> Add Download' . '</a>' );

		// $context .= '<span class="wp-media-buttons-icon"></span></a>';	
	}

	return $context;
}
add_action( 'media_buttons', 'dedo_media_button' );

/**
 * Add Modal Window to Footer
 *
 * @since  1.0
 */
function dedo_media_modal() {
	global $pagenow;

	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) { 
		
		// Get published downloads
		$downloads = get_posts( array(
			'post_type'		=> 'dedo_download',
			'post_status'	=> 'publish',
			'orderby'		=> 'title',
			'order'			=> 'ASC',
			'posts_per_page'=> -1	
		) );

		// Get registered styles
		$styles = dedo_get_shortcode_styles();
		// Get registered buttons
		$buttons = dedo_get_shortcode_buttons();
		?>
			<div id="dedo-shortcode-modal" class="dedo-modal" style="display: none; width: 30%; left: 50%; margin-left: -15%;">
				<a href="#" class="dedo-modal-close" title="<?php _e( 'Close', 'delightful-downloads' ); ?>"><span class="media-modal-icon"></span></a>
				<div class="dedo-modal-content">
					<h1><?php _e( 'Insert Download', 'delightful-downloads' ); ?></h1>
							
					<?php if ( $downloads ) : ?>
						<p>
							<label><span><?php _e( 'Download', 'delightful-downloads' ); ?></span>
								<select id="dedo-select-download-dropdown">
									<?php foreach ( $downloads as $download ) : ?>
										<option value="<?php echo $download->ID; ?>"><?php echo $download->post_title; ?></option>
									<?php endforeach; ?>
								</select>
							</label>
						</p>
						<p class="clear">
							<label id="dedo-style-dropdown-container" class="column-2"><span><?php _e( 'Style', 'delightful-downloads' ); ?></span>
								<select id="dedo-select-style-dropdown">
									<optgroup label="<?php _e( 'Global', 'delightful-downloads' ); ?>">
										<option value=""><?php _e( 'Inherit', 'delightful-downloads' ); ?></option>
									</optgroup>
									<optgroup label="<?php _e( 'Styles', 'delightful-downloads' ); ?>">
										<?php foreach ( $styles as $key => $value ) : ?>
											<option value="<?php echo $key; ?>"><?php echo $value['name']; ?></option>
										<?php endforeach; ?>
									</optgroup>
								</select>
							</label>

							<label id="dedo-button-dropdown-container" class="column-2"><span><?php _e( 'Button', 'delightful-downloads' ); ?></span>
								<select id="dedo-select-button-dropdown">
									<optgroup label="<?php _e( 'Global', 'delightful-downloads' ); ?>">
										<option value=""><?php _e( 'Inherit', 'delightful-downloads' ); ?></option>
									</optgroup>
									<optgroup label="<?php _e( 'Buttons', 'delightful-downloads' ); ?>">
										<?php foreach ( $buttons as $key => $value ) : ?>
											<option value="<?php echo $key; ?>"><?php echo $value['name']; ?></option>
										<?php endforeach; ?>
									</optgroup>
								</select>
							</label>
						</p>
						<p>
							<label><span><?php _e( 'Text', 'delightful-downloads' ); ?></span>	
								<input id="dedo-custom-text" type="text" placeholder="<?php _e( 'Inherit', 'delightful-downloads' ); ?>" />
							</label>
						</p>
						<p class="buttons clear">
							<a href="#" id="dedo-insert" class="button button-large button-primary"><?php _e( 'Insert', 'delightful-downloads' ); ?></a>
							<a href="#" id="dedo-file-size" class="button button-large right"><?php _e( 'File Size', 'delightful-downloads' ); ?></a>
							<a href="#" id="dedo-download-count" class="button button-large right"><?php _e( 'Download Count', 'delightful-downloads' ); ?></a>
						</p>
					<?php else: ?>
						<p><?php echo sprintf( __( 'Please %sadd%s a new download.', 'delightful-downloads' ), '<a href="' . admin_url( 'post-new.php?post_type=dedo_download' ) . '" target="_blank">', '</a>' ); ?></p>
					<?php endif; ?>

				</div>
			</div>

		<?php 
	}
}
add_action( 'admin_footer', 'dedo_media_modal', 100 );

/* ===== END includes/admin/media-button.php ===== */

/* ===== BEGIN includes/admin/meta-boxes.php ===== */
/**
 * Delightful Downloads Metaboxes
 *
 * @package     Delightful Downloads
 * @subpackage  Admin/Metaboxes
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Meta Boxes
 *
 * @since  1.0
 */
function dedo_register_meta_boxes() {
	add_meta_box( 'dedo_download', __( 'Download', 'delightful-downloads' ), 'dedo_meta_box_download', 'dedo_download', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'dedo_register_meta_boxes' );

/**
 * Add Correct Enc Type
 *
 * @since  1.0
 */
function dedo_form_enctype() {
	if ( get_post_type() == 'dedo_download' ) {
		echo ' enctype="multipart/form-data"';
	}
}
add_action( 'post_edit_form_tag', 'dedo_form_enctype' );

/**
 * Add post type custom update messages
 *
 * @since  1.3.8
 */
function dedo_update_messages( $messages ) {
	global $post, $post_ID;

	$messages['dedo_download'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Download updated. Use the %s shortcode to include it in posts or pages.', 'delightful-downloads'), '<code>[ddownload id="' . $post_ID . '"]</code>' ),
		2 => __('Custom field updated.', 'delightful-downloads'),
		3 => __('Custom field deleted.', 'delightful-downloads'),
		4 => sprintf( __('Download updated. Use the %s shortcode to include it in posts or pages.', 'delightful-downloads'), '<code>[ddownload id="' . $post_ID . '"]</code>' ),
		5 => isset($_GET['revision']) ? sprintf( __('Download restored to revision from %s', 'delightful-downloads'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Download published. Use the %s shortcode to include it in posts or pages.', 'delightful-downloads'), '<code>[ddownload id="' . $post_ID . '"]</code>' ),
		7 => __('Download saved.', 'delightful-downloads'),
		8 => __('Download submitted.', 'delightful-downloads'),
		9 => sprintf( __('Download scheduled for: <strong>%1$s</strong>.', 'delightful-downloads'),
		  wp_date( __( 'M j, Y @ G:i', 'delightful-downloads' ), strtotime( $post->post_date ) ) ),
		10 => __('Download draft updated.', 'delightful-downloads'),
	);

	return $messages;
}
add_filter( 'post_updated_messages', 'dedo_update_messages' );

/**
 * Bulk messages
 *
 * @param array $bulk_messages
 * @param array $bulk_counts
 *
 * @return array
 */
function dedo_bulk_messages( $bulk_messages, $bulk_counts ) {
	$screen = get_current_screen();

	if ( 'dedo_download' !== $screen->post_type ) {
		return $bulk_messages;
	}

	$bulk_messages['post'] = array(
		'updated'   => _n( '%s download updated.', '%s downloads updated.', $bulk_counts['updated'], 'delightful-downloads-customizer' ),
		'locked'    => ( 1 == $bulk_counts['locked'] ) ? __( '1 download not updated, somebody is editing it.', 'delightful-downloads-customizer' ) : _n( '%s download not updated, somebody is editing it.', '%s downloads not updated, somebody is editing them.', $bulk_counts['locked'], 'delightful-downloads-customizer' ),
		'deleted'   => _n( '%s download permanently deleted.', '%s downloads permanently deleted.', $bulk_counts['deleted'], 'delightful-downloads-customizer' ),
		'trashed'   => _n( '%s download moved to the Trash.', '%s downloads moved to the Trash.', $bulk_counts['trashed'], 'delightful-downloads-customizer' ),
		'untrashed' => _n( '%s download restored from the Trash.', '%s downloads restored from the Trash.', $bulk_counts['untrashed'], 'delightful-downloads-customizer' ),
	);

	return $bulk_messages;
}
add_filter( 'bulk_post_updated_messages', 'dedo_bulk_messages', 10, 2 );

/**
 * Render Download Metabox
 *
 * @since  1.5
 */
function dedo_meta_box_download( $post ) {
	global $post;

	$file_url = get_post_meta( $post->ID, '_dedo_file_url', true );
	$file_url = ( false != $file_url ) ? $file_url : '';
	
	$file_size = get_post_meta( $post->ID, '_dedo_file_size', true );
	$file_size = ( false != $file_size ) ? size_format( $file_size, 1 ) : '';
	
	$file_count = get_post_meta( $post->ID, '_dedo_file_count', true );
	$file_count = ( false != $file_count ) ? $file_count : 0;

	$file_oneday = get_post_meta( $post->ID, '_dedo_oneday_count', true );
	$file_oneday = ( false != $file_oneday ) ? $file_oneday : 0;

	$file_options = get_post_meta( $post->ID, '_dedo_file_options', true );

	// Update status args
	$status_args = array(
		'ajaxURL'		=> admin_url( 'admin-ajax.php', isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://' ),
		'nonce' 		=> wp_create_nonce( 'dedo_download_update_status' ),
		'action'    	=> 'dedo_download_update_status',
		'default_icon'	=> dedo_get_file_icon( '_blank' ),
		'lang_local'	=> __( 'Local File', 'delightful-downloads' ),
		'lang_remote'	=> __( 'Remote File', 'delightful-downloads' ),
		'lang_warning'	=> __( 'Inaccessible File', 'delightful-downloads' )
	);

	// Plupload args
	$plupload_args = array(
		'runtimes'            => 'html5, silverlight, flash, html4',
		'browse_button'       => 'dedo-upload-button',
		'container'           => 'dedo-upload-container',
		'drop_element'		  => 'dedo-drag-drop-area',
		'file_data_name'      => 'async-upload',            
		'multiple_queues'     => false,
		'multi_selection'	  => false,
		'max_file_size'       => wp_max_upload_size() . 'b',
		'url'                 => admin_url( 'admin-ajax.php' ),
		'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
		'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
		'filters'             => array( array( 'title' => __( 'Allowed Files' ), 'extensions' => '*' ) ),
		'multipart'           => true,
		'urlstream_upload'    => true,

		// additional post data to send to our ajax hook
		'multipart_params'    => array(
			'_ajax_nonce' 		=> wp_create_nonce( 'dedo_download_upload' ),
			'action'      		=> 'dedo_download_upload',
			'post_id'			=> $post->ID
		)
	);

	// File browser args
	$file_browser_args = array(
		'ajaxURL'		=> admin_url( 'admin-ajax.php', is_ssl() ? 'https' : 'http' ),
		'nonce'		=> wp_create_nonce( 'dedo_file_browser' ),
		'action'		=> 'dedo_file_browser',
		'baseUrl'		=> trailingslashit( dedo_get_upload_dir( 'baseurl' ) ),
		'rootLabel'	=> __( 'Uploads', 'delightful-downloads' )
	);

	?>

	<script type="text/javascript">
		var updateStatusArgs = <?php echo json_encode( $status_args ); ?>;
	</script>
	
	<div id="dedo-new-download" style="<?php echo ( !isset( $file_url ) || empty( $file_url ) ) ? 'display: block;' : 'display: none;'; ?>">		
		<a href="#dedo-upload-modal" class="button dedo-modal-action"><?php _e( 'Upload File', 'delightful-downloads' ); ?></a>
		<a href="#dedo-select-modal" class="button dedo-modal-action select-existing"><?php _e( 'Existing File', 'delightful-downloads' ); ?></a>
	</div>
	<div id="dedo-existing-download" style="<?php echo ( isset( $file_url ) && !empty( $file_url ) ) ? 'display: block;' : 'display: none;'; ?>">		
		<div class="left-panel">
			<div class="file-icon">	
			<?php echo dedo_get_file_icon( $file_url ); ?>
			</div>
			<div class="file-name"><?php echo dedo_get_file_name( $file_url ); ?></div>
			<div class="file-size"><?php echo $file_size; ?></div>
			<div class="file-status">
				<span class="status spinner"></span>
			</div>
		</div>
		<div class="right-panel">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<?php _e( 'Download Count', 'delightful-downloads' ); ?>
						</th>
						<td>
							<input name="download_count" id="download_count" class="regular-text" type="number" min="0" value="<?php echo $file_count; ?>" />
							<?php  echo '&nbsp; <b>'.__( 'Oneday-Pass Count', 'delightful-downloads' ) .': </b>'. $file_oneday; ?>
							<p class="description"><?php _e( 'The number of times this file has been downloaded.', 'delightful-downloads' ); ?></p>
						</td>
					</tr>
					<?php $members_only = ( isset( $file_options['members_only'] ) ? $file_options['members_only'] : '' ); ?>
					<?php $members_only_redirect = ( isset( $file_options['members_only_redirect'] ) ? $file_options['members_only_redirect'] : '' ); ?>
					<tr>
						<th scope="row">
							<?php _e( 'Members Only', 'delightful-downloads' ); ?>
						</th>
						<td>
							<label for="members_only_true"><input name="members_only" id="members_only_true" type="radio" value="1" <?php echo ( 1 === $members_only ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
							<label for="members_only_false"><input name="members_only" id="members_only_false" type="radio" value="0" <?php echo ( 0 === $members_only ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
							<label for="members_only_inherit"><input name="members_only" id="members_only_inherit" type="radio" value <?php echo ( '' === $members_only ) ? 'checked' : ''; ?> /> <?php _e( 'Inherit', 'delightful-downloads' ); ?></label>
							<p class="description"><?php _e( 'Allow only logged in users to download this file.', 'delightful-downloads' ); ?></p>
							<div id="members_only_sub" class="dedo-sub-option" style="<?php echo ( 0 === $members_only ) ? 'display: none;' : ''; ?>">
								<?php 

								$args = array(
									'name'						=> 'members_only_redirect',
									'depth'						=> 0,
									'selected'					=> $members_only_redirect,
									'show_option_none'			=> __( 'Inherit', 'delightful-downloads' ),
									'option_none_value'			=> 	'',
									'echo'						=> 0
								);
								
								$list = wp_dropdown_pages( $args );

								// Add option groups
								$list = explode( '<option value="">' . __( 'Inherit', 'delightful-downloads' ) . '</option>', $list );
								$list = implode( '<optgroup label="' . __( 'Global', 'delightful-downloads' ) . '"><option value="">' . __( 'Inherit', 'delightful-downloads' ) . '</option></optgroup><optgroup label="' . __( 'Pages', 'delightful-downloads' ) . '">', $list );
								$list = explode( '</select>', $list );
								$list = implode( '</optgroup></select>', $list );

								echo $list; 
								?>

								<p class="description"><?php _e( 'The page to redirect non-members.', 'delightful-downloads' ); ?></p>
							</div>
						</td>
					</tr>
					<?php $open_browser = ( isset( $file_options['open_browser'] ) ? $file_options['open_browser'] : '' ); ?>
					<tr>
						<th scope="row">
							<?php _e( 'Open In Browser', 'delightful-downloads' ); ?>
						</th>
						<td>
							<label for="open_browser_true"><input name="open_browser" id="open_browser_true" type="radio" value="1" <?php echo ( 1 === $open_browser ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
							<label for="open_browser_false"><input name="open_browser" id="open_browser_false" type="radio" value="0" <?php echo ( 0 === $open_browser ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
							<label for="open_browser_inherit"><input name="open_browser" id="open_browser_inherit" type="radio" value <?php echo ( '' === $open_browser ) ? 'checked' : ''; ?> /> <?php _e( 'Inherit', 'delightful-downloads' ); ?></label>
							<p class="description"><?php echo sprintf( __( 'This file will attempt to open in the browser window. If the file is located within the Delightful Downloads upload directory, you will need to set the %sfolder protection%s setting to \'No\'.', 'delightful-downloads' ), '<a href="' . admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings&tab=advanced' ) . '" target="_blank">', '</a>' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="footer">
			<?php _e( 'Replace File:', 'delightful-downloads' ); ?>
			<a href="#dedo-upload-modal" class="button dedo-modal-action"><?php _e( 'Upload', 'delightful-downloads' ); ?></a>
			<a href="#dedo-select-modal" class="button dedo-modal-action select-existing"><?php _e( 'Select Existing', 'delightful-downloads' ); ?></a>
			<a href="#dedo-delete-modal" class="delete dedo-delete-file"><?php _e( 'Unlink file from post', 'delightful-downloads' ); ?></a>
		</div>
	</div>

	<script type="text/javascript">
		var pluploadArgs = <?php echo json_encode( $plupload_args ); ?>;
	</script>

	<div id="dedo-upload-modal" class="dedo-modal" style="display: none;top:65%;left:20%;width:60%;height:380px">
		<a href="#" class="dedo-modal-close" title="Close"><span class="media-modal-icon"></span></a>
		<div id="dedo-upload-container" class="dedo-modal-content">
			<h1><?php _e( 'Upload File', 'delightful-downloads' ); ?></h1>
			<div id="dedo-drag-drop-area-container">
				<div id="dedo-drag-drop-area">
					<p class="drag-drop-info"><?php _e( 'Drop file here', 'delightful-downloads' ); ?></p>
					<p><?php _e( 'or', 'delightful-downloads' ); ?></p>
					<p class="drag-drop-button"><input id="dedo-upload-button" type="button" value="<?php _e( 'Select File', 'delightful-downloads' ); ?>" class="button" />
					<div id="dedo-progress-percent" style="width: 0%;"></div>
					<div id="dedo-progress-text">0%</div>
				</div>
			</div>
			<p><?php printf( __( 'Maximum upload file size: %s.', 'delightful-downloads' ), size_format( wp_max_upload_size(), 1 ) ); ?></p>
			<div id="dedo-progress-error" style="display: none"></div>
		</div>
	</div>

	<script type="text/javascript">
		var fileBrowserArgs = <?php echo json_encode( $file_browser_args ); ?>;
	</script>

	<div id="dedo-select-modal" class="dedo-modal" style="display: none; width: 40%; left: 50%; margin-left: -20%;">
		<a href="#" class="dedo-modal-close" title="Close"><span class="media-modal-icon"></span></a>
		<div class="dedo-modal-content">
			<h1><?php _e( 'Existing File', 'delightful-downloads' ); ?></h1>
			<p><?php _e( 'Manually enter a file URL, or use the file browser.', 'delightful-downloads' ); ?></p>
			<p>	
				<?php wp_nonce_field( 'ddownload_file_save', 'ddownload_file_save_nonce' ); ?>
				<input name="dedo-file-url" id="dedo-file-url" type="text" class="large-text" value="<?php echo $file_url; ?>" placeholder="<?php _e( 'File URL or path...', 'delightful-downloads' ); ?>" />
			</p>
			<p>
				<div id="dedo-file-browser"><p><?php _e( 'Loading...', 'delightful-downloads' ); ?></p></div>
			</p>
			<p>
				<a href="#" id="dedo-select-done" class="button button-primary"><?php _e( 'Confirm', 'delightful-downloads' ); ?></a>
			</p>
		</div>
	</div>

	<?php
	
}

/**
 * Update Status Ajax
 *
 * @since  1.5
*/
function dedo_download_update_status_ajax() {

	global $dedo_statistics;

	// Check for nonce and permission
	if ( !check_ajax_referer( 'dedo_download_update_status', 'nonce', false ) || !current_user_can( apply_filters( 'dedo_cap_add_new', 'edit_pages' ) ) ) {
		echo json_encode( array(
			'status'	=> 'error',
			'content'	=> __( 'Failed security check!', 'delightful-downloads' )
		) );

		die();
	}

	$file_url = isset( $_REQUEST['url'] ) ? trim( wp_unslash( $_REQUEST['url'] ) ) : '';

	if ( '' === $file_url ) {
		echo json_encode( array(
			'status'  => 'error',
			'content' => array(
				'filename' => '--',
				'size'     => '--',
				'icon'     => '',
				'type'     => 'warning',
			),
		) );
		die();
	}

	if( $result = dedo_get_file_status( $file_url ) ) {
		// Cache remote file sizes, for 15 mins
		if ( 'remote' === $result['type'] ) {
			$cached_remotes = get_transient( 'dedo_remote_file_sizes' );

			if ( false === $cached_remotes || !isset( $cached_remotes[esc_url_raw( $file_url )] ) ) {
				$cached_remotes[esc_url_raw( $file_url )] = $result['size'];
				set_transient( 'dedo_remote_file_sizes', $cached_remotes, 900 );
			}
		}

		// Add extra data to result
		$result['size'] = size_format( $result['size'], 1 );
		$result['icon']	= dedo_get_file_icon( $file_url );
		$result['filename'] = dedo_get_file_name( $file_url );

		// Exists
		echo json_encode( array (
			'status'	=> 'success',
			'content'	=> $result
		) );
	}
	else {
		$result['filename'] = dedo_get_file_name( $file_url );

		echo json_encode( array (
			'status'	=> 'error',
			'content'	=> $result
		) );
	}

	die();
}
add_action( 'wp_ajax_dedo_download_update_status', 'dedo_download_update_status_ajax' );

/**
 * Save Meta Boxes
 *
 * @since  1.0
 */
function dedo_meta_boxes_save( $post_id ) {
	// Check for autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	
	// Check for other post types
	if ( isset( $post->post_type ) && $post->post_type != 'dedo_download' ) {
		return;
	}

	// Check user has permission
	if ( !current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	
	// Check for file nonce
	if ( isset( $_POST['ddownload_file_save_nonce'] ) && wp_verify_nonce( $_POST['ddownload_file_save_nonce'], 'ddownload_file_save' ) ) {	
		if ( isset( $_POST['dedo_source_type'] ) && 'folder' === sanitize_key( wp_unslash( $_POST['dedo_source_type'] ) ) ) return;

		$file_url = trim( $_POST['dedo-file-url'] );
		if (empty($file_url)) $file_url="none";

		/**
		 * Get cached remote file sizes
		 *
		 * Ajax grabs the remote file size on each file update, it makes sense to cache
		 * the value and use it here. Otherwise, the user has to wait for headers to return
		 * when saving a file.
		 */
		$cached_remotes = get_transient( 'dedo_remote_file_sizes' );
		
		// Check for cached remote file size
		if ( false === $cached_remotes || !isset( $cached_remotes[esc_url_raw( $file_url )] ) ) {
			$file = dedo_get_file_status( $file_url );
			$file_size = $file['size'];
		}
		else {
			$file_size = $cached_remotes[esc_url_raw( $file_url )];
		}

		// Save file url and size
		update_post_meta( $post_id, '_dedo_file_url', $file_url );
		update_post_meta( $post_id, '_dedo_file_size', $file_size );

		// Save download count
		if ( isset( $_POST['download_count'] ) && '' !== trim( $_POST['download_count'] ) ) {
			update_post_meta( $post_id, '_dedo_file_count', trim( $_POST['download_count'] ) );
		}

		// Get current file options
		$file_options = get_post_meta( $post_id, '_dedo_file_options', true );
		$file_options = ( false == $file_options ) ? array() : $file_options;

		// Set file options
		$default_options = array(
			'members_only',
			'members_only_redirect',
			'open_browser'
		);

		// Loop through and save to file array
		foreach ( $default_options as $option ) {
			if ( isset( $_POST[$option] ) && '' !== $_POST[$option] ) {
				$file_options[$option] = absint( $_POST[$option] );
			}
			else {
				unset( $file_options[$option] );
			}
		}

		update_post_meta( $post_id, '_dedo_file_options', $file_options );
	}
}
add_action( 'save_post', 'dedo_meta_boxes_save' );

/* ===== END includes/admin/meta-boxes.php ===== */

/* ===== BEGIN includes/admin/page-settings.php ===== */
/**
 * Delightful Downloads Page Settings
 *
 * @package     Delightful Downloads
 * @subpackage  Admin/Page Settings
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Settings Page
 *
 * @since  1.3
 */
function dedo_register_page_settings() {
	add_submenu_page( 'edit.php?post_type=dedo_download', __( 'Download Settings', 'delightful-downloads' ), __( 'Settings', 'delightful-downloads' ), 'manage_options', 'dedo_settings', 'dedo_render_page_settings' );
}
add_action( 'admin_menu', 'dedo_register_page_settings', 30 );

/**
 * Register Settings Sections and Fields
 *
 * @since  1.3
 */
function dedo_register_settings() {
	
	// Get registered tabs and settings
	$registered_tabs = dedo_get_tabs();
	$registered_settings = dedo_get_options();

	// Register whitelist
	register_setting( 'dedo_settings', 'delightful-downloads', 'dedo_validate_settings' ); 

	// Register form sections
	foreach ( $registered_tabs as $key => $value ) {
		
		add_settings_section(
			'dedo_settings_' . $key,
			'',
			function_exists( 'dedo_settings_' . $key . '_section' ) ? 'dedo_settings_' . $key . '_section' : 'dedo_settings_section',
			'dedo_settings_' . $key
		);

	}
	
	// Register form fields
	foreach ( $registered_settings as $key => $value ) {
		$callback = 'dedo_settings_' . $key . '_field';

		if ( ! empty( $value['callback'] ) ) {
			$callback = $value['callback'];
		}

		add_settings_field(
			$key,
			$value['name'],
			$callback,
			'dedo_settings_' . $value['tab'],
			'dedo_settings_' . $value['tab']
		);

	}
} 
add_action( 'admin_init', 'dedo_register_settings' );

/**
 * Render Settings Page
 *
 * @since  1.3
 */
function dedo_render_page_settings() {
	
	// Get registered tabs
	$registered_tabs = dedo_get_tabs();

	// Get current tab
	$active_tab = isset( $_GET['tab'] ) ? esc_html($_GET['tab']) : 'general'; 
	?>

	<div class="wrap">
		
		<h1><?php _e( 'Download Settings', 'delightful-downloads' ); ?>
			<a href="#dedo-settings-import" class="add-new-h2 dedo-modal-action"><?php _e( 'Import', 'delightful-downloads' ); ?></a>
			<a href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings&action=export' ), 'dedo_export_settings', 'dedo_export_settings_nonce' ) ?>" class="add-new-h2"><?php _e( 'Export', 'delightful-downloads' ); ?></a>
			<a href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings&action=reset_defaults' ), 'dedo_reset_settings', 'dedo_reset_settings_nonce' ) ?>" class="add-new-h2 dedo_confirm_action" data-confirm="<?php _e( 'You are about to reset the download settings.', 'delightful-downloads' ); ?>"><?php _e( 'Reset Defaults', 'delightful-downloads' ); ?></a>
		</h1>
		
		<?php if ( isset( $_GET['settings-updated'] ) ) {
			echo '<div class="notice updated is-dismissible"><p>' . __( 'Settings updated successfully.', 'delightful-downloads' ) . '</p></div>';
		} ?>

		<h2 id="dedo-settings-tabs" class="nav-tab-wrapper">
			
			<?php // Generate tabs
			
			foreach ( $registered_tabs as $key => $value ) {
				
				echo '<a href="#dedo-settings-tab-' . $key . '" class="nav-tab ' . ( $active_tab == $key ? 'nav-tab-active' : '' ) . '">' . $value . '</a>';
   	 		} ?>

		</h2>

		<div id="dedo-settings-main" <?php echo ( !apply_filters( 'dedo_admin_sidebar', true ) ) ? 'style="float: none; width: 100%; padding:3px"' : ''; ?>>	

			<form action="options.php" method="post">	
				<?php // Setup fields
				settings_fields( 'dedo_settings' );

				// Display correct fields
				foreach ( $registered_tabs as $key => $value ) {
					$active_class = ( $key === $active_tab ) ? 'active' : '';
					?>

					<section id="dedo-settings-tab-<?php echo $key; ?>" class="dedo-settings-tab <?php echo $active_class; ?>" style="<?php echo ( '' === $active_class ) ? 'display: none;' : ''; ?>">
						<?php 

						if ( 'support' === $key ) {
							dedo_render_part_support();
						}
						else {
							do_settings_sections( 'dedo_settings_' . $key );
						}

						?>
					</section>

					<?php
				}
				
				// Submit button
				submit_button(); ?>
			</form>
	
		</div>

		<?php dedo_render_part_sidebar(); ?>

	</div>
	
	<?php
}

/**
 * Render Support Section
 *
 * @since  1.5
 */
function dedo_render_part_support() {

	global $wpdb, $dedo_options;

	// Get current theme data
	$theme = wp_get_theme();

	// Get active plugins
	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	// Prior version
	$prior_version = get_option( 'delightful-downloads-prior-version' );
	?>

	<textarea id="dedo_support" readonly>

## Server Information ##

Server: <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>
PHP Version: <?php echo PHP_VERSION . "\n"; ?>
MySQL Version: <?php echo $wpdb->db_version() . "\n"; ?>

PHP Safe Mode: <?php echo ini_get( 'safe_mode' ) ? "Yes\n" : "No\n"; ?>
PHP Memory Limit: <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
PHP Time Limit: <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>
PHP Max Post Size: <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Max Upload Size: <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>


## WordPress Information ##

WordPress Version: <?php echo get_bloginfo( 'version' ) . "\n"; ?>
Multisite: <?php echo ( is_multisite() ) ? 'Yes' . "\n" : 'No' . "\n" ?>
Max Upload Size: <?php echo size_format( wp_max_upload_size(), 1 ) . "\n"; ?>

Site Address: <?php echo home_url() . "\n"; ?>
WordPress Address: <?php echo site_url() . "\n"; ?>
Download Address: <?php echo dedo_download_link( 1 ) . "\n"; ?>

<?php echo ( defined('UPLOADS') ? 'Upload Directory: ' . UPLOADS . "\n" : '' ); ?>
Directory (wp-content): <?php echo ( defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR . "\n" : '' ); ?>
URL (wp-content): <?php echo ( defined('WP_CONTENT_URL') ? WP_CONTENT_URL . "\n" : '' ); ?>

## Active Theme ## 

<?php echo $theme->Name . " " . $theme->Version . "\n"; ?>


## Active Plugins ##			

<?php 
foreach ( $plugins as $key => $value ) {
	
	if ( in_array( $key, $active_plugins ) ) {
		echo $value['Name'] . ' ' . $value['Version'] . "\n";
	}
	
}
?>


## Delightful Downloads Information ##

Version: <?php echo DEDO_VERSION . "\n"; ?>
Prior Version: <?php echo $prior_version . "\n"; ?>

<?php

foreach ( $dedo_options as $key => $value ) {
	echo $key . ": " . $value . "\n";
}

?>
	</textarea>
	<?php
}

/**
 * Render Sidebar
 */
function dedo_render_part_sidebar() {
	if ( apply_filters( 'dedo_admin_sidebar', true ) ) : ?>

		<?php $current_user = wp_get_current_user(); ?>

		<div id="dedo-settings-sidebar" class="postbox">

			<h4><?php _e( 'Help and Support', 'delightful-downloads' ); ?></h4>
			<p><?php printf( __( 'Having issues? Check out the %sdocumentation%s. For bugs please raise an issue on the %ssupport forums%s.', 'delightful-downloads' ), '<a target="_blank" href="'.DEDO_PLUGIN_URL.'readme.txt">', '</a>', '<a href="https://github.com/svenbolte/delightful-downloads/issues">', '</a>' ); ?></p>
			<h4><?php _e( 'sample shortcodes', 'delightful-downloads' ); ?></h4>
			<p><code>[ddownload id="6779" style="infobox" text="%title%  - %filename% - %date% - %filesize% - (%count%x)"]</code></p>
				<p><code>[ddownload_list] - Liste mit Icons</code></p>
				<p><code>[ddownload id="3071" style="link" text="%title% %filename% - %date% - %filesize% - (%count%x)"]</code></p>
				<p><code>[ddownload id="3071"] - Infobox Layout mit File Icon</code></p>
				<p><code>[ddownload id="3071" style="button" button="accent"] - Download button in themes button color</code></p>
	</div>

	<?php endif;

}

/**
 * Render Settings Sections
 *
 * @since  1.3
 */
function dedo_settings_section() { return; }

/**
 * Render enable taxonomies field
 *
 * @since  1.3
 */
function dedo_settings_enable_taxonomies_field() {
	global $dedo_options;
	$checked = absint( $dedo_options['enable_taxonomies'] ); 
	?>
	
	<label for="enable_taxonomies_true"><input name="delightful-downloads[enable_taxonomies]" id="enable_taxonomies_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="enable_taxonomies_false"><input name="delightful-downloads[enable_taxonomies]" id="enable_taxonomies_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Allow downloads to be tagged or categorised.', 'delightful-downloads' ); ?></p>
	<?php
}

/**
 * Render members only field
 *
 * @since  1.3
 */
function dedo_settings_members_only_field() {
	global $dedo_options;
	$checked = absint( $dedo_options['members_only'] );
	?>

	<label for="members_only_true"><input name="delightful-downloads[members_only]" id="members_only_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="members_only_false"><input name="delightful-downloads[members_only]" id="members_only_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Allow only logged in users to download files. This can be overridden on a per-download basis.', 'delightful-downloads' ); ?></p>
	<?php
	// Default selected item
	$selected = $dedo_options['members_only_redirect'];
	
	// Output select input
	$args = array(
		'name'						=> 'delightful-downloads[members_only_redirect]',
		'selected'					=> $selected,
		'show_option_none'			=> __( 'No Page (Generic Error)', 'delightful-downloads' ),
		'option_none_value'			=> 0
	); ?>
	
	<div class="dedo-sub-option">
		<?php wp_dropdown_pages( $args ); ?>
		<p class="description"><?php _e( 'The page to redirect non-members. If no page is selected, a generic error message will be displayed. This can be overridden on a per-download basis.', 'delightful-downloads' ); ?></p>

	</div>
	<?php
}

/**
 * Render open in browser field
 *
 * @since  1.5
 */
function dedo_settings_open_browser_field() {
	global $dedo_options;
	$checked = absint( $dedo_options['open_browser'] );
	?>

	<label for="open_browser_true"><input name="delightful-downloads[open_browser]" id="open_browser_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="open_browser_false"><input name="delightful-downloads[open_browser]" id="open_browser_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Attempt to open files in the browser window. This can be overridden on a per-download basis. For files located within the Delightful Downloads upload directory, set folder protection to \'No\', which can be found under the advanced tab.', 'delightful-downloads' ); ?></p>
	<?php
}

/**
 * Render block user agents field
 *
 * @since  1.3
 */
function dedo_settings_block_agents_field() {
	global $dedo_options;

	$agents = $dedo_options['block_agents'];

	echo '<textarea name="delightful-downloads[block_agents]" class="dedo-settings-textarea">' . esc_attr( $agents ) . '</textarea>';
	echo '<p class="description">' . __( 'User agents to block from downloading files. One per line.', 'delightful-downloads' ) . '</p>';
}

/**
 * Render default text field
 *
 * @since  1.3
 */
function dedo_settings_default_text_field() {
	global $dedo_options;

	$text = $dedo_options['default_text'];

	echo '<input type="text" name="delightful-downloads[default_text]" value="' . esc_attr( $text ) . '" class="regular-text" />';
	echo '<p class="description">' . sprintf( __( 'The default text to display, when using the %s shortcode. This can be overridden on a per-download basis.', 'delightful-downloads' ), '<code>[ddownload]</code>' );
}

/**
 * Render default style field
 *
 * @since  1.3
 */
function dedo_settings_default_style_field() {
	global $dedo_options;

	$styles        = dedo_get_shortcode_styles();
	$default_style = $dedo_options['default_style'];
	$disabled      = empty( $styles ) ? 'disabled' : '';

	echo '<select name="delightful-downloads[default_style]" ' . $disabled . '>';

	if ( ! empty( $styles ) ) {
		foreach ( $styles as $key => $value ) {
			$selected = ( $default_style == $key ? ' selected="selected"' : '' );
			echo '<option value="' . $key . '" ' . $selected . '>' . $value['name'] . '</option>';
		}
	} else {
		echo '<option>' . __( 'No styles registered', 'delightful-downloads' ) . '</option>';
	}

	echo '</select>';
	echo '<p class="description">' . sprintf( __( 'The default output style, when using the %s shortcode. This can be overridden on a per-download basis.', 'delightful-downloads' ), '<code>[ddownload]</code>' );
}

/**
 * Render default button field
 *
 * @since  1.3
 */
function dedo_settings_default_button_field() {
	global $dedo_options;

	$colors        = dedo_get_shortcode_buttons();
	$default_color = $dedo_options['default_button'];
	$disabled      = empty( $colors ) ? 'disabled' : '';

	echo '<select name="delightful-downloads[default_button]" ' . $disabled . '>';

	if ( ! empty( $colors ) ) {
		foreach ( $colors as $key => $value ) {
			$selected = ( $default_color == $key ? ' selected="selected"' : '' );
			echo '<option value="' . $key . '" ' . $selected . '>' . $value['name'] . '</option>';
		}
	} else {
		echo '<option>' . __( 'No button styles registered', 'delightful-downloads' ) . '</option>';
	}

	echo '</select>';
	echo '<p class="description">' . sprintf( __( 'The default button style, when using the %s shortcode. This can be overridden on a per-download basis.', 'delightful-downloads' ), '<code>[ddownload]</code>' );
}

/**
 * Render default list field
 *
 * @since  1.3
 */
function dedo_settings_default_list_field() {
	global $dedo_options;

	$lists        = dedo_get_shortcode_lists();
	$default_list = $dedo_options['default_list'];
	$disabled     = empty( $lists ) ? 'disabled' : '';

	echo '<select name="delightful-downloads[default_list]" ' . $disabled . '>';

	if ( ! empty( $lists ) ) {
		foreach ( $lists as $key => $value ) {
			$selected = ( $default_list == $key ? ' selected="selected"' : '' );
			echo '<option value="' . $key . '" ' . $selected . '>' . $value['name'] . '</option>';
		}
	} else {
		echo '<option>' . __( 'No list styles registered', 'delightful-downloads' ) . '</option>';
	}

	echo '</select>';
	echo '<p class="description">' . sprintf( __( 'The default output style, when using the %s shortcode. This can be overridden on a per-list basis.', 'delightful-downloads' ), '<code>[ddownload_list]</code>' );
}

/**
 * Render log admin downloads field
 *
 * @since  1.3
 */
function dedo_settings_log_admin_downloads_field() {
	global $dedo_options;
	$checked = absint( $dedo_options['log_admin_downloads'] );
	?>
	
	<label for="log_admin_downloads_true"><input name="delightful-downloads[log_admin_downloads]" id="log_admin_downloads_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="log_admin_downloads_false"><input name="delightful-downloads[log_admin_downloads]" id="log_admin_downloads_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Log events triggered by admin users.', 'delightful-downloads' ); ?></p>
	<?php
}

/**
 * Render grace period field
 *
 * @since  1.4
 */
function dedo_settings_grace_period_field() {
	global $dedo_options;
	$grace_period = $dedo_options['grace_period'];
	$duration = $dedo_options['grace_period_duration'];
	?>
	
	<label for="grace_period_toggle_true"><input name="delightful-downloads[grace_period]" id="grace_period_toggle_true" type="radio" value="1" <?php echo ( 1 == $grace_period ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="grace_period_toggle_false"><input name="delightful-downloads[grace_period]" id="grace_period_toggle_false" type="radio" value="0" <?php echo ( 0 == $grace_period ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Stop multiple logs of the same type from being saved, in quick succession.', 'delightful-downloads' ); ?></p>
	<div id="grace_period_sub" class="dedo-sub-option" style="<?php echo ( $grace_period == 1 ) ? 'display: block;' : 'display: none;';?> ">
		<input type="number" name="delightful-downloads[grace_period_duration]" value="<?php echo esc_attr( $duration ); ?>" min="1" class="small-text" />
		<p class="description"><?php _e( 'The time in minutes before creating a new log.', 'delightful-downloads' ); ?></p>
	</div>
	<?php
}

/**
 * Render auto delete field
 *
 * @since  1.4
 */
function dedo_settings_auto_delete_field() {
	global $dedo_options;
	$auto_delete = $dedo_options['auto_delete'];
	$duration = $dedo_options['auto_delete_duration'];
	?>
	
	<label for="auto_delete_toggle_true"><input name="delightful-downloads[auto_delete]" id="auto_delete_toggle_true" type="radio" value="1" <?php echo ( 1 == $auto_delete ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="auto_delete_toggle_false"><input name="delightful-downloads[auto_delete]" id="auto_delete_toggle_false" type="radio" value="0" <?php echo ( 0 == $auto_delete ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Automatically delete old logs.', 'delightful-downloads' ); ?></p>
	<div id="auto_delete_sub" class="dedo-sub-option" style="<?php echo ( $auto_delete == 1 ) ? 'display: block;' : 'display: none;';?> ">
		<input type="number" name="delightful-downloads[auto_delete_duration]" value="<?php echo esc_attr( $duration ); ?>" min="1" class="small-text" />
		<p class="description"><?php _e( 'The time in days to keep logs.', 'delightful-downloads' ); ?></p>
	</div>
	<?php
}

/**
 * Render enable css field
 *
 * @since  1.3
 */
function dedo_settings_enable_css_field() {
	global $dedo_options;
	$checked = absint( $dedo_options['enable_css'] );
	?>

	<label for="enable_css_true"><input name="delightful-downloads[enable_css]" id="enable_css_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="enable_css_false"><input name="delightful-downloads[enable_css]" id="enable_css_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Output the Delightful Downloads stylesheet on the front-end.', 'delightful-downloads' ); ?></p>
	<?php
}

/**
 * Render cache duration field
 *
 * @since  1.3
 */
function dedo_settings_cache_field() {
	global $dedo_options;
	$cache = $dedo_options['cache'];
	$duration = $dedo_options['cache_duration'];
	?>

	<label for="cache_toggle_true"><input name="delightful-downloads[cache]" id="cache_toggle_true" type="radio" value="1" <?php echo ( 1 == $cache ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="cache_toggle_false"><input name="delightful-downloads[cache]" id="cache_toggle_false" type="radio" value="0" <?php echo ( 0 == $cache ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Cache database queries that are expensive to generate.', 'delightful-downloads' ); ?></p>
	<div id="cache_sub" class="dedo-sub-option" style="<?php echo ( $cache == 1 ) ? 'display: block;' : 'display: none;';?> ">
		<input type="number" name="delightful-downloads[cache_duration]" value="<?php echo esc_attr( $duration ); ?>" min="1" class="small-text" />
		<p class="description"><?php _e( 'The time in minutes to cache queries.', 'delightful-downloads' ); ?></p>
	</div>
	<?php
}

/**
 * Render Download Address field
 *
 * @since  1.3
 */
function dedo_settings_download_url_field() {
	global $dedo_options;

	$text = $dedo_options['download_url'];

	echo '<input type="text" name="delightful-downloads[download_url]" value="' . esc_attr( $text ) . '" class="regular-text" />';
	echo '<p class="description">' . __( 'The URL for download links.', 'delightful-downloads' ) . ' <code>' . dedo_download_link( 123 ) . '</code></p>';
}


/**
 * Render Upload Directory field
 */
function dedo_settings_upload_directory_field() {
	global $dedo_options;

	$text = $dedo_options['upload_directory'];

	echo '<input type="text" name="delightful-downloads[upload_directory]" value="' . esc_attr( $text ) . '" class="regular-text" />';
	echo '<p class="description">' . __( 'The directory to upload files.', 'delightful-downloads' ) . ' <code>' . trailingslashit( dedo_get_upload_dir( 'dedo_baseurl' ) ) . '</code></p>';
}

/**
 * Render Download Address Quicklink
 *
 * @since  1.3
 */
function dedo_settings_download_quicklink_field() {
	global $dedo_options;
	$checked = absint( $dedo_options['download_quicklink'] );
   	?>
  	<label for="quicklink_true"><input name="delightful-downloads[download_quicklink]" id="quicklink_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="quicklink_false"><input name="delightful-downloads[download_quicklink]" id="quicklink_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Display Quicklink column in download list.', 'delightful-downloads' ); ?></p> 
	<?php
}

/**
 * Render Folder Protection field
 *
 * @since  1.5
 */
function dedo_settings_folder_protection_field() {
	global $dedo_options;
	$checked = absint( $dedo_options['folder_protection'] );
	?>

	<label for="folder_protection_true"><input name="delightful-downloads[folder_protection]" id="folder_protection_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="folder_protection_false"><input name="delightful-downloads[folder_protection]" id="folder_protection_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Stop direct access to uploaded files, within the Delightful Downloads upload directory.', 'delightful-downloads' ); ?></p>
	<?php
}

/**
 * Render Uninstall field
 *
 * @since  1.3.6
 */
function dedo_settings_uninstall_field() {
	global $dedo_options;
	$checked = absint( $dedo_options['uninstall'] );
	?>

	<label for="uninstall_true"><input name="delightful-downloads[uninstall]" id="uninstall_true" type="radio" value="1" <?php echo ( 1 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
	<label for="uninstall_false"><input name="delightful-downloads[uninstall]" id="uninstall_false" type="radio" value="0" <?php echo ( 0 === $checked ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
	<p class="description"><?php _e( 'Completely remove all data associated with Delightful Downloads, when uninstalling the plugin. All downloads, categories, tags and statistics will be removed.', 'delightful-downloads' ); ?></p>
	<?php
}

/**
 * Validate settings callback
 *
 * @since  1.3
 */
function dedo_validate_settings( $input ) {
	global $dedo_options;

	// Registered options
	$options              = dedo_get_options();
	$dedo_default_options = dedo_get_default_options();

	// Ensure text fields are not blank
	foreach( $options as $key => $value ) {
		if ( 'text' !== $options[ $key ]['type'] ) {
			continue;
		}
		// None empty text fields
	}
	 
	// Ensure download URL does not contain illegal characters
	$input['download_url'] = strtolower( preg_replace( '/[^A-Za-z0-9\_\-]/', '', $input['download_url'] ) );

	// Ensure upload directory does not contain illegal characters
	$input['upload_directory'] = strtolower( preg_replace( '/[^A-Za-z0-9\_\-]/', '', $input['upload_directory'] ) );

	// Run folder protection if option changed
	if ( $input['folder_protection'] != $dedo_options['folder_protection'] ) {
		dedo_folder_protection( $input['folder_protection'] );
	}
	
	// Clear transients
	dedo_delete_all_transients();

	return apply_filters( 'dedo_validate_settings', $input );
}

/**
 * Render Import Modal
 *
 * @since  1.5
 */
function dedo_render_part_import() {

	// Ensure only added on settings screen	
	$screen = get_current_screen();

	if ( 'dedo_download_page_dedo_settings' !== $screen->id ) {

		return;
	}

	?>

	<div id="dedo-settings-import" class="dedo-modal" style="display: none; width: 400px; left: 50%; margin-left: -200px;">
		<a href="#" class="dedo-modal-close" title="Close"><span class="media-modal-icon"></span></a>
		<div class="media-modal-content">
			<h1><?php _e( 'Import Settings', 'delightful-downloads' ); ?></h1>
			<p><?php _e( 'Select a Delightful Downloads settings file to import:', 'delightful-downloads' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings&action=import' ); ?>">
				<p><input type="file" name="json_file"/></p>
				<p>
					<?php wp_nonce_field( 'dedo_import_settings','dedo_import_settings_nonce' ); ?>
					<input type="submit" value="<?php _e( 'Import', 'delightful-downloads' ); ?>" class="button button-primary"/>
				</p>
			</form>
		</div>
	</div>

	<?php

}
add_action( 'admin_footer', 'dedo_render_part_import' );

/**
 * Settings Page Actions
 *
 * @since  1.4
 */
function dedo_settings_actions() {

	//Only perform on settings page, when form not submitted
	if ( isset( $_GET['page'] ) && 'dedo_settings' == $_GET['page'] ) {

		// Import
		if( isset( $_GET['action'] ) && 'import' == $_GET['action'] ) {

			dedo_settings_actions_import();
		}
		// Export
		else if( isset( $_GET['action'] ) && 'export' == $_GET['action'] ) {

			dedo_settings_actions_export();
		}
		// Reset default settings
		else if( isset( $_GET['action'] ) && 'reset_defaults' == $_GET['action'] ) {
			
			dedo_settings_actions_reset();
		}

	}
}
add_action( 'init', 'dedo_settings_actions', 0 );

/**
 * Settings Page Actions Import
 *
 * @since  1.5
 */
function dedo_settings_actions_import() {

	global $dedo_notices;

	// Verfiy nonce
	check_admin_referer( 'dedo_import_settings', 'dedo_import_settings_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {

		return;
	}

	// Check file is uploaded
	if ( isset( $_FILES['json_file'] ) && $_FILES['json_file']['size'] > 0 ) {

		// Check file extension
		if ( 'json' !== dedo_get_file_ext( $_FILES['json_file']['name'] ) ) {

			$dedo_notices->add( 'error', __( 'Invalid settings file.', 'delightful-downloads' ) );

			return;
		}

		// Import and display success
		$import = json_decode( file_get_contents( $_FILES['json_file']['tmp_name'] ), true );

		update_option( 'delightful-downloads', $import );

		$dedo_notices->add( 'updated', __( 'Settings have been successfully imported.', 'delightful-downloads' ) );

		// Redirect page to remove action from URL
		wp_redirect( admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings' ) );
		exit();	
	}
	else {

		$dedo_notices->add( 'error', __( 'No file uploaded.', 'delightful-downloads' ) );

		return;
	}
}

/**
 * Settings Page Actions Export
 *
 * @since  1.5
 */
function dedo_settings_actions_export() {

	global $dedo_options;

	// Verfiy nonce
	check_admin_referer( 'dedo_export_settings', 'dedo_export_settings_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {

		return;
	}

	// Set filename
	$filename = 'delightful-downloads-' . date( 'Ymd' ) . '.json';

	// Output headers so that the file is downloaded
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Expires: 0' );

	echo json_encode( $dedo_options );	

	die();
}

/**
 * Settings Page Actions Reset
 *
 * @since  1.5
 */
function dedo_settings_actions_reset() {

	global $dedo_default_options, $dedo_notices;

	// Verfiy nonce
	check_admin_referer( 'dedo_reset_settings', 'dedo_reset_settings_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {

		return;
	}

	delete_option( 'delightful-downloads' );
	add_option( 'delightful-downloads', $dedo_default_options );

	// Add success notice
	$dedo_notices->add( 'updated', __( 'Default settings reset successfully.', 'delightful-downloads' ) );

	// Redirect page to remove action from URL
	wp_redirect( admin_url( 'edit.php?post_type=dedo_download&page=dedo_settings' ) );
	exit();	
}

/* ===== END includes/admin/page-settings.php ===== */

/* ===== BEGIN includes/admin/page-statistics.php ===== */
/**
 * Delightful Downloads Page Statistics
 *
 * @package     Delightful Downloads
 * @subpackage  Admin/Page Statistics
 * @since       1.4
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Statistics Page
 *
 * @since  1.4
 */
function dedo_register_page_statistics() {
	
	global $dedo_statistics_page;

	$dedo_statistics_page = add_submenu_page( 'edit.php?post_type=dedo_download', __( 'Download Logs', 'delightful-downloads' ), __( 'Logs', 'delightful-downloads' ), 'manage_options', 'dedo_statistics', 'dedo_render_page_statistics' );

	// Hook for screen options dropdown
	add_action( "load-$dedo_statistics_page", 'dedo_statistics_screen_options' );
}
add_action( 'admin_menu', 'dedo_register_page_statistics', 20 );

/**
 * Render Statistics Page
 *
 * @since  1.4
 */
function dedo_render_page_statistics() {
	
	?>
	<div class="wrap">
		<h1><?php _e( 'Download Logs', 'delightful-downloads' ); ?>
			<a href="#dedo-stats-export" class="add-new-h2 dedo-modal-action"><?php _e( 'Export', 'delightful-downloads' ); ?></a>
			<a href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=dedo_download&page=dedo_statistics&action=empty_logs' ), 'dedo_empty_logs', 'dedo_empty_logs_nonce' ); ?>" class="add-new-h2 dedo_confirm_action" data-confirm="<?php _e( 'You are about to permanently delete the download logs.', 'delightful-downloads' ); ?>"><?php _e( 'Delete', 'delightful-downloads' ); ?></a>
		</h1>

		<div id="dedo-settings-main">	
			<?php do_action( 'ddownload_statistics_header' ); ?>
			
			<?php $table = new DEDO_List_table(); ?>
			<?php $table->display(); ?>

			<?php do_action( 'ddownload_statistics_footer' ); ?>
		</div>
	</div>
	<?php
}

/**
 * Render Export Logs Modal
 *
 * @since  1.5
 */
function dedo_render_export_modal() {
	// Ensure only added on statistics screen	
	$screen = get_current_screen();

	if ( 'dedo_download_page_dedo_statistics' !== $screen->id ) {
		return;
	}

	?>

	<div id="dedo-stats-export" class="dedo-modal" style="display: none; width: 400px; left: 50%; margin-left: -200px;">
		<a href="#" class="dedo-modal-close" title="Close"><span class="media-modal-icon"></span></a>
		<div class="media-modal-content">
			<h1><?php _e( 'Export Logs', 'delightful-downloads' ); ?></h1>
			<p><?php _e( 'Export log entries to a CSV file. Please select a date range, or leave blank to export all:', 'delightful-downloads' ); ?></p>
			<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'edit.php?post_type=dedo_download&page=dedo_statistics&action=export' ); ?>">
				<p class="left">
					<label for="dedo_start_date"><?php _e( 'Start Date', 'delightful-downloads' ); ?></label>
					<input name="dedo_start_date" id="dedo_start_date" type="date" ?>
				</p>
				<p class="right">
					<label for="dedo_end_date"><?php _e( 'End Date', 'delightful-downloads' ); ?></label>
					<input name="dedo_end_date" id="dedo_end_date" type="date" ?>
				</p>
				<p>
					<?php wp_nonce_field( 'dedo_export_stats','dedo_export_stats_nonce' ); ?>
					<input type="submit" value="<?php _e( 'Export', 'delightful-downloads' ); ?>" class="button button-primary"/>
				</p>
			</form>
		</div>
	</div>

	<?php

}
add_action( 'admin_footer', 'dedo_render_export_modal' );

/**
 * Statistics Page Actions
 *
 * @since  1.4
 */
function dedo_statistics_actions() {

	//Only perform on statistics page
	if ( isset( $_GET['page'] ) && 'dedo_statistics' == $_GET['page'] ) {

		// Export statistics
		if( isset( $_GET['action'] ) && 'export' == $_GET['action'] ) {
			dedo_statistics_actions_export();	
		}

		// Empty statistics
		if( isset( $_GET['action'] ) && 'empty_logs' == $_GET['action'] ) {
			dedo_statistics_actions_empty();
		}
	}
}
add_action( 'init', 'dedo_statistics_actions', 0 );

/**
 * Statistics Page Action Export
 *
 * @since  1.5
 */
function dedo_statistics_actions_export() {
	global $dedo_statistics, $dedo_notices;

	// Disable max_execution_time
	set_time_limit( 0 );

	// Verfiy nonce
	check_admin_referer( 'dedo_export_stats', 'dedo_export_stats_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {
		return;
	}

	// Add args to query
	$args = array();

	if ( isset( $_POST['dedo_start_date'] ) && !empty( $_POST['dedo_start_date'] ) ) {
		$args['start'] = $_POST['dedo_start_date'] . ' 00:00:00';
	}

	if ( isset( $_POST['dedo_end_date'] )  && !empty( $_POST['dedo_end_date'] ) ) {
		$args['end'] = $_POST['dedo_end_date'] . ' 23:59:59';
	}

	// Get logs
	$logs = $dedo_statistics->get_logs( $args );

	// Check we have logs before creating file
	if ( NULL == $logs ) {
		$dedo_notices->add( 'error', __( 'You do not have any logs to export in that date range.', 'delightful-downloads' ) );
		
		// Redirect page to remove action from URL
		wp_redirect( admin_url( 'edit.php?post_type=dedo_download&page=dedo_statistics' ) );
		exit();	
	}

	// Get download titles
	$downloads = get_posts( array( 'post_type' => 'dedo_download', 'posts_per_page'   => -1, ) );

	foreach( $downloads as $download ) {
		$download_title[$download->ID] =  $download->post_title;
	}

	// Get user names
	$users = get_users();

	foreach( $users as $user ) {
		$user_name[$user->ID] = $user->user_email;
	}

	// Set filename
	$filename = 'download-logs-' . date( 'Ymd' ) . '.csv';

	// Output headers so that the file is downloaded
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Expires: 0' );

	$output = fopen( 'php://output', 'w' );

	// Column headings
	fputcsv( $output, array( __( 'ID', 'delightful-downloads' ), __( 'Status', 'delightful-downloads' ), __( 'Date', 'delightful-downloads' ), __( 'Download', 'delightful-downloads' ), __( 'User', 'delightful-downloads' ), __( 'IP Address', 'delightful-downloads' ), __( 'User Agent', 'delightful-downloads' ) ), escape: "" );

	// Add data
	foreach( $logs as $log ) {
		// Convert download ID to title
		$log['post_id'] = ( isset( $download_title[$log['post_id']] ) ) ? $download_title[$log['post_id']] : __( 'Unknown', 'delightful-downloads' );

		// Convert user ID to email
		$log['user_id'] = ( isset( $user_name[$log['user_id']] ) ) ? $user_name[$log['user_id']] : __( 'Non-member', 'delightful-downloads' );

		// Convert ip to human readable
        if ( ! empty( $log['user_ip'] ) ) {
            $log['user_ip'] = inet_ntop( $log['user_ip'] );
        }
		
		fputcsv( $output, $log, escape: "" );
	}	

	die();
}

/**
 * Statistics Page Action Empty
 *
 * @since  1.5
 */
function dedo_statistics_actions_empty() {
	global $dedo_statistics, $dedo_notices;

	// Verfiy nonce
	check_admin_referer( 'dedo_empty_logs', 'dedo_empty_logs_nonce' );

	// Admins only
	if ( !current_user_can( 'manage_options' ) ) {
		return;
	}

	$result = $dedo_statistics->empty_table();

	if ( false === $result ) {
		// Error
		$dedo_notices->add( 'error', __( 'Logs could not be deleted.', 'delightful-downloads' ) );
	}
	else {
		// Success
		$dedo_notices->add( 'updated', __( 'Logs deleted successfully.', 'delightful-downloads' ) );
	}

	// Redirect page to remove action from URL
	wp_redirect( admin_url( 'edit.php?post_type=dedo_download&page=dedo_statistics' ) );
	exit();
}

/**
 * Statistics Sreen Options
 *
 * @since  1.4
 */
function dedo_statistics_screen_options() {
 
	global $dedo_statistics_page;

	$screen = get_current_screen();

	if ( !is_object( $screen ) || $screen->id != $dedo_statistics_page ) {
		return;
	}

	// Per page option
	$args = array(
	    'label' => __( 'Download Logs', 'delightful-downloads' ),
	    'default' => 20,
	    'option' => 'dedo_logs_per_page'
	);
	 
	add_screen_option( 'per_page', $args );
 
}

/**
 * Statistics Save Sreen Options
 *
 * @since  1.4
 */
function dedo_statistics_save_screen_options( $status, $option, $value ) {
	
	if ( 'dedo_logs_per_page' == $option ) {
		
		return $value;
	}
}
add_filter( 'set-screen-option' , 'dedo_statistics_save_screen_options', 10, 3 );

/* ===== END includes/admin/page-statistics.php ===== */

