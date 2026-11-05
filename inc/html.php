<?php
/**
 * The Custom HTML block (marccent/html) keeps its code in its attributes, which WordPress's HTML filter (kses) doesn't
 * read: so, as for WordPress's own Custom HTML, only people who may post unfiltered HTML can save it. Anyone else's
 * save empties its html, css and js, wherever it is (posts, pages, templates, patterns, block widgets; nested in other blocks).
 */

defined( 'ABSPATH' ) || exit;

// The blocks with every marccent/html emptied, at any depth.
function marccent_blocks_strip_html( array $blocks ): array {
	foreach ( $blocks as &$b ) {
		if ( 'marccent/html' === $b['blockName'] ) {
			$b['attrs'] = array_diff_key( $b['attrs'], array_flip( array( 'html', 'css', 'js' ) ) );
		}
		if ( $b['innerBlocks'] ) {
			$b['innerBlocks'] = marccent_blocks_strip_html( $b['innerBlocks'] );
		}
	}
	return $blocks;
}

add_filter( 'wp_insert_post_data', function ( $data ) {
	if ( current_user_can( 'unfiltered_html' ) ) {
		return $data;
	}
	$content = wp_unslash( $data['post_content'] );
	// The parser takes any whitespace after "<!--" (a newline, two spaces): so does this test, or they'd get past it.
	if ( preg_match( '#<!--\s+wp:marccent/html\b#', $content ) ) {
		$data['post_content'] = wp_slash( serialize_blocks( marccent_blocks_strip_html( parse_blocks( $content ) ) ) );
	}
	return $data;
}, 20 );

// Block widgets aren't posts: their content is filtered (kses) on save, but not a block's attributes. Same rule.
add_filter( 'widget_update_callback', function ( $instance, $new, $old, $widget ) {
	if ( $widget instanceof WP_Widget_Block && ! current_user_can( 'unfiltered_html' ) && isset( $instance['content'] )
		&& preg_match( '#<!--\s+wp:marccent/html\b#', $instance['content'] ) ) {
		$instance['content'] = serialize_blocks( marccent_blocks_strip_html( parse_blocks( $instance['content'] ) ) );
	}
	return $instance;
}, 20, 4 );
