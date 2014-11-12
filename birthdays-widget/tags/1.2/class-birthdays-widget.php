<?php
/**
 * Adds Birthdays_Widget widget.
 */
class Birthdays_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'birthdays_widget', // Base ID
			__('Birthdays Widget'), // Name
			array( 'description' => __( 'Happy birthday widget', 'birthdays-widget' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		$birthdays = birthdays_widget_check_for_birthdays();
		
		if( count($birthdays) >= 1 ){
			
			$flag = false;
			
			/* wp_enqueue_script('birthdays-widget-script', plugins_url('script.js', __FILE__ ), array('jquery'));
			wp_localize_script('birthdays-widget-script', 'ratingsL10n', array(
			'admin_ajax_url' => admin_url('admin-ajax.php')
			)); */
			
			echo $args['before_widget'];
			echo '<div class="birthdays-widget">';
		?>
			<span class="birthday">
				<span style="color: red; font-weight: bold; margin: 5px auto 5px auto; text-align: center;">
						<img style="display: block;" src="<?php echo plugins_url( '/images/birthday_cake.png' , __FILE__ ); ?>" alt="birthday_cake" class="aligncenter" width="100" height="100"/>
						<?php _e( 'Happy Birthday', 'birthdays-widget' ); ?>
				</span>
				<?php 
				foreach( $birthdays as $name ){
					if( $flag )
						echo ', ';
					echo $name->name;
					$flag = true;
				}
					
				?>
			</span>
			
			<?php //TODO make again ajax support?
		/* 
				<script type='text/javascript'>
				function showNames(data){
					var a = data.split(";", 100);
					var ret = "";
					if (a[0] != 0){
						ret = "		<span style=\"color: red; font-weight: bold; margin: 5px auto 5px auto; text-align: center;\">" +
										"<img style=\"display: block;\" src=\"<?php echo plugins_url( '/images/birthday_cake.png' , __FILE__ ); ?>\" alt=\"birthday_cake\" width=\"100\" height=\"100\"/>" +
										"<?php _e( 'Happy Birthday', 'birthdays-widget' ); ?> " +
									"</span>" + a[1] + "!!";
					}
					return ret;
				}
			</script>
			*/ ?>
		<?php
			echo $args['after_widget'];
			
			echo '</div>';
		}
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'Template' ); ?>"><?php _e( 'Template:', 'birthdays-widget' ); ?></label> 
		<select class="widefat" id="<?php echo $this->get_field_id( 'Template' ); ?>" name="<?php echo $this->get_field_name( 'Template' ); ?>">
			<option value="0">Default</option>
		</select>
		This widget diplays happy birthday from your birthday list
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['Template'] = ( ! empty( $new_instance['Template'] ) ) ? strip_tags( $new_instance['Template'] ) : '';

		return $instance;
	}

} // class Birthdays_Widget
