<?php
/**
 * Ticket card: an island (runtime/islands/ticket-card.js): its label, title, note, list and ribbon as props; its
 * blocks (the action: a button) in the slot.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a     = $attributes;
$items = array();
foreach ( (array) $a['items'] as $item ) {
	if ( '' !== trim( (string) ( $item['text'] ?? '' ) ) ) {
		$items[] = array( 'text' => (string) $item['text'], 'icon' => (string) ( $item['icon'] ?? '' ) ?: 'check' );
	}
}
$props = array( 'label' => $a['label'], 'title' => $a['title'], 'note' => $a['note'], 'items' => $items, 'ribbon' => $a['ribbon'] );
echo marccent_blocks_island( $block, 'ticket-card', $props, marccent_blocks_island_stand_in( $props ) . marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
