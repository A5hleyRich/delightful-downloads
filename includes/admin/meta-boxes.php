<?php
/**
 * @package Meta-Boxes
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register meta boxes
 *
 * @return void
 */
function dedo_register_meta_boxes() {
	// File
	add_meta_box( 'dedo_file', __( 'File', 'delightful-downloads' ), 'dedo_meta_box_file', 'dedo_download', 'normal', 'high' );
	// Stats
	add_meta_box( 'dedo_stats', __( 'Download Stats', 'delightful-downloads' ), 'dedo_meta_box_stats', 'dedo_download', 'side', 'core' );
}
add_action( 'add_meta_boxes', 'dedo_register_meta_boxes' );

/**
 * Add correct enc type for non-ajax uploads
 *
 * @return void
 */
function dedo_form_enctype() {
	if( get_post_type() == 'dedo_download' ) {
		echo ' enctype="multipart/form-data"';
	}
}
add_action( 'post_edit_form_tag', 'dedo_form_enctype' );

/**
 * Render file meta box
 *
 * @param object $post current post object
 *
 * @return void
 */
function dedo_meta_box_file( $post ) {
	$file_url = get_post_meta( $post->ID, '_dedo_file_url', true );
	$file_size = get_post_meta( $post->ID, '_dedo_file_size', true );
	
	global $post;
	 
	$plupload_init = array(
		'runtimes'            => 'html5, silverlight, flash, html4',
		'browse_button'       => 'plupload-browse-button',
		'container'           => 'plupload-container',
		'file_data_name'      => 'async-upload',            
		'multiple_queues'     => false,
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
		),
	);
	 
	// Pass to plupload
	$plupload_init = apply_filters( 'plupload_init', $plupload_init );
	
	?>
	
	<script type="text/javascript">
		var plupload_args = <?php echo json_encode( $plupload_init ); ?>;
	</script>
	
	<div id="plupload-container" class="hide-if-no-js">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						File URL:
					</th>
					<td id="plupload-file">
						<?php echo ($file_url !== '' ? $file_url : '-----' ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						File Size:
					</th>
					<td id="plupload-file-size">
						<?php echo ($file_size !== '' ? dedo_human_filesize( $file_size ) : '-----' ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
					</th>
					<td>
						<input id="plupload-browse-button" type="button" value="<?php _e( 'Select File', 'delightful-downloads' ); ?>" class="button" />
						<input id="plupload-upload-button" type="button" value="<?php _e( 'Upload', 'delightful-downloads' ); ?>" class="button" style="display: none" />
						<a id="plupload-cancel-button" href="#" style="display: none">Cancel</a>
						<p class="description"><?php printf( __( 'Maximum file size: %s.', 'delightful-downloads' ), dedo_human_filesize( wp_max_upload_size() ) ); ?></p>

						<div id="plupload-progress" style="display: none">
							<div class="bar" style="width: 0%"></div>
							<div class="percent"><p>Uploading...</p></div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	
	<div id="plupload-container" class="hide-if-js">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						File URL:
					</th>
					<td id="plupload-file">
						<?php echo ($file_url !== '' ? $file_url : '-----' ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						File Size:
					</th>
					<td id="plupload-file-size">
						<?php echo ($file_size !== '' ? dedo_human_filesize( $file_size ) : '-----' ); ?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
					</th>
					<td>
						<label for="async-upload"><?php _e( 'Upload', 'delightful-downloads' ); ?>:</label>
						<input type="file" name="async-upload" id="async-upload" />
						<p class="description"><?php printf( __( 'Maximum file size: %s.', 'delightful-downloads' ), dedo_human_filesize( wp_max_upload_size() ) ); ?></p>					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Save file metabox
 *
 * @param object $post current post object
 *
 * @return void
 */
function dedo_meta_box_file_save( $post_id ) {
	// First check we have file present
	if( isset( $_FILES['async-upload'] ) && $_FILES['async-upload']['size'] > 0 ) {
		// Set upload dir
		add_filter( 'upload_dir', 'dedo_set_upload_dir' );
	
		// Upload the file
		$file = wp_handle_upload( $_FILES['async-upload'], array( 'test_form' => false ) );
	
		// Check for success
		if( isset( $file['file'] ) ) {
			// Post ID
			$post_id = $_REQUEST['post_id'];
		
			// Add/update post meta
			update_post_meta( $post_id, '_dedo_file_url', $file['url'] );
			update_post_meta( $post_id, '_dedo_file_size', $_FILES['async-upload']['size'] );
			update_post_meta( $post_id, '_dedo_file_type', $file['type'] );
		}	
	}
}
add_action( 'save_post', 'dedo_meta_box_file_save' );

/**
 * Render stats meta box
 *
 * @param object $post current post object
 *
 * @return void
 */
function dedo_meta_box_stats( $post ) {
	$file_count = get_post_meta( $post->ID, '_dedo_file_count', true );
	?>
	<div id="dedo-file-stats-container">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="dedo_file_count"><?php _e( 'Count' , 'delightful-downloads' ); ?>:</label>
					</th>
					<td>
						<input type="text" name="dedo_file_count" class="text-small" value="<?php echo ($file_count !== '' ? $file_count : 0 ); ?>" />
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	
	<?php
}

/**
 * Save stats meta box
 *
 * @param int $post_id current post id
 *
 * @return void
 */
function dedo_meta_box_stats_save( $post_id ) {
	if( isset( $_POST['dedo_file_count'] ) ) {
		update_post_meta( $post_id, '_dedo_file_count', strip_tags( trim( $_POST['dedo_file_count'] ) ) );
	}
}
add_action( 'save_post', 'dedo_meta_box_stats_save' );