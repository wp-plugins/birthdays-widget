<?php
    class Birthdays_Widget_Settings
    {
        private $options;
    
        public function __construct() {
            add_action( 'admin_menu', array( &$this, 'add_plugin_page' ) );
        }
    
        public function add_plugin_page() {
            add_menu_page( 'Birthdays Widget', __( 'Birthdays', 'birthdays-widget' ), 'read', 'birthdays-widget', array( &$this, 'create_plugin_page' ) );
            add_submenu_page( 'birthdays-widget', __( 'Options', 'birthdays-widget' ), __( 'Options', 'birthdays-widget' ), 'read', 'birthdays-widget-options', array( &$this, 'create_options_page' ) );
            add_submenu_page( 'birthdays-widget', __( 'Import', 'birthdays-widget' ), __( 'Import', 'birthdays-widget' ), 'read', 'birthdays-widget-import', array( &$this, 'create_submenu_page_import' ) );
            add_submenu_page( 'birthdays-widget', __( 'Export', 'birthdays-widget' ), __( 'Export', 'birthdays-widget' ), 'read', 'birthdays-widget-export', array( &$this, 'create_submenu_page_export' ) );
        }
    
        public function sanitize( $input ) {
            $new_input = array( );
            if ( isset( $input[ 'id_number' ] ) )
                $new_input[ 'id_number' ] = absint( $input[ 'id_number' ] );
    
            if (isset($input[ 'title']))
                $new_input[ 'title' ] = sanitize_text_field( $input[ 'title' ] );
    
            return $new_input;
        }
    
        public function print_section_info() {
            print 'Enter your settings below:';
        }
        
        public function id_number_callback() {
            printf(
                '<input type="text" id="id_number" name="my_option_name[id_number]" value="%s" />',
                isset( $this->options[ 'id_number' ] ) ? esc_attr( $this->options[ 'id_number' ] ) : ''
            );
        }
    
        public function title_callback() {
            printf(
                '<input type="text" id="title" name="my_option_name[title]" value="%s" />',
                isset( $this->options[ 'title' ] ) ? esc_attr( $this->options[ 'title' ] ) : ''
            );
        }
        
        public function create_options_page() {
            if ( ! current_user_can( 'manage_options' ) && ! self::birthdays_user_edit() ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.', 'birthdays-widget' ) );
            } ?>
        <div class="wrap">
            <div id="icon-edit" class="icon32"></div>
            <h2><?php _e( 'Birthdays Widget Options ', 'birthdays-widget' ); ?></h2>
            <?php
                wp_enqueue_script( 'birthdays-script' );
                wp_enqueue_style ( 'birthdays-css' );
                wp_enqueue_media();
                
                $birthdays_settings = get_option( 'birthdays_settings' );
                $birthdays_settings = maybe_unserialize( $birthdays_settings );
                if ( isset( $_POST[ 'birthdays_save' ] ) && check_admin_referer( 'birthdays_form' ) ) {
                    $birthdays_settings[ 'roles' ] = $_POST[ 'roles'];
                    if ( isset( $_POST[ 'birthdays_register_form' ] ) && $_POST[ 'birthdays_register_form' ] != '0' ) {
                        $birthdays_settings[ 'register_form' ] = 1;
                    } else {
                        $birthdays_settings[ 'register_form' ] = 0;
                    }
                    if ( isset( $_POST[ 'birthdays_meta_field' ] ) ) {
                        $birthdays_settings[ 'meta_field' ] = wp_strip_all_tags( $_POST[ 'birthdays_meta_field' ] );
                    } else {
                        $birthdays_settings[ 'meta_field' ] = 'display_name';
                    }
                    if ( isset( $_POST[ 'birthdays_comma' ] ) && $_POST[ 'birthdays_comma' ] != '0' ) {
                        $birthdays_settings[ 'comma' ] = 1;
                    } else {
                        $birthdays_settings[ 'comma' ] = 0;
                    }
                    if ( isset( $_POST[ 'birthdays_user_age' ] ) && $_POST[ 'birthdays_user_age' ] != '0' ) {
                        $birthdays_settings[ 'user_age' ] = 1;
                    } else {
                        $birthdays_settings[ 'user_age' ] = 0;
                    }
                    if ( isset( $_POST[ 'upcoming_mode' ] ) && $_POST[ 'upcoming_mode' ] != '0' ) {
                        $birthdays_settings[ 'upcoming_mode' ] = 1;
                    } else {
                        $birthdays_settings[ 'upcoming_mode' ] = 0;
                    }
                    if ( isset( $_POST[ 'upcoming_days_birthdays' ] ) ) {
                        if ( !is_numeric( $_POST[ 'upcoming_days_birthdays' ] ) ) {
                            $birthdays_settings[ 'upcoming_days_birthdays' ] = 3;
                        } else {
                            $birthdays_settings[ 'upcoming_days_birthdays' ] = $_POST[ 'upcoming_days_birthdays' ];
                        }
                    } else {
                        $birthdays_settings[ 'upcoming_days_birthdays' ] = 3;
                    }
                    if ( isset( $_POST[ 'upcoming_consecutive_days' ] ) ) {
                        if ( !is_numeric( $_POST[ 'upcoming_consecutive_days' ] ) ) {
                            $birthdays_settings[ 'upcoming_consecutive_days' ] = 3;
                        } else {
                            $birthdays_settings[ 'upcoming_consecutive_days' ] = $_POST[ 'upcoming_consecutive_days' ];
                        }
                    } else {
                        $birthdays_settings[ 'upcoming_consecutive_days' ] = 3;
                    }
                    if ( isset( $_POST[ 'birthdays_date_meta_field' ] ) ) {
                        $birthdays_settings[ 'date_meta_field' ] = $_POST[ 'birthdays_date_meta_field' ];
                    } else {
                        $birthdays_settings[ 'date_meta_field' ] = '';
                    }
                    if ( isset( $_POST[ 'wp_user_gravatar' ] ) && $_POST[ 'wp_user_gravatar' ] != '0' ) {
                        $birthdays_settings[ 'wp_user_gravatar' ] = 1;
                    } else {
                        $birthdays_settings[ 'wp_user_gravatar' ] = 0;
                    }
                    if ( isset( $_POST[ 'birthdays_user_data' ] ) ) {
                        $birthdays_settings[ 'user_data' ] = $_POST[ 'birthdays_user_data' ];
                        if ( $_POST[ 'birthdays_user_data' ] == 0 ) {
                            /* Birthdays of WP Users are saved in our table and field in WP Profile is shown */
                            $birthdays_settings[ 'profile_page' ] = 1;
                            $birthdays_settings [ 'date_from_profile' ] = 0;
                            //TODO Maybe add a function to delete all WP User Birthdays saved in our table
                        } elseif ( $_POST[ 'birthdays_user_data' ] == 1 ) {
                            /* Birthdays of WP Users are being drawn from a WP User meta field specified by the user */
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
                    if ( isset( $_POST[ 'birthdays_widget_image_width' ] ) && !empty( $_POST[ 'birthdays_widget_image_width' ] ) ) {
                        $birthdays_settings[ 'image_width' ] = wp_strip_all_tags( $_POST[ 'birthdays_widget_image_width' ] );
                    } else {
                        $birthdays_settings[ 'image_width' ] = '55%';
                    }
                    if ( isset( $_POST[ 'birthdays_list_image_width' ] ) && !empty( $_POST[ 'birthdays_list_image_width' ] ) ) {
                        $birthdays_settings[ 'list_image_width' ] = wp_strip_all_tags( $_POST[ 'birthdays_list_image_width' ] );
                    } else {
                        $birthdays_settings[ 'list_image_width' ] = '20%';
                    }
                    if ( isset( $_POST[ 'birthdays_widget_image_url' ] ) && !empty( $_POST[ 'birthdays_widget_image_url' ] ) ) {
                        $birthdays_settings[ 'image_url' ] = wp_strip_all_tags( $_POST[ 'birthdays_widget_image_url' ] );
                    } else {
                        $birthdays_settings[ 'image_url' ] = plugins_url( '/images/birthday_cake.png' , __FILE__ );
                    }
                    if ( isset( $_POST[ 'birthdays_enable_image' ] ) && !empty( $_POST[ 'birthdays_enable_image' ] ) ) {
                        $birthdays_settings[ 'image_enabled' ] = 1;
                    } else {
                        $birthdays_settings[ 'image_enabled' ] = 0;
                    }
                    if ( isset( $_POST[ 'birthdays_user_image_url' ] ) && !empty( $_POST[ 'birthdays_user_image_url' ] ) ) {
                        $birthdays_settings[ 'user_image_url' ] = wp_strip_all_tags( $_POST[ 'birthdays_user_image_url' ] );
                    } else {
                        $birthdays_settings[ 'user_image_url' ] = plugins_url( '/images/default_user.png' , __FILE__ );
                    }
                    if ( isset( $_POST[ 'birthdays_enable_user_image' ] ) && !empty( $_POST[ 'birthdays_enable_user_image' ] ) ) {
                        $birthdays_settings[ 'user_image_enabled' ] = 1;
                    } else {
                        $birthdays_settings[ 'user_image_enabled' ] = 0;
                    }
                    if ( isset( $_POST[ 'birthdays_wish' ] ) && !empty( $_POST[ 'birthdays_wish' ] ) ) {
                        $birthdays_settings[ 'wish' ] = wp_strip_all_tags( $_POST[ 'birthdays_wish' ] );
                    } else {
                        $birthdays_settings[ 'wish' ] = __( 'Happy Birthday', 'birthdays-widget' );
                    }
                    if ( isset( $_POST[ 'color_current_day' ] ) && !empty( $_POST[ 'color_current_day' ] ) ) {
                        $birthdays_settings[ 'color_current_day' ] = wp_strip_all_tags( $_POST[ 'color_current_day' ] );
                    } else {
                        $birthdays_settings[ 'color_current_day' ] = '#FF8000';
                    }
                    if ( isset( $_POST[ 'color_one' ] ) && !empty( $_POST[ 'color_one' ] ) ) {
                        $birthdays_settings[ 'color_one' ] = wp_strip_all_tags( $_POST[ 'color_one' ] );
                    } else {
                        $birthdays_settings[ 'color_one' ] = '#2277cc';
                    }
                    if ( isset( $_POST[ 'color_two' ] ) && !empty( $_POST[ 'color_two' ] ) ) {
                        $birthdays_settings[ 'color_two' ] = wp_strip_all_tags( $_POST[ 'color_two' ] );
                    } else {
                        $birthdays_settings[ 'color_two' ] = '#cc7722';
                    }
                    if ( isset( $_POST[ 'second_color' ] ) && !empty( $_POST[ 'second_color' ] ) ) {
                        $birthdays_settings[ 'second_color' ] = 1;
                    } else {
                        $birthdays_settings[ 'second_color' ] = 0;
                    }
                    $saved_values = maybe_serialize( $birthdays_settings );
                    update_option( 'birthdays_settings', $saved_values );
                    ?><div class="updated fade">
                        <p><strong><?php _e( 'Options successfully updated.', 'birthdays-widget' ); ?></strong></p>
                    </div><?php
                }
                $current_roles = $birthdays_settings[ 'roles' ];
                $sup_roles = get_editable_roles();
                $supported_roles = array();
                foreach ( $sup_roles as $role ) {
                    $supported_roles[] = $role[ 'name' ];
                }
                wp_enqueue_style( 'wp-color-picker' );
                wp_enqueue_script( 'wp-color-picker' );
            ?>
            <h2 class="nav-tab-wrapper">
                <a href="#birthdays-tab-general" class="nav-tab nav-tab-active"><?php _e( 'General settings', 'birthdays-widget' ); ?></a>
                <a href="#birthdays-tab-wp-user" class="nav-tab"><?php _e( 'WordPress User', 'birthdays-widget' ); ?></a>
                <a href="#birthdays-tab-image" class="nav-tab"><?php _e( 'Image Settings', 'birthdays-widget' ); ?></a>
                <a href="#birthdays-tab-access" class="nav-tab"><?php _e( 'Access', 'birthdays-widget' ); ?></a>
                <a href="#birthdays-tab-calendar" class="nav-tab"><?php _e( 'Calendar', 'birthdays-widget' ); ?></a>
                <a href="#birthdays-tab-upcoming" class="nav-tab"><?php _e( 'Upcoming', 'birthdays-widget' ); ?></a>
            </h2>
            <form id="birthdays_settings_form" name="birthdays_form" method="post" action="">
                <div class="table" id="birthdays-tab-general">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th><?php _e( 'Birthday "wish"', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Birthday "wish"', 'birthdays-widget' ); ?></span></legend>
                                    <label for="birthdays_wish">
                                        <input type="text" size="35" name="birthdays_wish" value="<?php echo $birthdays_settings[ 'wish' ]; ?>" />
                                        <br /><?php _e( 'Write your custom "wish"', 'birthdays-widget' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Fields in Registration Form', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Fields in Registration Form', 'birthdays-widget' ); ?></span></legend>
                                    <label for="birthdays_register_form">
                                        <select name="birthdays_register_form" id="birthdays_register_form">
                                            <option value='1' <?php if ( $birthdays_settings[ 'register_form' ] == 1 ) echo "selected='selected'"; ?> ><?php _e( 'Yes' , 'birthdays-widget' ); ?></option>
                                            <option value='0' <?php if ( $birthdays_settings[ 'register_form' ] == 0 ) echo "selected='selected'"; ?> ><?php _e( 'No' , 'birthdays-widget' ); ?></option>
                                        </select>
                                        <br /><?php _e( 'Name and Birthday fields at WordPress User Registration Form', 'birthdays-widget' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Comma between names', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Comma between names', 'birthdays-widget' ); ?></span></legend>
                                    <label for="birthdays_comma">
                                        <select name="birthdays_comma" id="birthdays_comma">
                                            <option value='1' <?php if ( $birthdays_settings[ 'comma' ] == 1 ) echo "selected='selected'"; ?> ><?php _e( 'Yes' , 'birthdays-widget' ); ?></option>
                                            <option value='0' <?php if ( $birthdays_settings[ 'comma' ] == 0 ) echo "selected='selected'"; ?> ><?php _e( 'No' , 'birthdays-widget' ); ?></option>
                                        </select>
                                        <br /><?php _e( 'Select if you want comma (,) to be displayed between the names in widget', 'birthdays-widget' ); ?>
                                        <br /><span class="description">
                                            <?php _e( 'If you want space between the names but no comma, consider adding a CSS rule to class ', 'birthdays-widget' ); ?>
                                            <b>birthday_element</b>
                                        </span>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Show User\'s age', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Show User\'s age', 'birthdays-widget' ); ?></span></legend>
                                    <label for="birthdays_user_age">
                                        <select name="birthdays_user_age" id="birthdays_user_age">
                                            <option value='1' <?php if ( $birthdays_settings[ 'user_age' ] == 1 ) echo "selected='selected'"; ?> ><?php _e( 'Yes' , 'birthdays-widget' ); ?></option>
                                            <option value='0' <?php if ( $birthdays_settings[ 'user_age' ] == 0 ) echo "selected='selected'"; ?> ><?php _e( 'No' , 'birthdays-widget' ); ?></option>
                                        </select>
                                        <br /><?php _e( 'Select if you want age of Users to be displayed', 'birthdays-widget' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table ui-tabs-hide" id="birthdays-tab-wp-user">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th><?php _e( 'Birthday for WordPress Users', 'birthdays-widget' ); ?></th>
                            <td>
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
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Meta value as name', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Meta value as name', 'birthdays-widget' ); ?></span></legend>
                                    <label for="birthdays_meta_field">
                                        <select name="birthdays_meta_field">
                                            <?php $meta_keys = self::birthday_get_filtered_meta();
                                                foreach ( $meta_keys as $key ): ?>
                                                    <option value="<?php echo $key; ?>" <?php echo ( $birthdays_settings[ 'meta_field' ] == $key ) ? "selected=\"selected\"" : ''; ?> >
                                                        <?php echo $key; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <br /><?php _e( 'Select which Wordpress User\'s meta value you like to be shown as name in widget', 'birthdays-widget' ); ?>
                                        <br /><span class="description">
                                            <?php _e( 'Careful! The meta you select must be present in every WP User you set a birthday, otherwise nothing will be displayed.', 'birthdays-widget' ); ?>
                                        </span>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Gravatar\'s profile image', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Gravatar\'s profile image', 'birthdays-widget' ); ?></span></legend>
                                    <label for="wp_user_gravatar">
                                        <select name="wp_user_gravatar">
                                            <option value="1" <?php echo ( $birthdays_settings[ 'wp_user_gravatar' ] == 1 ) ? "selected=\"selected\"" : ''; ?> ><?php _e( 'Enabled' , 'birthdays-widget' ); ?></option>
                                            <option value="0" <?php echo ( $birthdays_settings[ 'wp_user_gravatar' ] == 0 ) ? "selected=\"selected\"" : ''; ?> ><?php _e( 'Disabled' , 'birthdays-widget' ); ?></option>
                                        </select>
                                        <br /><span class="description">
                                            <?php _e( 'If disabled images for WordPress Users will be handled with WordPress Media Manager.', 'birthdays-widget' ); ?>
                                        </span>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table ui-tabs-hide" id="birthdays-tab-image">
                    <?php 
                        $widget_image = $birthdays_settings[ 'image_enabled' ]; 
                        $user_image = $birthdays_settings[ 'user_image_enabled' ];
                        if ( is_numeric( $birthdays_settings[ 'image_url' ] ) ) {
                            $default_image_src = wp_get_attachment_image_src( $birthdays_settings[ 'image_url' ], 'medium' );
                            $default_image_src = $default_image_src[ 0 ];
                        } else {
                            $default_image_src = $birthdays_settings[ 'image_url' ];
                        }
                        if ( is_numeric( $birthdays_settings[ 'user_image_url' ] ) ) {
                            $default_user_image_src = wp_get_attachment_image_src( $birthdays_settings[ 'user_image_url' ], 'medium' );
                            $default_user_image_src = $default_user_image_src[ 0 ];
                        } else {
                            $default_user_image_src = $birthdays_settings[ 'user_image_url' ];
                        }
                        $disabled_txt = __( 'Disabled', 'birthdays-widget' );
                        $enabled_txt = __( 'Enabled', 'birthdays-widget' );
                    ?>
                    <span class="hidden" id="disabled_txt" ><?php echo $disabled_txt; ?></span>
                    <span class="hidden" id="enabled_txt" ><?php echo $enabled_txt; ?></span>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th><?php _e( 'Select the image displayed on top of widget', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Select the image displayed on top of widget', 'birthdays-widget' ); ?></span></legend>
                                    <label for="birthdays_widget_image_url">
                                        <img id="birthdays_widget_image_preview" class="birthday_admin_image" src="<?php echo $default_image_src; ?>" alt="Default Widget Image" />
                                        <input id="birthdays_widget_image" class="bw-image" type="hidden" size="20" name="birthdays_widget_image_url" value="<?php echo $birthdays_settings[ 'image_url' ]; ?>" 
                                            <?php echo ( $widget_image ) ? '' : 'disabled="disabled"' ; ?> />
                                        <input name="image" type="button" class="button-primary upload_image_button select-image" value="<?php _e( 'Select Image', 'birthdays-widget' ); ?>" 
                                            <?php echo ( $widget_image ) ? '' : 'disabled="disabled"' ; ?> data-url-input="birthdays_widget_image" />
                                        <input name="default-image" type="button" class="button-primary default-image" value="<?php _e( 'Default', 'birthdays-widget' ); ?>"
                                            <?php echo ( $widget_image ) ? '' : 'disabled="disabled"' ; ?> data-default-image="<?php echo plugins_url( '/images/birthday_cake.png' , __FILE__ ); ?>" data-url-input="birthdays_widget_image" />
                                        <input type="button" class="button-primary disable-image" value="
                                            <?php echo ( $widget_image ) ? $disabled_txt : $enabled_txt ; ?>" />
                                        <input class="disable-img" name="birthdays_enable_image" type="hidden" value="<?php echo $widget_image; ?>" />
                                        <br /><span class="description">
                                            <?php _e( 'Leaving this field empty will revert to the default image.', 'birthdays-widget' ); ?>
                                        </span>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Select default User image', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Select default User image', 'birthdays-widget' ); ?></span></legend>
                                    <label for="birthdays_user_image_url">
                                        <img id="birthdays_user_image_preview" src="<?php echo $default_user_image_src; ?>" alt="Default User Image" class="birthday_admin_image" />
                                        <input id="birthdays_user_image" class="bw-image" type="hidden" size="20" name="birthdays_user_image_url" value="<?php echo $birthdays_settings[ 'user_image_url' ]; ?>" 
                                            <?php echo ( $user_image ) ? '' : 'disabled="disabled"' ; ?> />
                                        <input name="image" type="button" class="button-primary upload_image_button select-image" value="<?php _e( 'Select Image', 'birthdays-widget' ); ?>" 
                                            <?php echo ( $user_image ) ? '' : 'disabled="disabled"' ; ?> data-url-input="birthdays_user_image" />
                                        <input name="default-image" type="button" class="button-primary default-image" value="<?php _e( 'Default', 'birthdays-widget' ); ?>"
                                            <?php echo ( $user_image ) ? '' : 'disabled="disabled"' ; ?> data-default-image="<?php echo plugins_url( '/images/default_user.png' , __FILE__ ); ?>" data-url-input="birthdays_user_image" />
                                        <input type="button" class="button-primary disable-image" value="
                                            <?php echo ( $user_image ) ? $disabled_txt : $enabled_txt ; ?>" />
                                        <input class="disable-img" name="birthdays_enable_user_image" type="hidden" value="<?php echo $user_image; ?>" />
                                        <br /><span class="description">
                                            <?php _e( 'Leaving this field empty will revert to the default image.', 'birthdays-widget' ); ?>
                                        </span>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Set the width', 'birthdays-widget' ); ?></th>                            
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Set the width of ', 'birthdays-widget' ); ?></span></legend>
                                    <label for="birthdays_widget_image_width">
                                        <?php _e( 'Widget\'s image', 'birthdays-widget' ); ?>: 
                                        <input name="birthdays_widget_image_width" type="text" size="3" value="<?php echo $birthdays_settings[ 'image_width' ]; ?>" />
                                        <br /><?php _e( 'Images in list template', 'birthdays-widget' ); ?>: 
                                        <input name="birthdays_list_image_width" type="text" size="3" value="<?php echo $birthdays_settings[ 'list_image_width' ]; ?>" />
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table ui-tabs-hide" id="birthdays-tab-access">
                    <p><?php _e( 'Here you can select which roles of your website can have access to page editing/viewing the birthday list.', 'birthdays-widget' ); ?></p>
                    <?php foreach ( $supported_roles as $role ) : ?>
                        <input type="checkbox" name="roles[]" value="<?php echo $role; ?>" 
                            <?php if ( in_array( $role, $current_roles ) ) echo 'checked="checked"'; ?> />
                        <?php echo $role.'<br />';
                    endforeach; ?>
                    <input type="hidden" name="birthdays_save" value="1" />
                </div>
                
                <div class="table ui-tabs-hide" id="birthdays-tab-calendar">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th><?php _e( 'Current Day Color', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Colour for birthdays in Calendar Widget', 'birthdays-widget' ); ?></span></legend>
                                    <label for="color_current_day">
                                        <input name="color_current_day" type="text" value="<?php echo $birthdays_settings[ 'color_current_day' ]; ?>" class="color_field" 
                                            data-default-color="#FF8000" />
                                        <br /><?php _e( 'Select the color of current day in Calendar view of widget', 'birthdays-widget' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Calendar Color #1', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Calendar Color #1', 'birthdays-widget' ); ?></span></legend>
                                    <label for="color_one">
                                        <input name="color_one" type="text" value="<?php echo $birthdays_settings[ 'color_one' ]; ?>" class="color_field" 
                                            data-default-color="#2277cc" />
                                        <br /><?php _e( 'Select the color of days marked with birthdays in Calendar view of widget', 'birthdays-widget' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <?php $sec_color = $birthdays_settings[ 'second_color' ]; ?>
                        <tr>
                            <th><?php _e( 'Second Color', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Second Color', 'birthdays-widget' ); ?></span></legend>
                                    <label for="second_color">
                                        <select name="second_color" id="second_color">
                                            <option value='1' <?php if ( $sec_color == 1 ) echo "selected='selected'"; ?> ><?php _e( 'Yes' , 'birthdays-widget' ); ?></option>
                                            <option value='0' <?php if ( $sec_color == 0 ) echo "selected='selected'"; ?> ><?php _e( 'No' , 'birthdays-widget' ); ?></option>
                                        </select>
                                        <br /><?php _e( 'Select if you want the colors of days marked with birthdays to change', 'birthdays-widget' ); ?>
                                        <br /><span class="description">
                                            <?php _e( 'If you have consecutive days with birthdays and don\'t want the same color', 'birthdays-widget' ); ?>
                                        </span>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr <?php echo 'class="birthdays_hidden ' . ( ( !$sec_color ) ? 'hidden "' : ' "' ); ?> >
                            <th><?php _e( 'Calendar Color #2', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Calendar Color #2', 'birthdays-widget' ); ?></span></legend>
                                    <label for="color_two">
                                        <input name="color_two" type="text" value="<?php echo $birthdays_settings[ 'color_two' ]; ?>" class="color_field" 
                                            data-default-color="#cc7722" id="color_two" />
                                        <br /><?php _e( 'Select the second color to be displayed for consecutive days', 'birthdays-widget' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table ui-tabs-hide" id="birthdays-tab-upcoming">
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th><?php _e( 'Mode', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Mode', 'birthdays-widget' ); ?></span></legend>
                                    <label for="upcoming_mode">
                                        <select name="upcoming_mode" id="upcoming_mode">
                                            <option value='1' <?php if ( $birthdays_settings[ 'upcoming_mode' ] == 1 ) echo "selected='selected'"; ?> ><?php _e( 'Consecutive days' , 'birthdays-widget' ); ?></option>
                                            <option value='0' <?php if ( $birthdays_settings[ 'upcoming_mode' ] == 0 ) echo "selected='selected'"; ?> ><?php _e( 'Days with birthdays' , 'birthdays-widget' ); ?></option>
                                        </select>
                                        <br /><?php _e( 'Select if you want the plugin to display # consecutive days from the current day or search for days with birthdays.', 'birthdays-widget' ); ?>
                                        <br /><span class="description">
                                            <?php _e( 'Ex: Consecutive days would be 1rst February, 2nd February, 3rd February ... etc', 'birthdays-widget' ); ?><br />
                                            <?php _e( 'Days with birthdays could be 1rst February, 3rd March, 15th March ... etc, based on the birthdays in your list', 'birthdays-widget' ); ?>
                                        </span>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Days with birthdays', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Days with birthdays', 'birthdays-widget' ); ?></span></legend>
                                    <label for="upcoming_days_birthdays">
                                        <input name="upcoming_days_birthdays" id="upcoming_days_birthdays" type="number" 
                                            value="<?php echo ( $birthdays_settings[ 'upcoming_days_birthdays' ] ); ?>" />
                                        <br /><?php _e( 'Select number of days with birthdays displayed in upcoming view', 'birthdays-widget' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e( 'Consecutive days', 'birthdays-widget' ); ?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e( 'Consecutive days', 'birthdays-widget' ); ?></span></legend>
                                    <label for="upcoming_consecutive_days">
                                        <input name="upcoming_consecutive_days" id="upcoming_consecutive_days" type="number" 
                                            value="<?php echo ( $birthdays_settings[ 'upcoming_consecutive_days' ] ); ?>" />
                                        <br /><?php _e( 'Select number of consecutive days displayed in upcoming view', 'birthdays-widget' ); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <p><input name="save" type="submit" class="button-primary" value="<?php _e( 'Save', 'birthdays-widget' ); ?>" /></p>
                <?php wp_nonce_field( 'birthdays_form' ); ?>
            </form>               
            <hr />
            <p>
                <span class="description alignright"><?php echo __( 'Plugin Version ', 'birthdays-widget' ) . BW; ?></span>
                <?php _e( '<b>Shortcode</b> is also available for use in posts or pages: ', 'birthdays-widget' ); ?>
                &nbsp;<span class="description">[birthdays class="your_class" img_width="desired_width" template="default | list | calendar"]</span><br />
                <?php _e( 'You can either add it your self, ', 'birthdays-widget' ); ?>
                <?php _e( 'or you can click on our birthday button.', 'birthdays-widget' ); ?>
            </p>
        </div><?php
        }

        public function birthday_get_filtered_meta() {
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

        public function birthdays_user_edit() {
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
            wp_enqueue_script( 'jquery-ui-tooltip' );
            wp_enqueue_script( 'birthdays-table-js' );
            wp_enqueue_script( 'birthdays-script' );
            wp_enqueue_style ( 'jquery-style' );
            wp_enqueue_style ( 'birthdays-table-css' );
            wp_enqueue_style ( 'birthdays-css' );

            if ( ! self::birthdays_user_edit() ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.', 'birthdays-widget' ) );
            }

            $table_name = $wpdb->prefix . 'birthdays';
            echo '<div class="wrap">
                    <h2><div id="icon-options-general" class="icon32"></div>'.__( 'Birthdays Widget - List of Birthdays', 'birthdays-widget' ).
                        '<a href="#birthday_name" class="add-new-h2">'. __( 'Add New', 'birthdays-widget' ) .'</a>'.
                        '<a href="' . $setting_url . '&birthdays_delete_all=1" class="add-new-h2 delete_link">'. __( 'Delete All', 'birthdays-widget' ) .'</a></h2>';

            if ( isset( $_POST[ 'birthdays_add_new' ] ) && check_admin_referer( 'birthdays_add_form' ) ) {
                if ( !isset( $_POST[ 'birthday_name' ] ) || empty( $_POST[ 'birthday_name' ] ) || !isset( $_POST[ 'birthday_date' ] ) || empty( $_POST[ 'birthday_date' ] )) {
                    ?><div id="message" class="error"><p><?php _e( 'Please fill all the boxes!', 'birthdays-widget' ); ?></p></div><?php
                } else {
                    //add the new entry
                    $insert_query = "INSERT INTO $table_name (name, date, email, image) VALUES (%s, %s, %s, %s);";    
                    $query = $wpdb->prepare( $insert_query, $_POST[ 'birthday_name' ], date( 'Y-m-d' , strtotime( $_POST[ 'birthday_date' ] ) ), $_POST[ 'birthday_email' ], $_POST[ 'birthday_image' ] );
                    if ( $wpdb->query( $query ) == 1 ) {
                        ?><div id="message" class="updated"><p><?php _e( 'Your new record was added!', 'birthdays-widget' ); ?></p></div><?php
                    } else {
                        ?><div id="message" class="error"><p><?php _e( 'Query error', 'birthdays-widget' ); ?></p></div><?php
                    }
                }
            }

			if ( isset( $_GET[ 'birthdays_delete_all' ] ) ) {
                //drop a custom db table
				global $wpdb;
				$table_name = $wpdb->prefix . "birthdays";
				$sql = "TRUNCATE TABLE `$table_name`;" ;
				$wpdb->query( $sql );
            }

            if ( isset( $_GET[ 'action' ] ) && !isset( $_POST[ 'birthdays_add_new'] ) ) {
                if ( !isset( $_GET[ 'id' ] ) || empty( $_GET[ 'id' ] ) ) {
                    //id is not set, some error must have occurred
                    ?><div id="message" class="error"><p><?php _e( 'There was an error!', 'birthdays-widget' ); ?></p></div><?php
                } elseif ( $_GET[ 'action' ] == "delete" ) {
                    //delete the record
                    $delete_query = "DELETE FROM $table_name WHERE id = '%d' LIMIT 1;";
                    if ( $wpdb->query( $wpdb->prepare( $delete_query, $_GET[ 'id' ] ) ) == 1 )
                        echo '<div id="message" class="updated"><p>'. __( 'The record was deleted!', 'birthdays-widget' ) .'</p></div>';
                    else
                        echo '<div id="message" class="error"><p>Query error</p></div>';
                } elseif ( $_GET[ 'action' ] == "edit" ) {
                    if ( isset( $_GET[ 'do' ] ) && $_GET[ 'do' ] == "save" && isset( $_POST[ 'birthdays_edit' ] ) ) {
                        //update the record
                        if ( !isset( $_POST[ 'birthday_name' ] ) || empty( $_POST[ 'birthday_name' ] ) || !isset( $_POST[ 'birthday_date' ] ) || empty( $_POST[ 'birthday_date' ] ) ) {
                            echo '<div id="message" class="error"><p>'. __( 'Please fill all the boxes!', 'birthdays-widget' ) .'</p></div>';
                        } else {
                            if ( !isset( $_POST[ 'birthday_image' ] ) )
                                $_POST[ 'birthday_image' ] = '';
                            $update_query = "UPDATE $table_name SET name = '%s', date = '%s', email = '%s', image = '%s' WHERE id = '%d' LIMIT 1;";
                            $query = $wpdb->prepare( $update_query, $_POST[ 'birthday_name' ], date( 'Y-m-d' , strtotime( $_POST[ 'birthday_date' ] ) ), $_POST[ 'birthday_email' ], $_POST[ 'birthday_image' ], $_GET[ 'id' ] );
                            if ( $wpdb->query( $query ) == 1 ) {
                                echo '<div id="message" class="updated"><p>'. __( 'The record was updated!', 'birthdays-widget' ) .'</p></div>';
                            }
                        }
                    } else {
                        //get record to edit
                        $select_query = "SELECT * FROM $table_name WHERE id = '%d' LIMIT 1;";
                        $result = $wpdb->get_row( $wpdb->prepare( $select_query, $_GET[ 'id' ] ) );
                        $birthday_edit = true;
                    }
                }
            }
            wp_enqueue_media();
            $query = "SELECT * FROM $table_name;";
            $results = $wpdb->get_results( $query );
            ?>
            <div id="birthdays_list">
                <table class="widefat dataTable display" id="birthday_table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php _e( 'Name', 'birthdays-widget' ); ?></th>
                            <th><?php _e( 'Date', 'birthdays-widget' ); ?></th>
                            <th><?php _e( 'Email', 'birthdays-widget' ); ?></th>
                            <th><?php _e( 'Image', 'birthdays-widget' ); ?></th>
                            <th><?php _e( 'Action', 'birthdays-widget' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $birthdays_settings = get_option( 'birthdays_settings' );
                        $birthdays_settings = maybe_unserialize( $birthdays_settings );
                        $meta_key = $birthdays_settings[ 'meta_field' ];
                        
                        $prefix = "cs_birth_widg_";
                        $flag_row = true;

                        foreach( $results as $row ) {
                            //Check if this is record represents a WordPress user
                            $wp_usr = strpos( $row->name, $prefix );
                            if ( $wp_usr !== false ) {
                                //If birthdays are disabled for WP Users, or birthday date is drown from WP Profile, skip the record
                                if ( ( $birthdays_settings[ 'profile_page' ] == 0 && $birthdays_settings[ 'date_from_profile' ] == 0 ) || $birthdays_settings[ 'date_from_profile' ] ) {
                                    continue;
                                }
                                $birth_user = get_userdata( substr( $row->name, strlen( $prefix ) ) );
                                $row->name = $birth_user->{$meta_key};
                                $row->email = $birth_user->user_email;
                                //If user's image is drawn from Gravatar
                                if ( $birthdays_settings[ 'wp_user_gravatar' ] ) {
                                    $row->image = self::get_avatar_url( $birth_user->user_email, 256 );
                                    $image_name = $birth_user->{$meta_key};
                                } else {
                                    $row->image = wp_get_attachment_image_src( $row->image, 'full' );
                                    $row->image = $row->image[ 0 ];
                                    $image_name = explode( '/', $row->image );
                                    $image_name = $image_name[ count( $image_name ) - 1 ];
                                }
                            } else {
                                $row->image = wp_get_attachment_image_src( $row->image, 'full' );
                                $row->image = $row->image[ 0 ];
                                $image_name = explode( '/', $row->image );
                                $image_name = $image_name[ count( $image_name ) - 1 ];                            
                            }
                            echo '<tr>
                                    <td>' . $row->id . '</td>
                                    <td class="birthday_name">' . $row->name . '</td>
                                    <td>' . date_i18n( get_option( 'date_format' ), strtotime( $row->date ) ) . '</td>
                                    <td>' . $row->email . '</td>
                                    <td';
                            if ( $row->image ) {
                                echo ' class="list-image birthday_name"><a href="' . $row->image . '" target="_blank">' . $image_name . '</a';
                            }
                            echo '></td>
                                    <td><a href="'. $setting_url .'&action=edit&id='. $row->id .'">'. __( 'Edit', 'birthdays-widget' ) .'</a> 
                                        | <a class="delete_link" href="'. $setting_url .'&action=delete&id='. $row->id .'">'. __( 'Delete', 'birthdays-widget' ) .'</a>
                                    </td>
                                </tr>';
                            if ( $flag_row )
                                $flag_row = false;
                            else
                                $flag_row = true;
                        } ?>
                    </tbody>
                </table>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th></th>
                            <th><?php _e( 'Name', 'birthdays-widget' ); ?></th>
                            <th><?php _e( 'Date', 'birthdays-widget' ); ?></th>
                            <th><?php _e( 'Email', 'birthdays-widget' ); ?></th>
                            <th><?php _e( 'Image', 'birthdays-widget' ); ?></th>
                            <th><?php _e( 'Action', 'birthdays-widget' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><?php
                        $flag = isset( $birthday_edit );
                        if ( $flag ) {
                            echo '<form method="POST" action="'. $setting_url .'&action=edit&id='. $_GET[ 'id' ] .'&do=save">
                                  <td>'. __( 'Editing', 'birthdays-widget') .'</td>
                                  <input type="hidden" name="birthdays_edit" value="1" />';
                        } else {
                            echo '<form method="POST" action="'. $setting_url .'">
                                  <td>'. __( 'New', 'birthdays-widget') .'</td>
                                  <input type="hidden" name="birthdays_add_new" value="1" />';
                        }
                        $wp_usr = false;
                        if ( $flag ) {
                            $tmp = "";
                            $name_hidden = "";
                            $email_hidden = "";
                            //Check if this is record represents a WordPress user
                            $wp_usr = strpos( $result->name, $prefix );
                            if ( $wp_usr !== false ) {
                                $birth_usr_id = substr( $result->name, strlen( $prefix ) );
                                $birth_user = get_userdata( $birth_usr_id );
                                $result->name = $birth_user->{$meta_key};
                                $result->email = $birth_user->user_email;
                                //If user's image is drawn from Gravatar
                                if ( $birthdays_settings[ 'wp_user_gravatar' ] ) {
                                    $result->image = self::get_avatar_url( $birth_user->user_email, 256 );
                                }
                                $name_hidden = '<input type="hidden" name="birthday_name" value="cs_birth_widg_' . $birth_usr_id . '"/>';
                                $email_hidden = '<input type="hidden" name="birthday_email" value="' . $result->email . '" />';
                                $tmp .= 'disabled="disabled"';
                            }
                            $tmp1 = $tmp . 'value="' . $result->name . '"';
                            $tmp2 = $tmp . 'value="' . $result->email . '"';
                            $name_input = "<input type=\"text\" name=\"birthday_name\" $tmp1 id=\"birthday_name\"  />" . $name_hidden;
                            $date_input = "<input type=\"text\" name=\"birthday_date\" id=\"birthday_date\" value=\"" . 
                                            date_i18n( 'd-m-Y', strtotime( $result->date ) ) . "\" />";
                            $email_input = "<input type=\"email\" name=\"birthday_email\" $tmp2 />" . $email_hidden;
                            if ( $birthdays_settings[ 'wp_user_gravatar' ] && $wp_usr !== false ) {
                                $image_input = "<input type=\"text\" name=\"birthday_image\" value=\"Gravatar\" disabled=\"disabled\" />";
                            } else {
                                if ( is_numeric( $result->image ) ) {
                                    $img = wp_get_attachment_image_src( $result->image, 'medium' );
                                    $img = $img[ 0 ];
                                } else {
                                    $img = $birthdays_settings[ 'user_image_url' ];
                                }                                
                                $image_input = '<img id="birthdays_user_image_preview" class="birthday_admin_edit_image" src="' . $img . '" alt="User Image" />'.
                                    '<input name="image" type="button" class="button-primary upload_image_button" value="' . __( '+', 'birthdays-widget' ) . '" data-url-input="birthdays_user_image" >'.
                                    '<input type="hidden" name="birthday_image" id="birthdays_user_image" class="upload_image_button bw-image" value="' . $result->image . '" />';
                            }
                        } else {
                            $name_input = '<input type="text" name="birthday_name" id="birthday_name" value="" />';
                            $date_input = '<input type="text" name="birthday_date" id="birthday_date" value="" />';
                            $email_input = '<input type="email" name="birthday_email" value="" />';
                            $image_input = '<img id="birthdays_user_image_preview" class="birthday_admin_edit_image" src="' . $birthdays_settings[ 'user_image_url' ] . '" alt="User Image" />'.
                                           '<input name="image" type="button" class="button-primary upload_image_button" value="' . __( '+', 'birthdays-widget' ) . '" data-url-input="birthdays_user_image" >'.
                                           '<input type="hidden" id="birthdays_user_image" name="birthday_image" class="upload_image_button bw-image" value="" />';
                        } ?>
                            <td>
                                <?php echo $name_input; ?>
                            </td>
                            <td>
                                <?php echo $date_input; ?>
                            </td>
                            <td>
                                <?php echo $email_input; ?>
                            </td>
                            <td>
                                <?php echo $image_input; ?>
                            </td>
                            <td>
                                <input name="save" type="submit" class="button-primary" value="<?php _e( 'Save', 'birthdays-widget' ); ?>" />
                                <?php wp_nonce_field( 'birthdays_add_form' ); ?>
                            </td>
                        </form></tr>
                    </tbody>
                </table>
            </div>
            </div>
            <div id="delete-msg" class="hidden">
                <?php _e( 'Are you sure you want to delete this record?', 'birthdays-widget' ); ?>
            </div><?php
        }

        public function create_submenu_page_import() {
            if ( ! self::birthdays_user_edit() ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.', 'birthdays-widget' ) );
            } ?>
            <div class="wrap">
                <h2><?php _e( 'Import Birthday List Page', 'birthdays-widget' ); ?></h2>
                <p><?php _e( 'Here you can upload a CSV file with your own data or from a plugin-export.', 'birthdays-widget' ); ?><br />
                <?php _e( 'CSV must have format &lt;name&gt;,&lt;date&gt; where &lt;name&gt; a string and &lt;date&gt; as Y-m-D', 'birthdays-widget' ); ?></p>
                <span class="description"><?php _e( 'Make sure that the imported file is encoded in UTF-8.', 'birthdays-widget' ); ?></span>
                <div class="wrap">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <label for="uploadedfile"><?php _e( 'File', 'birthdays-widget' ); ?></label>
                        <input type="file" name="uploadedfile" id="uploadedfile" accept=".csv" />
                        <input name="upload" type="submit" class="button-primary" value="<?php _e( 'Upload', 'birthdays-widget' ); ?>" />
                        <input type="hidden" name="birthdays_upload_file" value="1" />
                    </form>
            <?php
            if ( isset( $_POST[ 'birthdays_upload_file' ] ) && $_POST[ 'birthdays_upload_file' ] == 1 ) {
                if ( isset( $_FILES[ 'uploadedfile' ] ) ) {
                    $target_path = dirname( __FILE__ ) . "/uploads/";
                    $target_path = $target_path . basename( $_FILES[ 'uploadedfile' ][ 'name' ] );

                    if ( move_uploaded_file( $_FILES[ 'uploadedfile' ][ 'tmp_name' ], $target_path ) ) {
                        ?><p><?php echo __( 'File', 'birthdays-widget' ) . " " . basename( $_FILES[ 'uploadedfile' ][ 'name' ] ) . __( ' has been uploaded. ', 'birthdays-widget' );
                        $row = 0;
                        if ( FALSE !== ( $handle = fopen( $target_path, "r" ) ) ) {
                            global $wpdb;
                            $table_name = $wpdb->prefix . "birthdays";
                            while( FALSE !== ( $data = fgetcsv( $handle, 1000, "," ) ) ) {
                                if ( count( $data ) > 4 ) {
                                    _e( 'Wrong CSV format!<br />', 'birthdays-widget' );
                                    break;
                                }
                                $row++;
                                $new_record[ 'name' ] = $data[ 0 ];
                                $new_record[ 'date' ] = $data[ 1 ];
                                $new_record[ 'email' ] = $data[ 2 ];
                                //TODO maybe convert date format
                                $wpdb->insert( 
                                            $table_name, 
                                            array( 
                                                'name' => $new_record[ 'name'], 
                                                'date' => $new_record[ 'date' ],
                                                'email' => $new_record[ 'email' ]
                                            ), 
                                            array( 
                                                '%s', 
                                                '%s',
                                                '%s'
                                            ));
                            }
                            fclose( $handle );
                            ?><b><?php echo $row; ?></b><?php _e( ' records inserted!', 'birthdays-widget' ); ?></p><?php
                        }
                        @unlink( $target_path );
                    } else {
                        echo '<div id="message" class="error"><p>'. __( 'There was an error uploading the file, please try again!', 'birthdays-widget' ) .'</p></div>';
                    }
                } else {
                    echo '<div id="message" class="error"><p>'. __( 'Select a file first!', 'birthdays-widget' ) .'</p></div>';
                }
            }
            ?></div></div><?php
        }
        
        public function create_submenu_page_export() {
            if ( ! self::birthdays_user_edit() ) {
                wp_die( __( 'You do not have sufficient permissions to access this page.', 'birthdays-widget' ) );
            }
            wp_enqueue_script( 'birthdays-script' );
            ?><div class="wrap">
                <h2><?php _e( 'Export Birthday List Page', 'birthdays-widget' ); ?></h2>
                <p><input type="checkbox" id="wp_users_export" value="yes" checked="checked"><?php _e( 'Export WordPress Users', 'birthdays-widget' ); ?><br />
                    <?php _e( 'In order to download the export file press ', 'birthdays-widget' ); ?>
                    <a href="<?php echo admin_url( 'admin-ajax.php' ) . '?action=get_birthdays_export_file&wp_users=yes"'; ?> target="_blank" 
                    class="button-primary" id="birthdays-export-button" data-orig-link="<?php echo admin_url( 'admin-ajax.php' ) . '?action=get_birthdays_export_file"'; ?>>
                        <?php _e( 'Download', 'birthdays-widget' ); ?>
                    </a>
                </p>
            </div><?php
        }

        static function get_avatar_url( $id_or_email, $size ) {
            $avatar = get_avatar( $id_or_email , $size, 'birthdays_def' );
            preg_match( "/src=[\"'](.*?)[\"']/i", $avatar, $matches );
            $str = substr( $matches[ 0 ], 5 );
            $pos = strpos( $str, "d=birthdays_def" );
            if( $pos != 0 )
                $str = substr( $str, 0, $pos - 1 );
            return $str;
        }
    }
