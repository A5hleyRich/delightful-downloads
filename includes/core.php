<?php
/**
 * Delightful Downloads Core Bundle
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


// Shared helpers: use theme/other-plugin implementations when available, otherwise lean WordPress fallbacks.
if ( ! function_exists( 'dd_shared_number_format_short' ) ) {
	function dd_shared_number_format_short( $n ) {
		if ( function_exists( 'number_format_short' ) ) return number_format_short( $n );
		$n = (float) $n;
		if ( $n <= 0 ) return '<span title="0">0</span>';
		$units = array( '', 'K', 'M', 'G', 'T', 'P' );
		$i = min( (int) floor( log( max( 1, $n ), 1024 ) ), count( $units ) - 1 );
		$v = $n / pow( 1024, $i );
		$dec = ( $i > 0 && $v < 10 ) ? 1 : 0;
		return '<span title="' . esc_attr( number_format_i18n( $n ) ) . '">' . number_format_i18n( $v, $dec ) . $units[$i] . '</span>';
	}
}

if ( ! function_exists( 'dd_shared_ago' ) ) {
	function dd_shared_ago( $timestamp ) {
		$timestamp = (int) $timestamp;
		if ( $timestamp <= 0 ) return '';
		if ( function_exists( 'ago' ) ) return ago( $timestamp );
		$now = current_time( 'timestamp' );
		return $timestamp > $now
			? sprintf( __( 'in %s', 'delightful-downloads' ), human_time_diff( $now, $timestamp ) )
			: sprintf( __( '%s ago', 'delightful-downloads' ), human_time_diff( $timestamp, $now ) );
	}
}

if ( ! function_exists( 'dd_shared_colordatebox' ) ) {
	function dd_shared_colordatebox( $created, $modified = null, $noicon = null, $showago = null ) {
		$created = (int) $created;
		$modified = $modified !== null ? (int) $modified : $created;
		if ( function_exists( 'colordatebox' ) ) return colordatebox( $created, $modified, $noicon, $showago );
		$ts = $modified > 0 ? $modified : $created;
		if ( $ts <= 0 ) return '';
		return esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $ts ) );
	}
}

/**
 * Return the filesystem modification time for a download.
 *
 * For folder downloads the newest contained file wins. Remote/unresolvable
 * downloads fall back to the WordPress post modification time.
 *
 * @param int $post_id Download post ID.
 * @return int Unix timestamp.
 */
function dedo_get_file_modified_time( $post_id ) {
	$post_id = absint( $post_id );

	if ( function_exists( 'dedo_is_folder_download' ) && dedo_is_folder_download( $post_id ) && function_exists( 'dedo_folder_download_scan' ) ) {
		$files = dedo_folder_download_scan( $post_id );
		if ( ! empty( $files ) ) {
			$mtimes = array_map( static fn( $file ) => (int) ( $file['mtime'] ?? 0 ), $files );
			$mtime = max( $mtimes );
			if ( $mtime > 0 ) return $mtime;
		}
	}

	$file_url = get_post_meta( $post_id, '_dedo_file_url', true );
	if ( $file_url ) {
		$file_path = dedo_get_abs_path( $file_url );
		if ( $file_path && is_file( $file_path ) ) {
			$mtime = (int) @filemtime( $file_path );
			if ( $mtime > 0 ) return $mtime;
		}
	}

	return (int) get_post_modified_time( 'U', true, $post_id );
}

/**
 * Cache Class
 * @package  	Delightful Downloads
 * @author   	Ashley Rich
 * @copyright   Copyright (c) 2014, Ashley Rich
*/

class DEDO_Cache {

	/**
	 * Cache Enabled
	 * Is caching enabled on the settings screen?
	 * @var boolean
	 * @access private
	 */
	private $cache_enabled;

	/**
	 * Cache Duration
	 * How long are we caching data for?
	 * @var boolean
	 * @access private
	 */
	private $cache_duration;

	/**
	 * Cache Key
	 * Unique key for data.
	 * @var string
	 * @access private
	 */
	private $key;

	/**
	 * Cached
	 * Cached flag for data.
	 * @var boolean
	 * @access private
	 */
	private $cached = false;

	/**
	 * Init
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
	 * Check for cached data in transients.
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
	 * Cache data in transients.
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

/**
 * Logging Class
*/


class DEDO_Logging {

	/**
	 *	Init Logging
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Hooks
		add_action( 'ddownload_download_before', array( $this, 'save_success' ), 10, 1 );
	}

	/**
	 * Save Success Log
	 * @access public
	 * @return void
	 */
	public function save_success( $download_id ) {
		
		// Hook before log
		do_action( 'ddownload_save_success_before', $download_id );

		$log = array(
			'status'	=> 'success',
			'post_id'	=> $download_id
		);

		$this->insert_log( $log );

		// Hook after log
		do_action( 'ddownload_save_success_after', $download_id );
	}

	/**
	 * Save Blocked Log
	 *
	 * @access public
	 * @return void
	 */
	public function save_blocked( $download_id ) {
		
		// Hook before log
		do_action( 'ddownload_save_blocked_before', $download_id );
		$log = array(
			'status'	=> 'blocked',
			'post_id'	=> $download_id
		);
		$this->insert_log( $log );
		// Hook after log
		do_action( 'ddownload_save_blocked_after', $download_id );
	}

	/**
	 * Save Permission Log
	 * @access public
	 * @return void
	 */
	public function save_permission( $download_id ) {
		
		// Hook before log
		do_action( 'ddownload_save_permission_before', $download_id );

		$log = array(
			'status'	=> 'permission',
			'post_id'	=> $download_id
		);

		$this->insert_log( $log );

		// Hook after log
		do_action( 'ddownload_save_permission_after', $download_id );
	}	

	/**
	 * Insert Log
	 * @access public
	 * @return void
	 */
	public function insert_log( $log ) {
		
		global $wpdb, $dedo_options;

		// Hook before log
		do_action( 'ddownload_insert_log_before', $log );

		if ( ! $this->table_exists() ) {
			$this->setup_table();
		}

		if ( ! $this->table_exists() ) {
			return;
		}

		// Build log array
		$defaults = array(
			'post_id'	=> 0,
			'status'	=> 'success',
			'date'		=> current_time( 'mysql' ),
			'user_id'	=> get_current_user_id(),
			'ip_address'=> dedo_download_ip(),
			'agent'		=> sanitize_text_field( ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) )	
		);

		$log = wp_parse_args( $log, $defaults );
		if ( isset( $_GET[ 'sdownload' ] ) ) { $oneday = 'ONEDAYPASS | '; } else { $oneday='DOWNLOAD | '; }

		// Are we logging events for this user role?
		if ( false === $this->role_check( $log ) ) {
			return;
		}

		// Do we have a grace period?
		if ( true === $this->grace_period( $log ) ) {
			return;
		}

		// Prepare sql query
		$sql = $wpdb->prepare( "
				INSERT INTO $wpdb->ddownload_statistics (status, post_id, date, user_id, user_ip, user_agent)
				VALUES (%s, %d, %s, %d, %s, %s)
			",
			$log['status'],
			$log['post_id'],
			$log['date'],
			$log['user_id'],
			$this->prepare_ip_address( $log['ip_address'] ),
			$oneday . $log['agent'] 
		);

		// Run and update count if successfull
		if ( $wpdb->query( $sql ) && 'success' === $log['status'] ) {
			$count = get_post_meta( $log['post_id'], '_dedo_file_count', true );
			update_post_meta( $log['post_id'], '_dedo_file_count', ++$count );
			// log and increase another counter if oneday pass download
			if ( $oneday == 'ONEDAYPASS | ' ) {
				$scount = get_post_meta( $log['post_id'], '_dedo_oneday_count', true );
				update_post_meta( $log['post_id'], '_dedo_oneday_count', ++$scount );
			}
		}
		// Hook after log
		do_action( 'ddownload_insert_log_after', $log );
	}

	/**
	 * Role Check
	 * Are we logging events for this user role?
	 * @access public
	 * @return boolean
	 */
	public function role_check( $log ) {
		global $dedo_options;
		
		if ( current_user_can( 'administrator' ) && !$dedo_options['log_admin_downloads'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Grace Period
	 *
	 * Has a log of the same type been logged recently?
	 *
	 * @access public

	 * @return boolean
	 */
	public function grace_period( $log ) {
		global $wpdb, $dedo_options;

		if ( ! $this->table_exists() ) {
			return false;
		}

		if ( $dedo_options['grace_period'] == 1 ) {
			// Check for recent log of same download and status within grace period
			$sql = $wpdb->prepare( "
				SELECT ID FROM $wpdb->ddownload_statistics
				WHERE status = %s
					AND post_id = %d
					AND date > DATE_SUB(%s, INTERVAL %d MINUTE)
					AND user_ip = %s
			",
			$log['status'],
			$log['post_id'],
			$log['date'],
			$dedo_options['grace_period_duration'],
			$this->prepare_ip_address( $log['ip_address'] ) );

			if ( $wpdb->query( $sql ) ) {
				// We have a grace period
				return true;
			}
		}

		return false;
	}

	/**
	 *
	 * @param $ip_address
	 * @return string
	 * Check whether the statistics table exists.
	 * @access public
	 * @return bool
	 */
	public function table_exists() {
		global $wpdb;

		$wpdb->ddownload_statistics = $wpdb->prefix . 'ddownload_statistics';
		$table = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->ddownload_statistics ) );

		return ( $table === $wpdb->ddownload_statistics );
	}

	/**
	 * Create the statistics table when needed.
	 * @access public
	 * @return void
	 */
	public function setup_table() {
		global $wpdb;
		$wpdb->ddownload_statistics = $wpdb->prefix . 'ddownload_statistics';
		$sql = "
			CREATE TABLE {$wpdb->ddownload_statistics} (
				ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				status varchar(10) NOT NULL DEFAULT 'success',
				date datetime NOT NULL,
				post_id bigint(20) unsigned NOT NULL,
				user_id bigint(20) unsigned NOT NULL DEFAULT '0',
				user_ip varbinary(16) NOT NULL,
				user_agent varchar(255) NOT NULL,
			PRIMARY KEY  (ID)
			) DEFAULT CHARSET={$wpdb->charset};
		";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public function prepare_ip_address( $ip_address ) {
		// PHP versions prior to 5.3.0 on Windows did not support the inet_pton function
		if ( ! function_exists( 'inet_pton' ) ) {
			return '';
		}

		// Some servers pass an IP range, exploding suppresses PHP Warning
		$ip_address = explode( ',', $ip_address );

		return inet_pton( $ip_address[0] );
	}

}

// Initiate the logging system
$GLOBALS['dedo_logging'] = new DEDO_Logging();

/**
 * Statistics Class
 * @package  	Delightful Downloads
*/


class DEDO_Statistics {

	/**
	 *	Init Statistics
	 * @access public
	 * @return void
	 */
	public function __construct() {

		global $wpdb;

		// Add custom table to wpdb.
		$wpdb->ddownload_statistics = $wpdb->prefix . 'ddownload_statistics';
	}

	/**
	 * Get Logs
	 *
	 * Get logs, allows date range, type and limit to be specified.
	 *
	 * @access public
	 * @since 1.5
	 * @return string
	 */
	public function get_logs( $args = array() ) {
		global $wpdb;

		extract( wp_parse_args( $args, array(
			'status'	=> false,	
			'start'		=> false,
			'end'		=> false,
			'limit'		=> false
		) ) ) ;

		$sql = "SELECT * FROM $wpdb->ddownload_statistics";

		// Add where clause
		if ( $status ) {
			$sql .= $wpdb->prepare( " WHERE status = %s", $status );
		}
		else {
			$sql .= $wpdb->prepare( " WHERE 1 = %d", 1 );
		}

		// Start date
		if ( $start ) {
			$sql .= $wpdb->prepare( " AND date >= %s", $start );
		}

		// End date
		if ( $end ) {
			$sql .= $wpdb->prepare( " AND date <= %s", $end );
		}

		// Limit
		if ( $limit ) {
			$sql .= $wpdb->prepare( " LIMIT %d", $limit );
		}

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Count Downloads
	 * Count total downloads for all/single downloads/download. If a date range is set
	 * the statistics table is used. If not, the meta keys are used.
	 * Data is cached in transients.
	 * @access public
	 * @return string
	 */
	public function count_downloads( $args = array() ) {

		global $wpdb;

		// Parse arguments with defaults
		extract( wp_parse_args( $args, array(
			'days'			=> false,
			'download_id'	=> false,
			'cache'			=> true
		) ) );

		// Create cache key
		$key = 'dedo_count_days' . absint( $days ) . 'id' . absint( $download_id );

		$dedo_cache = new DEDO_Cache( $key );

		// Check for cached data
		if ( true == $cache && false !== ( $cached_data = $dedo_cache->get() ) ) {

			return $cached_data;
		}

		// Days set, convert to start date and pass to count_logs
		if ( $days ) {

			$start_date = $this->convert_days_date( $days );
			
			$result = $this->count_logs( array( 
				'download_id' => $download_id, 
				'start_date' => $start_date, 
				'status' => 'success' 
			) );
		}
		// No days set, sum up file count meta_value
		else {

			// Set query
			$sql = $wpdb->prepare( "
				SELECT SUM(meta_value)
				FROM $wpdb->postmeta
				WHERE meta_key = %s
			",
			'_dedo_file_count' );

			// Append download id
			if ( $download_id ) {

				$sql .= $wpdb->prepare( " AND post_id = %d", $download_id );
			}

			$result = $wpdb->get_var( $sql );
		}

		// Save to cache
		if ( true == $cache ) {
			
			$dedo_cache->set( $result );
		}

		return ( $result === NULL ) ? 0 : $result;
	}

	/**
	 * Count logs from statistics table.
	 * @access public
	 * @return string
	 */
	public function count_logs( $args = array() ) {

		global $wpdb;

		// Parse arguments with defaults
		extract( wp_parse_args( $args, array(
			'download_id'	=> false,
			'start_date'	=> false,
			'end_date'		=> false,
			'status'		=> false
		) ) );

		// Set main SQL query
		$sql = "
			SELECT COUNT(ID)
			FROM $wpdb->ddownload_statistics
		";

		// Append where clause for status
		if ( $status ) {

			$sql .= $wpdb->prepare( " WHERE status = %s", $status );
		}
		else {

			$sql .= $wpdb->prepare( " WHERE 1 = %d", 1 );
		}

		// Append download id
		if ( $download_id ) {

			$sql .= $wpdb->prepare( " AND post_id = %d", $download_id );
		}

		// Append start date
		if ( $start_date ) {

			$sql .= $wpdb->prepare( " AND date >= %s", $start_date );
		}

		// Append end date
		if ( $end_date ) {

			$sql .= $wpdb->prepare( " AND date <= %s", $end_date );
		}

		$result = $wpdb->get_var( $sql );

		return ( $result === NULL ) ? 0 : $result;
	}

	/**
	 * Get Popular Downloads
	 *
	 * Get popular downloads and order by download count.
	 * If days supplied use statistics table, else use download meta.
	 *
	 * Selecting by days is slow. Use responsibly!
	 * @access public
	 * @return array
	 */
	function get_popular_downloads( $args = array() ) {

		global $wpdb;

		// Parse arguments with defaults
		extract( wp_parse_args( $args, array(
			'days'		=> false,
			'limit'		=> 5,
			'cache'		=> true
		) ) );

		// Create cache key
		$key = 'dedo_popular_days' . absint( $days ) . 'limit' . absint( $limit );

		$dedo_cache = new DEDO_Cache( $key );

		// Check for cached data
		if ( true == $cache && false !== ( $cached_data = $dedo_cache->get() ) ) {

			return $cached_data;
		}

		// Days set, convet to start date and use statistics table
		if ( $days ) {

			$start_date = $this->convert_days_date( $days );

			$sql = $wpdb->prepare( "
				SELECT $wpdb->ddownload_statistics.post_id AS ID, COUNT( $wpdb->ddownload_statistics.ID ) AS downloads
				FROM $wpdb->ddownload_statistics
				WHERE $wpdb->ddownload_statistics.status = %s
					AND $wpdb->ddownload_statistics.date >= %s
				GROUP BY $wpdb->ddownload_statistics.post_id
				ORDER BY downloads DESC
				LIMIT %d
			",
			'success',
			$start_date,
			$limit );

			$result = $wpdb->get_results( $sql, ARRAY_A );

			// Get title for each download
			foreach ( $result as $key2 => $value ) {

				$result[$key2]['title'] = get_the_title( $result[$key2]['ID'] );
			}
		}
		// User meta_value file_count
		else {

			$sql = $wpdb->prepare( "
				SELECT $wpdb->posts.ID AS ID, $wpdb->posts.post_title AS title, $wpdb->postmeta.meta_value AS downloads
				FROM $wpdb->posts
				LEFT JOIN $wpdb->postmeta
					ON $wpdb->posts.ID = $wpdb->postmeta.post_id
				WHERE $wpdb->posts.post_type = %s
					AND $wpdb->posts.post_status = %s
					AND meta_key = %s
					AND meta_value > 0
				ORDER BY CAST( $wpdb->postmeta.meta_value AS unsigned ) DESC
				LIMIT %d
			",
			'dedo_download',
			'publish',
			'_dedo_file_count',
			$limit );

			$result = $wpdb->get_results( $sql, ARRAY_A );
		}

		// Save to cache
		if ( true == $cache ) {
			
			$dedo_cache->set( $result );
		}

		return $result;
	}

	/**
	 * Delete logs, oldest first.
	 * @access public
	 * @return string
	 */
	public function delete_logs( $args = array() ) {

		global $wpdb;

		// Parse arguments with defaults
		extract( wp_parse_args( $args, array(
			'start_date'	=> false,
			'end_date'		=> false,
			'limit'			=> false,
			'status'		=> false
		) ) );

		$sql = "
			DELETE FROM $wpdb->ddownload_statistics
			WHERE 1 = 1
		";

		// Append start date
		if ( $start_date ) {

			$sql .= $wpdb->prepare( " AND date > %s", $start_date );
		}

		// Append start date
		if ( $end_date ) {

			$sql .= $wpdb->prepare( " AND date < %s", $end_date );
		}

		// Append status
		if ( $status ) {

			$sql .= $wpdb->prepare( " AND status = %s", $status );
		}

		// Append orderby
		$sql .= " ORDER BY date ASC";

		// Append limit
		if ( $limit ) {

			$sql .= $wpdb->prepare( " LIMIT %d", $limit );
		}

		return $wpdb->query( $sql );
	}

	/**
	 * Converts number of days into current date minus days.
	 * @access public
	 * @return string
	 */
	public function convert_days_date( $days ) {

		$now = current_time( 'timestamp' );
		$timestamp = strtotime( '-' . $days . ' days', $now );

		return date( 'Y-m-d H:i:s', $timestamp );
	}	

	/**
	 * Check whether statistics table exists.
	 * @access public
	 * @return bool
	 */
	public function table_exists() {
		global $wpdb;

		$table_name = isset( $wpdb->ddownload_statistics ) ? $wpdb->ddownload_statistics : $wpdb->prefix . 'ddownload_statistics';
		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );

		return $found === $table_name;
	}

