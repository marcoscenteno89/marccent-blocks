<?php
/**
 * The theme's text formats in the editor (#147): assets/formats.js, with marccent-theme's markup/formats.json
 * (copied to build/formats.json by webpack.config.js) as window.marccentFormats.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'enqueue_block_editor_assets', function () {
	$formats = json_decode( (string) @file_get_contents( MARCCENT_BLOCKS_DIR . '/build/formats.json' ), true ) ?: array();
	wp_enqueue_script( 'marccent-formats', plugins_url( 'assets/formats.js', MARCCENT_BLOCKS_FILE ), array( 'wp-block-editor', 'wp-data', 'wp-element', 'wp-format-library', 'wp-rich-text' ), filemtime( MARCCENT_BLOCKS_DIR . '/assets/formats.js' ), true );
	wp_add_inline_script( 'marccent-formats', 'window.marccentFormats = ' . wp_json_encode( (object) $formats ) . ';', 'before' );
} );
