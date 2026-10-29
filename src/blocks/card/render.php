<?php
/**
 * Card: a raised box, its blocks stacked inside (marccent_blocks_card, inc/markup.php; index.js has the classes'
 * meaning).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_card( $attributes, marccent_blocks_inner( $block ), fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput
