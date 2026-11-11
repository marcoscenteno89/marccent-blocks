<?php
/**
 * Radial menu item: one of its Radial menu's icons (the theme's RadialMenuItem), in the block or a Loop there.
 * Its settings for the island, and the page's text until it's drawn (marccent_blocks_item, inc/islands.php).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_item( marccent_blocks_item_props( $attributes, $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
