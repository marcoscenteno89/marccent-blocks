<?php
/**
 * Plugin Name: Marccent Blocks
 * Description: The Marccent theme (marccent-theme) in WordPress: its styles, its live theme engine (palettes, light / dark, design styles) and its components as blocks.
 * Version: 0.4.1
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
require __DIR__ . '/inc/assets.php'; // the styles and scripts, only what each page uses
require __DIR__ . '/inc/menu.php';   // menus in the shape the theme's components take
require __DIR__ . '/inc/markup.php'; // the theme's markup, written by the server (the blocks' render.php)

// Blocks: every block.json the build wrote (src/blocks/* → build/blocks/*).
add_action( 'init', function () {
	foreach ( glob( MARCCENT_BLOCKS_DIR . '/build/blocks/*/block.json' ) as $file ) {
		register_block_type( dirname( $file ) );
	}
	register_nav_menus( array( 'primary' => __( 'Primary', 'marccent-blocks' ) ) );
} );

add_filter( 'block_categories_all', function ( $categories ) {
	array_unshift( $categories, array( 'slug' => 'marccent', 'title' => 'Marccent' ) );
	return $categories;
} );
