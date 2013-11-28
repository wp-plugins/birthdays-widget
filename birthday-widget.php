<?php
/*
	Plugin Name: Birthdays Widget
	Plugin URI: http://wordpress.org/extend/plugins/ TODO
	Description: Birthdays Widget
	Author: lion2486
	Version: 0.1
	Author URI: http://codescar.eu 
	Contributors: lion2486
	Tags: widget, birthdays, custom
	Requires at least: 3.0.1
	Tested up to: 3.7.1
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
			$settings_link = '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/admin.php?page=birthdays-widget">'. _( 'Ρυθμίσεις' ) .'</a>';
			array_unshift($links, $settings_link);
		}
	
		return $links;
	}
	
	add_action( 'widgets_init', 'register_birthdays_widget' );
	
	function birthdays_widget_load_languages() {
		load_plugin_textdomain('birthdays-widget', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}
	
	add_action('plugins_loaded', 'birthdays_widget_load_languages');
