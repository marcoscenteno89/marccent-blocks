<?php
/**
 * Tabs: an island (runtime/islands/tabs.js): each tab's label and icon as props, and in the slot each tab's blocks
 * under its label (marccent/tab), which is what the page shows until the tab bar is drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$tabs = array();
foreach ( $block->inner_blocks as $tab ) {
	$tabs[] = array( 'label' => marccent_blocks_text( (string) ( $tab->attributes['label'] ?? '' ) ), 'icon' => (string) ( $tab->attributes['icon'] ?? '' ) );
}
echo marccent_blocks_island( $block, 'tabs', array( 'tabs' => $tabs ), marccent_blocks_inner( $block ) ); // phpcs:ignore WordPress.Security.EscapeOutput
