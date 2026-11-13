<?php
/**
 * Count up: the theme's CountUp, written by the server (marccent_blocks_count_up, inc/markup.php): the final number,
 * which the theme's engine counts up to as it scrolls into view.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_count_up( $attributes, fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput
