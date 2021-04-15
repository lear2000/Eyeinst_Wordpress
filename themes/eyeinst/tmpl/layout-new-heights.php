<?php
/**
 * Block template file: tmpl/layout-new-heights.php
 *
 * Newheights Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'newheights-' . $block['id'];
if ( ! empty($block['anchor'] ) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$classes = 'block-newheights';
if ( ! empty( $block['className'] ) ) {
    $classes .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $classes .= ' align' . $block['align'];
}
?>

<section class="layout-new-heights <?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>" >
	<div class="layout-new-heights__wrapper">

		<div class="layout-new-heights__heading">
			<h2 class="is-heading u-text-center"><?php the_field( 'heading' ); ?></h2>
		</div>

		<div class="layout-new-heights__items u-text-center">
		<?php if ( have_rows( 'icons' ) ) : ?>
		<?php while ( have_rows( 'icons' ) ) : the_row(); ?>
			<?php $icon_image = get_sub_field( 'icon_image' ); ?>
			<?php $link_icon = get_sub_field( 'link_icon' ); ?>
				<div class="layout-new-heights__item">
					<div class="layout-new-heights__item-image">
						<?php if ( $link_icon ) : ?>
						<a href="<?php echo esc_url( $link_icon) ; ?>"><img src="<?php echo esc_url( $icon_image['url'] ); ?>" alt="<?php echo esc_attr( $icon_image['alt'] ); ?>" /></a>
						<?php endif; ?>
					</div>
					<div class="layout-new-heights__item-text">
						<h3 class="layout-new-heights__item-text-heading">
							<?php if ( $link_icon ) : ?>
							<a href="<?php echo esc_url( $link_icon) ; ?>"><?php the_sub_field( 'title' ); ?></a>
							<?php else: ?>
								<?php the_sub_field( 'title' ); ?>
							<?php endif; ?>
						</h3>
						<div class="layout-new-heights__item-text-text">
							<?php the_sub_field( 'copy' ); ?>
						</div>
					</div>
				</div>
			<?php  endwhile;?>
			<?php endif; ?>
		</div>
	</div>

	<hr class="u-mountain-lines">

</section>
