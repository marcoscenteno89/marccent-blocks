<?php
/**
 * Typography set on the server (#143): every block's text through marccent-theme's server/typeset.php (copied to
 * build/typeset.php by webpack.config.js), so a page arrives set and nothing shifts after it loads. Dashes, the
 * ellipsis, ™ ® ©, feet and inches, units kept by their numbers, ordinals raised, a paragraph's last two words kept
 * together; never inside code, pre, kbd, samp, script, style, textarea, sup, sub, .no-typeset or [contenteditable].
 *
 * WordPress's own wptexturize is off: it rewrites the same dashes and dots by other rules, so the two would fight
 * (PRODUCT-LINE.md section 9, Decisions, 2026-11-18). A block inside another is set with it and then again with the
 * block around it, which leaves set text as it is.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'Marccent\typeset_html' ) && is_readable( MARCCENT_BLOCKS_DIR . '/build/typeset.php' ) ) {
	require_once MARCCENT_BLOCKS_DIR . '/build/typeset.php';
}

if ( function_exists( 'Marccent\typeset_html' ) ) {
	add_filter( 'run_wptexturize', '__return_false' );
	add_filter(
		'render_block',
		fn( $html ) => is_string( $html ) && '' !== trim( $html ) ? \Marccent\typeset_html( $html ) : $html,
		20
	);
}
