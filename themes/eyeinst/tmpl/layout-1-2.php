<?php
/**
 * Block template file: tmpl/layout-1-2.php
 *
 * Pageheader Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'pageheader-' . $block['id'];
if ( ! empty($block['anchor'] ) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$classes = 'block-pageheader';
if ( ! empty( $block['className'] ) ) {
    $classes .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $classes .= ' align' . $block['align'];
}
?>
<section class="layout-1-2 layout-1-2-type__index <?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>" >
	<?php $header_image = get_field( 'header_image' ); ?>

	<div class="layout-1-2__image" style="background-image:url(<?php echo esc_url( $header_image['url'] ); ?>);">

	</div>

	<div class="layout-1-2__text">
		<?php if ( get_field( 'new_page_title' ) == 1 ) : ?>
				<h1 class="is-heading"><?php the_field( 'alt_page_title' ); ?></h1>
			<?php else : ?>
				<h1 class="is-heading"><?php the_title(); ?></h1>
			<?php endif; ?>
			<div><?php the_field( 'header_copy' ); ?></div>
			<?php $header_button = get_field( 'header_button' ); ?>
				<?php if ( $header_button ) : ?>
		<p>
					<a class="button btn primary" href="<?php echo esc_url( $header_button['url'] ); ?>" target="<?php echo esc_attr( $header_button['target'] ); ?>"><?php echo esc_html( $header_button['title'] ); ?></a>
		</p>
		<?php endif; ?>
	</div>

</section>
