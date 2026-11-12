<?php
/**
 * Tabs: an island (runtime/islands/tabs.js) of its tabs (marccent/tab), saved or from a Loop: each one's label and
 * blocks, which is what the page shows until the tab bar is drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_island( $block, 'tabs', array(), marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
