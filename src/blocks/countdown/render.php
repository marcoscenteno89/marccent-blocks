<?php
/**
 * Countdown: an island (runtime/islands/countdown.js), the theme's Countdown markup until the island draws it.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$from = max( 0, min( 9, (int) $attributes['from'] ) );
echo marccent_blocks_island( $block, 'countdown', array( 'from' => $from ), marccent_blocks_countdown( wp_unique_id( 'goo-' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput
