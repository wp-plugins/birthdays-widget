<?php
    class Birthdays_Widget_Settings
    {
        private $options;
    
        public function __construct() {
            add_action( 'admin_menu', array( &$this, 'add_plugin_page' ) );
        }
    
        public function add_plugin_page() {
            add_menu_page( 'Birthdays Widget', 'Birthdays', 'read', 'birthdays-widget', array( &$this, 'create_plugin_page' ) );
            add_submenu_page( 'birthdays-widget', 'Options', 'Options', 'manage_options', 'birthdays-widget-options', array( &$this, 'create_options_page' ) );
            add_submenu_page( 'birthdays-widget', 'Import', 'Import', 'read', 'birthdays-widget-import', array( &$this, 'create_submenu_page_import' ) );
            add_submenu_page( 'birthdays-widget', 'Export', 'Export', 'read', 'birthdays-widget-export', array( &$this, 'create_submenu_page_export' ) );
        }
    
        public function sanitize( $input ) {
            $new_input = array( );
            if ( isset( $input['id_number'] ) )
                $new_input['id_number'] = absint( $input['id_number'] );
    
            if (isset($input['title']))
                $new_input['title'] = sanitize_text_field( $input['title'] );
    
            return $new_input;
        }
    
        public function print_section_info() {
            print 'Enter your settings below:';
        }
        
        public function id_number_callback() {
            printf(
                '<input type="text" id="id_number" name="my_option_name[id_number]" value="%s" />',
                isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number'] ) : ''
            );
        }
    
        public function title_callback() {
            printf(
                '<input type="text" id="title" name="my_option_name[title]" value="%s" />',
                isset( $this->options['title'] ) ? esc_attr( $this->options['title'] ) : ''
            );
        }
        
        public function create_options_page() {
            ?> <div class="wrap">
            <h2><?php _e( 'Birthdays Widget Options ', 'birthdays-widget' ); ?></h2>
            <form method="POST">
            <?php
                wp_enqueue_script( 'birthdays-date-picker' );
                wp_enqueue_style ( 'birthdays-css' );
                wp_enqueue_media();
                
                $birthdays_settings = get_option( 'birthdays_settings' );
                $birthdays_settings = maybe_unserialize( $birthdays_settings );
                if ( isset( $_POST[ 'birthdays_save' ] ) ) {
                    $birthdays_settings[ 'roles' ] = $_POST['roles'];
                    if ( isset( $_POST[ 'birthdays_register_form' ] ) ) {
                        $birthdays_settings[ 'register_form' ] = 1;
                    } else {
                        $birthdays_settings[ 'register_form' ] = 0;
                    }
                    if ( isset( $_POST[ 'birthdays_meta_field' ] ) ) {
                        $birthdays_settings[ 'meta_field' ] = $_POST[ 'birthdays_meta_field' ];
                    } else {
                        $birthdays_settings[ 'meta_field' ] = 'display_name';
                    }
                    if ( isset( $_POST[ 'birthdays_date_meta_field' ] ) ) {
                        $birthdays_settings[ 'date_meta_field' ] = $_POST[ 'birthdays_date_meta_field' ];
                    } else {
                        $birthdays_settings[ 'date_meta_field' ] = '';
                    }
                    if ( isset( $_POST[ 'birthdays_user_data' ] ) ) {
                        $birthdays_settings[ 'user_data' ] = $_POST[ 'birthdays_user_data' ];
                        if ( $_POST[ 'birthdays_user_data' ] == 0 ) {
                            /* Birthdays of WP Users are saved in our table and field in WP Profile is shown */
                            $birthdays_settings[ 'profile_page' ] = 1;
                            $birthdays_settings [ 'date_from_profile' ] = 0;
                            //TODO Maybe add a function to delete all WP User Birthdays saved in our table
                        } elseif ( $_POST[ 'birthdays_user_data' ] == 1 ) {
                            /* Birthdays of WP Users are beign drawn from a WP User meta field specified by the user */
                            $birthdays_settings[ 'profile_page' ] = 0;
                            $birthdays_settings [ 'date_from_profile' ] = 1;
                        } else {
                            /* Birthdays of WP Users are disabled, which is the default scenario */
                            $birthdays_settings[ 'profile_page' ] = 0;
                            $birthdays_settings [ 'date_from_profile' ] = 0;
                        }
                    } else {
                        $birthdays_settings[ 'user_data' ] = 2;
                        $birthdays_settings[ 'profile_page' ] = 0;
                        $birthdays_settings [ 'date_from_profile' ] = 0;
                    }
                    if ( isset( $_POST[ 'birthdays_widget_image_width' ] ) ) {
                        $birthdays_settings[ 'image_width' ] = $_POST[ 'birthdays_widget_image_width' ];
                    } else {
                        $birthdays_settings[ 'image_width' ] = '55%';
                    }
                    if ( isset( $_POST[ 'birthdays_widget_image_url' ] ) && !empty( $_POST[ 'birthdays_widget_image_url' ] ) ) {
                        $birthdays_settings[ 'image_url' ] = $_POST[ 'birthdays_widget_image_url' ];
                    } else {
                        $birthdays_settings[ 'image_url' ] = plugins_url( '/images/birthday_cake.png' , __FILE__ );
                    }
                    if ( isset( $_POST[ 'birthdays_enable_image' ] ) ) {
                        $birthdays_settings[ 'image_enabled' ] = 1;
                    } else {
                        $birthdays_settings[ 'image_enabled' ] = 0;
                    }
                    if ( isset( $_POST[ 'birthdays_wish' ] ) && !empty( $_POST[ 'birthdays_wish' ] ) ) {
                        $birthdays_settings[ 'wish' ] = $_POST[ 'birthdays_wish' ];
                    } else {
                        $birthdays_settings[ 'wish' ] = __( 'Happy Birthday', 'birthdays-widget' );
                    }
                    $saved_values = maybe_serialize( $birthdays_settings );
                    update_option( 'birthdays_settings', $saved_values );
                }
                $current_roles = $birthdays_settings[ 'roles' ];
                $sup_roles = get_editable_roles();
                $supported_roles = array();
                foreach ( $sup_roles as $role ) {
                    $supported_roles[] = $role[ 'name' ];
                } ?>
                <div class="wrap">
                    <h3><?php _e( 'General Options', 'birthdays-widget' ); ?>:</h3>
                    <p><?php _e( 'Write your custom "wish"', 'birthdays-widget' ); ?>:<br />
                    <input type="text" size="35" name="birthdays_wish" value="<?php echo $birthdays_settings[ 'wish' ]; ?>" /></p>
                    <p><?php _e( 'Name and Birthday fields at WordPress User Registration Form', 'birthdays-widget' ); ?>:<br />
                    <input type="checkbox" name="birthdays_register_form" value="1" id="birthdays_register_form"
                        <?php if ( $birthdays_settings[ 'register_form' ] == TRUE ) echo 'checked="checked"'; ?> />
                    <label for="birthdays_register_form"><?php _e( 'Fields in Registration Form', 'birthdays-widget' ); ?></label></p>
                    <p><?php _e( 'Select which Wordpress User\'s meta value you like to be shown as name in widget', 'birthdays-widget' ); ?>:<br />
                        <select name="birthdays_meta_field">
                            <?php $meta_keys = self::birthday_get_filtered_meta();
                                foreach ( $meta_keys as $key ): ?>
                                    <option value="<?php echo $key; ?>" <?php echo ($birthdays_settings[ 'meta_field' ] == $key) ? "selected=\"selected\"" : ''; ?> >
                                        <?php echo $key; ?></option>
                            <?php endforeach; ?>
                        </select><br />
                        <span class="description">
                            <?php _e( 'Careful! The meta you select must be present in every WP User you set a birthday, otherwise nothing will be displayed.', 'birthdays-widget' ); ?>
                        </span>
                    </p>
                    <p>
                        <?php _e( '<b>Shortcode</b> is also available for use in posts or pages: ', 'birthdays-widget' ); ?>
                        &nbsp;<span class="description">[birthdays class="your_class" img_width="desired_width"]</span><br />
                        <?php _e( 'You can either add it your self, ', 'birthdays-widget' ); ?>
                        <?php _e( 'or you can click on our birthday button.', 'birthdays-widget' ); ?>
                    </p>
                </div>
                <hr />
                <div class="wrap">
                    <h3><?php _e( 'WordPress User Options', 'birthdays-widget' ); ?>:</h3>
                    <div class="opt_item <?php echo ($birthdays_settings[ 'user_data' ] == '0') ? 'opt_item_selected' : ''; ?>">
                        <input type="radio" name="birthdays_user_data" value="0"
                            <?php echo ($birthdays_settings[ 'user_data' ] == '0') ? "checked=\"checked\"" : ''; ?>/><br />
                        <?php _e( 'Save birthday of WordPress Users in our table and show birthday field in WordPress Profile Page', 'birthdays-widget' ); ?>
                    </div>
                    <div class="opt_item <?php echo ($birthdays_settings[ 'user_data' ] == '1') ? 'opt_item_selected' : ''; ?>">
                        <input type="radio" name="birthdays_user_data" value="1"
                            <?php echo ($birthdays_settings[ 'user_data' ] == '1') ? "checked=\"checked\"" : ''; ?>/><br />
                        <?php _e( 'Draw birthday of WordPress Users from the ', 'birthdays-widget' ); ?>
                        <select name="birthdays_date_meta_field" <?php echo ($birthdays_settings[ 'user_data' ] == '1') ? '' : "disabled=\"disabled\""; ?>>
                            <?php $meta_keys = self::birthday_get_filtered_meta();
                                foreach ( $meta_keys as $key ): ?>
                                    <option value="<?php echo $key; ?>" <?php echo ($birthdays_settings[ 'date_meta_field' ] == $key) ? "selected=\"selected\"" : ''; ?> >
                                        <?php echo $key; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php _e( ' meta field of WordPress User Profile', 'birthdays-widget' ); ?>
                    </div>
                    <div class="opt_item <?php echo ($birthdays_settings[ 'user_data' ] == '2') ? 'opt_item_selected' : ''; ?>">
                        <input type="radio" name="birthdays_user_data" value="2"
                            <?php echo ($birthdays_settings[ 'user_data' ] == '2') ? "checked=\"checked\"" : ''; ?>/><br />
                        <?php _e( 'Disabled birthday field for WordPress Users', 'birthdays-widget' ); ?>
                    </div>
                </div>
                <hr />
                <div class="wrap">
                    <h3><?php _e( 'Widget Image Options', 'birthdays-widget' ); ?>:</h3>
                    <?php $widget_image = $birthdays_settings[ 'image_enabled' ]; ?>
                    <p><?php _e( 'Select the image you want for the birthdays widget. Leaving this field empty will revert to the default image.', 'birthdays-widget' ); ?></p>
                    <input id="bw-image" type="text" size="50" name="birthdays_widget_image_url" value="<?php echo $birthdays_settings[ 'image_url' ]; ?>" 
                        <?php echo ( $widget_image ) ? '' : 'disabled="disabled"' ; ?> />
                    <input id="select-image" name="image" type="button" class="button-primary upload_image_button" value="<?php _e( 'Select Image', 'birthdays-widget' ); ?>" 
                        <?php echo ( $widget_image ) ? '' : 'disabled="disabled"' ; ?> />
                    <input id="default-image" name="default-image" type="button" class="button-primary" value="<?php _e( 'Default', 'birthdays-widget' ); ?>"
                        <?php echo ( $widget_image ) ? '' : 'disabled="disabled"' ; ?> data-default-image="<?php echo plugins_url( '/images/birthday_cake.png' , __FILE__ ); ?>" />
                    <input id="disable-image" type="button" class="button-primary" value="
                        <?php echo ( $widget_image ) ? __( 'Disable Image', 'birthdays-widget' ) : __( 'Enable Image', 'birthdays-widget' ) ; ?>" />
                    <input id="disable-img" name="birthdays_enable_image" type="hidden" value="<?php echo $widget_image; ?>" />
                    <p><?php _e( 'Select the width of the widget\'s image', 'birthdays-widget' ); ?>
                        <input name="birthdays_widget_image_width" type="text" size="3" value="<?php echo $birthdays_settings[ 'image_width' ]; ?>" />
                    </p>
                </div>
                <hr />
                <div class="wrap">
                    <h3><?php _e( 'Access to Plugin Options', 'birthdays-widget' ); ?>:</h3>
                    <p><?php _e( 'Here you can select which roles of your website can have access to page editing/viewing the birthday list.', 'birthdays-widget' ); ?></p>
                    <?php foreach ( $supported_roles as $role ) : ?>
                        <input type="checkbox" name="roles[]" value="<?php echo $role; ?>" 
                            <?php if ( in_array( $role, $current_roles ) ) echo 'checked="checked"'; ?> />
                        <?php echo $role.'<br />';
                    endforeach; ?>
                    <input type="hidden" name="birthdays_save" value="1" />
                </div>
                <p><input name="save" type="submit" class="button-primary" value="<?php _e( 'Save', 'birthdays-widget' ); ?>" /></p>
            </form>
            </div>
        <?php
        }

        public function birthday_get_filtered_meta( ) {
            global $wpdb;
            $select = "SELECT distinct $wpdb->usermeta.meta_key FROM $wpdb->usermeta";
            $tmp = $wpdb->get_results( $select );
            $meta_keys = array();
            foreach( $tmp as $key )
                $meta_keys[] = $key->meta_key;
            $arr = array( 'rich_editing', 'comment_shortcuts', 'admin_color', 'use_ssl', 'show_admin_bar_front', 'wp_capabilities', 
                            'wp_user_level', 'dismissed_wp_pointers', 'show_welcome_panel', 'wp_dashboard_quick_press_last_post_id',
                            'session_tokens', 'wporg_favorites', 'birthday_id' );
            //remove those meta for security reasons
            $meta_keys = array_diff( $meta_keys, $arr );
            $arr = array ( 'user_login', 'user_nicename', 'user_url', 'user_email', 'display_name' );
            //add some meta that are saved in wp_user table and can't be fetched with get_metadata
            $meta_keys = array_merge( $meta_keys, $arr );
            return $meta_keys;
        }

        public function birthdays_user_edit(){
            $current_user = wp_get_current_user();
            $birthdays_settings = get_option( 'birthdays_settings' );
            $birthdays_settings = maybe_unserialize( $birthdays_settings );
            $current_roles = $birthdays_settings [ 'roles' ];
            foreach($current_user->roles as $role) {
                if ( in_array(ucfirst($role), $current_roles) )
                    return true; 
            }
            return false;
        }

        public function create_plugin_page() {
            global $wpdb;
            $setting_url = admin_url( 'admin.php' ) . '?page=birthdays-widget';

            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_script( 'birthdays-table-js' );
            wp_enqueue_script( 'birthdays-date-picker' );
            wp_enqueue_style ( 'jquery-style' );
            wp_enqueue_style ( 'birthdays-table-css' );

            if ( ! current_user_can( 'manage_options' ) && ! self::birthdays_user_edit() ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.', 'birthdays-widget' ) );
            }

            echo '<div class="wrap">
                    <h2><div id="icon-options-general" class="icon32"></div>'.__( 'Birthdays Widget - List of Birthdays', 'birthdays-widget' ).
                        '<a href="#birthday_name" class="add-new-h2">'. __( 'Add New', 'birthdays-widget' ) .'</a>'.
                        '<a href="#birthday_name" class="add-new-h2">'. __( 'Bottom', 'birthdays-widget' ) .'</a></h2>';
            $table_name = $wpdb->prefix . 'birthdays';
                
            if ( isset( $_POST['birthdays_add_new'] ) ){
                if ( !isset( $_POST['birthday_name'] ) || empty( $_POST['birthday_name'] ) || !isset( $_POST['birthday_date'] ) || empty( $_POST['birthday_date'] )) {
                    echo '<div id="message" class="error"><p>'. __( 'Please fill all the boxes!', 'birthdays-widget' ) .'</p></div>';
                } else {
                    //add the new entry
                    $insert_query = "INSERT INTO $table_name (name, date) VALUES (%s, %s);";    
                    if ( $wpdb->query( $wpdb->prepare( $insert_query, $_POST['birthday_name'], date( 'Y-m-d' , strtotime($_POST['birthday_date'] ) ) ) ) == 1)
                        echo '<div id="message" class="updated"><p>'. __( 'Your new record was added!', 'birthdays-widget' ) .'</p></div>';
                    else 
                        echo '<div id="message" class="error"><p>Query error</p></div>';
                }
            }
            
            if ( isset( $_GET['action'] ) && !isset($_POST['birthdays_add_new']) ){
                if ( !isset( $_GET['id'] ) || empty( $_GET['id'] ) ){
                    //id is not set, some error must have occured
                    echo '<div id="message" class="error"><p>'. __( 'There was an error!', 'birthdays-widget' ) .'</p></div>';
                } elseif ( $_GET['action'] == "delete" ) {
                    //delete the record
                    $delete_query = "DELETE FROM $table_name WHERE id = '%d' LIMIT 1;";
                    if ( $wpdb->query( $wpdb->prepare( $delete_query, $_GET['id'] ) ) == 1 )
                        echo '<div id="message" class="updated"><p>'. __( 'The record was deleted!', 'birthdays-widget' ) .'</p></div>';
                    else
                        echo '<div id="message" class="error"><p>Query error</p></div>';
                }elseif ( $_GET['action'] == "edit" ){
                    if ( isset( $_GET['do'] ) && $_GET['do'] == "save" && isset( $_POST['birthdays_edit'] ) ){
                        //update the record
                        if ( !isset( $_POST['birthday_name'] ) || empty( $_POST['birthday_name'] ) || !isset( $_POST['birthday_date'] ) || empty( $_POST['birthday_date'] ) ) {
                            echo '<div id="message" class="error"><p>'. __( 'Please fill all the boxes!', 'birthdays-widget' ) .'</p></div>';
                            var_dump ( $_POST );
                        } else {
                            $update_query = "UPDATE $table_name SET name = '%s', date = '%s' WHERE id = '%d' LIMIT 1;";
                            if ( $wpdb->query( $wpdb->prepare( $update_query, $_POST['birthday_name'], date( 'Y-m-d' , strtotime( $_POST['birthday_date'] ) ), $_GET['id'] ) ) == 1)
                                echo '<div id="message" class="updated"><p>'. __( 'The record was updated!', 'birthdays-widget' ) .'</p></div>';
                        }
                    }
                    else{
                        //get record to edit
                        $select_query = "SELECT * FROM $table_name WHERE id = '%d' LIMIT 1;";
                        $result = $wpdb->get_row( $wpdb->prepare( $select_query, $_GET['id'] ) );
                        $birthday_edit = true;
                    }
                }
            }

            echo '<div id="birthdays_list">';

            $query = "SELECT * FROM $table_name;";
            $results = $wpdb->get_results( $query );

            echo '<table class="widefat dataTable display" id="birthday_table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>'. __( 'Name', 'birthdays-widget' ).'</th>       
                            <th>'. __( 'Date', 'birthdays-widget' ).'</th>
                            <th>'. __( 'Action', 'birthdays-widget' ).'</th>
                        </tr>
                    </thead>
                    <tbody>';

            $birthdays_settings = get_option( 'birthdays_settings' );
            $birthdays_settings = maybe_unserialize( $birthdays_settings );
            $meta_key = $birthdays_settings[ 'meta_field' ];
            
            $prefix = "cs_birth_widg_";
            $flag_row = true;

            foreach( $results as $row ) {
                $wp_usr = strpos( $row->name, $prefix );
                if ( $wp_usr !== false ) {
                    $birth_user = get_userdata( substr( $row->name, strlen( $prefix ) ) );
                    $row->name = $birth_user->{$meta_key};
                }
                echo '<tr>';
                echo    '<td>'. $row->id .'</td>
                        <td>'. $row->name .'</td>
                        <td>'. date_i18n( get_option( 'date_format' ), strtotime( $row->date ) ) .'</td>
                        <td><a href="'. $setting_url .'&action=edit&id='. $row->id .'">'. __( 'Edit', 'birthdays-widget' ) .'</a> 
                            | <a class="delete_link" href="'. $setting_url .'&action=delete&id='. $row->id .'">'. __( 'Delete', 'birthdays-widget' ) .'</a>
                        </td>                   
                    </tr>';
                if ( $flag_row )
                    $flag_row = false;
                else
                    $flag_row = true;
            }
            echo '</tbody>
                </table>';
            
            echo '<table class="widefat">
                    <thead>
                        <tr>
                            <th></th>
                            <th>'. __( 'Name', 'birthdays-widget' ).'</th>       
                            <th>'. __( 'Date', 'birthdays-widget' ).'</th>
                            <th>'. __( 'Action', 'birthdays-widget' ).'</th>
                        </tr>
                    </thead>
                    <tbody>';
            $flag = isset( $birthday_edit );
            if ($flag) {
                echo '<tr><form method="POST" action="'. $setting_url .'&action=edit&id='. $_GET['id'] .'&do=save">
                      <td>'. __( 'Editing', 'birthdays-widget') .'</td>
                      <input type="hidden" name="birthdays_edit" value="1" />';
            } else {
                echo '<tr><form method="POST" action="'. $setting_url .'">
                      <td>'. __( 'New', 'birthdays-widget') .'</td>
                      <input type="hidden" name="birthdays_add_new" value="1" /></td>';
            }
            echo '<td><input type="text" maxlength="45" style="width: 85%;" size="10" ';
                $wp_usr = false;
                if ( $flag ) {
                    $wp_usr = strpos( $result->name, $prefix );
                    if ( $wp_usr !== false ) {
                        $birth_usr_id = substr( $result->name, strlen( $prefix ) );
                        $birth_user = get_userdata( $birth_usr_id );
                        $result->name = $birth_user->{$meta_key};
                        echo 'disabled="disabled"';
                    }
                    echo 'value="' . $result->name . '"';
                } else {
                    echo 'value=""';
                }
                echo ' id="birthday_name" name="birthday_name" />';
                if ( $flag && $wp_usr !== false )
                    echo '<input type="hidden" name="birthday_name" value="cs_birth_widg_' . $birth_usr_id . '"/>';
                echo '</td><td><input type="text" size="10" id="birthday_date" name="birthday_date" value="';
            echo ($flag) ? date_i18n( 'd-m-Y', strtotime( $result->date ) ) : '';
            echo '" id="station_url" name="station_url" /></td>
                    <td><input name="save" type="submit" class="button-primary" value="'. __( 'Save', 'birthdays-widget' ) .'" /></td>
                    </form></tr></tbody>
                </table>';

            echo '</div>
            </div>';
            
        }

        public function create_submenu_page_import() {
            if ( ! current_user_can( 'manage_options' ) && ! self::birthdays_user_edit() ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.', 'birthdays-widget' ) );
            }
            echo '<div class="wrap">
                    <h2>Import Birthday List Page</h2>
                    <p>' . __( 'Here you can upload a CSV file with your own data or from a plugin-export. (CSV must have format <name>,<date> where <name> a string and <date> as Y-m-D)', 'birthdays-widget' ) .'</p>
                    <div class="wrap">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <label for="uploadedfile">'. __( 'File', 'birthdays-widget' ) .'</label> <input type="file" name="uploadedfile" id="uploadedfile" accept=".csv" />
                            <input name="upload" type="submit" class="button-primary" value="'. __( 'Upload', 'birthdays-widget' ) .'" />
                            <input type="hidden" name="birthdays_upload_file" value="1" />
                        </form>';
            if ( isset( $_POST['birthdays_upload_file'] ) && $_POST['birthdays_upload_file'] == 1 ) {
                if ( isset( $_FILES['uploadedfile'] ) ) {
                    $target_path = dirname( __FILE__ )."/uploads/";
                    $target_path = $target_path . basename( $_FILES['uploadedfile']['name'] );
                    
                    if ( move_uploaded_file( $_FILES['uploadedfile']['tmp_name'], $target_path ) ) {
                        echo "<p>The file " . basename( $_FILES['uploadedfile']['name'] ) ." has been uploaded. ";
                        $row = 0;
                        if ( FALSE !== ( $handle = fopen( $target_path, "r" ) ) ) {
                            global $wpdb;
                            $table_name = $wpdb->prefix . "birthdays";
                            while( FALSE !== ( $data = fgetcsv( $handle, 1000, "," ) ) ) {
                                if ( 2 != count( $data ) ){
                                    echo 'Wrong csv format!<br/>';
                                    break;
                                }
                                $row++;
                                $new_record['name'] = $data[0];
                                $new_record['date'] = $data[1];
                                //TODO maybe convert date format
                                $wpdb->insert( 
                                            $table_name, 
                                            array( 
                                                'name' => $new_record['name'], 
                                                'date' => $new_record['date'] 
                                            ), 
                                            array( 
                                                '%s', 
                                                '%s' 
                                            ));
                            }
                            echo '<b>' . $row . '</b> records inserted!</p>';
                            fclose( $handle );
                        }
                        @unlink( $target_path );
                    } else {
                        echo '<div id="message" class="error"><p>'. __( 'There was an error uploading the file, please try again!', 'birthdays-widget' ) .'</p></div>';
                    }
                } else {
                    echo '<div id="message" class="error"><p>'. __( 'Select a file first!', 'birthdays-widget' ) .'</p></div>';
                }
            }
            echo '</div>';
        }
        
        public function create_submenu_page_export() {
            if ( ! current_user_can( 'manage_options' ) && ! self::birthdays_user_edit() ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.', 'birthdays-widget' ) );
            }
            echo '<div class="wrap">
                    <h2>Export Birthday List Page</h2>
                    <p>'. __( 'In order to download the export file press ', 'birthdays-widget' ) .'
                        <a href="'. admin_url( 'admin-ajax.php' ) .'?action=get_birthdays_export_file" target="_blank" class="button-primary" id="birthdays-export-button">'. __( 'Download', 'birthdays-widget' ) .'</a>
                    </p>
                </div>';
        }
        
    }
    