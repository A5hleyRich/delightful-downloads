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
	
	global $post, $dedo_options;;
	 
	$plupload_init = array(
		'runtimes'            => 'html5, silverlight, flash, html4',
		'browse_button'       => 'dedo-upload-button',
		'container'           => 'plupload-container',
		'file_data_name'      => 'async-upload',            
		'multiple_queues'     => false,
		'multi_selection'	  => false,
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
	
	$filebrowser_init = array(
		'root'		=> dedo_get_upload_dir( 'basedir' ) . '/',
		'url'		=> dedo_get_upload_dir( 'baseurl' ) . '/',
		'script'	=> DEDO_PLUGIN_URL . 'includes/js/jqueryFileTree/connectors/jqueryFileTree.php'
	);
	
	?>
	
	<script type="text/javascript">
		var plupload_args = <?php echo json_encode( $plupload_init ); ?>;
		var filebrowser_args = <?php echo json_encode( $filebrowser_init ); ?>;
	</script>
	
	<div id="plupload-container">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'File URL:', 'delightful-downloads' ); ?>
					</th>
					<td id="plupload-file">
						<input type="text" name="dedo-file-url" id="dedo-file-url" value="<?php echo $file_url ?>" class="large-text" placeholder="<?php _e( 'Enter file URL here', 'delightful-downloads' ); ?>" />
						<p class="description"><?php printf( __( 'You may manually enter a file URL here, for files that are already uploaded to the server. Please note that only files within the WordPress directory structure are allowed (for example: %s). Alternatively, you can browse to the file using the file browser, or upload a new file:', 'delightful-downloads' ), dedo_get_upload_dir( 'dedo_baseurl' ) ); ?></p>
						<!-- Display with JS tuned on-->
						<div class="hide-if-no-js">
							<input id="dedo-upload-button" type="button" value="<?php _e( 'Upload File', 'delightful-downloads' ); ?>" class="button" />
							<input id="dedo-select-button" type="button" value="<?php _e( 'Select Existing File', 'delightful-downloads' ); ?>" class="button" />
							<div id="dedo-file-upload">
								<p id="plupload-error" class="error" style="display: none"></p>
								<div id="plupload-progress" style="display: none">
									<div class="bar" style="width: 0%"></div>
									<div class="percent"><p>Uploading...</p></div>
								</div>
							</div>
							<div id="dedo-file-browser" style="display: none"></div>
						</div>
						<!-- Display with JS tuned off-->
						<div class="hide-if-js">
							<input type="file" name="dedo-async-upload" id="dedo-async-upload" />
						</div>
						<p class="description"><?php printf( __( 'Maximum upload file size: %s.', 'delightful-downloads' ), dedo_human_filesize( wp_max_upload_size() ) ); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<?php _e( 'File Size:', 'delightful-downloads' ); ?>
					</th>
					<td id="plupload-file-size">
						<?php echo ($file_size !== '' ? dedo_human_filesize( $file_size ) : '-----' ); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<?php
}

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
 * Save meta boxes
 *
 * @param int $post_id current post id
 *
 * @return void
 */
function dedo_meta_boxes_save( $post_id ) {
	// Check for autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	
	// Check for revision
	if ( isset( $post->post_type ) && $post->post_type == 'revision' ) {
		return;
	}
	
	// Handle no-ajax file uploads
	if( isset( $_FILES['dedo-async-upload'] ) && $_FILES['dedo-async-upload']['size'] > 0 ) {
		// Set upload dir
		add_filter( 'upload_dir', 'dedo_set_upload_dir' );
	
		// Upload the file
		$file = wp_handle_upload( $_FILES['dedo-async-upload'], array( 'test_form' => false ) );
	
		// Check for success
		if( $file ) {
			// Add/update post meta
			update_post_meta( $post_id, '_dedo_file_url', $file['url'] );
			update_post_meta( $post_id, '_dedo_file_size', $_FILES['dedo-async-upload']['size'] );
		}
		else {
			// Display upload error
			$notices = get_option( 'delightful-downloads-notices', array() );
			$notices[] = '<div class="error"><p>' . __( 'There was an error when uploading the file. Please try again.', 'delightful-downloads' ) . '</p></div>';
			update_option( 'delightful-downloads-notices', $notices );
		}
	}
	// No file present, lets save post URL if isset
	else {
		if( isset( $_POST['dedo-file-url'] ) ) {
			$file_url = trim( $_POST['dedo-file-url'] );
			$file_path = dedo_url_to_absolute( $file_url );
			
			// Does file exist?
			if( file_exists( $file_path ) ) {
				
				// Check the file is within the WordPress directory structure
				if( strpos( $file_url, site_url() ) !== false ) {
					$file_size = filesize( $file_path );
					
					update_post_meta( $post_id, '_dedo_file_url', $file_url );
					update_post_meta( $post_id, '_dedo_file_size', $file_size );
				}
				else {
					// Display file location error
					$notices = get_option( 'delightful-downloads-notices', array() );
					$notices[] = '<div class="error"><p>' . sprintf( __( 'The file must be saved within the WordPress directory structure. (For example: %s)', 'delightful-downloads' ), dedo_get_upload_dir( 'dedo_baseurl' ) ) . '</p></div>';
					update_option( 'delightful-downloads-notices', $notices );
				}
			}
			else {
				// Display file does not exist error
				$notices = get_option( 'delightful-downloads-notices', array() );
				$notices[] = '<div class="error"><p>' . __( 'The file specified does not exist!', 'delightful-downloads' ) . '</p></div>';
				update_option( 'delightful-downloads-notices', $notices );
			}
		}
	}
	
	// Save download count
	if( isset( $_POST['dedo_file_count'] ) ) {
		update_post_meta( $post_id, '_dedo_file_count', strip_tags( trim( $_POST['dedo_file_count'] ) ) );
	}
}
add_action( 'save_post', 'dedo_meta_boxes_save' );

/**
 * Display notice to user, resolves issue with post redirect
 *
 * @return void
 */
function dedo_meta_boxes_notice() {
	if( $notices = get_option( 'delightful-downloads-notices' ) ) {
		foreach( $notices as $notice ) {
			echo $notice;
		}
		delete_option( 'delightful-downloads-notices' );
	}
}
add_action( 'admin_notices', 'dedo_meta_boxes_notice' );