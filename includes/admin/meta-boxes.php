<?php
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
	// add_meta_box( 'dedo_stats', __( 'Download Stats', 'delightful-downloads' ), 'dedo_meta_box_stats', 'dedo_download', 'side', 'core' );
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
		  date_i18n( __( 'M j, Y @ G:i', 'delightful-downloads' ), strtotime( $post->post_date ) ) ),
		10 => __('Download draft updated.', 'delightful-downloads'),
	);

	return $messages;
}
add_action( 'post_updated_messages', 'dedo_update_messages' );

/**
 * Render Download Metabox
 *
 * @since  1.5
 */
function dedo_meta_box_download( $post ) {
	
	$file_url = get_post_meta( $post->ID, '_dedo_file_url', true );
	$file_size = size_format( get_post_meta( $post->ID, '_dedo_file_size', true ), 1 );
	
	?>
	
	<div id="dedo-new-download" style="display: block;">		
		<a href="#dedo-upload-modal" class="button dedo-modal-action"><?php _e( 'Upload File', 'delightful-downloads' ); ?></a>
		<a href="#dedo-select-modal" class="button dedo-modal-action"><?php _e( 'Existing File', 'delightful-downloads' ); ?></a>
	</div>

	<div id="dedo-existing-download" style="display: none;">		
		<div class="left-column">
			<img src="#" id="dedo-download-icon">
			<a href="#dedo-delete-modal" class="button delete"><?php _e( 'Delete File', 'delightful-downloads' ); ?></a>
		</div>
		<div class="right-column">
			test
		</div>
	</div>

	<?php
	
}

/**
 * Render Upload Modal
 *
 * @since  1.5
 */
function dedo_render_part_upload() {

	// Ensure only added on add/edit screen
	$screen = get_current_screen();

	if ( 'post' !== $screen->base || 'dedo_download' !== $screen->post_type ) {

		return;
	}

	?>

	<div id="dedo-upload-modal" class="dedo-modal" style="display: none; width: 40%; left: 50%; margin-left: -20%;">
		<a href="#" class="dedo-modal-close" title="Close"><span class="media-modal-icon"></span></a>
		<div class="dedo-modal-content">
			<h1><?php _e( 'Upload File', 'delightful-downloads' ); ?></h1>
			<p><?php _e( 'Select a Delightful Downloads settings file to import:', 'delightful-downloads' ); ?></p>
			
		</div>
	</div>

	<?php

}
add_action( 'admin_footer', 'dedo_render_part_upload' );

/**
 * Render Select Modal
 *
 * @since  1.5
 */
function dedo_render_part_select() {

	// Ensure only added on add/edit screen
	$screen = get_current_screen();

	if ( 'post' !== $screen->base || 'dedo_download' !== $screen->post_type ) {

		return;
	}

	// File browser args
	$filebrowser_init = array(
		'root'			=> dedo_get_upload_dir( 'basedir' ) . '/',
		'url'			=> dedo_get_upload_dir( 'baseurl' ) . '/',
		'script'		=> DEDO_PLUGIN_URL . 'assets/js/jqueryFileTree/connectors/jqueryFileTree.php'
	);

	?>

	<script type="text/javascript">
		var filebrowser_args = <?php echo json_encode( $filebrowser_init ); ?>;
	</script>

	<div id="dedo-select-modal" class="dedo-modal" style="display: none; width: 40%; left: 50%; margin-left: -20%;">
		<a href="#" class="dedo-modal-close" title="Close"><span class="media-modal-icon"></span></a>
		<div class="dedo-modal-content">
			<h1><?php _e( 'Existing File', 'delightful-downloads' ); ?></h1>
			<p><?php _e( 'Manaully enter a file URL, or use the file browser.', 'delightful-downloads' ); ?></p>
			<p>	
				<input name="dedo-select-url" id="dedo-select-url" type="url" class="large-text" placeholder="<?php _e( 'File path or URL...', 'delightful-downloads' ); ?>" />
			</p>
			<p>
				<div id="dedo-file-browser"><p><?php _e( 'Loading...', 'delightful-downloads' ); ?></p></div>
			</p>
			<p>
				<a href="#" id="dedo-select-done" class="button button-primary">Done</a>
			</p>
		</div>
	</div>

	<?php

}
add_action( 'admin_footer', 'dedo_render_part_select' );

/**
 * Render stats meta box
 *
 * @since  1.0
 */
function dedo_meta_box_stats( $post ) {
	$file_count = get_post_meta( $post->ID, '_dedo_file_count', true );
	?>
	<div id="dedo-file-stats-container">
		<label for="dedo_file_count"><?php _e( 'Count' , 'delightful-downloads' ); ?>:</label>
		<?php wp_nonce_field( 'ddownload_stats_save', 'ddownload_stats_save_nonce' ); ?>
		<input type="text" name="dedo_file_count" class="large-text" value="<?php echo ($file_count !== '' ? esc_attr( $file_count ) : 0 ); ?>" />
	</div>
	
	<?php
}

/**
 * Save meta boxes
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
	
	// Check for save stats nonce
	if ( isset( $_POST['ddownload_file_save_nonce'] ) && wp_verify_nonce( $_POST['ddownload_file_save_nonce'], 'ddownload_file_save' ) ) {	
		
		// Save file url
		if ( isset( $_POST['dedo-file-url'] ) && !empty( $_POST['dedo-file-url'] ) ) {
			
			$file_url = trim( $_POST['dedo-file-url'] );
			
			if ( !$file_path = dedo_get_abs_path( $file_url ) ) {

				// No file found locally, attempt to get file size from remote
				$response = get_headers( $file_url, 1 );
				
				if ( 'HTTP/1.1 404 Not Found' !== $response[0] && isset( $response['Content-Length'] )  ) {
					
					$file_size = $response['Content-Length'];
				}
				else {

					$file_size = 0;
				}

			}
			else {
				
				$file_size = filesize( $file_path );
			}

		}
		else {
			
			$file_size = 0;
			$file_url = '';
		}

		update_post_meta( $post_id, '_dedo_file_url', $file_url );
		update_post_meta( $post_id, '_dedo_file_size', $file_size );
	}
	
	// Check for save stats nonce
	if ( isset( $_POST['ddownload_stats_save_nonce'] ) && wp_verify_nonce( $_POST['ddownload_stats_save_nonce'], 'ddownload_stats_save' ) ) {
		
		// Save download count
		if ( isset( $_POST['dedo_file_count'] ) ) {
			update_post_meta( $post_id, '_dedo_file_count', strip_tags( trim( $_POST['dedo_file_count'] ) ) );
		}

	}
}
add_action( 'save_post', 'dedo_meta_boxes_save' );