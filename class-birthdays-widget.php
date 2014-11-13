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
        
        if ( $instance[ 'template' ] == 2 ) {
            $birthdays = birthdays_widget_check_for_birthdays( true );
        } else {
            $birthdays = birthdays_widget_check_for_birthdays();
        }
        if ( count( $birthdays ) >= 1 ) {
            $title = apply_filters( 'widget_title', $instance[ 'title' ] );
            echo $args[ 'before_widget' ];
            if ( ! empty( $title ) )
                echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];

            echo self::birthdays_code( $instance, $birthdays );

            /* TODO make again ajax support?
                wp_enqueue_script('birthdays-widget-script', plugins_url('script.js', __FILE__ ), array('jquery'));
                wp_localize_script('birthdays-widget-script', 'ratingsL10n', array( 'admin_ajax_url' => admin_url('admin-ajax.php')));
            */
            echo $args[ 'after_widget' ];
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
        $birth_widg = get_option( 'birthdays_widget_settings' );
        $birth_widg = maybe_unserialize( $birth_widg );
        $instance = wp_parse_args( (array) $instance, $birth_widg );
        if ( !isset( $instance[ 'title' ] ) )
			$instance[ 'title' ] = "Birthdays Widget";
		if ( !isset( $instance[ 'template' ] ) )
			$instance[ 'template' ] = 0;
        ?>
        <p><fieldset class="basic-grey">
            <legend><?php _e( 'Settings', 'birthdays-widget' ); ?>:</legend>
            <label>
                <span><?php _e( 'Title', 'birthdays-widget' ); ?></span>
                <input  id="<?php echo $this->get_field_id( 'title' ); ?>" 
                        name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" 
                        value="<?php empty( $instance[ 'title' ] ) ? '' : esc_attr_e( $instance[ 'title' ] ) ; ?>" />
            </label>
            <label>
                <span><?php _e( 'Template', 'birthdays-widget' ); ?></span>
                <select id="<?php echo $this->get_field_id( 'template' ); ?>" 
                        name="<?php echo $this->get_field_name( 'template' ); ?>">
                    <option value="0" <?php if ( $instance[ 'template' ] == 0 ) echo "selected='selected'"; ?>>Default</option>
                    <option value="1" <?php if ( $instance[ 'template' ] == 1 ) echo "selected='selected'"; ?>>List</option>
                    <option value="2" <?php if ( $instance[ 'template' ] == 2 ) echo "selected='selected'"; ?>>Calendar</option>
                </select>
            </label>
        </fieldset></p>
		<?php
	}

    public static function birthdays_code( $instance, $birthdays = NULL ) {
        wp_enqueue_style( 'birthdays-css' );
        $html = "";
        $birthdays_settings = get_option( 'birthdays_settings' );
        $birthdays_settings = maybe_unserialize( $birthdays_settings );
        if ( isset( $instance[ 'img_width' ] ) ) {
            $birthdays_settings[ 'image_width' ] = $instance[ 'img_width' ];
        }
        if ( !isset( $instance[ 'class' ] ) ) {
            $instance[ 'class' ] = '';
        }
        if ( !isset( $instance[ 'template' ] ) ) {
            $instance[ 'template' ] = 0;
        }
        $html .= "<div class=\"birthdays-widget {$instance[ 'class' ]}\">";
            if ( $birthdays_settings[ 'image_enabled' ] ) {
                $tmp_size = $birthdays_settings[ 'image_width' ];
                if ( is_numeric( $birthdays_settings[ 'image_url' ] ) ) {
                    $default_image_src = wp_get_attachment_image_src( $birthdays_settings[ 'image_url' ], 'medium' );
                    if ( $default_image_src == false ) {
                        $default_image_src = $default_image_src[ 0 ];
                    } else {
                        $default_image_src = $birthdays_settings[ 'image_url' ];
                    }
                } else {
                    $default_image_src = $birthdays_settings[ 'image_url' ];
                }
                $html .= "<img style=\"width: {$birthdays_settings[ 'image_width' ]}\" 
                    src=\"$default_image_src\" alt=\"birthday_cake\" class=\"aligncenter\" />";
            }
            $html .= "<div class=\"birthday-wish\">{$birthdays_settings[ 'wish' ]}</div>";
            /*
             * For each user that has birthday today, if his name is
             * in the cs_birth_widg_# format (which means he is a WP User),
             * show his name if and only if the option to 
             * save Users' birthdays in our table is enabled.
             */
            $meta_key = $birthdays_settings[ 'meta_field' ];
            $prefix = "cs_birth_widg_";
            $filtered = array();
            foreach ( $birthdays as $row ) {
                //Check if this is record represents a WordPress user
                $wp_usr = strpos( $row->name, $prefix );
                if ( $instance[ 'template' ] == 2 ) {
                    $row->image = wp_get_attachment_image_src( $row->image, array( 150, 150 ) )[ 0 ];
                } else {
                    $row->image = wp_get_attachment_image_src( $row->image, 'medium' )[ 0 ];
                }
                if ( $wp_usr !== false ) {
                    //If birthdays are disabled for WP Users, or birthday date is drown from WP Profile, skip the record
                    if ( ( $birthdays_settings[ 'profile_page' ] == 0 && $birthdays_settings[ 'date_from_profile' ] == 0 ) || $birthdays_settings[ 'date_from_profile' ] ) {
                        continue;
                    }
                    //Get the ID from the record, which is of the format $prefixID and get the user's data
                    $birth_user = get_userdata( substr( $row->name, strlen( $prefix ) ) );
                    //If user's image is drawn from Gravatar
                    if ( $birthdays_settings[ 'wp_user_gravatar' ] ) {
                        if ( $instance[ 'template' ] == 2 ) {
                            $row->image = Birthdays_Widget_Settings::get_avatar_url( $birth_user->user_email, 96 );
                        } else {
                            $row->image = Birthdays_Widget_Settings::get_avatar_url( $birth_user->user_email, 256 );
                        }
                    }
                    //If birthdays are enabled for WP Users, draw user's name from the corresponding meta key
                    if ( $birthdays_settings[ 'profile_page' ] ) {
                        $row->name = $birth_user->{$meta_key};
                    }
                }
                //If user has no image, set the default
                if ( !isset( $row->image ) || empty( $row->image ) ) {
                    if ( is_numeric( $birthdays_settings[ 'image_url' ] ) && $instance[ 'template' ] == 2 ) {
                        $row->image = wp_get_attachment_image_src( $birthdays_settings[ 'image_url' ] )[ 0 ];
                    } else {
                        $row->image = $default_image_src;
                    }
                }
                array_push( $filtered, $row );
            }
            switch ( $instance[ 'template' ] ) {
                case 0:
                    $flag = false;
                    foreach ( $filtered as $row ) {
                        $html .= '<div class="birthday_element">';
                        if ( $flag && $birthdays_settings[ 'comma' ] ) {
                            $html .= ', ';
                        } else {
                            $flag = true;
                        }
                        $html .= $row->name;
                        $html .= '</div>';
                    }
                    break;
                case 1:
                    $html .= '<ul class="birthday_list">';
                        foreach ( $filtered as $row ) {
                            $html .= "<li><img style=\"width:{$birthdays_settings[ 'list_image_width' ]}\" src=\"{$row->image}\" 
                                    class=\"birthday_list_image\" />{$row->name}</li>";
                        }
                    $html .= '</ul>';
                    break;
                case 2:
                    if ( defined( 'CALENDAR' ) ) {
                        $html .= "<span class=\"description\">" . __( 'Only one calendar template is available per page. Please check your widget and shortcode options.', 'birthdays-widget' ) . "</span>";
                        break;
                    }
                    define( 'CALENDAR' , true );
                    $day_organized = array();
                    foreach ( $filtered as $user_birt ) {
                        $user_birt->tmp = substr( $user_birt->date, 5 );
                        if ( !isset ( $day_organized[ $user_birt->tmp ] ) ) {
                            $day_organized[ $user_birt->tmp ] = array();
                        }
                        $day_organized[ $user_birt->tmp ][] = $user_birt;
                    }
                    wp_enqueue_style( 'birthdays-bootstrap-css' );
                    wp_enqueue_style( 'birthdays-calendar-css' );
                    wp_enqueue_script( 'birthdays-bootstrap-js' );
                    wp_enqueue_script( 'birthdays-calendar-js' );
                    $html .= '<script>
                        jQuery( document ).ready( function() {
                            var monthNames = [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];
                            var dayNames = [ "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun" ];
                            var events = [ ';
                                $flag = false;
                                foreach ( $day_organized as $day ) {
                                    $html .= '{ date: "' . date( 'j/n', strtotime( $day[ 0 ]->date ) ) . '/' . date( 'Y' ) . '",';
                                    $html .= 'title: \'' . $birthdays_settings[ 'wish' ] . '\',';
                                    if ( date( 'm-d', strtotime( $day[ 0 ]->date ) ) == date( 'm-d' ) ) {
                                        $color = "orange";
                                    } else if ( $flag ) {
                                        $color = "#2277cc";
                                        $flag = false;
                                    } else {
                                        $color = "#cc7722";
                                        $flag = true;
                                    }
                                    $html .= ' color: "' . $color . '",';
                                    $html .= ' content: \''; 
                                    $comma = false;
                                    foreach ( $day as $user ) {
                                        /* if ( !$comma ) {
                                            echo '<img src="' . $user->image . '" />';
                                            $comma = true;
                                        } else {
                                        } */
                                        $html .= '<img src="' . $user->image . '" width="150" /><div class="birthdays_center">' . $user->name . '</div>';
                                    }
                                    $html .= '\' }, ';
                                }
                            $html .= ' ];';
                            $html .= "
                                jQuery( '#birthday_calendar' ).bic_calendar( {
                                    events: events,
                                    dayNames: dayNames,
                                    monthNames: monthNames,
                                    showDays: true,
                                    displayMonthController: true,
                                    displayYearController: false
                                } );
                            ";

                            $html .= "jQuery( '#bic_calendar_'+'";
                            $html .= date( 'd_m_Y' );
                            $html .= "' ).addClass( 'selection' ); ";
                        $html .= '} );';
                    $html .= '</script>';
                    $html .= '<div id="birthday_calendar"></div>';
                    break;
            }
        $html .= '</div>';
        return $html;
    }

    /**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
        $instance[ 'template' ] = ( $new_instance[ 'template' ] ) ? strip_tags( $new_instance[ 'template' ] ) : 0;
        return $instance;
    }

} // class Birthdays_Widget
