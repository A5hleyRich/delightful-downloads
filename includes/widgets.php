<?php
/**
 * Delightful Downloads Widgets
 *
 * @package     Delightful Downloads
 * @subpackage  Includes/Widgets
 * @since       1.6
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Widgets
 *
 * @since 1.6
 */
function dedo_widgets() {	
	register_widget( 'DEDO_Widget' );
}
add_action( 'widgets_init', 'dedo_widgets', 5 );