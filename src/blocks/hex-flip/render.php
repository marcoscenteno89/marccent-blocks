<?php
/**
 * Hex flip: one hexagon of its Hex grid, in the block or a Loop there: its settings for the island and its blocks, the
 * front (marccent_blocks_item, inc/islands.php).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a     = $attributes;
$props = marccent_blocks_item_props(
	array(
		'back'      => (string) $a['back'],
		'frontClass' => marccent_blocks_cx( ...array_map( 'sanitize_html_class', explode( ' ', (string) $a['frontClass'] ) ) ),
		'backClass' => marccent_blocks_cx( ...array_map( 'sanitize_html_class', explode( ' ', (string) $a['backClass'] ) ) ),
		'label'     => (string) $a['label'],
	),
	$block
);
echo marccent_blocks_item( $props, marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
