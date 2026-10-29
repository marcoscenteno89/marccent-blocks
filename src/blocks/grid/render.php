<?php
/**
 * Grid: the theme's .grid, each inner block one cell (marccent_blocks_grid, inc/markup.php; index.js has the layouts).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_grid( $attributes, marccent_blocks_inner( $block ), fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput
