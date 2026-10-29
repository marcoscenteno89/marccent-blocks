<?php
/**
 * Section: a full-width band and its content capped to the page width; with an image, a .stack of the image, a shade
 * and the content (marccent_blocks_section, inc/markup.php; index.js has the layout).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_section( $attributes, marccent_blocks_inner( $block ), fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput
