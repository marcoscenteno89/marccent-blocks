<?php
/**
 * Marquee: the theme's Marquee, written by the server (marccent_blocks_marquee, inc/markup.php): its blocks, twice,
 * in a row that scrolls without end.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_marquee( $attributes, marccent_blocks_inner( $block ), fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput
