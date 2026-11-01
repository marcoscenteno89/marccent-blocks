<?php
/**
 * Parallax: an island (runtime/islands/parallax.js) around its blocks, which show as they are until it moves them.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_island( $block, 'parallax', array( 'speed' => (float) $attributes['speed'] ), marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
