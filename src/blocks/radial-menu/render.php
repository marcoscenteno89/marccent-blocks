<?php
/**
 * Radial menu: an island (runtime/islands/radial-menu.js) of its Radial menu items, saved or from a Loop, which the
 * page shows as text until it's drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$props = array( 'label' => (string) $attributes['label'], 'radius' => (string) $attributes['radius'] );
echo marccent_blocks_island( $block, 'radial-menu', $props, marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
