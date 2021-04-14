<?php

function remove_redundant_shortlink() {
    // remove HTML meta tag
    // <link rel='shortlink' href='http://example.com/?p=25' />
    remove_action('wp_head', 'wp_shortlink_wp_head', 10);

    // remove HTTP header
    // Link: <https://example.com/?p=25>; rel=shortlink
    remove_action( 'template_redirect', 'wp_shortlink_header', 11);
}

// REMOVE WP EMOJI
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_action ('wp_head', 'rsd_link');

function crunchify_remove_version() {
  return '';
}


function crunchify_cleanup_query_string( $src ){
  $parts = explode( '?', $src );
  return $parts[0];
}

add_filter('the_generator', 'crunchify_remove_version');

add_filter( 'script_loader_src', 'crunchify_cleanup_query_string', 15, 1 );
add_filter( 'style_loader_src', 'crunchify_cleanup_query_string', 15, 1 );
add_filter('after_setup_theme', 'remove_redundant_shortlink');

remove_action( 'wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
remove_action('template_redirect', 'rest_output_link_header', 11, 0);

 ?>