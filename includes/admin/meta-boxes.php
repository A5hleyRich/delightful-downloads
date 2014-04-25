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
	add_meta_box( 'dedo_file', __( 'File', 'delightful-downloads' ), 'dedo_meta_box_file', 'dedo_download', 'normal', 'high' );
	add_meta_box( 'dedo_stats', __( 'Download Stats', 'delightful-downloads' ), 'dedo_meta_box_stats', 'dedo_download', 'side', 'core' );
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
 * Render File Metabox
 *
 * @since  1.0
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
		'script'	=> DEDO_PLUGIN_URL . 'assets/js/jqueryFileTree/connectors/jqueryFileTree.php'
	);
	
	?>
	
	<script type="text/javascript">
		var plupload_args = <?php echo json_encode( $plupload_init ); ?>;
		var filebrowser_args = <?php echo json_encode( $filebrowser_init ); ?>;
	</script>
	
	<div id="plupload-container">
		<label for="dedo-file-url"><?php _e( 'File URL:', 'delightful-downloads' ); ?></label>		
		<div class="file-url-container">	
			<input type="text" name="dedo-file-url" id="dedo-file-url" value="<?php echo esc_attr( $file_url ); ?>" class="large-text" placeholder="<?php _e( 'Upload or enter the file URL.', 'delightful-downloads' ); ?>" />
			<span class="remove" style="display: none"><a href="#">Remove</a></span>
		</div>
		<?php wp_nonce_field( 'ddownload_file_save', 'ddownload_file_save_nonce' ); ?>
		
		<div id="plupload-file">
			<input id="dedo-upload-button" type="button" value="<?php _e( 'Upload File', 'delightful-downloads' ); ?>" class="button" />
			<input id="dedo-select-button" type="button" value="<?php _e( 'Select Existing File', 'delightful-downloads' ); ?>" class="button" />
			<span class="description"><?php printf( __( 'Maximum upload file size: %s.', 'delightful-downloads' ), dedo_format_filesize( wp_max_upload_size() ) ); ?></span>
			<div id="dedo-file-upload">
				<p id="plupload-error" class="error" style="display: none"></p>
				<div id="plupload-progress" style="display: none">
					<div class="bar" style="width: 50%"></div>
					<div class="percent"><p>Uploading...</p></div>
				</div>
			</div>
			<div id="dedo-file-browser" style="display: none"></div>
		</div>
	</div>
	<?php
}

/**
 * Render stats meta box
 *
 * @since  1.0
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
						<?php wp_nonce_field( 'ddownload_stats_save', 'ddownload_stats_save_nonce' ); ?>
					</th>
					<td>
						<input type="text" name="dedo_file_count" class="text-small" value="<?php echo ($file_count !== '' ? esc_attr( $file_count ) : 0 ); ?>" />
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
	if ( !current_user_can( 'edit_post' ) ) {
		return;
	}
	
	// Check for save stats nonce
	if ( isset( $_POST['ddownload_file_save_nonce'] ) && wp_verify_nonce( $_POST['ddownload_file_save_nonce'], 'ddownload_file_save' ) ) {	
		// Save file url
		if ( isset( $_POST['dedo-file-url'] ) ) {
			$file_url = trim( $_POST['dedo-file-url'] );
			$file_path = dedo_url_to_absolute( $file_url );
			
			// Does file exist?
			if ( file_exists( $file_path ) ) {
				update_post_meta( $post_id, '_dedo_file_url', $file_url );
				update_post_meta( $post_id, '_dedo_file_size', filesize( $file_path ) );
			}
			else {
				// Display file does not exist error
				$notices = get_option( 'delightful-downloads-notices', array() );
				$notices[] = '<div class="error"><p>' . sprintf( __( 'The file does not exist! Please check the URL and ensure it is within the WordPress directory structure. (For example: %s)', 'delightful-downloads' ), dedo_get_upload_dir( 'dedo_baseurl' ) ) . '</p></div>';
				update_option( 'delightful-downloads-notices', $notices );
			}
		}
	}
	
	// Check for save stats nonce
	if ( isset( $_POST['ddownload_stats_save_nonce'] ) && wp_verify_nonce( $_POST['ddownload_stats_save_nonce'], 'ddownload_stats_save' ) ) {
		
		// Save download count
		if ( isset( $_POST['dedo_file_count'] ) ) {
			update_post_meta( $post_id, '_dedo_file_count', strip_tags( trim( $_POST['dedo_file_count'] ) ) );
		}

	}

	// Clear transients
	dedo_delete_all_transients();
}
add_action( 'save_post', 'dedo_meta_boxes_save' );

/**
 * Display notice to user, resolves issue with post redirect
 *
 * @since  1.0
 */
function dedo_meta_boxes_notice() {
	if ( $notices = get_option( 'delightful-downloads-notices' ) ) {
		
		foreach ( $notices as $notice ) {
			echo $notice;
		}

		delete_option( 'delightful-downloads-notices' );
	}
}
add_action( 'admin_notices', 'dedo_meta_boxes_notice' );