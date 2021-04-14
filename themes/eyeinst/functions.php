<?php
// INC
// include 'inc/breadcrumb.php';
// include 'nofollow/nofollow.php';
// include 'inc/media-uploader.php';
// include 'inc/acfsrcset.php';
// include 'inc/speedup.php';
include 'inc/acf-functions.php';


/**
 * Register new Navigation menu
 */
function register_my_menus() {
  register_nav_menus( array( 'header-menu' => __( 'Header Menu' ) ) );
}
add_action( 'init', 'register_my_menus' );


/**
 * Proper way to enqueue scripts and styles.
 */
function fs_theme_name_scripts() {

    wp_enqueue_style( 'mainstyle', get_template_directory_uri().'/style.css' );
    // if (!is_front_page()) {
    //   wp_enqueue_style( 'insidestyle', get_template_directory_uri().'/inside.css' );
    // }

    // REMOVE JQUERY
    wp_deregister_script( 'jquery' );
    wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js', false, '2', true);
    wp_enqueue_script( 'slick', get_template_directory_uri() . '/js/slick.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'scripts', get_template_directory_uri() . '/js/scripts.js', array('jquery'), '1.0.0', true );


}
add_action( 'wp_enqueue_scripts', 'fs_theme_name_scripts' );



// remove all from excerpt
function new_excerpt_more( $more ) {
    return '...';
}
add_filter('excerpt_more', 'new_excerpt_more');



// BODY CLASS
add_filter( 'body_class', 'custom_class' );
function custom_class( $classes ) {


    if (is_page() && !is_front_page() && !is_home()) {
      global $post;
      $classes[] = 'page-'.$post->post_name;
      $classes[] = 'insidepage';
      preg_replace('/\s+/', '', strtolower(get_the_title( $post->post_parent )));
      $classes[] = 'thispage-' . preg_replace('/\s+/', '', strtolower(get_the_title( $post->post_parent )));;
    }

    if (is_home() || is_single()) {
        $classes[] = $post->post_name;
        $classes[] = 'insidepage';

    }

    if (is_archive('rmg-category') || is_singular( 'rmg-category' ) || is_singular( "rmg-case" )) {
      $classes[] = 'gallerypage';
    }

    if (is_404()) {
        $classes[] = 'insidepage';
    }

    return $classes;
}


// LOGIN
function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/custom-login-logo.png);
        height:65px;
        width:320px;
        background-size: contain;
        background-repeat: no-repeat;
            padding-bottom: 30px;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );


// CONVERT HEX TO RGBA
function hex2rgb($hex) {
$hex = str_replace("#", "", $hex);

if(strlen($hex) == 3) {
$r = hexdec(substr($hex,0,1).substr($hex,0,1));
$g = hexdec(substr($hex,1,1).substr($hex,1,1));
$b = hexdec(substr($hex,2,1).substr($hex,2,1));
} else {
$r = hexdec(substr($hex,0,2));
$g = hexdec(substr($hex,2,2));
$b = hexdec(substr($hex,4,2));
}
$rgb = array($r, $g, $b);

return $rgb; // returns an array with the rgb values
}


/* EXTEND SUBNAV
******************************************/

class submenuWrap extends Walker_Nav_Menu {
    function start_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class='sub-menu depth-".$depth."'>\n";
    }
    function end_lvl( &$output, $depth = 0, $args = array() ) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }
}


// CONVERT TEL
function telly($number){
  $numbertel = "tel:+1" . preg_replace("/[^0-9]/", "", $number);
  $link = "<a href=".$numbertel.">".$number."</a>";
  return $link;
}

// CONVERT TEL
function telly_link($number){
  $numbertel = "tel:+1" . preg_replace("/[^0-9]/", "", $number);
  $tel = $numbertel;
  return $tel;
}







/*
*
* Walker for the main menu
*
*/
function add_arrow( $output, $item, $depth, $args ){
// //Only add class to 'top level' items on the 'primary' menu.
if('Main Menu' == $args->menu && $depth === 0 ){
     if (in_array("menu-item-has-children", $item->classes)) {
         $output .='<div class="nav-plus"><span></span><span></span></div>';
    }
    /* wraps the no dropdown */

    // if (in_array("nodrop", $item->classes)) {
    //     $output = '<a href="'. $item->url .'" title="'. $item->attr_title .'"><span>'.$item->title.'</span></a>';
    //  }

}
    return $output;
}
add_filter( 'walker_nav_menu_start_el', 'add_arrow',10,4);










?>