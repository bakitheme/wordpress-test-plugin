<?php
/*
Plugin Name: ComFilter
Description: This plugin adds a custom widget, which outputs number of comments of each user
Version: 1.0
Author: Denis Vakar
*/
// Create widget class
class Com_Filter_Widget extends WP_Widget {
	// Main widget constructor
	public function __construct() {
		parent::__construct(
			'com_filter_widget',
			__( 'Comment Filter', 'text_domain' ),
			array(
				'customize_selective_refresh' => true,
			)
		);
	}
	// The widget form (backend)
	public function form( $instance ) {
		// Set widget defaults values
		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number ) {
			$number = 5;
		}

		$defaults = array(
			'title'    => '',
			'number'     => $number,
			'is_zero_comments' => '',
			'display_number' => '',
		);
		
		// Parse current settings with defaults
		extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>

		<?php // Widget title ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Widget Title', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<?php // Text field with number of users to output ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php _e( 'Number of users:', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>" />
		</p>

		<?php // Checkbox "hide zero comments users?" ?>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'is_zero_comments' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'is_zero_comments' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $is_zero_comments ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'is_zero_comments' ) ); ?>"><?php _e( 'Hide users with zero comments?', 'text_domain' ); ?></label>
		</p>

		<?php // Checkbox "display number of comments?" ?>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'display_number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_number' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $display_number ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_number' ) ); ?>"><?php _e( 'Display number of comments?', 'text_domain' ); ?></label>
		</p>

	<?php }
	// Update widget settings
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']    = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['number']     = isset( $new_instance['number'] ) ? wp_strip_all_tags( $new_instance['number'] ) : '';
		$instance['is_zero_comments'] = isset( $new_instance['is_zero_comments'] ) ? 1 : false;
		$instance['display_number'] = isset( $new_instance['display_number'] ) ? 1 : false;
		return $instance;
	}
	// Display the widget function
	public function widget( $args, $instance ) {
		extract( $args );
		// Check the widget options
		$title    = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$number     = isset( $instance['number'] ) ? $instance['number'] : '';
		$is_zero_comments = ! empty( $instance['is_zero_comments'] ) ? $instance['is_zero_comments'] : false;
		$display_number = ! empty( $instance['display_number'] ) ? $instance['display_number'] : false;
		
		echo $before_widget;
		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';
		if ( $title ) {
				echo '<h1>' . $before_title . $title . $after_title . '</h1>';
			}
		$blogusers = get_users();

		$index = 0;
		$users_array = array();

		//Put walues into array to sort and output
		foreach ( $blogusers as $user ) {
			$args = array(
				'user_id' => ( $user->ID ),
				'count'   => true
			);
			$users_array[$index]['username'] = $user->user_login;
			$users_array[$index]['total_comments'] = get_comments($args);
			$index++;
		}
		rsort($users_array);
		
		//Output results
		foreach ($users_array as $unit) {
			static $iterator = 0;
			echo "<div>";
			if ( $is_zero_comments ) {
				if($unit['total_comments'] == 0) {
					continue;
				}	
			}
			echo $unit['username'];
			if ( $display_number) {
				echo ' (' . $unit['total_comments'] . ')';
			}
			echo "</div><hr>";
			$iterator++;
			if ($iterator >= $number) {
				break;
			}
		}
		echo '</div>';
		echo $after_widget;
	}
}
// Register the widget
function com_filter_widget_register() {
	register_widget( 'com_filter_widget' );
}
add_action( 'widgets_init', 'com_filter_widget_register' );