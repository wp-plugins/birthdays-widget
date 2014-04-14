<?php 

add_action( 'wp_ajax_get_birthdays', 'birthdays_widget_callback' );
add_action( 'wp_ajax_nopriv_get_birthdays', 'birthdays_widget_callback' );

//admnin ajax
add_action( 'wp_ajax_get_birthdays_export_file', 'get_birthdays_export_file_callback' );

function birthdays_widget_check_for_birthdays(){
	global $wpdb;
	
	$table_name = $wpdb->prefix . "birthdays";
	
	$query = "SELECT * FROM $table_name WHERE date LIKE '%%%s' ;";
	
	$results = $wpdb->get_results( $wpdb->prepare( $query, date_i18n( '-m-d' ) ) );
	
	return $results;
}

function birthdays_widget_callback() {
	@header( "Cache-Control: no-cache, must-revalidate" ); // HTTP/1.1
	@header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // Date in the past
	@header( "Content-Type: text/html; charset=utf-8" );
	//date_default_timezone_set( 'Europe/Athens' );

	$birthdays = birthdays_widget_check_for_birthdays();

	echo count( $birthdays ) .";";
	$flag = true;
	foreach($birthdays as $row){
		if ($flag)
			$flag = false;
		else
			echo ", ";
		echo $row->name;
	}
	
	die(); 
}

function get_birthdays_export_file_callback(){
	global $wpdb;
	
	if( !is_admin() )
		wp_die( 'Access denied' );
	
	//date_default_timezone_set( 'Europe/Athens' );
	
	@header( "Cache-Control: no-cache, must-revalidate" ); // HTTP/1.1
	@header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // Date in the past
	@header( "Content-Type: application/octet-stream" );
	@header( "Content-Disposition: attachment; filename=\"export_birthdays_". date_i18n( 'd-m-Y' ) .".csv\"" );
	
	$table_name = $wpdb->prefix . "birthdays";
	
	$results = $wpdb->get_results( "SELECT name, date FROM $table_name;", ARRAY_A );
	
	$output = fopen("php://output", "w");
	
	foreach($results as $row){
		fputcsv($output, $row);
	}
	
	fclose($output);
	
	$wpdb->__destruct();
	
	die();
}