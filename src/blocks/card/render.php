<?php
/**
 * Card: a raised box, its blocks stacked inside (index.js has the classes' meaning).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a      = $attributes;
$layout = $a['rows'] ? 'sub rows-' . (int) $a['rows'] : marccent_blocks_cx( 'grid cols-1 content-start', $a['gap'] );
$class  = marccent_blocks_cx( $a['surface'], $a['round'] ? 'round' : '', $a['pad'], $layout, $a['hover'], $a['center'] ? 'text-center justify-center' : '' );
?>
<article <?php echo marccent_blocks_wrapper( $block, array( 'class' => $class ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo marccent_blocks_inner( $block ); // phpcs:ignore WordPress.Security.EscapeOutput ?></article>
