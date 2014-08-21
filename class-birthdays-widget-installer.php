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
			
			add_option( 'Birthdays_Widget_Installed', '1' );
			$roles = array('Administrator' => 'Administrator');
			add_option('birthdays_widget_roles', $roles);
			
			return;
		}
		
		static function uninstall() {
			global $wpdb;
			
			$table_name = $wpdb->prefix . "birthdays";
			
			$sql = "DROP TABLE IF EXISTS $table_name;";
			
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			
			dbDelta( $sql );
			
			delete_option( 'Birthdays_Widget_Installed' );
		}
		
		static function activate() {
			if( ! current_user_can ( 'activate_plugins' ) )
				return "You cannot activate it";
			if( ! get_option ( 'Birthdays_Widget_Installed' ) )
				return Birthdays_Widget_Installer::install();
			
			return;
		}
	}
