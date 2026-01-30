<?php

defined( 'ABSPATH' ) or die('This page may not be accessed directly.');

class OneSignalWidget extends WP_Widget {
  
	function __construct() {
    parent::__construct('OneSignalWidget', 'OneSignal', array( 'description' => __( 'Subscribe to notifications', 'onesignal-free-web-push-notifications' ) ));
	}
  
    // Admin editor
	function form($instance) {
    $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Follow', 'onesignal-free-web-push-notifications' );
    $text  = ! empty( $instance['text'] ) ? $instance['text'] : __( 'Subscribe to notifications', 'onesignal-free-web-push-notifications' );
		?>
		<p>
		<label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_attr_e( 'Title:', 'onesignal-free-web-push-notifications' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
    		<label for="<?php echo esc_attr($this->get_field_id( 'text' )); ?>"><?php esc_attr_e( 'Body:', 'onesignal-free-web-push-notifications' ); ?></label> 
		<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'text' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'text' )); ?>" type="text" value="<?php echo esc_attr( $text ); ?>">
		</p>
		<?php 
	}
  
	function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
    	$instance['text'] = ( ! empty( $new_instance['text'] ) ) ? wp_strip_all_tags( $new_instance['text'] ) : '';
    
		return $instance;
	}

	// Public display
	function widget($args, $instance) {
		echo wp_kses_post($args['before_widget']);
		if ( ! empty( $instance['title'] ) ) {
			echo wp_kses_post($args['before_title']) . wp_kses_post(apply_filters( 'widget_title', $instance['title'])). wp_kses_post($args['after_title']);
		}
    	if ( ! empty( $instance['text'] ) ) {
			echo '<a href="#" class="OneSignal-prompt">' . wp_kses_post($instance['text']) . '</a>';
		}
		echo wp_kses_post($args['after_widget']);
	}
}

add_action('widgets_init', function(){register_widget("OneSignalWidget");});

?>