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
	global $pagenow;

	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) ) { 
		$context .= '<a href="#dedo-shortcode-modal" id="dedo-media-button" class="button dedo-modal-action add-download" data-editor="content" title="Add Download"><span class="wp-media-buttons-icon"></span>Add Download</a>';	
	}

	return $context;
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
			
			<div id="dedo-shortcode-modal" class="dedo-modal" style="display: block; width: 40%; left: 50%; margin-left: -20%;">
				<a href="#" class="dedo-modal-close" title="<?php _e( 'Close', 'delightful-downloads' ); ?>"><span class="media-modal-icon"></span></a>
				<div class="dedo-modal-content">
					<h1><?php _e( 'Insert Download', 'delightful-downloads' ); ?></h1>
					<?php if ( $downloads ) : ?>
						<p>
							<select id="dedo-select-download-dropdown" data-placeholder="<?php _e( 'Choose a download...', 'delightful-downloads' ); ?>">
								<option></option>
								<?php foreach ( $downloads as $download ) : ?>
									<option value="<?php echo $download->ID; ?>"><?php echo $download->post_title; ?></option>
								<?php endforeach; ?>
							</select>
						</p>
						<p>
							<label for=""><?php _e( 'Style', 'delightful-downloads' ); ?></label>
							<select id="dedo-select-style-dropdown" data-placeholder="<?php _e( 'Inherit', 'delightful-downloads' ); ?>" >
								<option></option>
								<?php foreach ( $styles as $key => $value ) : ?>
									<option value="<?php echo $key; ?>"><?php echo $value['name']; ?></option>
								<?php endforeach; ?>
							</select>

							<label for=""><?php _e( 'Button', 'delightful-downloads' ); ?></label>
							<select id="dedo-select-button-dropdown" data-placeholder="<?php _e( 'Inherit', 'delightful-downloads' ); ?>" >
								<option></option>
								<?php foreach ( $buttons as $key => $value ) : ?>
									<option value="<?php echo $key; ?>"><?php echo $value['name']; ?></option>
								<?php endforeach; ?>
							</select>
						</p>
					<?php endif; ?>
				</div>
			</div>

		<?php 
	}
}
add_action( 'admin_footer', 'dedo_media_modal', 100 );