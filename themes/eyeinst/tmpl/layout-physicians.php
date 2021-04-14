<?php
/**
 * Block template file: tmpl/layout-layout-physicians.php
 *
 * Physgrid Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'physgrid-' . $block['id'];
if ( ! empty($block['anchor'] ) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$classes = 'block-physgrid';
if ( ! empty( $block['className'] ) ) {
    $classes .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $classes .= ' align' . $block['align'];
}
?>


<section class="layout-physicians <?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>">
	<div class="layout-physicians__wrapper">
		<div class="layout-physicians__left">
			<h2 class="is-heading">Our Physicians</h2>
		</div>
		<div class="layout-physicians__right">
			<ul class="layout-physicians__list">
					<?php if ( have_rows( 'doctors' ) ) : ?>
						<?php while ( have_rows( 'doctors' ) ) : the_row(); ?>
							<?php $image = get_sub_field( 'image' ); ?>
							<li class="layout-physicians__list-item">
								<div class="layout-physicians__list-item-image">
									<img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>" />
								</div>
								<div class="layout-physicians__list-item-text u-text-center">
									<span><?php the_sub_field( 'name' ); ?></span>
									<span><?php the_sub_field( 'title' ); ?></span>
								</div>
							</li>
						<?php endwhile; ?>
					<?php endif; ?>
				</div>
			</ul>
		</div>
	</div>
</section>
