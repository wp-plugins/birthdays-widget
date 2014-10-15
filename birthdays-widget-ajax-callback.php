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

    $birthdays_settings = get_option( 'birthdays_settings' );
    $birthdays_settings = maybe_unserialize( $birthdays_settings );
    
    if ( $birthdays_settings[ 'date_from_profile' ] ) {
        $birthday_date_meta_field = $birthdays_settings[ 'date_meta_field' ];
        $birthdays_meta_field = $birthdays_settings[ 'meta_field' ];
        $users = get_users();
        foreach ( $users as $user ) {
            if ( isset( $user->{$birthday_date_meta_field} ) ) {
                $date = date( "-m-d", strtotime( $user->{$birthday_date_meta_field} ) );
                if ( $date == date_i18n( '-m-d' ) ) {
                    $tmp_user = new stdClass();
                    $tmp_user->name = $user->{$birthdays_meta_field};
                    array_push( $results, $tmp_user );
                }
            }
        }
    }
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
    
    $birthdays_settings = get_option( 'birthdays_settings' );
    $birthdays_settings = maybe_unserialize( $birthdays_settings );
    $meta_key = $birthdays_settings[ 'meta_field' ];
    $prefix = "cs_birth_widg_";
    foreach($results as $row){
        $wp_usr = strpos( $row[ 'name' ], $prefix );
        if ( $wp_usr !== false ) {
            $birth_user = get_userdata( substr( $row[ 'name' ], strlen( $prefix ) ) );
            $row[ 'name' ] = $birth_user->$meta_key;
        }
        fputcsv($output, $row);
    }
    
    fclose($output);
    
    $wpdb->__destruct();
    
    die();
}
