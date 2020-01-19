<?php

defined( 'ABSPATH' ) or die('This page may not be accessed directly.');

class OneSignalWidget extends WP_Widget {

	/**
	 * Default strings for widget.
	 *
	 * @var array
	 */
	public $default_strings = array();

	function __construct() {
		parent::__construct('OneSignalWidget', 'OneSignal', array( 'description' => 'Subscribe to notifications'));
		$this->default_strings = array(
			'title'       => __( 'Follow' ),
			'text'        => __( 'Subscribe to notifications' ),
			'unsub-text'  => __( 'Unsubscribe from notifications' ),
		);
	}

	// Admin editor
	function form($instance) {
		$title        = ! empty( $instance['title'] ) ? $instance['title'] : $this->default_strings['title'];
		$text         = ! empty( $instance['text'] ) ? $instance['text'] : $this->default_strings['text'];
		$unsub_text   = ! empty( $instance['unsub-text'] ) ? $instance['unsub-text'] : $this->default_strings['unsub-text'];
		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id( 'title' )); ?>"><?php esc_attr_e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'title' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'title' )); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
			<label for="<?php echo esc_attr($this->get_field_id( 'text' )); ?>"><?php esc_attr_e( 'Body (Subscribe):' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'text' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'text' )); ?>" type="text" value="<?php echo esc_attr( $text ); ?>">
			<label for="<?php echo esc_attr($this->get_field_id( 'unsub-text' )); ?>"><?php esc_attr_e( 'Body (Change/Unsubscribe):' ); ?></label> 
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id( 'unsub-text' )); ?>" name="<?php echo esc_attr($this->get_field_name( 'unsub-text' )); ?>" type="text" value="<?php echo esc_attr( $unsub_text ); ?>">
		</p>
		<?php 
	}

	function update($new_instance, $old_instance) {
		$instance = array();

		$instance['title']      = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['text']       = ( ! empty( $new_instance['text'] ) ) ? wp_strip_all_tags( $new_instance['text'] ) : '';
		$instance['unsub-text'] = ( ! empty( $new_instance['unsub-text'] ) ) ? wp_strip_all_tags( $new_instance['unsub-text'] ) : '';

		return $instance;
	}

	// Public display
	function widget($args, $instance) {
		$strings = wp_parse_args(
			$instance,
			$this->default_strings
		);
		$loading_service = __( 'Loading notification service...' );
		$loading_status  = __( 'Loading notification status...' );
		echo wp_kses_post( $args['before_widget'] );
		if ( ! empty( $instance['title'] ) ) {
			echo wp_kses_post( $args['before_title'] . apply_filters( 'widget_title', $strings['title'] ). $args['after_title'] );
		}
		echo '<div class="textwidget">';
		printf(
			'<div class="OneSignal-prompt-no-js">%s</div>',
			esc_html( $loading_service )
		);
		$pattern = '<a href="#" class="OneSignal-prompt" style="display: none;" data-onesignal-subscribe="%1$s" data-onesignal-unsubscribe="%2$s">%3$s</a>';
		printf(
			$pattern,
			esc_attr( $strings['text'] ),
			esc_attr( $strings['unsub-text'] ),
			esc_html( $loading_status )
		);
		echo '</div>';
		echo wp_kses_post( $args['after_widget'] );
	}
}

add_action('widgets_init', function(){register_widget("OneSignalWidget");});

?>