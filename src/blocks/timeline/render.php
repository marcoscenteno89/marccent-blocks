<?php
/**
 * Timeline: an island (runtime/islands/timeline.js) of its Timeline items, saved or from a Loop, which the page
 * shows as text until it's drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_island( $block, 'timeline', array(), marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
