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

	require_once dirname( __FILE__ ) . '/class-birthdays-widget-main.php';
	
	$BirthdaysWidgetMain = new Birthdays_Widget_Main;
	
	require_once dirname( __FILE__ ) . '/class-birthdays-widget.php';
	require_once dirname( __FILE__ ) . '/class-birthdays-widget-installer.php';
	require_once dirname( __FILE__ ) . '/class-birthdays-widget-settings.php';	
	require_once dirname( __FILE__ ) . '/birthdays-widget-ajax-callback.php';	

	$BirthdaysWidgetMain->hooks();
	$BirthdaysWidgetMain->actions();
	$BirthdaysWidgetMain->filters();
	
	if( is_admin() )
		$my_settings_page = new Birthdays_Widget_Settings();
	
	// register Birthdays_Widget widget
	
	
	
