<?php
/**
 * Block template file: tmpl/layout-1-2-reverse.php
 *
 * Reverseheader Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'reverseheader-' . $block['id'];
if ( ! empty($block['anchor'] ) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$classes = 'block-reverseheader';
if ( ! empty( $block['className'] ) ) {
    $classes .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $classes .= ' align' . $block['align'];
}
?>
<?php $background_image = get_field( 'background_image' ); ?>
<?php $button = get_field( 'button' ); ?>
<section class="layout-1-2 layout-1-2-reverse layout-1-2-type__index <?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>">

	<div class="layout-1-2__image layout-1-2-reverse__image" style="background-image:url(<?php echo esc_url( $background_image['url'] ); ?>);">
		<!-- nothing goes here -->
	</div>

	<div class="layout-1-2__text layout-1-2-reverse__text">
		<h2 class="is-heading u-text-center">
			<?php the_field( 'text' ); ?>
		</h2>
			<?php if ( $button ) : ?>
			<p><a class="btn primary" href="<?php echo esc_url( $button['url'] ); ?>" target="<?php echo esc_attr( $button['target'] ); ?>"><?php echo esc_html( $button['title'] ); ?></a></p>
		<?php endif; ?>
	</div>

</section>
