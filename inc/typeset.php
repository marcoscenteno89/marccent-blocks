<?php
/**
 * Typography set on the server (marccent-theme#143, T-024): every text block's HTML goes through the theme's PHP port
 * of core/typeset.js (\Marccent\typeset_html, build/typeset.php, copied from the package by webpack.config.js), so the
 * page arrives set: dashes, ellipses, primes, units kept with their numbers, ordinals and marks raised. Nothing shifts
 * after it loads. WordPress's own wptexturize rewrites the same characters its own way, so it's off: one set of rules.
 * Code (code, pre, kbd, samp) and .no-typeset are left alone by the port.
 */

defined( 'ABSPATH' ) || exit;

if ( file_exists( MARCCENT_BLOCKS_DIR . '/build/typeset.php' ) ) {
	require_once MARCCENT_BLOCKS_DIR . '/build/typeset.php';
}

// The blocks whose content is text people write.
const MARCCENT_BLOCKS_TYPESET = array( 'core/paragraph', 'core/heading', 'core/list-item', 'core/quote', 'core/pullquote', 'core/details', 'core/table', 'core/media-text', 'core/verse' );

add_filter(
	'render_block',
	function ( $html, $block ) {
		if ( ! function_exists( '\Marccent\typeset_html' ) || ! in_array( $block['blockName'] ?? '', MARCCENT_BLOCKS_TYPESET, true ) || '' === trim( (string) $html ) ) {
			return $html;
		}
		return \Marccent\typeset_html( (string) $html );
	},
	10,
	2
);

add_filter( 'run_wptexturize', fn( $run ) => function_exists( '\Marccent\typeset_html' ) ? false : $run );
