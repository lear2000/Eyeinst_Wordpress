<?php
function fs_breadcrumb() {
			global $post;
			$curPostID = $post->ID;
			$anc = array_reverse( get_post_ancestors($curPostID) );

			$html = '
						<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
						<a href="' . get_bloginfo( 'url' ) . '" itemprop="item" itemtype="http://schema.org/WebPage"><span itemprop="name"><i class="far fa-home"></i>Home</span></a> <span>|</span>
						 <meta itemprop="position" content="1" />
						</span>';



		$cnt = 1;
		foreach ( $anc as $a ):
			$cnt++;

			$html .= '<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
						<a href="' . get_the_permalink($a) . '" itemprop="item" itemtype="http://schema.org/WebPage"><span itemprop="name">' . get_the_title($a) . '</span></a> |
						<meta itemprop="position" content="'.$cnt.'" />
						 </span>
						';

		endforeach;

		$cnt = $cnt + 1;
			$html .= '
			<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
			<a  href="'. get_the_permalink($curPostID) .'" style="text-decoration:none;" itemprop="item" itemtype="http://schema.org/WebPage"><span itemprop="name">' . get_the_title( $curPostID ) . '</span></a>
			<meta itemprop="position" content="'.$cnt.'" />
			</span>';



		echo $html;
}
?>