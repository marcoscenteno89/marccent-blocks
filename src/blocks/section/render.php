<?php
/**
 * Section: a full-width band and its content capped to the page width; with an image, a .stack of the image, a shade
 * and the content (index.js has the layout).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a     = $attributes;
$tag   = in_array( $a['tagName'], array( 'section', 'div', 'header', 'footer', 'aside' ), true ) ? $a['tagName'] : 'section';
$image = $a['imageUrl'] ?? '';
// A least height makes the band a one-cell grid, so its content box can center in it (not .grid: that brings columns).
$outer = array(
	'class' => marccent_blocks_cx( 'wrap wrap-fluid', $image ? 'stack' : $a['surface'] ),
	'style' => $a['minHeight'] ? marccent_blocks_css( array( 'min-height' => $a['minHeight'] ) + ( $image ? array() : array( 'display' => 'grid', 'grid-template-columns' => 'minmax(0, 1fr)' ) ) ) : null,
);
// Over an image: the content on top of the image and shade, centered in the band, in light text.
$inner_style = $image ? 'position: relative; align-content: center; color: #fff' : ( $a['minHeight'] ? 'align-content: center' : '' );
$inner_class = marccent_blocks_cx( $a['width'], $a['padY'], 'grid cols-1', $a['gap'], $a['center'] ? 'text-center justify-center' : '', $image ? $a['surface'] : '' );
?>
<<?php echo $tag; ?> <?php echo marccent_blocks_wrapper( $block, $outer ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<?php if ( $image ) : ?>
		<img class="cover-img" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $a['imageAlt'] ); ?>" loading="lazy" decoding="async">
		<div aria-hidden="true" style="background: rgb(0 0 0 / <?php echo (int) $a['shade']; ?>%)"></div>
	<?php endif; ?>
	<div class="<?php echo esc_attr( $inner_class ); ?>"<?php echo $inner_style ? ' style="' . esc_attr( $inner_style ) . '"' : ''; ?>><?php echo marccent_blocks_inner( $block ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
</<?php echo $tag; ?>>
