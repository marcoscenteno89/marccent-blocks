<?php
/**
 * Atom: an island (runtime/islands/atom.js), the theme's Atom. Nothing stands in for it: it's a picture that moves.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_island( $block, 'atom', array( 'speed' => (float) $attributes['speed'], 'label' => (string) $attributes['label'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput
