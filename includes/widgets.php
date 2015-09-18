<?php
/**
 * Delightful Downloads Widgets
 *
 * @package     Delightful Downloads
 * @subpackage  Includes/Widgets
 * @since       1.6
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widgets
 *
 * @since 1.6
 */
function dedo_widgets() {	
	register_widget( 'DEDO_Widget_List' );
}
add_action( 'widgets_init', 'dedo_widgets', 5 );