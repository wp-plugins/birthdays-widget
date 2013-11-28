<?php
class Birthdays_Widget_Main{
	
	public function languages(){
		load_plugin_textdomain('birthdays-widget', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}
	
	public function hooks(){
		register_activation_hook( __FILE__ , array( 'Birthdays_Widget_Installer', 'activate' ) );
		register_uninstall_hook( __FILE__ , array( 'Birthdays_Widget_Installer', 'uninstall' ) );
	}
	
	public function actions(){
		add_action( 'widgets_init', array( &$this, 'register_birthdays_widget' ) );
		add_action('plugins_loaded', array( &$this, 'languages' ) );
	}
	
	public function filters(){
		add_filter( 'plugin_action_links', array( &$this, 'birthdays_widget_action_links' ), 10, 2);
	}
	
	public function register_birthdays_widget() {
		register_widget( 'Birthdays_Widget' );
	}
	
	public function birthdays_widget_action_links($links, $file) {
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
	
}