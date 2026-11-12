<?php
/**
 * Orbit item: one of its Orbit's circles (the theme's OrbitItem), in the block or a Loop there.
 * Its settings for the island, and the page's text until it's drawn (marccent_blocks_item, inc/islands.php).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_item( marccent_blocks_item_props( $attributes, $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
