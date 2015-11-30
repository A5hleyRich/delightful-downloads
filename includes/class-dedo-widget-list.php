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

class DEDO_Widget_List extends WP_Widget {

	/**
	 *	Init Widget
	 *
	 * @access public
	 * @since 1.6
	 */
	public function __construct() {
		$widget_id = 'delightful-downloads-list-widget';
		$name      = __( 'Delightful Downloads', 'delightful-downloads' );
		$options   = array(
			'description' => __( "A list of your site's downloads.", 'delightful-downloads' ),
		);

		parent::__construct( $widget_id, $name, $options );
	}

	/**
	 * Widget output
	 */
	public function widget( $args, $instance ) {
		global $dedo_options;

		$atts = array(
			'limit'   => $instance['count'],
			'orderby' => $instance['orderby'],
			'order'   => $instance['order'],
			'style'   => $instance['style'],
		);

		if ( $dedo_options['enable_taxonomies'] ) {
			$atts['categories'] = $instance['category'];
			$atts['tags']       = $instance['tag'];
			$atts['relation']   = $instance['relation'];
		}

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		echo dedo_shortcode_ddownload_list( $atts );

		echo $args['after_widget'];
	}

	/**
	 * Save widget options
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title']   = sanitize_text_field( $new_instance['title'] );
		$instance['count']   = absint( $new_instance['count'] );
		$instance['orderby'] = sanitize_text_field( $new_instance['orderby'] );
		$instance['order']   = sanitize_text_field( $new_instance['order'] );
		$instance['style']   = sanitize_text_field( $new_instance['style'] );
		$instance['category'] = sanitize_text_field( $new_instance['category'] );
		$instance['tag']      = sanitize_text_field( $new_instance['tag'] );
		$instance['relation'] = sanitize_text_field( $new_instance['relation'] );

		return $instance;
	}

	/**
	 * Display backend form
	 *
	 * @param array $instance
	 *
	 * @return void
	 */
	public function form( $instance ) {
		global $dedo_options;

		$defaults = array(
			'title'    => __( 'Downloads', 'delightful-downloads' ),
			'count'    => 5,
			'orderby'  => 'title',
			'order'    => 'ASC',
			'style'    => 'title',
			'category' => '',
			'tag'      => '',
			'relation' => 'AND',
		);
		$instance = wp_parse_args( $instance, $defaults );

		$title    = $instance['title'];
		$count    = $instance['count'];
		$orderby  = $instance['orderby'];
		$order    = $instance['order'];
		$style    = $instance['style'];
		$category = $instance['category'];
		$tag      = $instance['tag'];
		$relation = $instance['relation'];

		$taxonomy_args = array(
			'hide_empty' => false,
		);

		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'delightful-downloads' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Number of downloads to show:', 'delightful-downloads' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="number" value="<?php echo $count; ?>" step="1" min="1" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order by:', 'delightful-downloads' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
				<option value="date" <?php selected( 'date', $orderby ); ?>><?php _e( 'Date', 'delightful-downloads' ); ?></option>
				<option value="count" <?php selected( 'count', $orderby ); ?>><?php _e( 'Download Count', 'delightful-downloads' ); ?></option>
				<option value="filesize" <?php selected( 'filesize', $orderby ); ?>><?php _e( 'File Size', 'delightful-downloads' ); ?></option>
				<option value="random" <?php selected( 'random', $orderby ); ?>><?php _e( 'Random', 'delightful-downloads' ); ?></option>
				<option value="title" <?php selected( 'title', $orderby ); ?>><?php _e( 'Title', 'delightful-downloads' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Order:', 'delightful-downloads' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
				<option value="asc" <?php selected( 'asc', $order ); ?>><?php _e( 'ASC', 'delightful-downloads' ); ?></option>
				<option value="desc" <?php selected( 'desc', $order ); ?>><?php _e( 'DESC', 'delightful-downloads' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'style' ); ?>"><?php _e( 'List Style:', 'delightful-downloads' ); ?></label>
			<?php $styles = dedo_get_shortcode_lists(); ?>
			<select class="widefat" id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>">
				<?php foreach( $styles as $index => $value ) : ?>
					<option value="<?php echo $index; ?>" <?php selected( $index, $style ); ?>><?php echo $value['name']; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php if ( $dedo_options['enable_taxonomies'] ) : ?>
			<p>
				<label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Category:', 'delightful-downloads' ); ?></label>
				<?php $categories = get_terms( 'ddownload_category', $taxonomy_args ); ?>
				<select class="widefat" id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>">
					<option value="" <?php selected( '', $category ); ?>><?php _e( 'All Categories', 'delightful-downloads' ); ?></option>
					<?php foreach( $categories as $c ) : ?>
						<option value="<?php echo $c->slug; ?>" <?php selected( $c->slug, $category ); ?>><?php echo $c->name; ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'tag' ); ?>"><?php _e( 'Tag:', 'delightful-downloads' ); ?></label>
				<?php $tags = get_terms( 'ddownload_tag', $taxonomy_args ); ?>
				<select class="widefat" id="<?php echo $this->get_field_id( 'tag' ); ?>" name="<?php echo $this->get_field_name( 'tag' ); ?>">
					<option value="" <?php selected( '', $tag ); ?>><?php _e( 'All Tags', 'delightful-downloads' ); ?></option>
					<?php foreach( $tags as $t ) : ?>
						<option value="<?php echo $t->slug; ?>" <?php selected( $t->slug, $tag ); ?>><?php echo $t->name; ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'relation' ); ?>"><?php _e( 'Relation:', 'delightful-downloads' ); ?></label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'relation' ); ?>" name="<?php echo $this->get_field_name( 'relation' ); ?>">
					<option value="AND" <?php selected( 'AND', $relation ); ?>>AND</option>
					<option value="OR" <?php selected( 'OR', $relation ); ?>>OR</option>
				</select>
				<small><?php _e( 'Downloads belong to category AND/OR tag.', 'delightful-downloads' ); ?></small>
			</p>
		<?php else: ?>
			<input type="hidden" id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>" value="<?php echo $category; ?>">
			<input type="hidden" id="<?php echo $this->get_field_id( 'tag' ); ?>" name="<?php echo $this->get_field_name( 'tag' ); ?>" value="<?php echo $tag; ?>">
			<input type="hidden" id="<?php echo $this->get_field_id( 'relation' ); ?>" name="<?php echo $this->get_field_name( 'relation' ); ?>" value="<?php echo $relation; ?>">
		<?php endif; ?>

		<?php
	}

}