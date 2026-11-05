<?php
/**
 * Card: a raised box, its blocks stacked inside (marccent_blocks_card, inc/markup.php; index.js has the classes'
 * meaning). Hover at Default takes the site's default hover (the theme's options panel, Marccent WP).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_card( array( 'hover' => $attributes['hover'] ?: marccent_blocks_default_hover() ) + $attributes, marccent_blocks_inner( $block ), fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput
