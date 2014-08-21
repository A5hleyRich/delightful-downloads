<?php
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
 *
 * @since  1.0
 */
function dedo_media_button( $context ) {
	
	return $context . '<a href="#dedo-shortcode-modal" id="dedo-media-button" class="button dedo-modal-action add-download" data-editor="content" title="Add Download"><span class="wp-media-buttons-icon"></span>Add Download</a>';	
}
add_filter( 'media_buttons_context', 'dedo_media_button' );

/**
 * Add Modal Window to Footer
 *
 * @since  1.0
 */
function dedo_media_modal() {
	global $pagenow;

	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) { 
		?>
			
			<div id="dedo-shortcode-modal" class="dedo-modal" style="display: block; width: 40%; left: 50%; margin-left: -20%;">
				<a href="#" class="dedo-modal-close" title="Close"><span class="media-modal-icon"></span></a>
				<div class="dedo-modal-content">
					<h1><?php _e( 'Insert Download', 'delightful-downloads' ); ?></h1>
					
					<p>
						<select id="dedo-select-download-dropdown">
							<option>Test</option>
						</select>
					</p>
				</div>
			</div>

		<?php 
	}
}
add_action( 'admin_footer', 'dedo_media_modal', 100 );