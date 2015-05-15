<?php

class OneSignalWidget extends WP_Widget {
  
	function __construct() {
    parent::__construct('OneSignalWidget', 'OneSignal', array( 'description' => 'Subscribe to notifications'));
	}
  
  // Admin editor
	function form($instance) {
    $title = ! empty( $instance['title'] ) ? $instance['title'] : 'Follow';
    $text = ! empty( $instance['text'] ) ? $instance['text'] : 'Subscribe to notifications';
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
    		<label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php _e( 'Body:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>" type="text" value="<?php echo esc_attr( $text ); ?>">
		</p>
		<?php 
	}
  
	function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
    $instance['text'] = ( ! empty( $new_instance['text'] ) ) ? strip_tags( $new_instance['text'] ) : '';
    
		return $instance;
	}

	// Public display
	function widget($args, $instance) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
    if ( ! empty( $instance['text'] ) ) {
			echo '<a href="#" class="OneSignal-prompt">' . $instance['text'] . '</a>';
		}
		echo $args['after_widget'];
	}
}

add_action('widgets_init', create_function('', 'return register_widget("OneSignalWidget");'));

?>