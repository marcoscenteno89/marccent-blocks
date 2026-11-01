<?php
/**
 * Mini theme editor: an island (runtime/islands/mini-theme-editor.js), the theme's row of controls for the site.
 * Nothing stands in for it without JavaScript: it can't do anything then.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_island( $block, 'mini-theme-editor', array( 'align' => in_array( $attributes['align'], array( 'start', 'center', 'end' ), true ) ? $attributes['align'] : 'start' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
