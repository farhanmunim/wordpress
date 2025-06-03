<?php 
if (!defined('ABSPATH')) exit;

/*Conditional enqueue function*/
function conditional_enqueue( $field_name, $type, $handle, $path ) {
    $field_value = get_post_meta( get_the_ID(), $field_name, true );
    
    if ( $field_value == 1 || $field_value === true ) {
        if ( $type === 'script' ) {
            wp_enqueue_script( $handle, get_stylesheet_directory_uri() . $path, array(), filemtime( get_stylesheet_directory() . $path ), true );
        } elseif ( $type === 'style' ) {
            wp_enqueue_style( $handle, get_stylesheet_directory_uri() . $path, array(), filemtime( get_stylesheet_directory() . $path ) );
        }
    }
}

/*Enqueue styles and scripts*/
add_action( 'wp_enqueue_scripts', function() {
    if ( ! bricks_is_builder_main() ) {
        // Main script
        wp_enqueue_script( 'custom-scripts', get_stylesheet_directory_uri() . '/assets/js/app.js', array(), filemtime( get_stylesheet_directory() . '/assets/js/app.js' ), true );
        
		// Script modules
		wp_enqueue_script( 'cookies-scripts', get_stylesheet_directory_uri() . '/assets/js/cookies.js', array(), filemtime( get_stylesheet_directory() . '/assets/js/cookies.js' ), true );

        // Conditional script modules
        conditional_enqueue( 'advance_codebox', 'script', 'prism-scripts', '/includes/vendor/prism.js' );
        conditional_enqueue( 'force_darkmode', 'script', 'darkmode-scripts', '/assets/js/darkmode.js' );
        conditional_enqueue( 'table_of_contents', 'script', 'toc-scripts', '/assets/js/toc.js' );
        conditional_enqueue( 'read_progress_bar', 'script', 'progress-bar-scripts', '/assets/js/progress-bar.js' );
        conditional_enqueue( 'social_share', 'script', 'social-share-scripts', '/assets/js/social-share.js' );
        
        // Main stylesheet
        wp_enqueue_style( 'custom-styles', get_stylesheet_uri(), ['bricks-frontend'], filemtime( get_stylesheet_directory() . '/style.css' ) );
    }
}, 999 );

/*WP login styles*/
add_action( 'login_enqueue_scripts', function() {
    wp_enqueue_style( 'custom-login-styles', get_stylesheet_directory_uri() . '/assets/css/login.css', array(), filemtime( get_stylesheet_directory() . '/assets/css/login.css' ) );
} );

/*Include PHP modules*/
require_once get_stylesheet_directory() . '/includes/turnstile.php';
