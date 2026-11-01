<?php
/**
 * Image card: an island (runtime/islands/image-card.js): the photo (or canvas) and its text as props, its blocks
 * (buttons) in the slot.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$props = array_intersect_key( $attributes, array_flip( array( 'src', 'alt', 'canvas', 'label', 'title', 'text', 'overlay', 'align', 'badge', 'big', 'ratio', 'position', 'shape' ) ) );
$props = array_filter( $props, fn( $v ) => '' !== $v && null !== $v );
echo marccent_blocks_island( $block, 'image-card', $props, marccent_blocks_island_stand_in( $props ) . marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
