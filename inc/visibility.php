<?php
/**
 * Visibility on every block (the marccentVisibility attribute; the editor's Visibility panel, assets/editor-extras.js):
 *
 *   who:   "in" (logged-in visitors only) or "out" (logged-out only); empty, everyone
 *   from, until: a date and time (the site's timezone) it shows from and until: a sale, a holiday notice
 *   hide:  [ "mobile", "tablet", "desktop" ]: the screen sizes it's hidden on (CSS, so it's still in the page)
 *
 * Who and when are decided by the server, so a page cache would keep one answer for everyone: on a cached site, use
 * them only on pages that aren't cached (or with a cache that varies by login).
 */

defined( 'ABSPATH' ) || exit;

// The screen sizes, as the theme's breakpoints: phones up to 767px, tablets to 1023px.
const MARCCENT_HIDE_CSS = '@media (max-width:767px){.hide-mobile{display:none!important}}@media (min-width:768px) and (max-width:1023px){.hide-tablet{display:none!important}}@media (min-width:1024px){.hide-desktop{display:none!important}}';

add_filter( 'register_block_type_args', function ( $args ) {
	$args['attributes']['marccentVisibility'] = array( 'type' => 'object', 'default' => array() );
	return $args;
} );

// Whether a block shows now, for this visitor.
function marccent_visible( array $v ): bool {
	$who = (string) ( $v['who'] ?? '' );
	if ( ( 'in' === $who && ! is_user_logged_in() ) || ( 'out' === $who && is_user_logged_in() ) ) {
		return false;
	}
	$now = current_time( 'Y-m-d\TH:i' );
	$at  = fn( $t ) => substr( str_replace( ' ', 'T', (string) $t ), 0, 16 );
	if ( ( ! empty( $v['from'] ) && $now < $at( $v['from'] ) ) || ( ! empty( $v['until'] ) && $now >= $at( $v['until'] ) ) ) {
		return false;
	}
	return true;
}

add_filter( 'render_block', function ( $html, $block ) {
	$v = $block['attrs']['marccentVisibility'] ?? null;
	if ( ! $v || ! is_array( $v ) || '' === $html || is_admin() ) {
		return $html;
	}
	if ( ! marccent_visible( $v ) ) {
		return '';
	}
	$hide = array_intersect( (array) ( $v['hide'] ?? array() ), array( 'mobile', 'tablet', 'desktop' ) );
	if ( $hide ) {
		$tags = new WP_HTML_Tag_Processor( $html );
		if ( $tags->next_tag() ) {
			foreach ( $hide as $size ) {
				$tags->add_class( "hide-$size" );
			}
			$html = $tags->get_updated_html();
		}
		$GLOBALS['marccent_hides'] = true;
	}
	return $html;
}, 6, 2 );

// The screen-size rules, on pages that hide something (a block theme renders the blocks before the head).
add_action( 'wp_enqueue_scripts', function () {
	if ( ! empty( $GLOBALS['marccent_hides'] ) ) {
		wp_add_inline_style( 'marccent', MARCCENT_HIDE_CSS );
	}
}, 20 );

// The editor's extras: the Visibility panel and copying a block's style (plain JS on WordPress's packages, no build).
add_action( 'enqueue_block_editor_assets', function () {
	wp_enqueue_script( 'marccent-editor-extras', plugins_url( 'assets/editor-extras.js', MARCCENT_BLOCKS_FILE ), array( 'wp-block-editor', 'wp-blocks', 'wp-components', 'wp-compose', 'wp-data', 'wp-element', 'wp-hooks', 'wp-notices', 'wp-plugins' ), filemtime( MARCCENT_BLOCKS_DIR . '/assets/editor-extras.js' ), true );
} );
