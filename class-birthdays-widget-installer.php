<?php
    class Birthdays_Widget_Installer{
        
        static public function install() {
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
                'comma' => '1',
                'user_data' => '2',
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

        public static function deactivate_multisite() {
        	global $wpdb;
        	
        	if ( function_exists( 'is_multisite' ) && is_multisite( ) ) {
        		// check if it is a network activation - if so, run the activation function for each blog id
        		if ( $networkwide ) {
        			$old_blog = $wpdb->blogid;
        			// Get all blog ids
        			$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
        			foreach ( $blogids as $blog_id ) {
        				switch_to_blog( $blog_id );
        				self::deactivate();
        			}
        			switch_to_blog( $old_blog );
        			return;
        		}
        	}
        	
        	self::deactivate();
        }
        
        static public function unistall() {
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
            $wpdb->query( $sql );
        }

        static function activate() {
        	global $wpdb;
        	
            if ( ! current_user_can ( 'activate_plugins' ) )
                return "You cannot activate it";
            
            if ( function_exists( 'is_multisite' ) && is_multisite( ) ) {
            	// check if it is a network activation - if so, run the activation function for each blog id
            	if ( $networkwide ) {
            		$old_blog = $wpdb->blogid;
            		// Get all blog ids
            		$blogids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            		foreach ( $blogids as $blog_id ) {
            			switch_to_blog( $blog_id );
            			self::install();
            		}
            		switch_to_blog( $old_blog );
            		return;
            	}
            }

            return self::install();
        }
        
        static public function deactivate() {
            if ( ! get_option( 'birthdays_settings' ) ) {
                $new = array();
                $new[ 'meta_field' ] = get_option( 'birthdays_meta_field' );
                if ( $new[ 'meta_field' ] == false )
                    $new[ 'meta_field' ] = 'display_name';
                $new[ 'date_from_profile' ] = get_option( 'birthdays_date_from_profile' );
                if ( $new[ 'date_from_profile' ] == false )
                    $new[ 'date_from_profile' ] = '2';
                $new[ 'date_meta_field' ] = get_option( 'birthdays_date_meta_field' );
                if ( $new[ 'date_meta_field' ] == false )
                    $new[ 'date_meta_field' ] = '';
                $new[ 'wish' ] = get_option( 'birthdays_wish' );
                if ( $new[ 'wish' ] == false )
                    $new[ 'wish' ] = __( 'Happy Birthday', 'birthdays-widget' );

                $birthdays_settings = array(
                    'widget_installed' => get_option( 'Birthdays_Widget_Installed' ),
                    'register_form' => get_option( 'birthdays_register_form' ),
                    'profile_page' => get_option( 'birthdays_profile_page' ),
                    'meta_field' => $new[ 'meta_field' ],
                    'comma' => '1',
                    'user_data' => $new[ 'date_from_profile' ],
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
            $birthdays_settings = get_option( 'birthdays_settings' );
            $birthdays_settings = maybe_unserialize( $birthdays_settings );
            if ( ! isset( $birthdays_settings[ 'comma' ] ) ) {
                $birthdays_settings[ 'comma' ] = 1;
                update_option( 'birthdays_settings', $birthdays_settings );
            }
        }
        
        
        
        public static function new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        	global $wpdb;
        
        	if ( is_plugin_active_for_network( 'birthdays-widget/birthday-widget.php' ) ) {
        		$old_blog = $wpdb->blogid;
        		switch_to_blog( $blog_id );
        		self::install();
        		switch_to_blog( $old_blog );
        	}
        }
    }
    