<?php
/**
 * Radar: the theme's Radar, written by the server (marccent_blocks_radar, inc/markup.php). Fewer than three axes:
 * nothing.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_radar( $attributes, fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput
