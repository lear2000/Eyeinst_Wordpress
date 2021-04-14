<?php
//TEMPLATE NAME: HOMEPAGE
?>
<?php get_header(); ?>

<!-- Start the Loop. -->
 <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
 	<?php the_content(); ?>
 <?php endwhile; else : ?>
 <?php endif; ?>

<?php get_footer(); ?>