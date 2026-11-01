<?php
/**
 * Timeline: an island (runtime/islands/timeline.js), its events as a list until it's drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$events = array_values( array_filter( (array) $attributes['events'], 'is_array' ) );
echo marccent_blocks_island( $block, 'timeline', array( 'events' => $events ), marccent_blocks_island_text( $events ) ); // phpcs:ignore WordPress.Security.EscapeOutput
