<?php
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

	$dedo_statistics_page = add_submenu_page( 'edit.php?post_type=dedo_download', __( 'Download Statistics', 'delightful-downloads' ), __( 'Statistics', 'delightful-downloads' ), 'manage_options', 'dedo_statistics', 'dedo_render_page_statistics' );

	add_action( "load-$dedo_statistics_page", 'dedo_statistics_screen_options' );
}
add_action( 'admin_menu', 'dedo_register_page_statistics', 5 );

/**
 * Render Statistics Page
 *
 * @since  1.4
 */
function dedo_render_page_statistics() {
	
	?>
	<div class="wrap">
		<h2><?php _e( 'Download Statistics', 'delightful-downloads' ); ?>
			<a href="<?php echo admin_url( 'edit.php?post_type=dedo_download&page=dedo_statistics&action=empty_logs' ) ?>" class="add-new-h2"><?php _e( 'Empty Logs', 'delightful-downloads' ); ?></a>
		</h2>

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
 * Statistics Page Actions
 *
 * @since  1.4
 */
function dedo_statistics_actions() {

	//Only perform on statistics page
	if ( isset( $_GET['page'] ) && 'dedo_statistics' == $_GET['page'] ) {

		// Empty statistics
		if( isset( $_GET['action'] ) && 'empty_logs' == $_GET['action'] ) {
			
			global $dedo_statistics;

			$result = $dedo_statistics->empty_table();

			if ( false === $result ) {

				// Error
				add_action( 'admin_notices', function() {
					printf( '<div class="error"><p>%s</p></div>', __( 'Logs could not be deleted.', 'delightful-downloads' ) );
				} );
			}
			else {

				// Success
				add_action( 'admin_notices', function() {
					printf( '<div class="updated"><p>%s</p></div>', __( 'Logs deleted successfully.', 'delightful-downloads' ) );
				} );
			}
		}
	}
}
add_action( 'init', 'dedo_statistics_actions', 1 );

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