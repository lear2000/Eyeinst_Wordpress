<?php
/**
 * Block template file: tmpl/layout-rating-quote.php
 *
 * Rating Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'rating-' . $block['id'];
if ( ! empty($block['anchor'] ) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$classes = 'block-rating';
if ( ! empty( $block['className'] ) ) {
    $classes .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $classes .= ' align' . $block['align'];
}
?>

<section class="layout-rating-quote <?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>">

	<div class="layout-rating-quote__quote">
		<h2 class="is-heading u-text-center"><?php the_field( 'rating_heading' ); ?></h2>
	</div>

	<div class="layout-rating-quote__rating">

		<div class="layout-rating-quote__rating-average u-text-center">
			<div class="layout-rating-quote__rating-average-image">
				<img src="<?php echo get_template_directory_uri();?>/img/rating.svg" alt="Eye Institute Rating Icon "  loading="lazy" />
			</div>
			<div class="layout-rating-quote__rating-average-text">
				<span class="layout-rating-quote__rating-average-text-1"><?php the_field( 'rating_number' ); ?></span>
				<span class="layout-rating-quote__rating-average-text-2">Average Rating</span>
				<span class="layout-rating-quote__rating-average-text-3"><?php the_field( 'rating_based' ); ?></span>
			</div>
		</div>

		<div class="layout-rating-quote__rating-link u-text-center">
			<?php $rating_button = get_field( 'rating_button' ); ?>
			<?php if ( $rating_button ) : ?>
					<a class="btn primary" href="<?php echo esc_url( $rating_button['url'] ); ?>" target="<?php echo esc_attr( $rating_button['target'] ); ?>"><?php echo esc_html( $rating_button['title'] ); ?></a>
				<?php endif; ?>
		</div>


	</div>

</section>