	/**
	 * Setup Statistics Table
	 * @access public
	 * @return void
	 */
	public function setup_table() {
		global $wpdb;
		$sql = "
			CREATE TABLE $wpdb->ddownload_statistics (
				ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				status varchar(10) NOT NULL DEFAULT 'success',
				date datetime NOT NULL,
				post_id bigint(20) unsigned NOT NULL,
				user_id bigint(20) unsigned NOT NULL DEFAULT '0',
				user_ip varbinary(16) NOT NULL,
				user_agent varchar(255) NOT NULL,
			PRIMARY KEY  (ID)
			) DEFAULT CHARSET=$wpdb->charset;
		";

		// Include our database function and run
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );
	}

	/**
	 * Empty Statistics Table
	 *
	 * @access public

	 * @return int/boolean (rows affected or false on error)
	 */
	public function empty_table() {
		
		global $wpdb;

		// Only admins allowed to empty table
		if ( !current_user_can( 'administrator' ) ) {
			return;
		}

		$sql = "TRUNCATE TABLE $wpdb->ddownload_statistics";

		return $wpdb->query( $sql );
	}

	/**
	 * Delete Statistics Table
	 * @access public
	 * @return int/boolean (rows affected or false on error)
	 */
	public function delete_table() {
		
		global $wpdb;

		// Only admins allowed to remove table
		if ( !current_user_can( 'administrator' ) ) {
			return;
		}

		$sql = "DROP TABLE IF EXISTS $wpdb->ddownload_statistics";

		return $wpdb->query( $sql );
	}	

}

// Initiate the logging system
$GLOBALS['dedo_statistics'] = new DEDO_Statistics();

/**
 * Delightful Downloads Cron
 * @subpackage  Includes/Cron
*/


/**
 * Register Cron Events
 */
function dedo_cron_register() {
	
	// Daily
	if ( !wp_next_scheduled( 'dedo_cron_daily' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'daily', 'dedo_cron_daily' );
	}	

	// Weekly
	if ( !wp_next_scheduled( 'dedo_cron_weekly' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'weekly', 'dedo_cron_weekly' );
	}	
}
add_action( 'admin_init', 'dedo_cron_register' );

/**
 * Daily Events
 *
 */
function dedo_cron_daily() {

	global $dedo_options, $dedo_statistics;

	// Delete old logs
	if ( $dedo_options['auto_delete'] == 1 ) {

		$date = $dedo_statistics->convert_days_date( $dedo_options['auto_delete_duration'] );
		$limit = apply_filters( 'dedo_cron_delete_limit', 1000 );

		$dedo_statistics->delete_logs( array( 'end_date' => $date, 'limit' => $limit ) );
	}

}
add_action( 'dedo_cron_daily', 'dedo_cron_daily' );

/**
 * Weekly Events
 */
function dedo_cron_weekly() {
	// Run folder protection
	dedo_folder_protection();
}
add_action( 'dedo_cron_weekly', 'dedo_cron_weekly' );

/**
 * Add Cron Schedules
 */
function dedo_cron_schedules( $schedules ) {
	// Adds once weekly to the existing schedules.
 	$schedules['weekly'] = array(
 		'interval' => 604800,
 		'display' => __( 'Once Weekly' )
 	);

 	return $schedules;
}
add_filter( 'cron_schedules', 'dedo_cron_schedules' );

/**
 * Delightful Downloads Functions
*/

/**
 * Append complete download details to the normal theme content on singular posts.
 * The active theme keeps full control of the single template and its own meta area.
 */
function dedo_append_singular_download_details( $content ) {
    if ( ! is_singular( 'dedo_download' ) || ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }

    $download_id = get_the_ID();
    if ( ! $download_id || ! dedo_download_valid( $download_id ) ) {
        return $content;
    }

    return $content . dedo_render_singular_download_details( $download_id );
}
add_filter( 'the_content', 'dedo_append_singular_download_details', 20 );

/**
 * Render the singular download information independently from compact shortcode cards.
 */
function dedo_render_singular_download_details( $id ) {
    $id = absint( $id );
    if ( ! $id || ! dedo_download_valid( $id ) ) {
        return '';
    }

    $is_folder = dedo_is_folder_download( $id );
    $file_url  = get_post_meta( $id, '_dedo_file_url', true );
    $file_path = $is_folder ? '' : dedo_get_abs_path( $file_url );
    $file_name = $is_folder ? basename( dedo_folder_path( $id ) ) : dedo_get_file_name( $file_url );
    $file_ext  = $is_folder ? 'DIR' : strtoupper( dedo_get_file_ext( $file_url ) );
    $mime      = $is_folder ? __( 'Folder', 'delightful-downloads' ) : dedo_get_file_mime( $file_url );

    $stored_size = (int) get_post_meta( $id, '_dedo_file_size', true );
    $actual_size = ( ! $is_folder && $file_path && is_file( $file_path ) ) ? (int) filesize( $file_path ) : $stored_size;
    $created     = get_post_time( 'U', false, $id, true ) - get_post_time( 'Z' );
    $modified    = dedo_get_file_modified_time( $id );
    $downloads   = (int) get_post_meta( $id, '_dedo_file_count', true );
    $total       = max( 0, (int) dedo_total_downloads() );
    $share       = $total > 0 ? round( $downloads / $total * 100 ) : 0;

    $status = post_password_required( $id ) ? __( 'Password protected', 'delightful-downloads' ) : __( 'Public download', 'delightful-downloads' );
    if ( $is_folder ) {
        $folder_protect = (int) get_post_meta( $id, '_dedo_folder_protect', true );
        $status = 2 === $folder_protect ? __( 'Protected - token required', 'delightful-downloads' ) : __( 'Public download', 'delightful-downloads' );
    }

    $download_url = $is_folder ? get_permalink( $id ) : dedo_download_link( $id );
    $icon_html    = $is_folder ? '<span class="dedo-icon dedo-icon--file" aria-hidden="true"></span>' : dedo_get_file_icon( $file_url );
    $thumb_html   = has_post_thumbnail( $id ) ? '<div class="dedo-single-preview">' . get_the_post_thumbnail( $id, 'medium' ) . '</div>' : '';

    $speed_title = '';
    $speed_main  = '';
    if ( $stored_size > 0 ) {
        foreach ( array( 16, 25, 50, 100, 200, 300, 500, 1000 ) as $speed ) {
            $seconds = floor( $stored_size * 8 / ( $speed * 1024 * 1024 ) );
            $s = $seconds % 60;
            $m = floor( ( $seconds % 3600 ) / 60 );
            $h = floor( ( $seconds % 86400 ) / 3600 );
            $formatted = ( $h > 0 ? $h . 'h ' : '' ) . ( $m > 0 ? $m . 'm ' : '' ) . $s . 's';
            $speed_title .= ( $speed_title ? "\n" : '' ) . $formatted . ' @ ' . $speed . ' MBit/s';
            if ( 300 === $speed ) {
                $speed_main = $formatted . ' @ 300 MBit/s';
            }
        }
    }

    // Compact facts inside the theme's native blockquote styling. No card/border override here.
    $facts = array();
    $facts[] = array( 'file', __( 'File name', 'delightful-downloads' ), esc_html( $file_name ) );
    $facts[] = array( 'file', __( 'File type', 'delightful-downloads' ), esc_html( $mime . ( $file_ext ? ' · ' . $file_ext : '' ) ) );
    $facts[] = array( 'calendar', __( 'Created', 'delightful-downloads' ), esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $created ) ) );
    $facts[] = array( 'clock', __( 'Modified', 'delightful-downloads' ), esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $modified ) . ' · ' . human_time_diff( $modified, current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'delightful-downloads' ) ) );

    if ( $actual_size > 0 ) {
        $size_value = size_format( $actual_size );
        if ( $stored_size > 0 && $actual_size !== $stored_size ) {
            $size_value .= ' (' . sprintf( __( 'stored: %s', 'delightful-downloads' ), size_format( $stored_size ) ) . ')';
        }
        $facts[] = array( 'file-size', __( 'File size', 'delightful-downloads' ), esc_html( $size_value ) );
    }
    if ( $speed_main ) {
        $facts[] = array( 'clock', __( 'Download time', 'delightful-downloads' ), '<span class="dedo-single-speed" title="' . esc_attr( $speed_title ) . '">' . esc_html( $speed_main ) . '</span>' );
    }

    $facts[] = array( 'download', __( 'Downloads', 'delightful-downloads' ), esc_html( number_format_i18n( $downloads ) . ( $total > 0 ? ' · ' . sprintf( __( '%d%% of all downloads', 'delightful-downloads' ), $share ) : '' ) ) );
    $is_protected = post_password_required( $id ) || ( $is_folder && isset( $folder_protect ) && 2 === $folder_protect );
    $facts[] = array( $is_protected ? 'lock' : 'unlock', __( 'Access', 'delightful-downloads' ), esc_html( $status ) );

    $categories = get_the_terms( $id, 'ddownload_category' );
    if ( $categories && ! is_wp_error( $categories ) ) {
        $links = array();
        foreach ( $categories as $term ) {
            $url = get_term_link( $term, 'ddownload_category' );
            if ( ! is_wp_error( $url ) ) {
                $links[] = '<a href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a>';
            }
        }
        if ( $links ) {
            $facts[] = array( 'filter', __( 'Categories', 'delightful-downloads' ), implode( ', ', $links ) );
        }
    }

    $tags = get_the_terms( $id, 'ddownload_tag' );
    if ( $tags && ! is_wp_error( $tags ) ) {
        $links = array();
        foreach ( $tags as $term ) {
            $url = get_term_link( $term, 'ddownload_tag' );
            if ( ! is_wp_error( $url ) ) {
                $links[] = '<a href="' . esc_url( $url ) . '">' . esc_html( $term->name ) . '</a>';
            }
        }
        if ( $links ) {
            $facts[] = array( 'hashtag', __( 'Tags', 'delightful-downloads' ), implode( ', ', $links ) );
        }
    }

    $details = '';
    foreach ( $facts as $fact ) {
        $details .= '<div class="dedo-single-detail-row">'
            . '<span class="dedo-single-detail-label"><span class="dedo-icon dedo-icon--' . esc_attr( $fact[0] ) . '" aria-hidden="true"></span><strong>' . esc_html( $fact[1] ) . '</strong></span>'
            . '<span class="dedo-single-detail-value">' . $fact[2] . '</span>'
            . '</div>';
    }

    if ( current_user_can( 'edit_post', $id ) ) {
        $details .= '<div class="dedo-single-detail-row">'
            . '<span class="dedo-single-detail-label"><span class="dedo-icon dedo-icon--edit" aria-hidden="true"></span><strong>' . esc_html__( 'Edit download', 'delightful-downloads' ) . '</strong></span>'
            . '<span class="dedo-single-detail-value"><a class="dedo-single-adminlink" href="' . esc_url( get_edit_post_link( $id ) ) . '">' . esc_html__( 'Edit download', 'delightful-downloads' ) . '</a></span>'
            . '</div>';
        $ticket = dedo_ticket_url( $id, 7 );
        if ( $ticket ) {
            $details .= '<div class="dedo-single-detail-row">'
                . '<span class="dedo-single-detail-label"><span class="dedo-icon dedo-icon--ticket" aria-hidden="true"></span><strong>' . esc_html__( '7-day ticket', 'delightful-downloads' ) . '</strong></span>'
                . '<span class="dedo-single-detail-value"><input type="text" class="copy-to-clipboard" value="' . esc_url( $ticket ) . '" readonly></span>'
                . '</div>';
        }
    }

    $id3 = dedo_search_replace_wildcards( '%id3tag%', $id );

    return '<div class="dedo-singular-download">'
        . '<div class="dedo-single-head">'
            . '<div class="dedo-single-icon">' . $icon_html . '</div>'
            . '<div class="dedo-single-primary"><strong class="dedo-single-name">' . esc_html( $file_name ) . '</strong><span class="dedo-single-type">' . esc_html( $mime . ( $file_ext ? ' · ' . $file_ext : '' ) ) . '</span></div>'
            . $thumb_html
            . '<a class="button page-numbers dedo-single-download-button noprint" href="' . esc_url( $download_url ) . '" rel="nofollow"><span class="dedo-icon dedo-icon--download" aria-hidden="true"></span> ' . esc_html__( 'download file', 'delightful-downloads' ) . '</a>'
        . '</div>'
        . '<blockquote class="dedo-single-details-box" aria-label="' . esc_attr__( 'Download details', 'delightful-downloads' ) . '"><div class="dedo-single-details-grid">' . $details . '</div></blockquote>'
        . $id3
        . '</div>';
}

