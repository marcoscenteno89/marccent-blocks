<?php
/**
 * Grid: the theme's .grid, each inner block one cell (index.js has the layouts).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a     = $attributes;
$equal = in_array( $a['layout'], array( 'cols', 'fixed' ), true );
$shape = 'cols' === $a['layout'] ? 'cols-' . (int) $a['cols'] : ( 'fixed' === $a['layout'] ? 'fixed cols-' . (int) $a['cols'] : $a['layout'] );
$attrs = array(
	'class' => marccent_blocks_cx( 'grid', $shape, $a['gap'], $a['alignItems'], $a['center'] ? 'cols-center justify-center text-center' : '' ),
	'style' => $equal ? marccent_blocks_css( array( '--min' => $a['min'] ) ) : null,
);
?>
<div <?php echo marccent_blocks_wrapper( $block, $attrs ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo marccent_blocks_inner( $block ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
