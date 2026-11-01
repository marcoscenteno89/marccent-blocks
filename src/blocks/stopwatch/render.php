<?php
/**
 * Stopwatch: an island (runtime/islands/stopwatch.js), Start and Stop and the elapsed time.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_island( $block, 'stopwatch', array(), '<p>0:00:00</p>' ); // phpcs:ignore WordPress.Security.EscapeOutput