// k,M,G T formatieren bei großen Zahlen
function dedo_number_format_short( $n ) {
    return dd_shared_number_format_short( $n );
}

// colorize heat after given value and return volor value
function dedo_heatcolor($visithotness) {
	$heatmapcols = array('#A9D0F5','#A9e2f3','#a9f5f2','#b2ff99','#ccff99','#e5ff99','#ffff99','#ffe599','#ffee99','#ffcc99','#ffb299aa','#ff9999aa','#D0A9F5aa','#F5A9F2aa');
	if ( $visithotness <=3 ) $hotcolor = $heatmapcols[0];
	else if ( $visithotness <=100 ) $hotcolor = $heatmapcols[floor(floor($visithotness) / 10)];
	else if ( $visithotness <=150 ) $hotcolor = $heatmapcols[11];
	else if ( $visithotness <=200 ) $hotcolor = $heatmapcols[12];
	else if ( $visithotness >200 ) $hotcolor = $heatmapcols[13];
	return $hotcolor;	
}

// Shortcode Styles
function dedo_get_shortcode_styles() {
    $format = '<article class="%class% dedo-list-item dedo-shortcode-card">
        <div class="dedo-list-row">
            <div class="dedo-list-icon-small">%icon%</div>
            <div class="dedo-list-content">
                <div class="dedo-card-body"><div class="dedo-card-main">
                    <div class="dedo-shortcode-title-row"><h6 class="dedo-list-title"><a href="%permalink%" rel="nofollow"><span class="dedo-icon dedo-icon--download" aria-hidden="true"></span> %title%</a></h6><a class="button page-numbers dedo-shortcode-download-button" href="%url%" rel="nofollow">' . __( 'download file', 'delightful-downloads' ) . '</a></div>
                    <div>%description%</div>
                </div>%thumb%</div>
                <div class="meta-icons dedo-meta-icons dedo-list-meta-bottom"><div class="meta-icons__bar noprint">%locked% %adminedit% %filename% %datesymbol% %filesize% %downloadtime% %count%</div><div class="meta-icons__terms">%category% %tags%</div></div>
            </div>
        </div>
    </article>';

    return apply_filters( 'dedo_get_styles', array(
        'download' => array( 'name' => __( 'Download', 'delightful-downloads' ), 'format' => $format ),
    ) );
}

/**
 * Returns List Styles
 */
function dedo_get_shortcode_lists() {
    $format = '<div class="dedo-list-row"><div class="dedo-list-icon-small">%icon%</div><div class="dedo-list-content"><div class="dedo-card-body"><div class="dedo-card-main"><h6 class="dedo-list-title"><a href="%url%" rel="nofollow"><span class="dedo-icon dedo-icon--download" aria-hidden="true"></span> %title%</a></h6><div>%description%</div></div>%thumb%</div><div class="meta-icons dedo-meta-icons dedo-list-meta-bottom"><div class="meta-icons__bar noprint">%locked% %adminedit% %filename% %datesymbol% %filesize% %downloadtime% %count%</div><div class="meta-icons__terms">%category% %tags%</div></div></div></div>';
    return apply_filters( 'dedo_get_lists', array( 'list' => array( 'name' => __( 'Downloads', 'delightful-downloads' ), 'format' => $format ) ) );
}

/**
 * Shortcode Buttons
 */
function dedo_get_shortcode_buttons() {
	
	$buttons =  array(
		'accent'		=> array(
			'name'		=> __( 'theme accent', 'delightful-downloads' ),
			'class'		=> 'page-numbers'
		),
		'black'		=> array(
			'name'		=> __( 'Black', 'delightful-downloads' ),
			'class'		=> 'button-black'
		),
		'grey'		=> array(
			'name'		=> __( 'Grey', 'delightful-downloads' ),
			'class'		=> 'button-grey'
		),
		'green'		=> array(
			'name'		=> __( 'Green', 'delightful-downloads' ),
			'class'		=> 'button-green'
		),
		'red'		=> array(
			'name'		=> __( 'Red', 'delightful-downloads' ),
			'class'		=> 'button-red'
		),
	);
	return apply_filters( 'dedo_get_buttons', $buttons );
}


// Get download-time for typical internet lines
function download_times($filesize) {
	$bbreite = array (25,50,100,200,300,500,1000,16);
	$outp = array();
	foreach ($bbreite as $value) {
		$time16 = floor($filesize * 8 / ($value*1024*1024));
		$s = floor($time16%60);
		$m = floor(($time16%3600)/60);
		$h = floor(($time16%86400)/3600);
		$outp[] = ($h>0 ? $h.'h ' :'').($m>0 ? $m.'m ' :'').$s.'s@'.$value.'MBit';
	}	
	if ($s > 0) $s=1;
	$dtime = '<a class="dedo-meta-chip dedo-meta-chip--neutral" title="'.implode("\n", $outp).'"><span class="dedo-icon dedo-icon--clock" aria-hidden="true"></span> '.$outp[4].'</a>';
	return $dtime;
}


/**
 * Create a signed, time-limited download ticket URL.
 *
 * @param int $download_id Download post ID.
 * @param int $valid_days  Ticket validity in calendar days, starting today.
 * @return string
 */
function dedo_ticket_url( $download_id, $valid_days ) {
    $download_id = absint( $download_id );
    $valid_days  = absint( $valid_days );

    if ( ! in_array( $valid_days, array( 7, 365 ), true ) ) {
        return '';
    }

    $today   = new DateTimeImmutable( 'today', wp_timezone() );
    $expires = $today->modify( '+' . ( $valid_days - 1 ) . ' days' )->format( 'Ymd' );
    $payload = $download_id . '|' . $valid_days . '|' . $expires;
    $code    = hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );

    return add_query_arg(
        array(
            'sdownload' => $download_id,
            'valid'     => $valid_days,
            'expires'   => $expires,
            'code'      => $code,
        ),
        home_url( '/' )
    );
}

/**
 * Validate a signed download ticket.
 *
 * Legacy one-day tickets for the current day remain valid for compatibility.
 *
 * @param int $download_id Download post ID.
 * @return bool
 */
function dedo_ticket_is_valid( $download_id ) {
    $download_id = absint( $download_id );
    $code        = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
    $valid_days  = isset( $_GET['valid'] ) ? absint( $_GET['valid'] ) : 0;
    $expires     = isset( $_GET['expires'] ) ? sanitize_text_field( wp_unslash( $_GET['expires'] ) ) : '';

    // Backwards compatibility for the former ticket valid until midnight today.
    if ( 0 === $valid_days && '' === $expires ) {
        $today       = new DateTimeImmutable( 'now', wp_timezone() );
        $legacy_code = md5( intval( $download_id ) + intval( $today->format( 'Ymd' ) ) );
        return hash_equals( $legacy_code, $code );
    }

    if ( ! in_array( $valid_days, array( 7, 365 ), true ) || ! preg_match( '/^\d{8}$/', $expires ) ) {
        return false;
    }

    $expiry_date = DateTimeImmutable::createFromFormat( '!Ymd', $expires, wp_timezone() );
    if ( ! $expiry_date || $expiry_date->format( 'Ymd' ) !== $expires ) {
        return false;
    }

    $today      = new DateTimeImmutable( 'today', wp_timezone() );
    $start_date = $expiry_date->modify( '-' . ( $valid_days - 1 ) . ' days' );
    if ( $today < $start_date || $today > $expiry_date ) {
        return false;
    }

    $payload       = $download_id . '|' . $valid_days . '|' . $expires;
    $expected_code = hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );

    return hash_equals( $expected_code, $code );
}

