<?php
/**
 * Widget Class
 *
 * @package  	Delightful Downloads
 * @author   	Ashley Rich
 * @copyright   Copyright (c) 2014, Ashley Rich
 * @since    	1.6
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class DEDO_Widget extends WP_Widget {
	
	private static $count = 1;
	private static $format = '[ddownload id="%ID%" style="%LINK%" text="%TEXT%"]';

	/**
	 *	Init Widget
	 *
	 * @access public
	 * @since 1.6
	 * @return void
	 */
	public function __construct() {

		parent::__construct(
			false,
			__( 'Delightful Downloads' ),
			array( 'description' => __( 'Display your downloads inside a widget.' ) )
		);
	}

	/**
	 * Widget output
	 */
	public function widget( $args, $instance ) {
		
		// Get published downloads
		$category_id = is_numeric(strip_tags($instance['category'])) ? strip_tags($instance['category']) : false;
		$tag_id      = is_numeric(strip_tags($instance['tag']))      ? strip_tags($instance['tag']) : false;
		$count       = is_numeric(esc_attr( $instance['count'] ))    ? esc_attr( $instance['count'] ) : 1;
		
		$post_query_args = array(
			'post_type'		=> 'dedo_download',
			'post_status'	=> 'publish',
			'orderby'		=> 'post_date',
			'order'			=> 'DESC',
			'posts_per_page'=> $count
		);
		if($category_id) {
			$post_query_args['tax_query'][] = array(
				'taxonomy' => 'ddownload_category',
				'field' => 'term_id',
				'terms' => $category_id
			);
		}
		if($tag_id) {
			$post_query_args['tax_query'][] = array(
				'taxonomy' => 'ddownload_tag',
				'field' => 'term_id',
				'terms' => $tag_id
			);
		}
		
		$downloads = get_posts( $post_query_args );
		
		// Display widget
		echo '<div id="'.$args['widget_id'].'" class="widget widget_dedo">';
		if(!empty($instance['title'])) {
			echo '<h3>'.$instance['title'].'</h3>';
		}
		if(is_array($downloads) && !empty($downloads)) {
			foreach($downloads as $download) {
				echo apply_filters( 'widget_text', $this->replaceFormatPlaceholders($instance['format'], $download) );
			}
		}
		echo '</div>';
	}
	
	/**
	 * Replace placeholders
	 * 
	 * Replaces placeholders in format text.
	 * 
	 * @param string $text
	 * @param object $download
	 * 
	 * @access private
	 * @since 1.6
	 * @return string
	 */
	private function replaceFormatPlaceholders($text, $download) {
		
		$placeholders = array( '%ID%', '%TEXT%' );
		$replacement = array( $download->ID, esc_html($download->post_title) );

		return str_replace($placeholders, $replacement, $text);
	}

	/**
	 * Save widget options
	 */
	public function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = is_numeric(strip_tags($new_instance['count'])) ? strip_tags($new_instance['count']) : self::$count;
		$instance['category'] = strip_tags($new_instance['category']);
		$instance['tag'] = strip_tags($new_instance['tag']);
		$instance['format'] = $new_instance['format'];
		
        return $instance;
	}

	/**
	 * Display backend form
	 */
	public function form( $instance ) {
		
		if ( $instance ) {
			$title = esc_attr( $instance['title'] );
			$count = is_numeric(esc_attr( $instance['count'] )) ? esc_attr( $instance['count'] ) : 1;
			$category = esc_attr( $instance['category'] );
			$tag = esc_attr( $instance['tag'] );
			$format = !empty( $instance['format'] ) ? esc_attr( $instance['format'] ) : self::$format;
		}
		else {
			$title = __( 'Downloads' );
			$count = self::$count;
			$category = '';
			$tag = '';
			$format = self::$format;
		}
		
		$ddownload_categories = get_terms( 'ddownload_category' );
		$ddownload_tags = get_terms( 'ddownload_tag' );
		
		// Title
		echo '<p>';
			echo '<label for="'.$this->get_field_id( 'title' ).'">'.__( 'Title:' ).'</label>';
			echo '<input class="widefat" id="'.$this->get_field_id( 'title' ).'" name="'.$this->get_field_name( 'title' ).'" type="text" value="'.$title.'" />';
		echo '</p>';
		
		// Count
		echo '<p>';
			echo '<label for="'.$this->get_field_id( 'count' ).'">'.__( 'Count:' ).'</label>';
			echo '<input class="widefat" id="'.$this->get_field_id( 'count' ).'" name="'.$this->get_field_name( 'count' ).'" type="number" value="'.$count.'" />';
		echo '</p>';
		
		// Category
		echo '<p>';
			echo '<label for="'.$this->get_field_id( 'category' ).'">'.__( 'Category:' ).'</label>';
			echo '<select class="widefat" id="'.$this->get_field_id( 'category' ).'" name="'.$this->get_field_name( 'category' ).'">';
				echo '<option>'.__( 'All categories' ).'</option>';
				if(is_array($ddownload_categories) && !empty($ddownload_categories)) {
					foreach($ddownload_categories as $ddownload_category) {
						$selected = ($ddownload_category->term_id == $category) ? ' selected' : '';
						echo '<option value="'.$ddownload_category->term_id.'"'.$selected.'>'.$ddownload_category->name.'</option>';
					}					
				}
			echo '</select>';
		echo '</p>';
		
		// Tag
		echo '<p>';
			echo '<label for="'.$this->get_field_id( 'tag' ).'">'.__( 'Tag:' ).'</label>';
			echo '<select class="widefat" id="'.$this->get_field_id( 'tag' ).'" name="'.$this->get_field_name( 'tag' ).'">';
				echo '<option>'.__( 'All tags' ).'</option>';
				if(is_array($ddownload_tags) && !empty($ddownload_tags)) {
					foreach($ddownload_tags as $ddownload_tag) {
						$selected = ($ddownload_tag->term_id == $tag) ? ' selected' : '';
						echo '<option value="'.$ddownload_tag->term_id.'"'.$selected.'>'.$ddownload_tag->name.'</option>';
					}					
				}
			echo '</select>';
		echo '</p>';
		
		// Format
		echo '<p>';
			echo '<label for="'.$this->get_field_id( 'format' ).'">'.__( 'Format:' ).'</label>';
			echo '<textarea class="widefat" id="'.$this->get_field_id( 'format' ).'" name="'.$this->get_field_name( 'format' ).'">'.$format.'</textarea>';
		echo '</p>';
	}

}