<?php
/**
 * Step bar: an island (runtime/islands/step-bar.js) of its Step bar steps, saved or from a Loop, which the page shows
 * as text until it's drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_island( $block, 'step-bar', array( 'start' => max( 0, (int) $attributes['start'] ) ), marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
