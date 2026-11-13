<?php
/**
 * Gauge: the theme's Gauge, written by the server (marccent_blocks_gauge, inc/markup.php).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_gauge( $attributes, fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput
