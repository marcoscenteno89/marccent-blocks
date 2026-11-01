<?php
/**
 * Step bar: an island (runtime/islands/step-bar.js), its steps as a list until it's drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$steps = array_values( array_filter( array_map( fn( $s ) => trim( (string) ( $s['label'] ?? '' ) ), (array) $attributes['steps'] ) ) );
$props = array( 'steps' => $steps, 'start' => max( 0, min( count( $steps ) - 1, (int) $attributes['start'] ) ) );
echo marccent_blocks_island( $block, 'step-bar', $props, marccent_blocks_island_text( $steps ) ); // phpcs:ignore WordPress.Security.EscapeOutput
