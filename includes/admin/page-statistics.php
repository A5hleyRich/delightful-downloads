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
	add_submenu_page( 'edit.php?post_type=dedo_download', __( 'Download Statistics', 'delightful-downloads' ), __( 'Statistics', 'delightful-downloads' ), 'manage_options', 'dedo_statistics', 'dedo_render_page_statistics' );
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
		<h2><?php _e( 'Download Statistics', 'delightful-downloads' ); ?></h2>

		<div id="dedo-settings-main">	
			
			
		</div>
	</div>
	<?php
}