// Replace Wildcards
 function dedo_search_replace_wildcards( $string, $id ) {
	global $wpdb;
 	//adminedit
 	if ( strpos( $string, '%adminedit%' ) !== false ) {
 		if(current_user_can('administrator')) {
			if ( is_singular() && in_the_loop() ) {
                $ticket_url = dedo_ticket_url( $id, 7 );
				$oneday = '<input type="text" title="7-Tage-Ticket ab heute" class="copy-to-clipboard dedo-copy-field" value="' . esc_url( $ticket_url ) . '" readonly> &nbsp;';
				$oneday .= '<p class="dedo-copy-feedback">' . __( 'Download ticket copied to clipboard.', 'delightful-downloads' ) . '</p>';
			} else $oneday='';
			$string = str_replace( '%adminedit%', ' <a href="'. get_home_url() . '/wp-admin/post.php?post='.$id.'&action=edit"><span class="dedo-icon dedo-icon--edit" title="'. __( 'edit this download', 'delightful-downloads' ) . '" aria-hidden="true"></span></a> &nbsp; '.$oneday, $string );
		} else {
			$string = str_replace( '%adminedit%', '', $string );
		}
 	}
	// id
 	if ( strpos( $string, '%id%' ) !== false ) {
 		$string = str_replace( '%id%', $id, $string );
 	}
 	// url
 	if ( strpos( $string, '%url%' ) !== false ) {
 		$value = dedo_is_folder_download( $id ) ? get_permalink( $id ) : dedo_download_link( $id );
 		$string = str_replace( '%url%', $value, $string );
 	}
 	// title
 	if ( strpos( $string, '%title%' ) !== false ) {
 		$value = get_the_title( $id );
 		$string = str_replace( '%title%', $value, $string );
 	}
 	// Kategorie (erste)
 	if ( strpos( $string, '%category%' ) !== false ) {
		$post_terms = get_the_terms( $id, 'ddownload_category' );
		if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {
			$term_link = get_term_link( $post_terms[0], 'ddownload_category' );
			$value = is_wp_error( $term_link ) ? '' : '<a class="dedo-meta-chip dedo-meta-chip--category" href="' . esc_url( $term_link ) . '">' . esc_html( $post_terms[0]->name ) . '</a>';
		} else {
			$value = '';
		}
		$string = str_replace( '%category%', $value, $string );
 	}
 	// Tags
 	if ( strpos( $string, '%tags%' ) !== false ) {
		$value = '';
		$post_terms = get_the_terms( $id, 'ddownload_tag' );
		if ( $post_terms && ! is_wp_error( $post_terms ) ) {
			foreach ( $post_terms as $term ) {
				$term_link = get_term_link( $term, 'ddownload_tag' );
				if ( ! is_wp_error( $term_link ) ) {
					$value .= '<a class="dedo-meta-chip dedo-meta-chip--tag" href="' . esc_url( $term_link ) . '">' . esc_html( $term->name ) . '</a>';
				}
			}
		}
		$string = str_replace( '%tags%', $value, $string );
 	}
 	// permalink single cpost
 	if ( strpos( $string, '%permalink%' ) !== false ) {
		$value = get_the_permalink($id);
 		$string = str_replace( '%permalink%', $value, $string );
 	}
 	// manual excerpt
 	if ( strpos( $string, '%manexcerpt%' ) !== false ) {
		if ( post_password_required( $id) ) {
			global $post;
			$post = get_post ( $id );
			$value = $post->post_excerpt;
		} else if (has_excerpt( $id )) $value = get_the_excerpt( $id );   // if manual excerpt exists
		else $value='';
 		$string = str_replace( '%manexcerpt%', $value, $string );
 	}
	 
	// Wenn MP3, dann ID3-Infos ausgeben
 	if ( strpos( $string, '%id3tag%' ) !== false ) {
		$mime_type = dedo_get_file_mime( get_post_meta( $id, '_dedo_file_url', true ) );
		if ($mime_type == 'audio/mpeg') {
			$mp3url = get_post_meta( $id, '_dedo_file_url', true );
			$mp3path = dedo_get_abs_path( $mp3url );
			$mp3filename = dedo_get_file_name( get_post_meta( $id, '_dedo_file_url', true ) );
			// Musicplayer
			$musifile = $mp3path;
			if (file_exists($musifile)) {
				require_once( ABSPATH . 'wp-admin/includes/media.php' );
				$musiurl = $mp3url;
				$filename = basename($musiurl);
				$meta = wp_read_audio_metadata( $musifile );
				$phtml = '<div class="timeline" style="border:1px dashed #ccc;display:grid;grid-template-columns:5fr 96px"><div>';
				// Player nur, wenn nicht password protected
				if ( !post_password_required( $id) ) $phtml .= '<audio class="noprint" controlsList="nodownload" style="width:100%" controls src="'.$musiurl.'" preload="metadata"></audio>';
				$phtml .='<span style="font-size:.9em;font-style:italic">';
				// Metadata Ausgabe auch für penguin podcasts template und für audio template --------------
				if (isset($meta['title'])) $phtml .= '<span class="dedo-icon dedo-icon--ticket" aria-hidden="true"></span> <b>'.$meta['title'].'</b>';
				if (isset($meta['artist'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--user" aria-hidden="true"></span> '.$meta['artist'];
				if (isset($meta['album'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--disc" aria-hidden="true"></span> ' . $meta['album'];
				if (isset($meta['track_number'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--hashtag" aria-hidden="true"></span> ' . $meta['track_number'];
				if (isset($meta['year'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--calendar" aria-hidden="true"></span> '.$meta['year'];
				if (isset($meta['genre'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--music" aria-hidden="true"></span> ' . $meta['genre'];
				if (isset($meta['length_formatted'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--clock" aria-hidden="true"></span> ' . $meta['length_formatted'];
				if (isset($meta['composer'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--music" aria-hidden="true"></span> ' . $meta['composer'];
				if (isset($meta['band'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--users" aria-hidden="true"></span> ' . $meta['band'];
				if (isset($meta['filesize'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--file-size" aria-hidden="true"></span> ' . dd_shared_number_format_short($meta['filesize']);
				if (isset($meta['part_of_a_set'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--set" aria-hidden="true"></span> ' . $meta['part_of_a_set'];
				if (isset($meta['encoder_options'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--settings" aria-hidden="true"></span> ' . $meta['encoder_options'];
				if (isset($meta['channelmode'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--microphone" aria-hidden="true"></span> ' . $meta['channelmode'];
				if (isset($meta['publisher'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--newspaper" aria-hidden="true"></span> ' . $meta['publisher'];
				if (isset($meta['comment'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--comment" aria-hidden="true"></span> ' . $meta['comment'];
				if ( !post_password_required( $id) && !empty(@$meta['unsynchronised_lyric'])) $phtml .= ' <span class="dedo-icon dedo-icon--spaced dedo-icon--text" aria-hidden="true"></span> ' . $meta['unsynchronised_lyric'];
				$phtml .= '</span></div><div>';
				if (!empty($meta['image']['data'])) $phtml .= '<img style="width:96px" src="data:'.$meta['image']['mime'].';charset=utf-8;base64,'.base64_encode($meta['image']['data']).'">';				$phtml .= '</div></div>';
			} else $phtml = '';		
			$value = $phtml;
		} else $value='';	
		$string = str_replace( '%id3tag%', $value, $string );
	}
	
	// beschreibung
 	if ( strpos( $string, '%description%' ) !== false ) {
		if ( post_password_required( $id) ) {
			global $post;
			$post = get_post ( $id );
			$value = $post->post_excerpt;
		} else $value = get_the_excerpt( $id );
 		$string = str_replace( '%description%', $value, $string );
 	}
	// post thumbnail - Beitragsbild mit img-zoom on hover
 	if ( strpos( $string, '%thumb%' ) !== false ) {
 		if ( has_post_thumbnail( $id ) ) {
 			$value = '<div class="dedo-thumb-inline"><img src="' . get_the_post_thumbnail_url( $id, 'medium' ) . '"></div>';
 		} else {
 			$value = '';
 		}
 		$string = str_replace( '%thumb%', $value, $string );
 	}
 	// file-date created modified und postdatum ändern, wenn Datei per sftp neuer im Dateisystem
 	if ( strpos( $string, '%filedate%' ) !== false ) {
 		if (!empty( get_post_meta( $id, '_dedo_file_url', true ) )) {
			$fpath = dedo_get_abs_path(get_post_meta( $id, '_dedo_file_url', true));
			$value = ( $fpath && is_file( $fpath ) ) ? dd_shared_colordatebox( filectime($fpath), filemtime($fpath) ,NULL,1) : '';
			// Post modified Datum aktualisieren, wenn File - Anhang neuer
			if ( $fpath && is_file( $fpath ) && filemtime($fpath) > get_the_modified_time('U', false, $id, true) - get_the_modified_time('Z') ) {
				$mysql_time_format= "Y-m-d H:i:s";
				$post_modified = wp_date( $mysql_time_format, filemtime($fpath) );
				$post_modified_gmt = gmdate( $mysql_time_format, ( filemtime($fpath) + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS )  );
				$wpdb->query("UPDATE $wpdb->posts SET post_modified = '{$post_modified}', post_modified_gmt = '{$post_modified_gmt}'  WHERE ID = {$id}" );
			}
		} else { $value='';  }	
		$string = str_replace( '%filedate%', $value, $string );
 	}
	// datesymbol Datum, farbig mit symbol und allen created und mod date.
 	if ( strpos( $string, '%datesymbol%' ) !== false ) {
		$erstelldat = get_post_time('U', false, $id, true) - get_post_time('Z');
		$moddat = dedo_get_file_modified_time( $id );
		$value = dd_shared_colordatebox( $erstelldat, $moddat, NULL, 1);
		$string = str_replace( '%datesymbol%', $value, $string );
 	}
	// dateago   - so viele Tage wochen her, sonntags rot, samstags orange
 	if ( strpos( $string, '%dateago%' ) !== false ) {
		$erstelldat = get_post_time('U', false, $id, true) - get_post_time('Z');
		$moddat = dedo_get_file_modified_time( $id );
		$value = dd_shared_colordatebox( $erstelldat, $moddat, NULL, 2);
		$string = str_replace( '%dateago%', $value, $string );
 	}
 	// filesize
 	if ( strpos( $string, '%filesize%' ) !== false ) {
		if ( dedo_is_folder_download( $id ) ) {
			$value = '<span class="dedo-meta-chip dedo-meta-chip--neutral">📁 ' . size_format( (int) get_post_meta( $id, '_dedo_file_size', true ), 1 ) . '</span>';
			$string = str_replace( '%filesize%', $value, $string );
		} else {
			$fpath = dedo_get_abs_path( get_post_meta( $id, '_dedo_file_url', true ) );
			$fsfrommeta = size_format( (int) get_post_meta( $id, '_dedo_file_size', true ), 0 );
			$fsfromfile = ( $fpath && is_file( $fpath ) ) ? size_format( (int) filesize( $fpath ) ) : '';
		if (!empty( get_post_meta( $id, '_dedo_file_size', true ) )) {
			$value = '<span class="dedo-meta-chip dedo-meta-chip--neutral"><span class="dedo-icon dedo-icon--save" title="filesize: '.$fsfrommeta.'" aria-hidden="true"></span>'.$fsfromfile.'</span>';
		} else { $value='';  }	
		$string = str_replace( '%filesize%', $value, $string );
        }
 	}
 	// downloadtime
 	if ( strpos( $string, '%downloadtime%' ) !== false ) {
 		$value = download_times(intval(get_post_meta( $id, '_dedo_file_size', true )));
 		$string = str_replace( '%downloadtime%', $value, $string );
 	}
 	// downloads (count)
 	if ( strpos( $string, '%count%' ) !== false ) {
		$filedlc = get_post_meta( $id, '_dedo_file_count', true );
		$totaldownloads = dedo_total_downloads();
		if ($totaldownloads > 0) {
			$perctotal = round( (int) $filedlc / $totaldownloads * 100) ;
			// Wenn heatcolor aus penguin_mod vorhanden
			$hotcolor = dedo_heatcolor($perctotal);
		} else { $perctotal = 0; $hotcolor = '#fff'; }
		$filedlc = (int) $filedlc;
		$fullcounter = number_format_i18n( $filedlc );
		$shortcounter = dedo_number_format_short( $filedlc );
 		$value = '<span title="DLCounter: '.$fullcounter.' Ranking: '.$perctotal.'%" class="dedo-meta-chip" style="--dedo-chip-bg:'.$hotcolor.'" ><span class="dedo-icon dedo-icon--download" aria-hidden="true"></span> ' . $shortcounter .'</span>';
 		$string = str_replace( '%count%', $value, $string );
 	}
 	// raw file name (for structured singular layouts)
 	if ( strpos( $string, '%filenameplain%' ) !== false ) {
        $value = dedo_is_folder_download( $id ) ? basename( dedo_folder_path( $id ) ) : dedo_get_file_name( get_post_meta( $id, '_dedo_file_url', true ) );
        $string = str_replace( '%filenameplain%', esc_html( $value ), $string );
    }
	// raw file extension (for structured singular layouts)
 	if ( strpos( $string, '%extplain%' ) !== false ) {
        $value = dedo_is_folder_download( $id ) ? 'DIR' : strtoupper( dedo_get_file_ext( get_post_meta( $id, '_dedo_file_url', true ) ) );
        $string = str_replace( '%extplain%', esc_html( $value ), $string );
    }
	// file name
 	if ( strpos( $string, '%filename%' ) !== false ) {
 		$value = dedo_is_folder_download( $id ) ? '<span class="dedo-meta-chip dedo-meta-chip--neutral">📁 ' . esc_html( basename( dedo_folder_path( $id ) ) ) . '</span>' : '<span title="Dateiname" class="dedo-meta-chip dedo-meta-chip--neutral"><span class="dedo-icon dedo-icon--file" aria-hidden="true"></span> ' . dedo_get_file_name( get_post_meta( $id, '_dedo_file_url', true ) ).'</span>';
 		$string = str_replace( '%filename%', $value, $string );
 	}
 	// protected file
 	if ( strpos( $string, '%locked%' ) !== false ) {
        if ( dedo_is_folder_download( $id ) && function_exists( 'dedo_folder_status_icon' ) ) {
            $folder_protect = (int) get_post_meta( $id, '_dedo_folder_protect', true );
            $value = dedo_folder_status_icon( 2 === $folder_protect, 2 === $folder_protect ? __( 'Protected - token required', 'delightful-downloads' ) : __( 'Download available', 'delightful-downloads' ) );
        } elseif ( post_password_required( $id ) ) {
			$value='<span class="dedo-icon dedo-icon--lock dedo-icon--danger" title="Kennwortgeschützt" aria-hidden="true"></span>';
		} else {
			$value='<span class="dedo-icon dedo-icon--unlock" title="öffentlich" aria-hidden="true"></span>';
		}
 		$string = str_replace( '%locked%', $value, $string );
 	}
 	// file extension
 	if ( strpos( $string, '%ext%' ) !== false ) {
 		$value = dedo_is_folder_download( $id ) ? 'DIR' : '<span class="dedo-icon dedo-icon--file" title="filename" aria-hidden="true"></span> '.strtoupper( dedo_get_file_ext( get_post_meta( $id, '_dedo_file_url', true ) ) );
 		$string = str_replace( '%ext%', $value, $string );
 	}
  	// file icon
 	if ( strpos( $string, '%icon%' ) !== false ) {
 		$ffile = ( get_post_meta( $id, '_dedo_file_url', true ) );
		$value = dedo_is_folder_download( $id ) ? '<a class="dedo-folder-icon" href="' . esc_url( get_permalink($id) ) . '">📁</a>' : ' <a href="'.get_the_permalink($id).'">'.dedo_get_file_icon( $ffile ).'</a>';
 		$string = str_replace( '%icon%', $value, $string );
 	}
 	// file mime
 	if ( strpos( $string, '%mime%' ) !== false ) {
 		$value = dedo_get_file_mime( get_post_meta( $id, '_dedo_file_url', true ) );
 		$string = str_replace( '%mime%', $value, $string );
 	}
 	return apply_filters( 'dedo_search_replace_wildcards', $string, $id );
 }

/**
 * Download Link * Generate download link based on provided id.
 */
function dedo_download_link( $id ) {
	global $dedo_options;
	$output = esc_html( home_url( '?' . $dedo_options['download_url'] . '=' . $id ) );
	return apply_filters( 'dedo_download_link', $output );
}

// Check for valid download
function dedo_download_valid( $download_id ) {
	$download_id = absint( $download_id );

	if ( $download = get_post( $download_id, ARRAY_A ) ) {
		
		if ( $download['post_type'] == 'dedo_download' && $download['post_status'] == 'publish' ) {
			return true;
		}
	}
	return false;
}

/**
 * Check user has permission to download file
 */
function dedo_download_permission( $options ) {
	global $dedo_options;
	// First check per-download settings, else revert to global setting
	$members_only = ( isset( $options['members_only'] ) ) ? $options['members_only'] : $dedo_options['members_only'];
	if ( $members_only ) {
		// Check user is logged in
		if ( is_user_logged_in() ) {
			return true;
		}
		else {
			return false;
		}
	}
	return true;
}

/**
 * Check if user is blocked
 */
function dedo_download_blocked( $current_agent ) {
	// Retrieve user agents
	$user_agents = dedo_get_agents();
	if ( ! $user_agents ) {
		return true;
	}
	foreach ( $user_agents as $user_agent ) {
		$current_agent = trim( strtolower( $current_agent ) );
		$user_agent    = trim( strtolower( $user_agent ) );

		if ( empty( $current_agent ) || empty( $user_agent ) ) {
			return true;
		}

		if ( false !== strpos( $current_agent, $user_agent ) ) {
			return false;
		}	
	}
	return true;
}

/**
 * Get blocked user agents
 */
function dedo_get_agents() {
	global $dedo_options;
	$crawlers = $dedo_options['block_agents'];
	if ( empty( $crawlers ) ) {
		return array();
	}
	$crawlers = explode( "\n", $crawlers );
	return $crawlers;
}

/**
 * Get users IP Address
 */
function dedo_download_ip() {
	if ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip_address = sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] );
	} 
	elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip_address = sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] );
	} 
	else {
		$ip_address = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
	}
	// letzte Stelle der IP anonymisieren (0 setzen)	
	// $ip_address = long2ip(ip2long($ip_address) & 0xFFFFFF00);
	return $ip_address;
}

/**
 * Get file mime type based on file extension
 */
function dedo_download_mime( $path ) {
	// Strip path, leave filename and extension
	$file = explode( '/', $path );
	$file = strtolower( end( $file ) );
	$filetype = wp_check_filetype( $file );	
	return $filetype['type'];
}

/**
 * Return various upload dirs/urls for Delightful Downloads.
 * @param string $return
 * @param string $upload_dir
 * @return string
 */
function dedo_get_upload_dir( $return = '', $upload_dir = '' ) {
	global $dedo_options;
	$upload_dir = ( $upload_dir === '' ? wp_upload_dir() : $upload_dir );
	$directory  = $dedo_options['upload_directory'];
	$upload_dir['path']         = trailingslashit( $upload_dir['basedir'] ) . $directory . $upload_dir['subdir'];
	$upload_dir['url']          = trailingslashit( $upload_dir['baseurl'] ) . $directory . $upload_dir['subdir'];
	$upload_dir['dedo_basedir'] = trailingslashit( $upload_dir['basedir'] ) . $directory;
	$upload_dir['dedo_baseurl'] = trailingslashit( $upload_dir['baseurl'] ) . $directory;
	switch ( $return ) {
		default:
			return $upload_dir;
			break;
		case 'path':
			return $upload_dir['path'];
			break;
		case 'url':
			return $upload_dir['url'];
			break;
		case 'subdir':
			return $upload_dir['subdir'];
			break;
		case 'basedir':
			return $upload_dir['basedir'];
			break;
		case 'baseurl':
			return $upload_dir['baseurl'];
			break;
		case 'dedo_basedir':
			return $upload_dir['dedo_basedir'];
			break;
		case 'dedo_baseurl':
			return $upload_dir['dedo_baseurl'];
			break;
	}
}

/**
 * Set the upload dir for Delightful Downloads.
 */
function dedo_set_upload_dir( $upload_dir ) {
    return dedo_get_upload_dir( '', $upload_dir );
}

/**
 * Protect uploads dir from direct access
 */
function dedo_folder_protection( $folder_protection = '' ) {
	global $dedo_options;
	// Allow custom options to be passed, set to save options if not
	$folder_protection = ( '' === $folder_protection ) ? $dedo_options['folder_protection'] : $folder_protection;
	// Get delightful downloads upload base path
	$upload_dir = dedo_get_upload_dir( 'dedo_basedir' );
	// Create upload dir if needed, return on fail. Causes fatal error on activation otherwise
	if ( !wp_mkdir_p( $upload_dir ) ) {
		return;
	}
	// Add htaccess protection if enabled, else delete it
	if ( 1 == $folder_protection ) {
		if ( !file_exists( $upload_dir . '/.htaccess' ) && wp_is_writable( $upload_dir ) ) {
			$content = "Options -Indexes\n";
			$content .= "deny from all";

			@file_put_contents( $upload_dir . '/.htaccess', $content );
		}
	}
	else {
		if ( file_exists( $upload_dir . '/.htaccess' ) && wp_is_writable( $upload_dir ) ) {
			@unlink( $upload_dir . '/.htaccess' );
		}
	}
	// Check for root index.php
	if ( !file_exists( $upload_dir . '/index.php' ) && wp_is_writable( $upload_dir ) ) {
		@file_put_contents( $upload_dir . '/index.php', '<?php' . PHP_EOL . '// You shall not pass!' );
	}
	// Check subdirs for index.php
	$subdirs = dedo_folder_scan( $upload_dir );

	foreach ( $subdirs as $subdir ) {
		if ( !file_exists( $subdir . '/index.php' ) && wp_is_writable( $subdir ) ) {
			@file_put_contents( $subdir . '/index.php', '<?php' . PHP_EOL . '// You shall not pass!' );
		}
	}
}

/**
 * Scan dir and return subdirs
 */
function dedo_folder_scan( $dir ) {
	// Check class exists
	if ( class_exists( 'RecursiveDirectoryIterator' ) ) {
		// Setup return array
		$return = array();
		$iterator = new RecursiveDirectoryIterator( $dir );
		// Loop through results and add uniques to return array
		foreach ( new RecursiveIteratorIterator( $iterator ) as $file ) {
			if ( !in_array( $file->getPath(), $return ) ) {	
				$return[] = $file->getPath();
			}
		}
		return $return;
	}
	return false;
}

/**
 * Get Downloads Filesize
 * Returns the total filesize of all files.
 */
function dedo_get_filesize( $download_id = false ) {
	global $wpdb;
	$sql = $wpdb->prepare( "
		SELECT SUM( meta_value )
		FROM $wpdb->postmeta
		WHERE meta_key = %s
	", 
	'_dedo_file_size' );
	if ( $download_id ) { $sql .= $wpdb->prepare( " AND post_id = %d", $download_id ); }
	$return = $wpdb->get_var( $sql );
	return ( NULL !== $return ) ? $return : 0;
}

/**
 * Delete All Transients
 * Deletes all transients created by Delightful Downloads
 */
function dedo_delete_all_transients() {
	global $wpdb;
	$sql = $wpdb->prepare( "
		DELETE FROM $wpdb->options
		WHERE option_name LIKE %s
		OR option_name LIKE %s
		OR option_name LIKE %s
		OR option_name LIKE %s
		", 
		'\_transient\_delightful-downloads%%', 
		'\_transient\_timeout\_delightful-downloads%%',
		'\_transient\_dedo_%%',
		'\_transient\_timeout\_dedo_%%' );
	$wpdb->query( $sql );
}

/**
 * Get Absolute Path
 * Searches various locations for download file.
 * It is always recommended that the file should be within /wp-content
 * otherwise it can't be guaranteed that the file will be found.
 * Also allows absolute path to store files outsite the document root.
 */
function dedo_get_abs_path( $requested_file ) {
	$requested_file = (string) $requested_file;
	// Folder downloads are virtual sources, never filesystem/URL wrappers.
	if ( 0 === strpos( $requested_file, 'folder://' ) ) {
		return false;
	}
	$parsed_file = parse_url( $requested_file );
	// Check for absolute path
	if ( ( !isset( $parsed_file['scheme'] ) || !in_array( $parsed_file['scheme'], array( 'http', 'https' ) ) ) && isset( $parsed_file['path'] ) && file_exists( $requested_file ) ) {
		$file = $requested_file;
	}
	// Falls within wp_content
	else if ( strpos( $requested_file, WP_CONTENT_URL ) !== false ) {
		$file_path = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $requested_file );
		$file = realpath( $file_path );
	}
	// Falls in multisite
	else if ( is_multisite() && !is_main_site() && strpos( $requested_file, network_site_url() ) !== false ) {
		$site_url = trailingslashit( site_url() );
		$file_path = str_replace( $site_url, ABSPATH, $requested_file );
		$site_url = trailingslashit( network_site_url() );
		$file_path = str_replace( $site_url, ABSPATH, $file_path );
		$file = realpath( $file_path );
	}
	// Falls within WordPress directory structure
	else if ( strpos( $requested_file, site_url() ) !== false ) {
		$site_url = trailingslashit( site_url() );
		$file_path = str_replace( $site_url, ABSPATH, $requested_file );

		$file = realpath( $file_path );
	}
	// Falls outside WordPress structure but within document root.
	else if ( strpos( $requested_file, site_url() ) && file_exists( $_SERVER['DOCUMENT_ROOT'] . $parsed_file['path'] ) ) {
		$file_path = $_SERVER['DOCUMENT_ROOT'] . $parsed_file['path'];
		
		$file = realpath( $file_path );
	}
	// Checks file exists
	if ( isset( $file ) && is_file( $file ) ) {
		return $file;
	}
	else {
		return false;
	}
}

/**
 * Get File Name
 * Strips the filename from a URL or path.
 * @param string $path File path/url of filename.
 * @return string Value of file name with extension.
 */
function dedo_get_file_name( $path ) {
	return basename( $path );
}

/**
 * Get File Mime
 * Get the file mime type from the file path using WordPress
 * built in filetype check.
 * @param string $path File path/url of filename.
 * @return string Value of file mime.
 */
function dedo_get_file_mime( $path ) {
	$file = wp_check_filetype( $path, array_merge(get_allowed_mime_types(),array('html' => 'text/html')) );
	return $file['type'];
}

/**
 * Get File Extension
 * Get the file extension from the file path using WordPress
 * built in filetype check.
 * @param string $path File path/url of filename.
 * @return string Value of file extension.
 */
function dedo_get_file_ext( $path ) {
	$file = wp_check_filetype( $path, array_merge(get_allowed_mime_types(),array('html' => 'text/html')) );
	return $file['ext'];
}

/**
 * Get File Status
 * Checks whether a file is accessible, either locally or remotely.
 * @param string $url File path/url of filename.
 * @return boolean/array.
 */
function dedo_get_file_status( $url ) {
	$url = is_string( $url ) ? trim( $url ) : '';

	if ( '' === $url || 0 === strpos( $url, 'folder://' ) ) {
		return false;
	}

	// Check locally
	if( $file = dedo_get_abs_path( $url ) ) {
		$type = 'local';
		$size = @filesize( $file );
	}
	else {
		// Cache the status check itself so repeated admin loads do not hammer
		// remote file hosts. The previous cache was populated only after the
		// request and therefore could not prevent repeated requests.
		$cache_key = 'dedo_remote_status_' . md5( $url );
		$cached    = get_transient( $cache_key );

		if ( is_array( $cached ) && isset( $cached['checked'] ) ) {
			if ( empty( $cached['ok'] ) ) {
				return false;
			}

			return array(
				'type' => 'remote',
				'size' => $cached['size'],
			);
		}

		$request_args = apply_filters( 'dedo_remote_status_request_args', array(
			'timeout'             => 8,
			'redirection'         => 3,
			'reject_unsafe_urls'  => true,
			'user-agent'          => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' ),
		) );

		$response = wp_remote_head( $url, $request_args );

		if ( is_wp_error( $response ) ) {
			set_transient( $cache_key, array( 'checked' => true, 'ok' => false ), 300 );
			return false;
		}

		$status_code = (int) wp_remote_retrieve_response_code( $response );

		// Back off longer when the remote host explicitly rate-limits us.
		if ( 429 === $status_code ) {
			set_transient( $cache_key, array( 'checked' => true, 'ok' => false ), 900 );
			return false;
		}

		if ( $status_code < 200 || $status_code >= 400 ) {
			set_transient( $cache_key, array( 'checked' => true, 'ok' => false ), 300 );
			return false;
		}

		$content_length = wp_remote_retrieve_header( $response, 'content-length' );
		if ( '' === $content_length || ! is_numeric( $content_length ) ) {
			set_transient( $cache_key, array( 'checked' => true, 'ok' => false ), 300 );
			return false;
		}

		$type = 'remote';
		$size = (int) $content_length;

		set_transient( $cache_key, array(
			'checked' => true,
			'ok'      => true,
			'size'    => $size,
		), 900 );
	}
	return array(
		'type'	=> $type,
		'size'	=> $size
	);
}

/**
 * Get File Icon
 * Return the correct file icon for a file type from css sprite.
 * @param string $file url/path.
 * @return string.
 */
function dedo_get_file_icon_color( $ext ) {
	$groups = array(
		'#e99000' => array( 'zip', 'rar', '7z', 'tar', 'gz', 'bz2' ),
		'#333333' => array( 'exe', 'msi', 'bin', 'dmg', 'iso' ),
		'#006fd6' => array( 'html', 'htm', 'css', 'js', 'json', 'xml', 'php' ),
		'#c93333' => array( 'pdf', 'xps' ),
		'#3366cc' => array( 'doc', 'docx', 'docm', 'dotx', 'dotm', 'odt', 'ott' ),
		'#159a8c' => array( 'pub', 'pubx' ),
		'#198754' => array( 'xls', 'xlsx', 'xlsm', 'xltx', 'xltm', 'ods', 'ots', 'csv' ),
		'#3f51b5' => array( 'vsd', 'vsdx', 'vss', 'vssx' ),
		'#ed6c02' => array( 'ppt', 'pps', 'pptx', 'ppsx', 'pot', 'potx', 'potm', 'pptm', 'odp' ),
		'#8e44ad' => array( 'mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac' ),
		'#d35400' => array( 'mp4', 'm4v', 'mov', 'avi', 'mkv', 'webm' ),
		'#168aad' => array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tif', 'tiff' ),
		'#795548' => array( 'txt', 'rtf', 'md', 'log' ),
	);
	$ext = strtolower( (string) $ext );
	foreach ( $groups as $color => $extensions ) {
		if ( in_array( $ext, $extensions, true ) ) return $color;
	}
	return '#777777';
}

/**
 * Add the compact file-type icon CSS inline, without a separate request.
 */
function dedo_enqueue_filetype_style() {
	static $inline_added = false;
	if ( ! wp_style_is( 'filetype-style', 'registered' ) ) {
		wp_register_style( 'filetype-style', false, array(), DEDO_VERSION );
	}
	wp_enqueue_style( 'filetype-style' );
	if ( ! $inline_added ) {
		wp_add_inline_style( 'filetype-style', '.ftyp{--ftyp-bg:#777;box-sizing:border-box;background:var(--ftyp-bg);border-radius:5px 18px 5px 5px;color:#fff;display:inline-flex;align-items:flex-end;justify-content:center;font-style:normal;font-weight:700;height:55px;line-height:1;overflow:hidden;padding:0 3px 5px;position:relative;text-align:center;text-transform:uppercase;width:45px}.ftyp:before{border-color:transparent transparent rgba(255,255,255,.5) rgba(255,255,255,.5);border-style:solid;border-width:6px;content:"";position:absolute;right:0;top:0}.ftyp:after{content:attr(data-ext);display:block;font-size:11px;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.ftyp:hover{filter:brightness(.82)}' );
		$inline_added = true;
	}
}

function dedo_get_file_icon( $file ) {
	$ext   = strtolower( (string) dedo_get_file_ext( $file ) );
	$label = $ext !== '' && $ext !== '_blank' ? $ext : '?';
	$fmime = dedo_get_file_mime( $file );
	$title = strtoupper( $label ) . '-Datei' . "\n" . $fmime;
	return '<i class="ftyp" data-ext="' . esc_attr( $label ) . '" style="--ftyp-bg:' . esc_attr( dedo_get_file_icon_color( $ext ) ) . '" title="' . esc_attr( $title ) . '"></i>';
}

// Get total downloads counter
function dedo_total_downloads() {
	global $wpdb;
	$sql = $wpdb->prepare( "SELECT SUM(meta_value) FROM $wpdb->postmeta	WHERE meta_key = %s	", '_dedo_file_count' );
	return $wpdb->get_var( $sql );
}

/**
 * Delightful Downloads Mime Types
*/


/**
 * Mime Types
 * Add additioanl mime types that WordPress is allowed to upload.
 */
function dedo_mime_types( $existing_mimes ) {

	// Developer
	$existing_mimes['php']	= 'php';

	// Image editors
	$existing_mimes['psd']  = 'image/photoshop';
	$existing_mimes['ai']  	= 'application/postscript';
	$existing_mimes['eps']  = 'application/postscript';
	$existing_mimes['pxm']  = 'application/octet-stream'; // Pixelmator

	// Ebooks
	$existing_mimes['mobi']	= 'application/x-mobipocket-ebook';
	$existing_mimes['epub'] = 'application/epub+zip';

	// Misc
	$existing_mimes['json']	= 'application/json';
	$existing_mimes['exe']	= 'application/octet-stream';
	$existing_mimes['msi']	= 'application/vnd.ms-msi';
	$existing_mimes['dmg']	= 'application/x-apple-diskimage';

	return $existing_mimes;
}
add_filter( 'upload_mimes', 'dedo_mime_types' );

/**
 * Delightful Downloads Options
 */

/**
 * Get Registered Tabs
 */
function dedo_get_tabs() {
	$tabs = apply_filters( 'dedo_settings_tabs', array(
		'general'    => __( 'General', 'delightful-downloads' ),
		'shortcodes' => __( 'Shortcodes', 'delightful-downloads' ),
		'statistics' => __( 'Statistics', 'delightful-downloads' ),
		'advanced'   => __( 'Advanced', 'delightful-downloads' ),
	) );

	$options = dedo_get_options();

	foreach ( $options as $option ) {
		if ( 'licenses' === $option['tab'] ) {
			$tabs['licenses'] = __( 'Licenses', 'delightful-downloads' );
			break;
		}
	}

	$tabs['support'] = __( 'Support', 'delightful-downloads' );

	return $tabs;
}

/**
 * Get Registered Options
 */
function dedo_get_options() {
	$options = array(
		'enable_taxonomies'   => array(
			'name'    => __( 'Categories and Tags', 'delightful-downloads' ),
			'tab'     => 'general',
			'type'    => 'radio',
			'default' => 1,
		),
		'members_only'        => array(
			'name'       => __( 'Members Only', 'delightful-downloads' ),
			'tab'        => 'general',
			'type'       => 'radio',
			'default'    => 0,
			'sub_option' => array(
				'redirect' => 0,
			),
		),
		'open_browser'        => array(
			'name'    => __( 'Open in Browser', 'delightful-downloads' ),
			'tab'     => 'general',
			'type'    => 'radio',
			'default' => 0,
		),
		'block_agents'        => array(
			'name'    => __( 'Block User Agents', 'delightful-downloads' ),
			'tab'     => 'general',
			'type'    => 'textarea',
			'default' => "Googlebot\nbingbot\nmsnbot\nyahoo! slurp\njeeves",
		),
		'default_text'        => array(
			'name'    => __( 'Default Text', 'delightful-downloads' ),
			'tab'     => 'shortcodes',
			'type'    => 'text',
			'default' => __( 'Download', 'delightful-downloads' ),
		),
		'default_style'       => array(
			'name'    => __( 'Default Style', 'delightful-downloads' ),
			'tab'     => 'shortcodes',
			'type'    => 'dropdown',
			'default' => 'download',
		),
		'default_button'      => array(
			'name'    => __( 'Default Button Style', 'delightful-downloads' ),
			'tab'     => 'shortcodes',
			'type'    => 'dropdown',
			'default' => 'grey',
		),
		'default_list'        => array(
			'name'    => __( 'Default List Style', 'delightful-downloads' ),
			'tab'     => 'shortcodes',
			'type'    => 'dropdown',
			'default' => 'list',
		),
		'log_admin_downloads' => array(
			'name'    => __( 'Admin Events', 'delightful-downloads' ),
			'tab'     => 'statistics',
			'type'    => 'radio',
			'default' => 1,
		),
		'grace_period'        => array(
			'name'       => __( 'Grace Period', 'delightful-downloads' ),
			'tab'        => 'statistics',
			'type'       => 'text',
			'default'    => 1,
			'sub_option' => array(
				'duration' => 3,
			),
		),
		'auto_delete'         => array(
			'name'       => __( 'Auto Delete', 'delightful-downloads' ),
			'tab'        => 'statistics',
			'type'       => 'text',
			'default'    => 0,
			'sub_option' => array(
				'duration' => 90,
			),
		),
		'enable_css'          => array(
			'name'    => __( 'Output CSS', 'delightful-downloads' ),
			'tab'     => 'advanced',
			'type'    => 'radio',
			'default' => 1,
		),
		'cache'               => array(
			'name'       => __( 'Cache', 'delightful-downloads' ),
			'tab'        => 'advanced',
			'type'       => 'text',
			'default'    => 0,
			'sub_option' => array(
				'duration' => 5,
			),
		),
		'download_url'        => array(
			'name'    => __( 'Download Address', 'delightful-downloads' ),
			'tab'     => 'advanced',
			'type'    => 'text',
			'default' => 'ddownload',
		),
   		'download_quicklink'   => array(
			'name'    => __( 'Download Address Quicklink', 'delightful-downloads' ),
			'tab'     => 'advanced',
			'type'    => 'radio',
			'default' => 1,
		),
		'upload_directory'    => array(
			'name'    => __( 'Upload Directory', 'delightful-downloads' ),
			'tab'     => 'advanced',
			'type'    => 'text',
			'default' => 'delightful-downloads',
		),
		'folder_protection'   => array(
			'name'    => __( 'Folder Protection', 'delightful-downloads' ),
			'tab'     => 'advanced',
			'type'    => 'radio',
			'default' => 1,
		),
		'uninstall'           => array(
			'name'    => __( 'Complete Uninstall', 'delightful-downloads' ),
			'tab'     => 'advanced',
			'type'    => 'radio',
			'default' => 0,
		),
	);

	return apply_filters( 'dedo_settings_options', $options );
}

/**
 * Get Default Options
 *

 */
function dedo_get_default_options() {
	// Get registered settings
	$options = dedo_get_options();

	// Loop through and find default value
	foreach ( $options as $key => $value ) {
		$default_options[ $key ] = $value['default'];

		// Add sub options
		if ( isset( $value['sub_option'] ) ) {
			foreach ( $value['sub_option'] as $key2 => $value2 ) {
				$default_options[ $key . '_' . $key2 ] = $value2;
			}
		}
	}

	return $default_options;
}

/**
 * Delightful Downloads Post Types
 */


/**
 * Register Download Post Type
 */
function dedo_download_post_type() {
	$labels = array(
		'name'               => __( 'Downloads', 'delightful-downloads' ),
		'singular_name'      => __( 'Download', 'delightful-downloads' ),
		'add_new'            => __( 'Add New', 'delightful-downloads' ),
		'add_new_item'       => __( 'Add New Download', 'delightful-downloads' ),
		'edit_item'          => __( 'Edit Download', 'delightful-downloads' ),
		'new_item'           => __( 'New Download', 'delightful-downloads' ),
		'all_items'          => __( 'All Downloads', 'delightful-downloads' ),
		'view_item'          => __( 'View Download', 'delightful-downloads' ),
		'search_items'       => __( 'Search Downloads', 'delightful-downloads' ),
		'not_found'          => __( 'No downloads found', 'delightful-downloads' ),
		'not_found_in_trash' => __( 'No downloads found in Trash', 'delightful-downloads' ),
		'parent_item_colon'  => '',
		'menu_name'          => __( 'Downloads', 'delightful-downloads' ),
	);

	$args = array(
		'labels'        => apply_filters( 'dedo_ddownload_labels', $labels ),
		'public'        => true,
		// 'rewrite'       => array( 'slug' => 'ddl' ),
		'has_archive'	=> true,
		'show_ui'       => true,
		'show_in_menu'  => true,
		'menu_icon'     => 'dashicons-download',
		'capability_type' => apply_filters( 'dedo_ddownload_cap', 'post' ),
		'supports'      => apply_filters( 'dedo_ddownload_supports', array( 'title', 'editor', 'thumbnail', 'excerpt' ) ),
	);
	register_post_type( 'dedo_download', apply_filters( 'dedo_ddownload_args', $args ) );
}
add_action( 'init', 'dedo_download_post_type' );

/**
 * Download Post Type Column Headings
 */
function dedo_download_column_headings( $columns ) {
	global $dedo_options;

	$columns = array(
		'cb'           => '<input type="checkbox" />',
		'title'        => __( 'Title', 'delightful-downloads' ),
		'file'         => __( 'File', 'delightful-downloads' ),
		'filesize'     => __( 'File Size', 'delightful-downloads' ),
		'shortcode'    => __( 'Shortcode', 'delightful-downloads' ),
		'onedaypass'    => __( 'Download tickets', 'delightful-downloads' ),
		'downloads'    => '<span class="dashicons dashicons-download" title="' . __( 'Downloads', 'delightful-downloads' ) . '"></span>',
		'members_only' => '<span class="dashicons dashicons-businessperson" title="' . __( 'Members Only', 'delightful-downloads' ) . '"></span>',
		'open_browser' => '<span class="dashicons dashicons-portfolio" title="' . __( 'Open in Browser', 'delightful-downloads' ) . '"></span>',
		'date'         => __( 'Date', 'delightful-downloads' ),
		'modified'         => __( 'modified', 'delightful-downloads' ),
	);

	// If Quicklinks is enabled add to columns array
	if ( $dedo_options['download_quicklink'] ) {
		$columns_quicklink = array(
			'quicklink'    => __( 'Quick Link', 'delightful-downloads' ),
		);

		// Splice and insert after shortcode column
		$spliced = array_splice( $columns, 4 );
		$columns = array_merge( $columns, $columns_quicklink, $spliced );
	}
  	
	// If taxonomies is enabled add to columns array
	if ( $dedo_options['enable_taxonomies'] ) {
		$columns_taxonomies = array(
			'taxonomy-ddownload_category' => __( 'Categories', 'delightful-downloads' ),
			'taxonomy-ddownload_tag'      => __( 'Tags', 'delightful-downloads' ),
		);

		// Splice and insert after shortcode column
		$spliced = array_splice( $columns, 4 );
		$columns = array_merge( $columns, $columns_taxonomies, $spliced );
	}

	return $columns;
}
add_filter( 'manage_dedo_download_posts_columns', 'dedo_download_column_headings' );


/**
 * Download Post Type Column Contents
 */
function dedo_download_column_contents( $column_name, $post_id ) {
	
	// File column
	if ( $column_name == 'file' ) {
		if ( function_exists( 'dedo_is_folder_download' ) && dedo_is_folder_download( $post_id ) ) {
			$folder = function_exists( 'dedo_folder_path' ) ? dedo_folder_path( $post_id ) : '';
			$label = $folder ? basename( $folder ) : __( 'Folder unavailable', 'delightful-downloads' );
			echo '<span style="font-weight:700">📁 ' . esc_html( $label ) . '</span>';
			return;
		}
		$file_url = get_post_meta( $post_id, '_dedo_file_url', true );
		$file_path = dedo_get_abs_path($file_url);
		if (isset($_GET['aktion'])) {
		  if ( current_user_can('administrator') && $_GET['aktion'] == 'dedodelete' && $_GET['post'] == $post_id && $file_path ) {
			  wp_delete_file( $file_path );
			  wp_redirect( admin_url( "edit.php?post_type=dedo_download") );
		  }	
		} 
		$file_url = dedo_get_file_name( $file_url );
		echo ( ! $file_url ) ? '<span class="blank">--</span>' : '<span style="font-weight:700">'. esc_attr( $file_url ) .'</span>';
		if ( $file_path && file_exists( $file_path ) ) { 
			echo '<br><a style="color:tomato;padding-top:7px" title="'.$file_path.'" onclick="return confirm(\''.__( 'really delete attached file from server?', 'delightful-downloads' ).'\');" href ="'.admin_url( "edit.php?post_type=dedo_download&post=$post_id&aktion=dedodelete").'">' . __( 'delete file', 'delightful-downloads' ) .'</a>';
		} else { echo '<br>'.__( 'file is deleted', 'delightful-downloads' ); }
	}

	// Filesize column
	if ( $column_name == 'filesize' ) {
		$file_size = get_post_meta( $post_id, '_dedo_file_size', true );
		$file_size = ( ! $file_size ) ? 0 : size_format( $file_size, 1 );
		echo ( ! $file_size ) ? '<span class="blank">--</span>' : esc_attr( $file_size );
	}

	// Modified date column
	if ( $column_name == 'modified' ) {
		$file_mtime = dedo_get_file_modified_time( $post_id );
		$file_datum = $file_mtime > 0 ? wp_date( get_option('date_format').' '.get_option('time_format'), $file_mtime ) : '';
		echo '<i title="' . esc_attr__( 'File modified', 'delightful-downloads' ) . '">' . esc_html( $file_datum ) . ' ' . esc_html( dd_shared_ago( $file_mtime ) ) . '</i>';
	}

	// Shortcode column
	if ( $column_name == 'shortcode' ) {
		echo '<input type="text" title="id=&quot;' . esc_attr( $post_id ) . '&quot;" class="copy-to-clipboard" value="[ddownload id=&quot;' . esc_attr( $post_id ) . '&quot;]" readonly>';
		echo '<p class="dedo-copy-feedback">' . __( 'Shortcode copied to clipboard.', 'delightful-downloads' ) . '</p>';
	}

	// QuickLink
	if ( $column_name == 'quicklink' ) {
		global $dedo_options;
		echo '<input type="text" title="'.$dedo_options['download_url'] . '=' . esc_attr( $post_id ) . '" class="copy-to-clipboard" value="' . get_site_url() . '?' . $text = $dedo_options['download_url'] . '=' . esc_attr( $post_id ) . '" readonly>';
		echo '<p class="dedo-copy-feedback">' . __( 'Quicklink copied to clipboard.', 'delightful-downloads' ) . '</p>';
	}
	
	// Time-limited download ticket column.
	if ( $column_name == 'onedaypass' ) {
        $ticket_7   = dedo_ticket_url( $post_id, 7 );
        $ticket_365 = dedo_ticket_url( $post_id, 365 );
        $today      = new DateTimeImmutable( 'today', wp_timezone() );
        $end_7      = $today->modify( '+6 days' );
        $end_365    = $today->modify( '+364 days' );

        echo '<label style="display:block;margin-bottom:6px"><strong>7 Tage</strong><br><input type="text" title="Gültig vom ' . esc_attr( $today->format( 'd.m.Y' ) ) . ' bis ' . esc_attr( $end_7->format( 'd.m.Y' ) ) . '" class="copy-to-clipboard" style="direction:rtl;cursor:pointer" value="' . esc_url( $ticket_7 ) . '" readonly></label>';
        echo '<label style="display:block"><strong>365 Tage</strong><br><input type="text" title="Gültig vom ' . esc_attr( $today->format( 'd.m.Y' ) ) . ' bis ' . esc_attr( $end_365->format( 'd.m.Y' ) ) . '" class="copy-to-clipboard" style="direction:rtl;cursor:pointer" value="' . esc_url( $ticket_365 ) . '" readonly></label>';
		echo '<p class="dedo-copy-feedback">' . __( 'Download ticket copied to clipboard.', 'delightful-downloads' ) . '</p>';
	}
	
	// Count column
	if ( $column_name == 'downloads' ) {
		$count = get_post_meta( $post_id, '_dedo_file_count', true );
		$count = ( ! $count ) ? 0 : number_format_i18n( $count );
		$scount = get_post_meta( $post_id, '_dedo_oneday_count', true );
		$scount = ( ! $scount ) ? 0 : number_format_i18n( $scount );
		echo esc_attr( $count ) . '<br><br><span title="onedaypass">' . esc_attr( $scount ) .'</span>';
	}

	// Members only column
	if ( 'members_only' == $column_name ) {
		$file = get_post_meta( $post_id, '_dedo_file_options', true );

		if ( isset( $file['members_only'] ) ) {
			echo ( 1 == $file['members_only'] ) ? '<span class="true" title="' . __( 'Yes', 'delightful-downloads' ) . '"></span>' : '<span class="false" title="' . __( 'No', 'delightful-downloads' ) . '"></span>';
		} else {
			echo '<span class="blank" title="' . __( 'Inherit', 'delightful-downloads' ) . '">--</span>';
		}
	}

	// Open browser column
	if ( 'open_browser' == $column_name ) {
		$file = get_post_meta( $post_id, '_dedo_file_options', true );

		if ( isset( $file['open_browser'] ) ) {
			echo ( 1 == $file['open_browser'] ) ? '<span class="true" title="' . __( 'Yes', 'delightful-downloads' ) . '"></span>' : '<span class="false" title="' . __( 'No', 'delightful-downloads' ) . '"></span>';
		} else {
			echo '<span class="blank" title="' . __( 'Inherit', 'delightful-downloads' ) . '">--</span>';
		}
	}

}
add_action( 'manage_dedo_download_posts_custom_column', 'dedo_download_column_contents', 10, 2 );

/**
 * Download Post Type Sortable Filter
 *
 */
function dedo_download_column_sortable( $columns ) {
	$columns['filesize']  = 'filesize';
	$columns['downloads'] = 'downloads';

	return $columns;
}
add_filter( 'manage_edit-dedo_download_sortable_columns', 'dedo_download_column_sortable' );

/**
 * Download Post Type Sortable Action
 *
 */
function dedo_download_column_orderby( $query ) {
	$orderby = $query->get( 'orderby' );

	if ( $orderby == 'filesize' ) {
		$query->set( 'meta_key', '_dedo_file_size' );
		$query->set( 'orderby', 'meta_value_num' );
	}

	if ( $orderby == 'downloads' ) {
		$query->set( 'meta_key', '_dedo_file_count' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
add_action( 'pre_get_posts', 'dedo_download_column_orderby' );

/**
 * Delightful Downloads Process Download
 */


/**
 * Process Download
 * Validate download and send file to user
 */

/**
 * Abort a download request safely even before wp_loaded.
 */
function dedo_download_abort( $message, $title = '' ) {
	$message = (string) $message;
	$title   = (string) $title;

	if ( did_action( 'wp_loaded' ) ) {
		wp_die( $message, $title );
	}

	if ( ! headers_sent() ) {
		status_header( 400 );
		nocache_headers();
		header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
	}

	echo '<!DOCTYPE html><html><head><meta charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '"><title>' . esc_html( $title ?: __( 'Download Error', 'delightful-downloads' ) ) . '</title></head><body>';
	echo wp_kses_post( wpautop( $message ) );
	echo '</body></html>';
	exit;
}

/**
 * Show the password prompt using the active theme wp_die() styling,
 * but suppress early doing_it_wrong notices (e.g. WooCommerce cart notices)
 * while the themed die template is rendered before wp_loaded.
 */
function dedo_download_password_prompt( $download_id ) {
	$download_id = (int) $download_id;
	$title       = __( 'Password Required', 'delightful-downloads' );
	$message     = get_the_password_form( $download_id );

	if ( ! did_action( 'wp_loaded' ) ) {
		add_filter( 'doing_it_wrong_trigger_error', '__return_false', 999999 );
	}

	wp_die( $message, $title );
}

// Secure download one day pass
function dedo_onedaypass_process( $download_id ) {
	global $dedo_options;

	// Check valid download
	if ( ! dedo_download_valid( $download_id ) ) {
		do_action( 'ddownload_download_invalid', $download_id );
		dedo_download_abort( __( 'Invalid download.', 'delightful-downloads' ) );
	}
	// Get file meta
	$download_url = get_post_meta( $download_id, '_dedo_file_url', true );
	$options      = get_post_meta( $download_id, '_dedo_file_options', true );

	// Disable max_execution_time
	set_time_limit( 0 );

    // Signed ticket prüfen (7 oder 365 Tage; alte heutige One-Day-Links bleiben kompatibel).
	$ticket_path = dedo_get_abs_path( $download_url );
	if ( $ticket_path && file_exists( $ticket_path ) && dedo_ticket_is_valid( $download_id ) ) {
        // Only valid ticket downloads are logged and counted.
        do_action( 'ddownload_download_before', $download_id );
		// $path = ABSPATH.'wp-content/uploads/delightful-downloads/2019/software.zip'; // the file made available for download via this PHP file
		$path = $ticket_path;
		$mm_type="application/octet-stream"; // modify accordingly to the file type of $path, but in most cases no need to do so
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-Type: " . $mm_type);
		header("Content-Length: " .(string)(filesize($path)) );
		header('Content-Disposition: attachment; filename="'.basename($path).'"');
		header("Content-Transfer-Encoding: binary\n");
		readfile($path); // outputs the content of the file
		// Hook when download complete
		do_action( 'ddownload_download_complete', $download_id );
		exit();		  
	} else {
		dedo_download_abort( __( 'Download not found or ticket invalid or expired.', 'delightful-downloads' ) ); // not legit
	}  

}


function dedo_download_process( $download_id ) {
	global $dedo_options;

	// Check valid download
	if ( ! dedo_download_valid( $download_id ) ) {
		do_action( 'ddownload_download_invalid', $download_id );
		dedo_download_abort( __( 'Invalid download.', 'delightful-downloads' ) );
	}

	// Check blocked user agents
	if ( ! dedo_download_blocked( ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ) ) {
		do_action( 'ddownload_download_blocked', $download_id );
		dedo_download_abort( __( 'You are blocked from downloading this file!', 'delightful-downloads' ) );
	}

	if ( apply_filters( 'dedo_abort_download', false, $download_id ) ) {
		return;
	}

	// Get file meta
	$download_url = get_post_meta( $download_id, '_dedo_file_url', true );
	$options      = get_post_meta( $download_id, '_dedo_file_options', true );

	// A folder download represents a collection and must be opened on its singular view.
	if ( function_exists( 'dedo_is_folder_download' ) && dedo_is_folder_download( $download_id ) ) {
		wp_safe_redirect( get_permalink( $download_id ) );
		exit();
	}

	// Check for members only
	if ( ! dedo_download_permission( $options ) ) {
		do_action( 'ddownload_download_permission', $download_id );

		// Get redirect location
		$location = ( isset( $options['members_only_redirect'] ) ) ? $options['members_only_redirect'] : $dedo_options['members_only_redirect'];

		// Try to redirect
		if ( $location = get_permalink( $location ) ) {
			wp_redirect( $location );
			exit();
		} else {
			// Invalid page provided, show error message
			dedo_download_abort( __( 'Please login to download this file!', 'delightful-downloads' ) );
		}
	}

	// Password protected
	if ( post_password_required( $download_id ) ) {
		dedo_download_password_prompt( $download_id );
	}

	// Empty file urls not allowed
	if ( '' === $download_url ) {
		dedo_download_abort( __( 'You must attach a file to this download.', 'delightful-downloads' ) );
	}

	// Stop page caching. Cause conflicts with WP Super Cache
	define( 'DONOTCACHEPAGE', true );

	// Disable php notices, can cause corrupt downloads
	@ini_set( 'display_errors', 0 );

	// Disable compression
	if ( function_exists( 'apache_setenv' ) ) {
		@apache_setenv( 'no-gzip', 1 );
	}

	@ini_set( 'zlib.output_compression', 'Off' );

	// Close sessions, which can sometimes cause buffering errors??
	@session_write_close();

	/**
	 * Output Buffering
	 *
	 * The majority of servers work when clearing output buffering.
	 * If you get corrupt or blank downloads try the following:
	 *
	 * Disable by adding the following, to your theme's functions.php file:
	 *
	 * add_filter( 'dedo_clear_output_buffers', '__return_false' );
	 *
	 */
	if ( apply_filters( 'dedo_clear_output_buffers', true ) ) {
		do {
			@ob_end_clean();
		} while ( ob_get_level() > 0 );
	}

	// Disable max_execution_time
	set_time_limit( 0 );

	// Hook before download starts
	do_action( 'ddownload_download_before', $download_id );

	// Open in browser
	$open_browser = ( isset( $options['open_browser'] ) ) ? $options['open_browser'] : $dedo_options['open_browser'];

	if ( $open_browser ) {
		header( "Location: $download_url" );
		exit();
	}

	// Convert to path
	if ( $download_path = dedo_get_abs_path( $download_url ) ) {
		// Try to open file, else display server error
		if ( ! $file = @fopen( $download_path, 'rb' ) ) {
			// Server error
			dedo_download_abort( __( 'Server error, file cannot be opened!', 'delightful-downloads' ) );
		}

		// Set headers
		nocache_headers();
		header( "X-Robots-Tag: noindex, nofollow", true );
		header( "Content-Type: " . dedo_download_mime( $download_path ) );
		header( "Content-Description: File Transfer" );
		header( "Content-Disposition: attachment; filename=\"" . basename( $download_path ) . "\";" );
		header( "Content-Transfer-Encoding: binary" );
		header( "Content-Length: " . @filesize( $download_path ) ); // filesize causes blank downloads on Windows servers

		// Output file in chuncks
		while ( ! feof( $file ) ) {

			print fread( $file, 1024 * 1024 );
			flush();

			// Check conection, if lost close file and end loop
			if ( connection_status() != 0 ) {

				fclose( $file );
				exit();
			}
		}

		// Reached end of file, close it. Job done!
		fclose( $file );

		// Hook when download complete
		do_action( 'ddownload_download_complete', $download_id );

		// Done! Exit
		exit();
	} else {
		// No disoverable path, redirect to file
		header( "Location: $download_url" );
		exit();
	}
}

/**
 * Init handle download.
 */
function dedo_init_handle_download() {
	global $dedo_options;

	if ( isset( $_GET[ $dedo_options['download_url'] ] ) ) {
		dedo_download_process( absint( $_GET[ $dedo_options['download_url'] ] ) );
	}
	if ( isset( $_GET[ 'sdownload' ] ) ) {
		dedo_onedaypass_process( absint( $_GET[ 'sdownload' ] ) );
	}
}
add_action( 'init', 'dedo_init_handle_download', 4 );

/**
 * Delightful Downloads Scripts
 */

/**
 * Register frontend scripts and styles.
 */
function dedo_enqueue_scripts( $page ) {
	global $dedo_options,$post;
	if ( 'dedo_download' == get_post_type() ) dedo_enqueue_filetype_style();
	// Enqueue frontend CSS if option is enabled
	if ( ! $dedo_options['enable_css'] ) { return; }
	$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : DEDO_VERSION;
	// Register frontend CSS
	$src = DEDO_PLUGIN_URL . 'assets/css/delightful-downloads.css';
	wp_enqueue_style( 'dedo-css', $src, array(), $version, 'all' );
}
add_action( 'wp_enqueue_scripts', 'dedo_enqueue_scripts' );

/**
 * Register Admin Scripts & Styles
 */
function dedo_admin_enqueue_scripts( $page ) {
	$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : DEDO_VERSION;
	$suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	dedo_enqueue_filetype_style();

	// Register scripts
	$src = DEDO_PLUGIN_URL . 'assets/js/dedo-admin-global' . $suffix . '.js';
	wp_register_script( 'dedo-admin-js-global', $src, array( 'jquery' ), $version, true );
	$src = DEDO_PLUGIN_URL . 'assets/js/dedo-admin-legacy-logs' . $suffix . '.js';
	wp_register_script( 'dedo-admin-js-legacy-logs', $src, array( 'jquery' ), $version, true ); // 1.4 upgrade
	$src = DEDO_PLUGIN_URL . 'assets/js/dedo-admin-media-button' . $suffix . '.js';
	wp_register_script( 'dedo-admin-js-media-button', $src, array( 'jquery' ), $version, true );
	$src = DEDO_PLUGIN_URL . 'assets/js/dedo-admin-post-download' . $suffix . '.js';
	wp_register_script( 'dedo-admin-js-post-download', $src, array(
		'jquery',
		'plupload-all'
	), $version . '-status-fix1', true );
	// Register styles
	$src = DEDO_PLUGIN_URL . 'assets/css/delightful-downloads-admin.css';
	wp_register_style( 'dedo-css-admin', $src, array(), $version, 'all' );

	// Enqueue on all admin pages
	wp_enqueue_style( 'dedo-css-admin' );
	wp_enqueue_script( 'dedo-admin-js-global' );
	// JS copy to clipboard
	$src = DEDO_PLUGIN_URL . 'assets/js/copy-to-clipboard' . $suffix . '.js';
	wp_enqueue_script( 'dedo-copy-to-clipboard', $src, array(
		'jquery',
	), $version . '-clipboard-fix1', true );
	// Enqueue on dedo_download post add/edit screen
	if ( in_array( $page, array(
			'post.php',
			'page.php',
			'post-new.php',
			'post-edit.php'
		) ) && get_post_type() == 'dedo_download'
	) {
		wp_enqueue_script( 'dedo-admin-js-post-download' );
	}

	// Enqueue on all post/edit screen
	if ( in_array( $page, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) {
		wp_enqueue_script( 'dedo-admin-js-media-button' );
	}
}
add_action( 'admin_enqueue_scripts', 'dedo_admin_enqueue_scripts' );

/**
 * Delightful Downloads Shortcodes
*/

/**
 * Download Shortcode.
 * Outputs a single download based on user defined attributes.
 * @param array $atts
 * @return string
 */
function dedo_shortcode_ddownload( $atts ) {
	/**
	 * Use get option again so that WPML can filter default text
	 * for translation, get_option() is cached so should not
	 * affect performance.
	 */
	global $dedo_default_options;
	$dedo_options = wp_parse_args( get_option( 'delightful-downloads' ), $dedo_default_options );

	// Compact file-type icon style (inline, no extra CSS request).
	dedo_enqueue_filetype_style();

	// Attributes
	extract( shortcode_atts(
		array(
			'id' 	=> '',
			'text'	=> $dedo_options['default_text'],
			'style'	=> $dedo_options['default_style'],
			'button'=> '',
			'class'	=> ''
		), $atts, 'ddownload' )
	);

	// Validate download id
	if ( $id == '' || !dedo_download_valid( $id ) ) {
		return __( 'Invalid download ID.', 'delightful-downloads' );
	}

	// The singular detail view has its own renderer and never reuses the compact card.
	if ( 'singlepost' === $style ) {
		return dedo_render_singular_download_details( $id );
	}

	// Other legacy style names remain accepted for old content.
	$legacy_styles = array( 'infobox','button','link','iconlink','plain_text' );
	if ( in_array( $style, $legacy_styles, true ) ) $style = 'download';
	if ( dedo_is_folder_download( $id ) ) return dedo_render_folder_download( $id );

	// Check style against registered styles
	$registered_styles = dedo_get_shortcode_styles();

	if ( array_key_exists( $style, $registered_styles ) ) {
		$style_format = $registered_styles[ $style ]['format'];
	}
	else {
		return __( 'Invalid style attribute.', 'delightful-downloads' );
	}

	// Check button against registered buttons
	if ( $style == 'button' ) {
		$button = ( empty( $button ) ) ? $dedo_options['default_button'] : $button;
		$registered_buttons = dedo_get_shortcode_buttons();
		if ( array_key_exists( $button, $registered_buttons ) ) {
			$button_class = $registered_buttons[ $button ]['class'];
		}
		else {
			return __( 'Invalid button attribute.', 'delightful-downloads' );
		}
	}

	// Generate correct class and add user defined
	$classes = 'ddownload-' . $style; // Output style
	$classes .= ( isset( $button_class ) ) ? ' ' . $button_class : ''; // Button style
	$classes .= ' id-' . $id; // Download id
	$classes .= ' ext-' . dedo_get_file_ext( get_post_meta( $id, '_dedo_file_url', true ) ); // File extension
	$classes .= ( !empty( $class ) ) ? ' ' . $class : ''; // User defined
	// Replace text and class att
	$replace = array(
		'%text%'	=> $text,
		'%class%'	=> $classes
	);
	
	foreach ( $replace as $key => $value ) {
 		$style_format = str_replace( $key, $value, $style_format );
 	}
	// Search and replace wildcards
	$output = dedo_search_replace_wildcards( $style_format, $id );
	return apply_filters( 'dedo_shortcode_ddownload', $output, $id, $atts, $classes );
}
add_shortcode( 'ddownload', 'dedo_shortcode_ddownload' );


/**
 * Downloads List Shortcode
 * Displays a list of downloads based on user defined attributes.
 * Extended:
 *  - search (Shortcode-Attribut) + ?search=... (URL)
 *  - show_search=0|1 (Suchfeld aus-/einblenden; Standard: 1)
 *  - Suche filtert NUR innerhalb dedo_download (Titel, Content, Dateiname/_dedo_file_url)
 *  - Cache-Code entfernt (auf Wunsch)
 * @since   1.3
 */
function dedo_shortcode_ddownload_list( $atts ) {
	global $dedo_options, $dedo_statistics;

	// Compact file-type icon style (inline, no extra CSS request).
	dedo_enqueue_filetype_style();
	
	// Attributes
	extract( shortcode_atts(
		array(
			'limit' 				=> 0,
			'orderby'			=> 'title',
			'order'				=> 'ASC',
			'categories'		=> '',
			'tags'				=> '',
			'exclude_categories'=> '',
			'exclude_tags'		=> '',
			'relation'			=> 'AND',
			'style'				=> $dedo_options['default_list'],
			'search'			=> '',
			'show_search'		=> 1,
		), $atts, 'ddownload_list' )
	);

	// --- Suchbegriff ermitteln (URL/Formular hat Priorität) ---
	$search_term = '';
	if ( isset( $_REQUEST['search'] ) && $_REQUEST['search'] !== '' ) {
		$search_term = sanitize_text_field( wp_unslash( $_REQUEST['search'] ) );
	} elseif ( ! empty( $search ) ) {
		$search_term = sanitize_text_field( $search );
	}

	// Default query args
	$query_args = array(
		'post_type'		=> 'dedo_download',
		'post_status'	=> 'publish',
	);

	// Validate and set limit
	$limit = abs( $limit );
	$query_args['posts_per_page'] = ( 0 === $limit ) ? -1 : $limit;

	// Validate and set orderby
	if ( !in_array( strtolower( $orderby ), array( 'title', 'date', 'modified', 'count', 'filesize', 'random' ) ) ) {
		return __( 'Invalid orderby attribute.', 'delightful-downloads' );
	}
	else {
		switch ( $orderby ) {
			case 'title':
				$query_args['orderby'] = 'title';
				break;

			case 'date':
				$query_args['orderby'] = 'date';
				break;

			case 'modified':
				$query_args['orderby'] = 'modified';
				break;
				
			case 'count':
				$query_args['meta_key'] = '_dedo_file_count';
				$query_args['orderby'] = 'meta_value_num';
				break;

			case 'filesize':
				$query_args['meta_key'] = '_dedo_file_size';
				$query_args['orderby'] = 'meta_value_num';
				break;

			case 'random':
				$query_args['orderby'] = 'rand';
				break;
		}
	}

	// Validate and set order
	if ( !in_array( strtoupper( $order ), array( 'ASC', 'DESC' ) ) ) {
		return __( 'Invalid order attribute.', 'delightful-downloads' );
	}
	else {
		$query_args['order'] = strtoupper( $order);
	}

	// Validate relation
	if ( !in_array( strtoupper( $relation ), array( 'AND', 'OR' ) ) ) {
		return __( 'Invalid relation attribute.', 'delightful-downloads' );
	}
	else {
		$relation = strtoupper( $relation );
	}

	// Validate and set categories/tags
	$tax_class = '';

	if ( !empty( $categories ) || !empty( $tags ) || !empty( $exclude_categories ) || !empty( $exclude_tags ) ) {
		$query_args['tax_query'] = array(
			'relation' => $relation,
		);

		if ( !empty( $categories ) ) {
			$categories_array = explode( ',' , $categories );
			$categories_array = array_map( 'trim', $categories_array );
			$query_args['tax_query'][] = array(
				'taxonomy'	=> 'ddownload_category',
				'field'		=> 'slug',
				'terms'		=> $categories_array,
			);
			// Set taxonomy class
			$tax_class .= ' category-' . implode( ' category-', $categories_array );
		}

		if ( !empty( $tags ) ) {
			$tags_array = explode( ',' , $tags );
			$tags_array = array_map( 'trim', $tags_array );
			$query_args['tax_query'][] = array(
				'taxonomy'	=> 'ddownload_tag',
				'field'		=> 'slug',
				'terms'		=> $tags_array,
			);
			// Set taxonomy class
			$tax_class .= ' tag-' . implode( ' tag-', $tags_array );
		}

		if ( !empty( $exclude_categories ) ) {
			$exclude_categories_array = explode( ',' , $exclude_categories );
			$exclude_categories_array = array_map( 'trim', $exclude_categories_array );

			$query_args['tax_query'][] = array(
				'taxonomy'	=> 'ddownload_category',
				'field'		=> 'slug',
				'terms'		=> $exclude_categories_array,
				'operator'	=> 'NOT IN',
			);
		}

		if ( !empty( $exclude_tags ) ) {
			$exclude_tags_array = explode( ',' , $exclude_tags );
			$exclude_tags_array = array_map( 'trim', $exclude_tags_array );

			$query_args['tax_query'][] = array(
				'taxonomy'	=> 'ddownload_tag',
				'field'		=> 'slug',
				'terms'		=> $exclude_tags_array,
				'operator'	=> 'NOT IN',
			);
		}
	}

	// One canonical list display. Map all former list styles to it.
	$style = 'list';
	// Check style against registered styles
	$registered_styles = dedo_get_shortcode_lists();
	$style_class       = ' list-' . $style;

	if ( array_key_exists( $style, $registered_styles ) ) {
		$style_format = $registered_styles[ $style ]['format'];
	}
	else {
		return __( $style. 'Invalid style attribute.', 'delightful-downloads' );
	}

	// --- Suche nur innerhalb dedo_download: Titel/Content ODER Datei-URL (Meta) ---
	if ( $search_term !== '' ) {
		// 1) Titel + Content (WP-Suche)
		$args_s = $query_args;
		$args_s['fields'] = 'ids';
		$args_s['posts_per_page'] = -1;
		$args_s['s'] = $search_term;
		$q_s = new WP_Query( $args_s );
		$ids_s = ( ! empty( $q_s->posts ) ) ? $q_s->posts : array();

		// 2) Datei-URL / Dateiname (Meta)
		$args_m = $query_args;
		$args_m['fields'] = 'ids';
		$args_m['posts_per_page'] = -1;
		$args_m['meta_query'] = array(
			array(
				'key'		=> '_dedo_file_url',
				'value'		=> $search_term,
				'compare'	=> 'LIKE',
			),
		);
		$q_m = new WP_Query( $args_m );
		$ids_m = ( ! empty( $q_m->posts ) ) ? $q_m->posts : array();

		$ids = array_values( array_unique( array_merge( $ids_s, $ids_m ) ) );

		// Wenn nichts gefunden: direkt "No downloads found"
		if ( empty( $ids ) ) {
			// Suchfeld trotzdem rendern (falls show_search=1)
			ob_start();
			if ( (int) $show_search === 1 ) {
				echo '<form method="get" class="ddownload-search-form">';
				foreach ( $_GET as $key => $value ) {
					if ( $key !== 'search' ) {
						echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
					}
				}
				echo '<div class="ddownload-search-field">';
				echo '<span class="ddownload-search-icon" aria-hidden="true"></span>';
				echo '<input type="search" name="search" class="search" value="' . esc_attr( $search_term ) . '" placeholder="Downloads durchsuchen …">';
				echo '</div>';
				echo '</form>';
			}
			$output = ob_get_clean();
			return $output . '<p>' . __( 'No downloads found.', 'delightful-downloads' ) . '</p>';
		}

		// Hauptquery auf gefundene IDs einschränken
		$query_args['post__in'] = $ids;
		// kein 's' und keine meta_query mehr im Hauptquery nötig
	}

	// Run query
	$downloads_list = new WP_Query( $query_args );

	// Begin output
	if ( $downloads_list->have_posts() ) {
		ob_start();
		$dlcount=0;
		$filecount=0;
		$tfilesize=0;
		$listfilter='';

		if (!empty($categories)) $listfilter .= ''.$categories;
		if (!empty($tags)) $listfilter .= ' &nbsp;'.$tags;
		if (!empty($exclude_categories)) $listfilter .= ' &nbsp;<span class="dedo-icon dedo-icon--filter" title="excluded cats" aria-hidden="true"></span>'.$exclude_categories;
		if (!empty($exclude_tags)) $listfilter .= ' &nbsp;<span class="dedo-icon dedo-icon--filter dedo-icon--danger" title="excluded tags" aria-hidden="true"></span>'.$exclude_tags;
		if (!empty($search_term)) $listfilter .= ' &nbsp;<span class="dedo-icon dedo-icon--search" aria-hidden="true"></span> <span class="ddownload-search-term">'.esc_html($search_term).'</span>';

		$total_files = (int) wp_count_posts( 'dedo_download' )->publish;
		$show_statistics = true;
		$listed_files = (int) $downloads_list->post_count;
		$listed_size = 0;
		$listed_downloads = 0;
		foreach ( $downloads_list->posts as $listed_download ) {
			$listed_id = is_object( $listed_download ) ? (int) $listed_download->ID : (int) $listed_download;
			$listed_size += (int) get_post_meta( $listed_id, '_dedo_file_size', true );
			$listed_downloads += (int) get_post_meta( $listed_id, '_dedo_file_count', true );
		}

		echo '<div class="ddownload-list-toolbar">';

		// Suchfeld (immer anzeigen, außer show_search=0)
		if ( (int) $show_search === 1 ) {
			echo '<form method="get" class="ddownload-search-form">';
			// vorhandene GET-Parameter erhalten (page_id etc.)
			foreach ( $_GET as $key => $value ) {
				if ( $key !== 'search' ) {
					echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
				}
			}
			echo '<label class="ddownload-search-field">';
			echo '<span class="screen-reader-text">' . esc_html__( 'Search Downloads', 'delightful-downloads' ) . '</span>';
			echo '<span class="ddownload-search-icon" aria-hidden="true"></span>';
			echo '<input type="search" name="search" class="search" value="' . esc_attr( $search_term ) . '" placeholder="Downloads durchsuchen …">';
			echo '</label>';
			echo '</form>';
		}

		echo '<div class="ddownload-list-summary">';
		echo '<span class="ddownload-list-context"><strong>' . esc_html__( 'Downloads', 'delightful-downloads' ) . '</strong>' . $listfilter . '</span>';
		if ( $show_statistics ) {
			if ( $search_term !== '' || $listed_files < $total_files ) {
				echo '<span class="ddownload-list-stat ddownload-list-stat-current"><span class="ddownload-list-stat-label">' . esc_html__( 'Visible', 'delightful-downloads' ) . '</span><strong>' . number_format_i18n( $listed_files ) . '</strong></span>';
				echo '<span class="ddownload-list-stat ddownload-list-stat-current"><span class="ddownload-list-stat-label">' . esc_html__( 'File Size', 'delightful-downloads' ) . '</span><strong>' . esc_html( size_format( $listed_size, 1 ) ) . '</strong></span>';
				echo '<span class="ddownload-list-stat ddownload-list-stat-current"><span class="ddownload-list-stat-label">' . esc_html__( 'Downloads', 'delightful-downloads' ) . '</span><strong>' . number_format_i18n( $listed_downloads ) . '</strong></span>';
			}
			echo '<span class="ddownload-list-stat"><span class="ddownload-list-stat-label">' . esc_html__( 'Total', 'delightful-downloads' ) . '</span><strong>' . number_format_i18n( $total_files ) . '</strong></span>';
			echo '<span class="ddownload-list-stat"><span class="ddownload-list-stat-label">' . esc_html__( 'File Size', 'delightful-downloads' ) . '</span><strong>' . esc_html( size_format( dedo_get_filesize(), 1 ) ) . '</strong></span>';
			echo '<span class="ddownload-list-stat"><span class="ddownload-list-stat-label">' . esc_html__( 'Downloads', 'delightful-downloads' ) . '</span><strong>' . number_format_i18n( dedo_total_downloads() ) . '</strong></span>';
		}
		echo '</div>';
		echo '</div>';

		echo '<div class="ddownloads_list' . $tax_class . $style_class . '">';
		while ( $downloads_list->have_posts() ) {
			$downloads_list->the_post();
			// Add classes
			$classes = 'id-' . get_the_ID(); // Download id
			$classes .= ' ext-' . dedo_get_file_ext( get_post_meta( get_the_ID(), '_dedo_file_url', true ) ); // File extension
			$new_style_format = str_replace( '%class%', $classes, $style_format );
			$filecount++;
			$dlcount += (int) get_post_meta( get_the_ID(), '_dedo_file_count', true );
			$tfilesize += (int) get_post_meta( get_the_ID(), '_dedo_file_size', true );
			echo '<article class="dedo-list-item"><span class="dedo-list-index" aria-hidden="true">' . $filecount . '</span>' . dedo_search_replace_wildcards( $new_style_format, get_the_ID() ) . '</article>';
			// Reset classes for next iteration
			unset( $classes );
			unset( $new_style_format );
		}
		echo '</div>';


		$output = ob_get_clean();
		wp_reset_postdata();
	}
	else {
		// Suchfeld trotzdem anzeigen (falls aktiv)
		ob_start();
		if ( (int) $show_search === 1 ) {
			echo '<form method="get" class="ddownload-search-form">';
			foreach ( $_GET as $key => $value ) {
				if ( $key !== 'search' ) {
					echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
				}
			}
			echo '<div class="ddownload-search-field">';
			echo '<span class="ddownload-search-icon" aria-hidden="true"></span>';
			echo '<input type="search" name="search" class="search" value="' . esc_attr( $search_term ) . '" placeholder="Downloads durchsuchen …">';
			echo '</div>';
			echo '</form>';
		}
		$output = ob_get_clean();
		return $output . '<p>' . __( 'No downloads found.', 'delightful-downloads' ) . '</p>';
	}

	return apply_filters( 'dedo_shortcode_ddownload_list', $output );
}
add_shortcode( 'ddownload_list', 'dedo_shortcode_ddownload_list' );


/**
 * Allow shortcodes in widgets
 */
add_filter( 'widget_text', 'do_shortcode' );

/**
 * Delightful Downloads Taxonomies
*/


/**
 * Register Download Taxonomies
 */
function dedo_download_taxonomies() {
	global $dedo_options;	

	// Register download category taxonomy
	$labels = array(
		'name'				=> __( 'Download Categories', 'delightful-downloads' ),
		'singular_name'		=> __( 'Download Category', 'delightful-downloads' ),
		'menu_name'			=> __( 'Categories', 'delightful-downloads' ),
		'all_items'			=> __( 'All Categories', 'delightful-downloads' ),
		'edit_item'			=> __( 'Edit Category', 'delightful-downloads' ),
		'view_item'			=> __( 'View Category', 'delightful-downloads' ),
		'update_item'		=> __( 'Update Category', 'delightful-downloads' ),
		'add_new_item'		=> __( 'Add New Category', 'delightful-downloads' ),
		'new_item_name'		=> __( 'New Category Name', 'delightful-downloads' ),
		'search_items'		=> __( 'Search Categories', 'delightful-downloads' ),
		'popular_items'		=> __( 'Popular Categories', 'delightful-downloads' ) 
	);

	$category_args = array(
		'labels'			=> apply_filters( 'dedo_ddownload_category_labels', $labels ),
		'public'			=> true,
		'show_in_nav_menus'	=> false,
		'show_tag_cloud'	=> false,
		'show_admin_column'	=> true,
		'hierarchical'		=> true
	);

	// Register download tag taxonomy
	$labels = array(
		'name'				=> __( 'Download Tags', 'delightful-downloads' ),
		'singular_name'		=> __( 'Download Tag', 'delightful-downloads' ),
		'menu_name'			=> __( 'Tags', 'delightful-downloads' ),
		'all_items'			=> __( 'All Tags', 'delightful-downloads' ),
		'edit_item'			=> __( 'Edit Tag', 'delightful-downloads' ),
		'view_item'			=> __( 'View Tag', 'delightful-downloads' ),
		'update_item'		=> __( 'Update Tag', 'delightful-downloads' ),
		'add_new_item'		=> __( 'Add New Tag', 'delightful-downloads' ),
		'new_item_name'		=> __( 'New Tag Name', 'delightful-downloads' ),
		'search_items'		=> __( 'Search Tags', 'delightful-downloads' ),
		'popular_items'		=> __( 'Popular Tags', 'delightful-downloads' )
	);

	$tag_args = array(
		'labels'			=> apply_filters( 'dedo_ddownload_tag_labels', $labels ),
		'public'			=> true,
		'show_in_nav_menus'	=> false,
		'show_tag_cloud'	=> false,
		'show_admin_column'	=> true,
		'hierarchical'		=> false
	);

	// Only register if enabled in settings
	if ( $dedo_options['enable_taxonomies'] ) {
		register_taxonomy( 'ddownload_category', array( 'dedo_download' ), $category_args );
		register_taxonomy( 'ddownload_tag', array( 'dedo_download' ), $tag_args );
	}
}
add_action( 'init', 'dedo_download_taxonomies', 3 );



