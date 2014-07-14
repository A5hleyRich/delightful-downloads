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
	global $post;

	$file = get_post_meta( $post->ID, '_dedo_file', true );
	$file_url = ( isset( $file['download_url'] ) ? $file['download_url'] : '' );
	$file_size = ( isset( $file['download_size'] ) ? size_format( $file['download_size'], 1 ) : '' );

	// Update status args
	$status_args = array(
		'ajaxURL'		=> admin_url( 'admin-ajax.php', isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://' ),
		'nonce' 		=> wp_create_nonce( 'dedo_download_update_status' ),
		'action'    	=> 'dedo_download_update_status',
		'default_icon'	=> dedo_get_file_icon( 'default' )
	);

	// Plupload args
	$plupload_args = array(
		'runtimes'            => 'html5, silverlight, flash, html4',
		'browse_button'       => 'dedo-upload-button',
		'container'           => 'dedo-upload-container',
		'drop_element'		  => 'dedo-drag-drop-area',
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
		)
	);

	// File browser args
	$file_browser_args = array(
		'root'			=> dedo_get_upload_dir( 'basedir' ) . '/',
		'url'			=> dedo_get_upload_dir( 'baseurl' ) . '/',
		'script'		=> DEDO_PLUGIN_URL . 'assets/js/jqueryFileTree/connectors/jqueryFileTree.php'
	);

	?>

	<script type="text/javascript">
		var updateStatusArgs = <?php echo json_encode( $status_args ); ?>;
	</script>
	
	<div id="dedo-new-download" style="<?php echo ( !isset( $file_url ) || empty( $file_url ) ) ? 'display: block;' : 'display: none;'; ?>">		
		<a href="#dedo-upload-modal" class="button dedo-modal-action"><?php _e( 'Upload File', 'delightful-downloads' ); ?></a>
		<a href="#dedo-select-modal" class="button dedo-modal-action select-existing"><?php _e( 'Existing File', 'delightful-downloads' ); ?></a>
	</div>
	<div id="dedo-existing-download" style="<?php echo ( isset( $file_url ) && !empty( $file_url ) ) ? 'display: block;' : 'display: none;'; ?>">		
		<div class="left-panel">
			<div class="file-icon">	
				<img src="<?php echo dedo_get_file_icon( $file_url ); ?>" />
			</div>
			<div class="file-name"><?php echo dedo_get_file_name( $file_url ); ?></div>
			<div class="file-size"><?php echo $file_size; ?></div>
			<div class="file-status">
				<span class="status spinner"></span>
			</div>
		</div>
		<div class="right-panel">
			<table class="form-table">
				<tbody>
					<?php $file_count = get_post_meta( $post->ID, '_dedo_file_count', true ); ?>
					<?php $file_count = ( !$file_count ) ? 0 : $file_count; ?>
					<tr>
						<th scope="row">
							<?php _e( 'Download Count', 'delightful-downloads' ); ?>
						</th>
						<td>
							<input name="download_count" id="download_count" class="regular-text" type="number" value="<?php echo $file_count; ?>" />
							<p class="description"><?php _e( 'The number of times this file has been downloaded.' ); ?></p>
						</td>
					</tr>
					<?php $members_only = ( isset( $file['options']['members_only'] ) ? $file['options']['members_only'] : '' ); ?>
					<tr>
						<th scope="row">
							<?php _e( 'Members Only', 'delightful-downloads' ); ?>
						</th>
						<td>
							<label for="members_only_true"><input name="members_only" id="members_only_true" type="radio" value="1" <?php echo ( 1 === $members_only ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
							<label for="members_only_false"><input name="members_only" id="members_only_false" type="radio" value="0" <?php echo ( 0 === $members_only ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
							<label for="members_only_inherit"><input name="members_only" id="members_only_inherit" type="radio" value <?php echo ( '' === $members_only ) ? 'checked' : ''; ?> /> <?php _e( 'Inherit', 'delightful-downloads' ); ?></label>
							<p class="description"><?php _e( 'Allow only logged in users to download this file.' ); ?></p>
						</td>
					</tr>
					<?php $members_redirect = ( isset( $file['options']['members_redirect'] ) ? $file['options']['members_redirect'] : '' ); ?>
					<tr id="dedo-members-only-redirect" <?php echo ( 1 !== $members_only ) ? 'style="display: none;"' : ''; ?>>
						<th scope="row">
							<?php _e( 'Non-Members', 'delightful-downloads' ); ?>
						</th>
						<td>
							<?php // Output select input
								$args = array(
									'name'						=> 'members_redirect',
									'depth'						=> 0,
									'selected'					=> $members_redirect,
									'show_option_none'			=> __( 'Inherit', 'delightful-downloads' ),
									'show_option_none_value'	=> '',
									'echo'						=> 0
								);
								
								$list = wp_dropdown_pages( $args );

								// Add option groups
								$list = explode( '<option value="">' . __( 'Inherit', 'delightful-downloads' ) . '</option>', $list );
								$list = implode( '<optgroup label="' . __( 'Global', 'delightful-downloads' ) . '"><option value="">' . __( 'Inherit', 'delightful-downloads' ) . '</option></optgroup><optgroup label="' . __( 'Pages', 'delightful-downloads' ) . '">', $list );
								$list = explode( '</select>', $list );
								$list = implode( '</optgroup></select>', $list );

								echo $list;
							?>
							<p class="description"><?php _e( 'The page to redirect non-members.' ); ?></p>
						</td>
					</tr>
					<?php $open_browser = ( isset( $file['options']['open_browser'] ) ? $file['options']['open_browser'] : '' ); ?>
					<tr>
						<th scope="row">
							<?php _e( 'Open In Browser', 'delightful-downloads' ); ?>
						</th>
						<td>
							<label for="open_browser_true"><input name="open_browser" id="open_browser_true" type="radio" value="1" <?php echo ( 1 === $open_browser ) ? 'checked' : ''; ?> /> <?php _e( 'Yes', 'delightful-downloads' ); ?></label>
							<label for="open_browser_false"><input name="open_browser" id="open_browser_false" type="radio" value="0" <?php echo ( 0 === $open_browser ) ? 'checked' : ''; ?> /> <?php _e( 'No', 'delightful-downloads' ); ?></label>
							<label for="open_browser_inherit"><input name="open_browser" id="open_browser_inherit" type="radio" value <?php echo ( '' === $open_browser ) ? 'checked' : ''; ?> /> <?php _e( 'Inherit', 'delightful-downloads' ); ?></label>
							<p class="description"><?php _e( 'This file will attempt to open in the browser window.', 'delightful-downloads' ); ?></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="footer">
			<a href="#dedo-upload-modal" class="button dedo-modal-action"><?php _e( 'Upload', 'delightful-downloads' ); ?></a>
			<a href="#dedo-select-modal" class="button dedo-modal-action select-existing"><?php _e( 'Select Existing', 'delightful-downloads' ); ?></a>
		</div>
	</div>

	<script type="text/javascript">
		var pluploadArgs = <?php echo json_encode( $plupload_args ); ?>;
	</script>

	<div id="dedo-upload-modal" class="dedo-modal" style="display: none; width: 40%; left: 50%; margin-left: -20%;">
		<a href="#" class="dedo-modal-close" title="Close"><span class="media-modal-icon"></span></a>
		<div id="dedo-upload-container" class="dedo-modal-content">
			<h1><?php _e( 'Upload File', 'delightful-downloads' ); ?></h1>
			<div id="dedo-drag-drop-area">
				<p class="drag-drop-info"><?php _e( 'Drop file here', 'delightful-downloads' ); ?></p>
				<p><?php _e( 'or', 'delightful-downloads' ); ?></p>
				<p class="drag-drop-button"><input id="dedo-upload-button" type="button" value="<?php _e( 'Select File', 'delightful-downloads' ); ?>" class="button" />
			</div>
			<p><?php printf( __( 'Maximum upload file size: %s.', 'delightful-downloads' ), size_format( wp_max_upload_size(), 1 ) ); ?></p>
			<div id="dedo-progress-bar" style="display: none">
				<div id="dedo-progress-percent" style="width: 0%;"></div>
				<div id="dedo-progress-text">0%</div>
			</div>
			<div id="dedo-progress-error" style="display: none"></div>
		</div>
	</div>

	<script type="text/javascript">
		var fileBrowserArgs = <?php echo json_encode( $file_browser_args ); ?>;
	</script>

	<div id="dedo-select-modal" class="dedo-modal" style="display: none; width: 40%; left: 50%; margin-left: -20%;">
		<a href="#" class="dedo-modal-close" title="Close"><span class="media-modal-icon"></span></a>
		<div class="dedo-modal-content">
			<h1><?php _e( 'Existing File', 'delightful-downloads' ); ?></h1>
			<p><?php _e( 'Manaully enter a file URL, or use the file browser.', 'delightful-downloads' ); ?></p>
			<p>	
				<?php wp_nonce_field( 'ddownload_file_save', 'ddownload_file_save_nonce' ); ?>
				<input name="dedo-file-url" id="dedo-file-url" type="text" class="large-text" value="<?php echo $file_url; ?>" placeholder="<?php _e( 'File URL or path...', 'delightful-downloads' ); ?>" />
			</p>
			<p>
				<div id="dedo-file-browser"><p><?php _e( 'Loading...', 'delightful-downloads' ); ?></p></div>
			</p>
			<p>
				<a href="#" id="dedo-select-done" class="button button-primary"><?php _e( 'Confirm', 'delightful-downloads' ); ?></a>
			</p>
		</div>
	</div>

	<?php
	
}

/**
 * Update Status Ajax
 *
 * @since  1.5
*/
function dedo_download_update_status_ajax() {

	global $dedo_statistics;

	// Check for nonce and permission
	if ( !check_ajax_referer( 'dedo_download_update_status', 'nonce', false ) || !current_user_can( apply_filters( 'dedo_cap_add_new', 'edit_pages' ) ) ) {
		echo json_encode( array(
			'status'	=> 'error',
			'content'	=> __( 'Failed security check!', 'delightful-downloads' )
		) );

		die();
	}

	$file_url = trim( $_REQUEST['url'] );

	if( $result = dedo_get_file_status( $file_url ) ) {
		// Cache remote file sizes, for 15 mins
		if ( 'remote' === $result['type'] ) {
			$cached_remotes = get_transient( 'dedo_remote_file_sizes' );

			if ( false === $cached_remotes || !isset( $cached_remotes[esc_url_raw( $file_url )] ) ) {
				$cached_remotes[esc_url_raw( $file_url )] = $result['size'];
				set_transient( 'dedo_remote_file_sizes', $cached_remotes, 900 );
			}
		}

		// Add extra data to result
		$result['size'] = size_format( $result['size'], 1 );
		$result['icon']	= dedo_get_file_icon( $file_url );
		$result['filename'] = dedo_get_file_name( $file_url );

		// Exists
		echo json_encode( array (
			'status'	=> 'success',
			'content'	=> $result
		) );
	}
	else {
		$result['filename'] = dedo_get_file_name( $file_url );

		echo json_encode( array (
			'status'	=> 'error',
			'content'	=> $result
		) );
	}

	die();
}
add_action( 'wp_ajax_dedo_download_update_status', 'dedo_download_update_status_ajax' );

/**
 * Save Meta Boxes
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
	
	// Check for file nonce
	if ( isset( $_POST['ddownload_file_save_nonce'] ) && wp_verify_nonce( $_POST['ddownload_file_save_nonce'], 'ddownload_file_save' ) ) {	

		$file = get_post_meta( $post_id, '_dedo_file', true );
		$file_url = trim( $_POST['dedo-file-url'] );

		// Get cached remote file sizes
		$cached_remotes = get_transient( 'dedo_remote_file_sizes' );
		
		// Check for cached remote file size
		if ( false === $cached_remotes || !isset( $cached_remotes[esc_url_raw( $file_url )] ) ) {
			$new_file = dedo_get_file_status( $file_url );
		}
		else {
			$new_file['size'] = $cached_remotes[esc_url_raw( $file_url )];
		}

		// File url and size
		$file['download_url'] = $file_url;
		$file['download_size'] = $new_file['size'];

		// Set file options
		$options = array(
			'members_only',
			'members_redirect',
			'open_browser'
		);

		// Loop through and save to file array
		foreach ( $options as $option ) {
			if ( isset( $_POST[$option] ) && '' !== $_POST[$option] ) {
				$file['options'][$option] = absint( $_POST[$option] );
			}
			else {
				unset( $file['options'][$option] );
			}
		}

		// Save file meta
		update_post_meta( $post_id, '_dedo_file', $file );

		// Save download count
		if ( isset( $_POST['download_count'] ) && !empty( trim( $_POST['download_count'] ) ) ) {
			update_post_meta( $post_id, '_dedo_file_count', trim( $_POST['download_count'] ) );
		}
	}
}
add_action( 'save_post', 'dedo_meta_boxes_save' );