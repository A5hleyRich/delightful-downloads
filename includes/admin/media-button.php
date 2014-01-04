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
	
	return $context . '<a href="#" id="dedo-media-button" class="button add-download" data-editor="content" title="Add Download"><span class="wp-media-buttons-icon"></span>Add Download</a>';	
}
add_filter( 'media_buttons_context', 'dedo_media_button' );

/**
 * Add Modal Window to Footer
 *
 * @since  1.0
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
					</div>
					<div class="dedo-download-list">
						<table class="wp-list-table widefat" cellspacing="0">
							<thead>
								<tr>
									<th scope="col" class="title_column"><?php _e( 'Title', 'delightful-downloads' ); ?></th>
									<th scope="col" class="size_column"><?php _e( 'Size', 'delightful-downloads' ); ?></th>
									<th scope="col" class="count_column"><?php _e( 'Downloads', 'delightful-downloads' ); ?></th>
								</tr>
							</thead>

							<tbody id="the-list">
<?php
	while ( $downloads->have_posts() ) {
		$downloads->the_post();
		$download_id = get_the_ID();
		$download_size = dedo_format_filesize( get_post_meta( $download_id, '_dedo_file_size', true ) );
		$download_count = dedo_format_number( get_post_meta( $download_id, '_dedo_file_count', true ) );

?>
								<tr id="dedo-download-<?php echo esc_attr( $download_id ); ?>" data-id="<?php echo esc_attr( $download_id ); ?>" data-size="<?php echo esc_attr( $download_size ); ?>" data-count="<?php echo esc_attr( $download_count ); ?>">
									<td class="title_column"><strong><?php the_title(); ?></strong></td>			
									<td class="size_column"><?php echo esc_attr( $download_size ); ?></td>
									<td class="count_column"><?php echo esc_attr( $download_count ); ?></td>
								</tr>
<?php
	}
?>	
			
							</tbody>
						</table>
					</div>
				</div>
				<div class="right-panel">
					<div class="download-details" style="display: none">
						<h3><?php _e( 'Download Details', 'delightful-downloads' ); ?></h3>
						<div class="meta">
							<div class="title"><strong></strong></div>
							<div class="count"><?php _e( 'Downloads:', 'delightful-downloads' ); ?> <span></span></div>
							<div class="size"></div>
						</div>
						<label for="dedo-download-text"><?php _e( 'Text', 'delightful-downloads' ); ?>:</label>
						<input type="text" name="dedo-download-text" id="dedo-download-text" placeholder="Default" />
						<label for="dedo-download-style"><?php _e( 'Style', 'delightful-downloads' ); ?>:</label>
						<select name="dedo-download-style" id="dedo-download-style">
							<option value="dedo_default"><?php _e( 'Default', 'delightful-downloads' ); ?></option>
							<?php
							$styles = dedo_get_shortcode_styles();
							
							foreach ( $styles as $key => $value ) {
								echo '<option value="' . $key . '">' . $value['name'] . '</option>';	
							}
							?>
						</select>
						<div class="dedo-download-color-container" style="display: none">
							<label for="dedo-download-color"><?php _e( 'Color', 'delightful-downloads' ); ?>:</label>
							<select name="dedo-download-color" id="dedo-download-color">
								<option value="dedo_default"><?php _e( 'Default', 'delightful-downloads' ); ?></option>
								<?php
								$colors = dedo_get_shortcode_buttons();

								foreach ( $colors as $key => $value ) {
									echo '<option value="' . $key . '">' . $value['name'] . '</option>';	
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
add_action( 'admin_footer', 'dedo_media_modal', 100 );