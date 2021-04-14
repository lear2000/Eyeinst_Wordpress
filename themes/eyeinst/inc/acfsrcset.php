<?php

add_image_size( 'mobile-header-small', 345 );
add_image_size( 'mobile-header', 767 );
add_image_size( 'ipad', 918 );
add_image_size( 'small-desktop', 1380 );
add_image_size( 'large-desktop', 1650 );

/**
 * Responsive Image Helper Function
 *
 * @param string $image_id the id of the image (from ACF or similar)
 * @param string $image_size the size of the thumbnail image or custom image size
 * @param string $max_width the max width this image will be shown to build the sizes attribute
 */

function awesome_acf_responsive_image($image_id,$image_size,$max_width){

	// check the image ID is not blank
	if($image_id != '') {

		// set the default src image size
		$image_src = wp_get_attachment_image_url( $image_id, $image_size );

		// set the srcset with various image sizes
		$image_srcset = wp_get_attachment_image_srcset( $image_id, $image_size );

		// generate the markup for the responsive image
		echo 'src="'.$image_src.'" srcset="'.$image_srcset.'" sizes="(max-width: '.$max_width.') 100vw, '.$max_width.'"';

	}
}

add_filter( 'max_srcset_image_width', 'awesome_acf_max_srcset_image_width', 10 , 2 );

// set the max image width
function awesome_acf_max_srcset_image_width() {
	return 2200;
}


add_filter( 'image_size_names_choose', 'my_custom_sizes' );

function my_custom_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'mobile-header-small' => __( 'MB 345' ),
        'mobile-header' => __( 'MB 767' ),
        'ipad' => __( 'ipad 918' ),
        'small-desktop' => __( 'Small Desk 1380' ),
        'large-desktop' => __( 'Large Desk 1650' ),
    ) );
}

?>