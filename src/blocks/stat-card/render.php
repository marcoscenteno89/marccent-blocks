<?php
/**
 * Stat card: an island (runtime/islands/stat-card.js): the number, its icon and label as props; its blocks (buttons)
 * in the slot, under what the card says.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a     = $attributes;
$props = array( 'icon' => $a['icon'], 'value' => $a['value'], 'label' => $a['label'], 'text' => $a['text'], 'accent' => (bool) $a['accent'] );
if ( isset( $a['fill'] ) && is_numeric( $a['fill'] ) ) {
	$props['fill'] = max( 0, min( 100, (float) $a['fill'] ) );
}
echo marccent_blocks_island( $block, 'stat-card', $props, marccent_blocks_island_stand_in( $props ) . marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
