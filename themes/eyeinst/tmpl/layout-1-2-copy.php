<?php
/**
 * Block template file: tmpl/layout-1-2-copy.php
 *
 * Copylrblock Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'copylrblock-' . $block['id'];
if ( ! empty($block['anchor'] ) ) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$classes = 'block-copylrblock';
if ( ! empty( $block['className'] ) ) {
    $classes .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $classes .= ' align' . $block['align'];
}
?>
<section class="layout-1-2 layout-1-2-copy <?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $id ); ?>">

	<div class="layout-1-2-copy__wrapper">
		<div class="layout-1-2-copy__copy layout-1-2-copy__copy-1">
			<p><?php the_field( 'left_heading_copy' ); ?></p>
		</div>

		<div class="layout-1-2-copy__copy layout-1-2-copy__copy-2">
			<div><?php the_field( 'right_copy' ); ?></div>
		</div>
	</div>

</section>
