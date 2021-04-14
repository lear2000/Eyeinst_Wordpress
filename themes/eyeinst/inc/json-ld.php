
<?php
function get_post_data() {
  global $post;
  return $post;
}

$frontpage_id = (int)get_option( 'page_on_front' );
$post_data = get_post_data();

$content = $post_data->post_content;
$content = strip_tags(apply_filters('the_content', $content));



	$frontpayload = array(
			  array(
			  	"@context" => "http://schema.org/",
				"@id" => "https://www.drkarenhorton.com",
				"@type" => "LocalBusiness",
				"url" => "https://www.drkarenhorton.com",
				"image" => get_bloginfo('template_directory')."/images/office.jpg",
				"name" => "Karen Horton, MD",
		    "description"=> "Cosmetic Plastic Surgery from our San Francisco Bay Area practice include options such as liposuction, facelift, breast augmentation, and more. Contact Karen Horton, MD. ",
				"sameas" => //social media or related urls
					array(
						"https://www.instagram.com/drkarenhorton/",
						"https://www.facebook.com/drkarenhorton",
						"https://www.youtube.com/channel/UCYtq6AWTUGTJqi10EroZt7Q",
						"https://twitter.com/drkarenhorton",
						"https://www.yelp.com/biz/karen-m-horton-md-san-francisco",
						"https://www.realself.com/find/California/San-Francisco/Doctor/Karen-Horton",
						"https://plus.google.com/u/0/114148668965363203478"
					),

				"address" =>
				array(
					"@type" => "PostalAddress",
					"streetAddress" => "2100 Webster St #506",
					"addressLocality" => "San Francisco",
					"addressRegion" => "CA",
					"postalCode" => "94115",
					"telephone" => "+1-415-570-7352",
				)
			  ),

		// WEBSITE
		array(
			"@context" =>  "http://schema.org",
			"@type" =>  "WebSite",
			"name" =>  "Dr. Karen Horton MD",
			"url" =>  "https://www.drkarenhorton.com",
			"potentialAction" => array(
			  "@type" =>  "SearchAction",
			  "target" =>  "https://www.drkarenhorton.com/?s={search_term}",
			  "query-input" =>  "required name=search_term"
			)
		),
		// ORG
	  array(
	  "@context" => "http://schema.org/",
		"@id" => "Dr. Karen Horton MD",
		"@type" => "Organization",
		"name"=> "Dr. Karen Horton MD",
		"url" => "https://www.drkarenhorton.com",
		"logo" => get_bloginfo( 'template_directory' )."/images/schemalogo.png",
		"sameas" => //social media or related urls
			array(
				"https://www.instagram.com/drkarenhorton/",
				"https://www.facebook.com/drkarenhorton",
				"https://www.youtube.com/channel/UCYtq6AWTUGTJqi10EroZt7Q",
				"https://twitter.com/drkarenhorton",
				"https://www.yelp.com/biz/karen-m-horton-md-san-francisco",
				"https://www.realself.com/find/California/San-Francisco/Doctor/Karen-Horton",
				"https://plus.google.com/u/0/114148668965363203478"
			),
		"contactPoint" => // contact point
			array(
				"telephone" => "+1-415-570-7352",
				"contactType" => "customer service"
			)
	  )
	);


	$newpayload = $frontpayload;


?>