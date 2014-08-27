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