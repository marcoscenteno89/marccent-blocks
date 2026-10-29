<?php
/**
 * Carousel: an island (data-island="carousel", runtime/islands/carousel.js): its slides, one per inner block, as plain
 * HTML in [data-slot], shown in a row until the runtime replaces them with the theme's Carousel.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a     = $attributes;
$attrs = array(
	'data-island' => 'carousel',
	'data-props'  => wp_json_encode( array( 'slideWidth' => $a['slideWidth'], 'label' => $a['label'] ) ),
	'style'       => marccent_blocks_css( array( '--slide-width' => $a['slideWidth'] ) ),
);
?>
<div <?php echo marccent_blocks_wrapper( $block, $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><div data-slot=""><?php echo marccent_blocks_inner( $block ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div></div>
