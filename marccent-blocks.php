<?php
/**
 * Plugin Name: Marccent Blocks
 * Description: The Marccent theme (marccent-theme) in WordPress: its styles, its live theme engine (palettes, light / dark, design styles) and its components as blocks.
 * Version: 0.6.0
 * Requires at least: 6.6
 * Requires PHP: 8.0
 * Author: Marcos Centeno
 * License: GPL-2.0-or-later
 * Text Domain: marccent-blocks
 */

defined( 'ABSPATH' ) || exit;

const MARCCENT_BLOCKS_DIR = __DIR__;
const MARCCENT_BLOCKS_FILE = __FILE__;

require __DIR__ . '/inc/theme.php';  // the site's starting theme, painted on <html> by the server
require __DIR__ . '/inc/keys.php';   // Appearance → Marccent settings: every API key, in one place (marccent_key)
require __DIR__ . '/inc/assets.php'; // the styles and scripts, only what each page uses
require __DIR__ . '/inc/menu.php';   // menus in the shape the theme's components take
require __DIR__ . '/inc/upgrade.php'; // content saved before the settings: old names and defaults (settings-upgrade.json)
require __DIR__ . '/inc/markup.php'; // the theme's markup, written by the server (the blocks' render.php)
require __DIR__ . '/inc/islands.php'; // blocks the theme's React components draw: data-island, props, a stand-in
require __DIR__ . '/inc/forms.php';   // the Form block's server side: checks, entries (Forms → Entries), email, Turnstile
if ( is_admin() ) {
	require __DIR__ . '/inc/forms-admin.php'; // the entries' screens and Settings → Marccent forms
}
require __DIR__ . '/inc/export.php'; // every Marccent plugin's settings as one JSON file (Tools → Marccent export, wp marccent)
require __DIR__ . '/inc/visibility.php'; // any block shown to whom, when and on which screens; copying a block's style
require __DIR__ . '/inc/html.php'; // the Custom HTML block's code: saved only by people who may post unfiltered HTML
require __DIR__ . '/inc/typeset.php'; // the text blocks' typography set on the server, the theme's rules (no shift)

// Blocks: every block.json the build wrote (src/blocks/* → build/blocks/*).
add_action( 'init', function () {
	foreach ( glob( MARCCENT_BLOCKS_DIR . '/build/blocks/*/block.json' ) as $file ) {
		// The blocks with blocks inside render them themselves (marccent_blocks_inner, inc/markup.php): without
		// skip_inner_blocks WordPress renders them first too, unused, and each one nested in another doubles that.
		register_block_type( dirname( $file ), array( 'skip_inner_blocks' => true ) );
	}
	register_nav_menus( array( 'primary' => __( 'Primary', 'marccent-blocks' ) ) );
} );

add_filter( 'block_categories_all', function ( $categories ) {
	array_unshift( $categories, array( 'slug' => 'marccent', 'title' => 'Marccent' ) );
	return $categories;
} );
