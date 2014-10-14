<?php
    class Birthdays_Widget_Installer{
        
        static function install() {
            global $wpdb;
            
            //create the new table
            $table_name = $wpdb->prefix . "birthdays"; 
            
            $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
                      id int(11) NOT NULL AUTO_INCREMENT,
                      name text  NOT NULL,
                      date date NOT NULL,
                      PRIMARY KEY  id(id)
                    ) DEFAULT CHARSET=utf8;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            
            dbDelta( $sql );
            //add some default options
            $birthdays_settings = array(
                'widget_installed' => '1',
                'register_form' => '0',
                'profile_page' => '0',
                'meta_field' => 'display_name',
                'date_from_profile' => '0',
                'date_meta_field' => '',
                'image_url' => plugins_url( '/images/birthday_cake.png' , __FILE__ ),
                'image_width' => '55%',
                'image_enabled' => '1',
                'wish' => __( 'Happy Birthday', 'birthdays-widget' ),
                'roles' => array( 'Administrator' => 'Administrator' )
                );
            $birthdays_settings = maybe_serialize( $birthdays_settings );
            add_option( 'birthdays_settings', $birthdays_settings );
            return;
        }

        static function unistall() {
            //delete plugin's options
            delete_option( 'birthdays_settings' );

            //delete all of our user meta
            $users = get_users( array( 'fields' => 'id' ) );
            foreach ( $users as $id ) {
                delete_user_meta( $id, 'birthday_id' );
            }

            //drop a custom db table
            global $wpdb;
            $table_name = $wpdb->prefix . "birthdays";
            $sql = "DROP TABLE IF EXISTS `$table_name`;" ;
            $e = $wpdb->query( $sql );
            die( var_dump( $e ) );

        }

        static function activate() {
            if( ! current_user_can ( 'activate_plugins' ) )
                return "You cannot activate it";

            return Birthdays_Widget_Installer::install();
        }
        
        static function deactivate() {
            if ( ! get_option( 'birthdays_settings' ) ) {
                $new = array();
                $new[ 'meta_field' ] = get_option( 'birthdays_meta_field' );
                if( $new[ 'meta_field' ] == false )
                    $new[ 'meta_field' ] = 'display_name';
                $new[ 'date_from_profile' ] = get_option( 'birthdays_date_from_profile' );
                if( $new[ 'date_from_profile' ] == false )
                    $new[ 'date_from_profile' ] = '0';
                $new[ 'date_meta_field' ] = get_option( 'birthdays_date_meta_field' );
                if( $new[ 'date_meta_field' ] == false )
                    $new[ 'date_meta_field' ] = '';
                $new[ 'wish' ] = get_option( 'birthdays_wish' );
                if( $new[ 'wish' ] == false )
                    $new[ 'wish' ] = __( 'Happy Birthday', 'birthdays-widget' );

                $birthdays_settings = array(
                    'widget_installed' => get_option( 'Birthdays_Widget_Installed' ),
                    'register_form' => get_option( 'birthdays_register_form' ),
                    'profile_page' => get_option( 'birthdays_profile_page' ),
                    'meta_field' => $new[ 'meta_field' ],
                    'date_from_profile' => $new[ 'date_from_profile' ],
                    'date_meta_field' => $new[ 'date_meta_field' ],
                    'image_url' => get_option( 'birthdays_widget_image' ),
                    'image_width' => get_option( 'birthdays_widget_image_width' ),
                    'image_enabled' => get_option( 'birthdays_widget_img' ),
                    'wish' => $new[ 'wish' ],
                    'roles' => get_option( 'birthdays_widget_roles' )
                    );
                $birthdays_settings = maybe_serialize( $birthdays_settings );
                add_option( 'birthdays_settings', $birthdays_settings );
                
                delete_option( 'Birthdays_Widget_Installed' );
                delete_option( 'birthdays_meta_field' );
                delete_option( 'birthdays_date_from_profile' );
                delete_option( 'birthdays_date_meta_field' );
                delete_option( 'birthdays_register_form' );
                delete_option( 'birthdays_profile_page' );
                delete_option( 'birthdays_widget_image' );
                delete_option( 'birthdays_widget_image_width' );
                delete_option( 'birthdays_widget_img' );
                delete_option( 'birthdays_wish' );
                delete_option( 'birthdays_widget_roles' );
            }
        }
    }
