<?php
/**
 * Theme editor: an island (runtime/islands/theme-editor.js), the theme's full editor for the site.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_island( $block, 'theme-editor', array() ); // phpcs:ignore WordPress.Security.EscapeOutput
