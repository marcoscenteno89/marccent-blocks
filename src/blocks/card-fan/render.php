<?php
/**
 * Card fan: the theme's CardFan, written by the server (marccent_blocks_card_fan, inc/markup.php): each inner block (a
 * Card) in its place in the fan.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$cards = array_map( fn( $inner ) => $inner->render(), iterator_to_array( $block->inner_blocks ) );
echo marccent_blocks_card_fan( $attributes, array_values( array_filter( $cards, fn( $html ) => '' !== trim( $html ) ) ), fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput
