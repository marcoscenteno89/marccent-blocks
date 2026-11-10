<?php
/**
 * Mini theme editor: an island (runtime/islands/mini-theme-editor.js), the theme's row of controls for the site.
 * Nothing stands in for it without JavaScript: it can't do anything then.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

// What Visitors allows (a header's): the Theme button, or single controls (runtime/islands/theme-button.js).
if ( ! empty( $attributes['visitors'] ) ) {
	$v = marccent_blocks_visitor_controls();
	echo marccent_blocks_island( $block, 'theme-button', array( 'themeButton' => $v['full'], 'controls' => $v['controls'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	return;
}
echo marccent_blocks_island( $block, 'mini-theme-editor', array( 'align' => in_array( $attributes['align'], array( 'start', 'center', 'end' ), true ) ? $attributes['align'] : 'start' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
