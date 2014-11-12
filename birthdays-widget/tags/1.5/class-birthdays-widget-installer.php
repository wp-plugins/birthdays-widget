<?php
    class Birthdays_Widget_Installer{
        
        static function install() {
            global $wpdb;
            
            //create the new table
            $table_name = $wpdb->prefix . "birthdays"; 
            
            $sql = "CREATE TABLE $table_name (
                      id int(11) NOT NULL AUTO_INCREMENT,
                      name text  NOT NULL,
                      date date NOT NULL,
                      PRIMARY KEY  id(id)
                    ) DEFAULT CHARSET=utf8;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            
            dbDelta( $sql );
            
            //add some default options
            add_option( 'Birthdays_Widget_Installed', '1' );
            add_option( 'birthdays_register_form', '0' );
			add_option( 'birthdays_profile_page', '0' );
            add_option( 'birthdays_meta_field', 'display_name' );
            add_option( 'birthdays_widget_image', plugins_url( '/images/birthday_cake.png' , __FILE__ ) );
            $roles = array( 'Administrator' => 'Administrator' );
            add_option( 'birthdays_widget_roles', $roles );
            return;
        }
        
        static function uninstall() {
            global $wpdb;
            
            $table_name = $wpdb->prefix . "birthdays";
            $sql = "DROP TABLE IF EXISTS $table_name;";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
            
            //delete plugin's options
            delete_option( 'Birthdays_Widget_Installed' );
            delete_option( 'birthdays_meta_field' );
            delete_option( 'birthdays_register_form' );
			delete_option( 'birthdays_profile_page' );
            delete_option( 'birthdays_widget_image' );
            delete_option( 'birthdays_widget_roles' );
            
            //delete all of our user meta
            $users = get_users( array( 'fields' => 'id' ) );
            foreach ( $users as $id ) {
                delete_user_meta( $id, 'birthday_id');
            }
        }
        
        static function activate() {
            if( ! current_user_can ( 'activate_plugins' ) )
                return "You cannot activate it";
            if( ! get_option ( 'Birthdays_Widget_Installed' ) )
                return Birthdays_Widget_Installer::install();
            
            return;
        }
    }
