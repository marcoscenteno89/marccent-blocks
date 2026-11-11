<?php
/**
 * Stat card: a Card (marccent_blocks_card, inc/markup.php) around an island (runtime/islands/stat-card.js), the theme's
 * StatCard, which has no box of its own: the number, its icon and label as props; its blocks (buttons) in the slot,
 * under what the card says.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a     = $attributes;
$props = array_filter( array( 'icon' => $a['icon'], 'value' => $a['value'], 'label' => $a['label'], 'text' => $a['text'], 'accent' => (bool) $a['accent'] ), fn( $v ) => '' !== $v );
if ( isset( $a['fill'] ) && is_numeric( $a['fill'] ) ) {
	$props['fill'] = max( 0, min( 100, (float) $a['fill'] ) );
}
$island = marccent_blocks_island( null, 'stat-card', $props, marccent_blocks_island_stand_in( $props ) . marccent_blocks_inner( $block ) );
echo marccent_blocks_card( array(), $island, fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) ); // phpcs:ignore WordPress.Security.EscapeOutput
