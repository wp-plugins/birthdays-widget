<?php
/*
	Plugin Name: Birthdays Widget
	Plugin URI: https://wordpress.org/plugins/birthdays-widget/
	Description: Birthdays Widget
	Author: lion2486, Sudavar
	Version: 1.3
	Author URI: http://codescar.eu 
	Contributors: lion2486, Sudavar
	Tags: widget, birthdays, custom
	Requires at least: 3.0.1
	Tested up to: 3.9.2
	Text Domain: birthdays-widget
	License: GPLv2
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

	require_once dirname( __FILE__ ) . '/class-birthdays-widget.php';
	require_once dirname( __FILE__ ) . '/class-birthdays-widget-installer.php';
	require_once dirname( __FILE__ ) . '/class-birthdays-widget-settings.php';	
	require_once dirname( __FILE__ ) . '/birthdays-widget-ajax-callback.php';	
	
	register_activation_hook( __FILE__ , array( 'Birthdays_Widget_Installer', 'activate' ) );
	register_uninstall_hook( __FILE__ , array( 'Birthdays_Widget_Installer', 'uninstall' ) );
	
	add_filter( 'plugin_action_links', 'birthdays_widget_action_links', 10, 2);
	
	if( is_admin() )
		$my_settings_page = new Birthdays_Widget_Settings();

	add_action( 'widgets_init', 'register_birthdays_widget' );
	// register Birthdays_Widget widget
	function register_birthdays_widget() {
		register_widget( 'Birthdays_Widget' );
	}

	function birthdays_widget_action_links($links, $file) {
		static $this_plugin;
		if( !$this_plugin ) {
			$this_plugin = plugin_basename( __FILE__ );
		}
		if ($file == $this_plugin) {
			// The "page" query string value must be equal to the slug
			// of the Settings admin page we defined earlier, which in
			// this case equals "myplugin-settings".
			$settings_link = '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/admin.php?page=birthdays-widget">'. _( 'Settings' ) .'</a>';
			array_unshift($links, $settings_link);
		}
		return $links;
	}

	function birthdays_widget_load_languages() {
		load_plugin_textdomain('birthdays-widget', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}
	add_action('plugins_loaded', 'birthdays_widget_load_languages');

	//1. Add a new form element...
	function birthdays_widget_register_form (){
		wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
		$first_name = ( isset( $_POST['first_name'] ) ) ? $_POST['first_name']: '';
		$date = ( isset( $_POST['birthday_date'] ) ) ? $_POST['birthday_date']: ''; ?>
		<p>
			<label for="first_name"><?php _e('First Name','birthdays-widget') ?><br />
				<input type="text" name="first_name" id="first_name" class="input" 
					value="<?php echo esc_attr(stripslashes($first_name)); ?>" /></label>
			<label for="birthday_date"><?php _e( 'User Birthday', 'birthdays-widget' ); ?></label>
                <input  type="text" id="birthday_date" name="birthday_date" 
					value="<?php if( $date != '' ) echo date_i18n( 'd-m-Y', strtotime( $date ) ); ?>" />
            <script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery("#birthday_date").datepicker({
						changeMonth: true,
						changeYear: true,
						"dateFormat" : "dd-mm-yy"
					})});
			</script>
		</p> <?php
	}

	//2. Add validation. In this case, we make sure first_name is required.
	function birthdays_widget_registration_errors ($errors, $sanitized_user_login, $user_email) {
		if ( empty( $_POST['first_name'] ) )
			$errors->add( 'first_name_error', __('<strong>ERROR</strong>: You must include a first name.','birthdays-widget') );

		return $errors;
	}

	//3. Finally, save our extra registration user meta.
	function birthdays_widget_user_register ($user_id) {
		if ( isset( $_POST['first_name'] ) )
			update_user_meta($user_id, 'first_name', $_POST['first_name']);
		if ( isset( $_POST['birthday_date'] ) )
			update_user_meta($user_id, 'birthday', $_POST['birthday_date']);
	}

	$register_form = get_option( 'birthdays_register_form' );
	if ( $register_form == TRUE ) {
		add_action('register_form','birthdays_widget_register_form');
		add_action('user_register', 'birthdays_widget_user_register');
		add_filter('registration_errors', 'birthdays_widget_registration_errors', 10, 3);
	}

    function birthdays_widget_usr_profile() {
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );

        if ( isset($_GET['user_id'] ) )
			$user_id = $_GET['user_id'];
		else
			$user_id = get_current_user_id();
        $date = get_user_meta( $user_id, 'birthday');
        $date = $date[ 0 ];
        echo '<table class="form-table">
                <tr>
                    <th><label for="birthday_date">' . __( 'User Birthday', 'birthdays-widget' ) . '</label></th>
                    <td>
                        <input  type="text" size="10" id="birthday_date" name="birthday_date" 
                                value="'. date_i18n( 'd-m-Y', strtotime( $date ) ) .'" />
                        <br /><span class="description">Please enter user\'s birthday requested by birthdays widget</span>
                    </td> 
                </tr></table>';
        echo '<script type="text/javascript">
				    jQuery(document).ready(function(){
					    jQuery("#birthday_date").datepicker({
						    changeMonth: true,
      						changeYear: true,
        					"dateFormat" : "dd-mm-yy"
        				})});
			  </script>';
    }

    add_action( 'edit_user_profile', 'birthdays_widget_usr_profile' );
    add_action( 'show_user_profile', 'birthdays_widget_usr_profile' );

    function birthdays_widget_update_profile() {
        $user_id = get_current_user_id();
        $value = $_POST[ 'birthday_date' ];
        update_user_meta( $user_id, 'birthday', $value, '' );
    }

    add_action( 'profile_update', 'birthdays_widget_update_profile' );

