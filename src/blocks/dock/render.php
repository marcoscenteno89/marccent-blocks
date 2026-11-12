<?php
/**
 * Dock: an island (runtime/islands/dock.js) of its Dock items, saved or from a Loop, which the page shows as text
 * until it's drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_island( $block, 'dock', array( 'label' => (string) $attributes['label'] ), marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
