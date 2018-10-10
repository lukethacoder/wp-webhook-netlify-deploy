<?php
/*
Plugin Name: Deploy Webhook Menu Button
Plugin URI:
Description: Lets you send a webhook via the WordPress admin menu 
Version: 0.1.0
Author: Luke Secomb
Author URI: https://lukesecomb.digital
*/

add_action( 'admin_menu', 'register_my_menu_item' );

function register_my_menu_item() {
    # the add_action ensures this is only run when admin pages are displayed
    add_menu_page( 'Build Website', 'Build Website', 'manage_options', 'query-string-parameter', 'my_menu_item');
}

function my_menu_item() {
    # your new admin page contents (or behaviour go here)
    echo '<h1>Deploying Website</h1>';

    $url = 'https://api.netlify.com/build_hooks/5b8f4d8073f2cf07b2c54431';
    
    $response = wp_remote_post( $url, '' );

    if ( is_wp_error( $response ) ) {
        $error_message = $response->get_error_message();
        print_r('Error', $error_message);
        print_r('<br><br>');
     } else {
        print_r('Site Request Successfully Sent');
        print_r('<br><br>');
        print_r('Please allow upto an hour for the website to build');
     }
}

?>