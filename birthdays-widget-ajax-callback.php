<?php 

add_action( 'wp_ajax_get_birthdays', 'birthdays_widget_callback' );
add_action( 'wp_ajax_nopriv_get_birthdays', 'birthdays_widget_callback' );

//admnin ajax
add_action( 'wp_ajax_get_birthdays_export_file', 'get_birthdays_export_file_callback' );

function birthdays_widget_callback() {
	global $wpdb; // this is how you get access to the database

	@header( "Cache-Control: no-cache, must-revalidate" ); // HTTP/1.1
	@header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // Date in the past
	@header( "Content-Type: text/html; charset=utf-8" );
	date_default_timezone_set( 'Europe/Athens' );

	$table_name = $wpdb->prefix . "birthdays";
	
	$query = "SELECT * FROM $table_name WHERE date LIKE '%%%s' ;";
	
	$results = $wpdb->get_results( $wpdb->prepare( $query, date( '-m-d' ) ) );

	echo $wpdb->num_rows.";";
	$flag = true;
	foreach($results as $row){
		if ($flag)
			$flag = false;
		else
			echo ", ";
		echo $row->name;
	}
	$wpdb->__destruct();
	//mysql_close();


	die(); // this is required to return a proper result
}

function get_birthdays_export_file_callback(){
	global $wpdb;
	
	if( !is_admin() )
		wp_die( 'Access denied' );
	
	date_default_timezone_set( 'Europe/Athens' );
	
	@header( "Cache-Control: no-cache, must-revalidate" ); // HTTP/1.1
	@header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // Date in the past
	@header( "Content-Type: application/octet-stream" );
	@header( "Content-Disposition: attachment; filename=\"export_birthdays_". date( 'd-m-Y' ) .".csv\"" );
	
	$table_name = $wpdb->prefix . "birthdays";
	
	$results = $wpdb->get_results( "SELECT * FROM $table_name;", ARRAY_A );
	
	$output = fopen("php://output", "w");
	
	foreach($results as $row){
		fputcsv($output, $row);
	}
	
	fclose($output);
	
	$wpdb->__destruct();
	
	die();
}