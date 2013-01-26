<?php
/**
 * @package Media Button
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Display Media Button
 *
 * @param string $content existing media buttons
 *
 * @return string $content + $output
 */
function dedo_media_button( $context ) {
	
	if( get_post_type() != 'dedo_download' ) {
		return $context . '<a href="#" id="dedo-media-button" class="button add-download" data-editor="content" title="Add Download"><span class="wp-media-buttons-icon"></span>Add Download</a>';
	}	
}
add_filter( 'media_buttons_context', 'dedo_media_button' );

/**
 * Add Modal Window to Footer
 *
 * @return void
 */
function dedo_media_modal() {
	global $dedo_options;
	
	$downloads = new WP_Query( 'post_type=dedo_download&nopaging=true&orderby=title&order=ASC' );
	?>
	<div id="dedo-download-modal" style="display: none">
		<div class="media-modal">
			<a id="dedo-download-modal-close" class="media-modal-close" href="#" title="Close"><span class="media-modal-icon"></span></a>
			<div class="media-modal-content">
				<div class="media-frame-title">
					<h1><?php _e( 'Insert Download', 'delightful-downloads' ); ?></h1>
				</div>
				<div class="left-panel">
					<div class="dedo-download-toolbar">
						<input type="search" id="dedo-download-search" class="search" placeholder="<?php _e( 'Search', 'delightful-downloads' ); ?>" />
						<a href="#" id="dedo-total-count-button" class="button"><?php _e( 'Insert Total Download Count', 'delightful-downloads' ); ?></a>
					</div>
					<div class="dedo-download-list">
						<ul id="selectable_list">
							<?php
							while ( $downloads->have_posts() ) {
								$downloads->the_post();
								$download_id = get_the_ID();
								$download_size = dedo_human_filesize( get_post_meta( $download_id, '_dedo_file_size', true ) );
								echo '<li data-ID="' .$download_id  . '"  data-size="' .$download_size  . '">';
								echo '<strong>' . get_the_title() . ' <span>(' . __( 'ID:', 'delightful-downloads' ) . ' ' . $download_id . ')</span></strong>';
								echo '<span class="download_meta">' . get_post_meta( $download_id, '_dedo_file_url', true ) . '</span>';
								echo '</li>';
							}
							?>
						</ul>
					</div>
				</div>
				<div class="right-panel">
					<div class="download-details" style="display: none">
						<h3><?php _e( 'Download Details', 'delightful-downloads' ); ?></h3>
						<div class="meta"></div>
						<label for="dedo-download-text"><?php _e( 'Text', 'delightful-downloads' ); ?>:</label>
						<input type="text" name="dedo-download-text" id="dedo-download-text" value="<?php echo $dedo_options['default_text']; ?>"/>
						<label for="dedo-download-style"><?php _e( 'Style', 'delightful-downloads' ); ?>:</label>
						<select name="dedo-download-style" id="dedo-download-style">
							<?php
							$styles = dedo_get_shortcode_styles();
							$default_style = $dedo_options['default_style'];
							
							foreach( $styles as $key => $value ) {
								$selected = ( $default_style == $key ? ' selected="selected"' : '' );
								echo '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';	
							}
							?>
						</select>
						<div class="dedo-download-color-container">
							<label for="dedo-download-color"><?php _e( 'Color', 'delightful-downloads' ); ?>:</label>
							<select name="dedo-download-color" id="dedo-download-color">
								<?php
								$colors = dedo_get_shortcode_colors();
								$default_color = $dedo_options['default_color'];

								foreach( $colors as $key => $value ) {
									$selected = ( $default_color == $key ? ' selected="selected"' : '' );
									echo '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';	
								}
								?>
							</select>
						</div>
						<a href="#" id="dedo-download-button" class="button-primary"><?php _e( 'Insert Download', 'delightful-downloads' ); ?></a>
						<a href="#" id="dedo-filesize-button" class="button"><?php _e( 'Insert File Size', 'delightful-downloads' ); ?></a>
						<a href="#" id="dedo-count-button" class="button"><?php _e( 'Insert Download Count', 'delightful-downloads' ); ?></a>
					</div>
				</div>
						
			</div>
		</div>
		<div class="media-modal-backdrop"></div>
	</div>
	<?php
}
add_action( 'admin_footer', 'dedo_media_modal' );