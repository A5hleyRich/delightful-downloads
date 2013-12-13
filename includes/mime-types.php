<?php
/**
 * Delightful Downloads Mime Types
 *
 * @package     Delightful Downloads
 * @subpackage  Mime Types
 * @since       1.3
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Mime Types
 *
 * Add additioanl mime types that WordPress is allowed to upload.
 *
 * @since   1.3
 * @return  array Mime types
 */
function dedo_mime_types( $existing_mimes ) {

	// Image editors
	$existing_mimes['psd']  = 'image/photoshop';
	$existing_mimes['ai']  	= 'application/postscript';
	$existing_mimes['eps']  = 'application/postscript';
	$existing_mimes['pxm']  = 'application/octet-stream';
	$existing_mimes['pxb']  = 'application/octet-stream';
	$existing_mimes['pxg']  = 'application/xml';
	$existing_mimes['pxs']  = 'application/octet-stream';

	// Ebooks
	$existing_mimes['mobi']	= 'application/x-mobipocket-ebook';
	$existing_mimes['epub'] = 'application/epub+zip';

	// Misc
	$existing_mimes['exe']	= 'application/octet-stream';
	$existing_mimes['dmg']	= 'application/x-apple-diskimage';

	return $existing_mimes;
}
add_filter( 'upload_mimes', 'dedo_mime_types' );