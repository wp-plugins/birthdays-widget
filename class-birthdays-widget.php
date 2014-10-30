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
        if ( count( $birthdays ) >= 1 ) {
            $title = apply_filters( 'widget_title', $instance[ 'title' ] );
            echo $args[ 'before_widget' ];
            if ( ! empty( $title ) )
                echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];

            echo self::birthdays_code( $birthdays );

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
        ?>
        <p><fieldset class="basic-grey">
            <legend><?php echo __( 'Settings', 'birthdays-widget' ); ?>:</legend>
            <label>
                <span><?php echo __( 'Title', 'birthdays-widget' ); ?></span>
                <input  id="<?php echo $this->get_field_id( 'title' ); ?>" 
                        name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" 
                        value="<?php empty( $instance[ 'title' ] ) ? '' : esc_attr_e( $instance[ 'title' ] ) ; ?>" />
            </label>
            <!-- <label>
            //TODO add some "templates"
                <span><?php echo __( 'Template', 'birthdays-widget' ); ?></span>
                <select id="<?php echo $this->get_field_id( 'template' ); ?>" 
                        name="<?php echo $this->get_field_name( 'template' ); ?>">
                    <?php 
                        /* foreach( $instance[ 'template' ] as $template ){
                            $line = "<option value=\"{$template[ 'url' ]}\"";
                            $line = ( $station[ 'url' ] == $instance[ 'default' ] ) ? $line . " selected=\"selected\">" : $line . ">";
                            $line = $line."{$station[ 'name' ]}</option>\n\t\t\t\t";
                            echo $line;
                        } */
                    ?>
                </select>
            </label> -->
        </fieldset></p>
		<?php
	}

    public static function birthdays_code( $birthdays = NULL, $class = NULL, $img_width = 0 ) {
        ob_start();
        wp_enqueue_style( 'birthdays-css' );
        $birthdays_settings = get_option( 'birthdays_settings' );
        $birthdays_settings = maybe_unserialize( $birthdays_settings );
        if ( $img_width != 0 )
            $birthdays_settings[ 'image_width' ] = $img_width;
        ?>
            <div class="birthdays-widget <?php echo ($class) ? $class : ''; ?>">
                <?php $img_flag = $birthdays_settings[ 'image_enabled' ];
                    if ( $img_flag ) { ?>
                        <img style="width: <?php echo $birthdays_settings[ 'image_width' ]; ?>;" 
                            src="<?php echo $birthdays_settings[ 'image_url' ]; ?>" alt="birthday_cake" class="aligncenter" />
                    <?php } 
                ?>
                <div class="birthday-wish">
                    <?php echo $birthdays_settings[ 'wish' ]; ?>
                </div>
                <?php
                /*
                 * For each user that has birthday today, if his name is
                 * in the cs_birth_widg_# format (which means he is a WP User),
                 * show his name if and only if the option to 
                 * save Users' birthdays in our table is enabled.
                 */
                $meta_key = $birthdays_settings[ 'meta_field' ];
                $prefix = "cs_birth_widg_";
                $flag = false;
                foreach ( $birthdays as $row ) {
                    $wp_usr = strpos( $row->name, $prefix );
                    if ( $wp_usr !== false ) {
                        if ( $birthdays_settings[ 'profile_page' ] ) {
                            $birth_user = get_userdata( substr( $row->name, strlen( $prefix ) ) );
                            $row->name = $birth_user->{$meta_key};
                        } else {
                            continue;
                        }
                    }
                    echo '<div class="birthday_element">';
                        if ( $flag ) {
                            echo ', ';
                        } else {
                            $flag = true;
                        }
                        echo $row->name;
                    echo '</div>';
                } ?>
            </div>
        <?php
        return ob_get_clean();
    }

    /**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance[ 'title' ] = strip_tags($new_instance[ 'title' ]);
        $instance[ 'template' ] = ($new_instance[ 'template' ]) ? strip_tags($new_instance[ 'template' ]) : '';
        return $instance;
    }

} // class Birthdays_Widget
