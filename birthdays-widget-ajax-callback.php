<?php 

add_action( 'wp_ajax_get_birthdays', 'birthdays_widget_callback' );
add_action( 'wp_ajax_nopriv_get_birthdays', 'birthdays_widget_callback' );

//admnin ajax
add_action( 'wp_ajax_get_birthdays_export_file', 'get_birthdays_export_file_callback' );

function birthdays_widget_check_for_birthdays( $all = false ) {
    global $wpdb;

    $table_name = $wpdb->prefix . "birthdays";
    if ( $all ) {
        $query = "SELECT * FROM $table_name ORDER BY DATE_FORMAT(date, '%m-%d');";
        $results = $wpdb->get_results( $query );
    } else {
        $query = "SELECT * FROM $table_name WHERE date LIKE '%%%s' ;";
        $results = $wpdb->get_results( $wpdb->prepare( $query, date_i18n( '-m-d' ) ) );
    }

    $birthdays_settings = get_option( 'birthdays_settings' );
    $birthdays_settings = maybe_unserialize( $birthdays_settings );

    //If birthdays for WordPress Users are drawn from a meta key of their profile
    if ( $birthdays_settings[ 'date_from_profile' ] ) {
        $birthday_date_meta_field = $birthdays_settings[ 'date_meta_field' ];
        $meta_key = $birthdays_settings[ 'meta_field' ];
        $users = get_users();
        foreach ( $users as $user ) {
            //If this meta key exists for this user, and it's his/her birthday
            if ( isset( $user->{$birthday_date_meta_field} ) ) {
                $date = date( "-m-d", strtotime( $user->{$birthday_date_meta_field} ) );
                if ( ( !$all && $date == date_i18n( '-m-d' ) ) || $all ) {
                    $tmp_user = new stdClass();
                    $tmp_user->name = $user->{$meta_key};
                    $tmp_user->email = $user->user_email;
                    //If user's image is drawn from Gravatar
                    if ( $birthdays_settings[ 'wp_user_gravatar' ] ) {
                        $tmp_user->image = Birthdays_Widget_Settings::get_avatar_url( $tmp_user->email, 256 );
                    }
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

function get_birthdays_export_file_callback() {
    global $wpdb;

    if( !is_admin() )
        wp_die( 'Access denied' );

    //date_default_timezone_set( 'Europe/Athens' );

    @header( "Cache-Control: no-cache, must-revalidate" ); // HTTP/1.1
    @header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // Date in the past
    @header( "Content-Type: application/octet-stream" );
    @header( "Content-Disposition: attachment; filename=\"export_birthdays_". date_i18n( get_option( 'date_format' ) ) .".csv\"" );

    $table_name = $wpdb->prefix . "birthdays";
    $results = $wpdb->get_results( "SELECT name, date, email FROM $table_name;", ARRAY_A );
    $output = fopen( "php://output", "w" );

    $birthdays_settings = get_option( 'birthdays_settings' );
    $birthdays_settings = maybe_unserialize( $birthdays_settings );
    $meta_key = $birthdays_settings[ 'meta_field' ];
    $prefix = "cs_birth_widg_";
    foreach( $results as $row ) {
        $wp_usr = strpos( $row[ 'name' ], $prefix );
        if ( $wp_usr !== false ) {
            if ( isset( $_GET[ 'wp_users' ] ) && $_GET[ 'wp_users' ] == 'yes' ) {
                $birth_user = get_userdata( substr( $row[ 'name' ], strlen( $prefix ) ) );
                $row[ 'name' ] = $birth_user->$meta_key;
                $row[ 'email' ] = $birth_user->user_email;
            } else {
                continue;
            }
        }
        fputcsv( $output, $row );
    }
    
    fclose( $output );
    
    $wpdb->__destruct();
    
    die();
}
