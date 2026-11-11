<?php
/**
 * Tab: one of its Tabs' tabs, in the block or a Loop there: its label and icon for the island, and its blocks, the
 * panel it shows (marccent_blocks_item, inc/islands.php); the label shows over them until the tab bar is drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$props = marccent_blocks_item_props( array( 'label' => marccent_blocks_text( (string) $attributes['label'] ), 'icon' => (string) $attributes['icon'] ), $block );
echo marccent_blocks_item( $props, marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
