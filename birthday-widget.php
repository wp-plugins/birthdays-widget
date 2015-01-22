<?php
/*
    Plugin Name: Birthdays Widget
    Plugin URI: https://wordpress.org/plugins/birthdays-widget/
    Description: Birthdays widget plugin produces a widget which displays a customizable happy birthday image and wish to your clients/users.
    Author: lion2486, Sudavar
    Version: 1.6.9
    Author URI: http://codescar.eu 
    Contributors: lion2486, Sudavar
    Tags: widget, birthdays, custom birthday list, WordPress User birthday, birthday calendar
    Requires at least: 3.0.1
    Tested up to: 4.0
    Text Domain: birthdays-widget
    License: GPLv2
    License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

    define( 'BW', '1.6.9' );
    require_once dirname( __FILE__ ) . '/class-birthdays-widget.php';
    require_once dirname( __FILE__ ) . '/class-birthdays-widget-installer.php';
    require_once dirname( __FILE__ ) . '/class-birthdays-widget-settings.php';  
    require_once dirname( __FILE__ ) . '/birthdays-widget-ajax-callback.php';   
    
    register_activation_hook( __FILE__ , array( 'Birthdays_Widget_Installer', 'activate' ) );
    register_deactivation_hook( __FILE__ , array( 'Birthdays_Widget_Installer', 'deactivate_multisite' ) );
    add_action( 'wpmu_new_blog', array( 'Birthdays_Widget_Installer', 'new_blog' ), 10, 6 );

    if ( is_admin() )
        $my_settings_page = new Birthdays_Widget_Settings();

    // register Birthdays_Widget widget
    function register_birthdays_widget() {
        register_widget( 'Birthdays_Widget' );
    }
    add_action( 'widgets_init', 'register_birthdays_widget' );

    // register our scripts
    function birthdays_extra_files() {
        wp_register_script( 'birthdays-script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ), BW );
        wp_register_script( 'birthdays-cal', plugins_url( 'js/cal.js', __FILE__ ), array( 'jquery' ), BW );
        wp_register_script( 'birthdays-calendar-js', plugins_url( 'js/bic_calendar.min.js', __FILE__ ), array( 'jquery' ), BW );
        wp_register_script( 'birthdays-bootstrap-js', plugins_url( 'js/bootstrap.min.js', __FILE__ ), array( 'jquery' ), BW );
        wp_register_script( 'birthdays-table-js', plugins_url( 'js/jquery.dataTables.min.js', __FILE__ ), array( 'jquery' ), BW );

        wp_register_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css', array(), BW );
        wp_register_style( 'birthdays-calendar-css', plugins_url( 'css/bic_calendar.css', __FILE__ ), array(), BW );
        wp_register_style( 'birthdays-bootstrap-css', plugins_url( 'css/bootstrap.min.css', __FILE__ ), array(), BW );
        wp_register_style( 'birthdays-table-css', plugins_url( 'css/jquery.dataTables.min.css', __FILE__ ), array(), BW );
        wp_register_style( 'birthdays-css', plugins_url( 'css/birthdays-widget.css', __FILE__ ), array(), BW );
    }
    add_action( 'wp_enqueue_scripts', 'birthdays_extra_files' );
    add_action( 'login_enqueue_scripts', 'birthdays_extra_files' );
    add_action( 'admin_enqueue_scripts', 'birthdays_extra_files' );

    function birthdays_widget_action_links($links, $file) {
        static $this_plugin;
        if ( !$this_plugin ) {
            $this_plugin = plugin_basename( __FILE__ );
        }
        if ( $file == $this_plugin ) {
            // The "page" query string value must be equal to the slug
            // of the Settings admin page we defined earlier, which in
            // this case equals "myplugin-settings".
            $settings_link = '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/admin.php?page=birthdays-widget">'. _( 'Settings' ) .'</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }
    add_filter( 'plugin_action_links', 'birthdays_widget_action_links', 10, 2 );

    function birthdays_widget_load_languages() {
        load_plugin_textdomain( 'birthdays-widget', false, basename( dirname( __FILE__ ) ) . '/languages' );
    }
    add_action( 'plugins_loaded', 'birthdays_widget_load_languages' );

    $birthdays_settings = get_option( 'birthdays_settings' );
    $birthdays_settings = maybe_unserialize( $birthdays_settings );

    // Feature: User name and User birthday field in User registration form
    // If option is on, enable that feature.
    if ( $birthdays_settings[ 'register_form' ] == TRUE ) {
        add_action( 'register_form', 'birthdays_widget_register_form' );
        add_filter( 'registration_errors', 'birthdays_widget_registration_errors', 10, 3 );
    }
    //1. Add a new form element...
    function birthdays_widget_register_form (){
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'birthdays-script' );
        wp_enqueue_style( 'jquery-style' );
        $first_name = ( isset( $_POST['first_name'] ) ) ? $_POST['first_name']: '';
        $date = ( isset( $_POST['birthday_date'] ) ) ? $_POST['birthday_date']: ''; ?>
        <p>
            <label for="first_name"><?php _e('First Name','birthdays-widget') ?><br />
                <input type="text" name="first_name" id="first_name" class="input" 
                    value="<?php echo esc_attr(stripslashes($first_name)); ?>" /></label>
            <label for="birthday_date"><?php _e( 'User Birthday', 'birthdays-widget' ); ?></label>
                <input  type="text" id="birthday_date" name="birthday_date" 
                    value="<?php if ( $date != '' ) echo date_i18n( 'd-m-Y', strtotime( $date ) ); ?>" />
        </p> <?php
    }

    //2. Add validation. No need yet
    function birthdays_widget_registration_errors ($errors, $sanitized_user_login, $user_email) {
        return $errors;
    }

    //3. Finally, save our extra registration user meta.
    function birthdays_widget_user_register ($user_id) {
        if ( isset( $_POST['first_name'] ) )
            update_user_meta( $user_id, 'first_name', $_POST['first_name'] );
        if ( isset( $_POST['birthday_date'] ) )
            update_user_meta( $user_id, 'birthday', $_POST['birthday_date'] );
    }

    // Feature: User name and User birthday field in User profile in admin section
    // If option is on, enable that feature.
    if ( $birthdays_settings[ 'profile_page' ] == TRUE ) {
		add_action( 'profile_update', 'birthdays_widget_update_profile' );
		add_action( 'edit_user_profile', 'birthdays_widget_usr_profile' );
		add_action( 'show_user_profile', 'birthdays_widget_usr_profile' );
        add_action( 'delete_user', 'birthdays_delete_user' );
    }    

	//1. Add new element to profile page, user birthday field
    function birthdays_widget_usr_profile() {
        global $wpdb;
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'birthdays-script' );
        wp_enqueue_style( 'jquery-style' );

        if ( isset($_GET['user_id'] ) )
            $user_id = $_GET['user_id'];
        else
            $user_id = get_current_user_id();

        $id = get_user_meta( $user_id, 'birthday_id' );
        if ( empty( $id ) ) {
			unset( $id );
            unset( $date );
        } else {
            $id = $id[ 0 ];
            $table_name = $wpdb->prefix . "birthdays";
            $query = "SELECT date FROM $table_name WHERE id = '%d' ;";
            $results = $wpdb->get_results( $wpdb->prepare( $query, $id ) );
            if ( empty( $results ) ) {
                delete_user_meta( $user_id, 'birthday_id' );
                unset( $id );
                unset( $date );
            } else {
                $date = $results[ 0 ]->date;
            }
        }
        ?>
            <table class="form-table">
                <tr>
                    <th><label for="birthday_date"><?php _e( 'User Birthday', 'birthdays-widget' ); ?></label></th>
                    <td><input type="text" size="10" id="birthday_date" name="birthday_date" 
                        <?php if ( isset( $date ) ) {
                                echo 'value="' . date_i18n( 'd-m-Y', strtotime( $date ) ) . '" />';
                              } else {
                                echo 'value="" />'; 
                        } ?>
                        <br /><span class="description"><?php _e( 'Please enter user\'s birthday requested by birthdays widget', 'birthdays-widget' ); ?></span>
						<input type="hidden" name="birthday_usr_id" value="<?php echo $user_id; ?>" />
        <?php 
        if ( isset( $id ) )
            echo '<input type="hidden" name="birthday_id" value="' . $id . '" />';
        echo '      </td> 
                </tr></table>';
        wp_enqueue_script( 'birthdays-widget-script', plugins_url( 'date-picker.js', __FILE__ ), array( 'jquery' ) );
    }

	//2. Validate and update field in WP user structure
    function birthdays_widget_update_profile() {
        global $wpdb;
        
        if ( empty ( $_POST[ 'birthday_date' ] ) )
            return;
        
        $user_id = $_POST[ 'birthday_usr_id' ];
        $value = $_POST[ 'birthday_date' ];

		//Shall now save it in our database table
        $birth_user = "cs_birth_widg_" . $user_id;
        $table_name = $wpdb->prefix . 'birthdays';

        if ( !isset( $_POST[ 'birthday_id' ] ) ) {
            //add the new entry
            $insert_query = "INSERT INTO $table_name (name, date) VALUES (%s, %s);";
            if ( $wpdb->query( $wpdb->prepare( $insert_query, $birth_user, date( 'Y-m-d' , strtotime($value) ) ) ) != 1 )
                echo '<div id="message" class="error"><p>Query error</p></div>';
            $birth_id = $wpdb->insert_id;
            update_user_meta( $user_id, 'birthday_id', $birth_id, '' );
        } else {
            //update the existing entry
            $update_query = "UPDATE $table_name SET date = %s, name = %s WHERE id = %d;";
            if ( $wpdb->query( $wpdb->prepare( $update_query, date( 'Y-m-d' , strtotime($value) ), $birth_user, $_POST[ 'birthday_id' ] ) ) != 1 )
                echo '<div id="message" class="error"><p>Query error</p></div>';
        }
    }
    
    //3. Remove WP user from our table upon delete of user
    function birthdays_delete_user( $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'birthdays';

        $user_birthday_id = get_user_meta( $user_id, 'birthday_id' );
        $delete_query = "DELETE FROM $table_name WHERE id = %d;";
        if ( $wpdb->query( $wpdb->prepare( $delete_query, $user_birthday_id ) ) != 1 )
            echo '<div id="message" class="error"><p>Query error</p></div>';
    }

    // Feature: Shortcode for birthdays in pages/posts
    function birthdays_shortcode( $atts ) {
        $attr = shortcode_atts( array(
            'class' => '',
            'img_width' => '0',
            'template' => '0'
        ), $atts );
        if ( $attr[ 'template' ] == 'default' ) {
            $attr[ 'template' ] = 0;
        } else if ( $attr[ 'template' ] == 'list' ) {
            $attr[ 'template' ] = 1;
        } else if ( $attr[ 'template' ] == 'calendar' ) {
            $attr[ 'template' ] = 2;
        } else {
            $attr[ 'template' ] = 0;
        }
        $instance = array( 'class' => $attr[ 'class' ], 'img_width' => $attr[ 'img_width' ], 'template' => $attr[ 'template' ] );
        if ( $attr[ 'template' ] == 2 ) {
            $birthdays = birthdays_widget_check_for_birthdays( true );
        } else {
            $birthdays = birthdays_widget_check_for_birthdays();
        }
        if ( count( $birthdays ) >= 1 ) {
            return Birthdays_Widget::birthdays_code( $instance, $birthdays );
        }
    }
    add_shortcode( 'birthdays', 'birthdays_shortcode' );

    // Feature: Add button for shortcode in WordPress editor
    // (thanks to: http://wordpress.stackexchange.com/questions/72394/how-to-add-a-shortcode-button-to-the-tinymce-editor)
    add_action('init', 'birthdays_shortcode_button_init');
    function birthdays_shortcode_button_init() {
          //Abort early if the user will never see TinyMCE
          if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) && get_user_option( 'rich_editing' ) == 'true' )
               return;

          //Add a callback to regiser our tinymce plugin   
          add_filter( "mce_external_plugins", "birthdays_register_tinymce_plugin" );

          // Add a callback to add our button to the TinyMCE toolbar
          add_filter( 'mce_buttons', 'birthdays_add_tinymce_button' );
    }

    //This callback registers our plug-in
    function birthdays_register_tinymce_plugin( $plugin_array ) {
        $plugin_array[ 'birthdays_button' ] = plugins_url() . '/birthdays-widget/js/shortcode.js';
        return $plugin_array;
    }

    //This callback adds our button to the toolbar
    function birthdays_add_tinymce_button( $buttons ) {
        //Add the button ID to the $button array
        $buttons[] = "birthdays_button";
        return $buttons;
    }

    //Check if there is a need to update
    function update_birthdays_widget() {
        $birthdays_settings = get_option( 'birthdays_settings' );
        $birthdays_settings = maybe_unserialize( $birthdays_settings );

        if ( !isset( $birthdays_settings[ 'version' ] ) || ( $birthdays_settings[ 'version' ] != BW ) ) {
            Birthdays_Widget_Installer::install();
            $birthdays_settings = get_option( 'birthdays_settings' );
            $birthdays_settings = maybe_unserialize( $birthdays_settings );
        }
    }
    add_action( 'plugins_loaded', 'update_birthdays_widget' );
