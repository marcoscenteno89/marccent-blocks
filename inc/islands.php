<?php
/**
 * Islands: blocks the theme's React components draw (src/runtime/islands/<name>.js; the runtime loads one only where
 * a page has it). The server writes the block's element with data-island and its props as JSON, and inside it, in
 * [data-slot], HTML that stands in until the component has rendered (the page reads, and is indexed, without
 * JavaScript): the block's inner blocks, one per item (a tab, a slide), or a plain rendering of its props.
 */

defined( 'ABSPATH' ) || exit;

function marccent_blocks_island( WP_Block $block, string $name, array $props, string $slot = '', array $extra = array() ): string {
	return sprintf(
		'<div %s><div data-slot="">%s</div></div>',
		marccent_blocks_wrapper( $block, array( 'data-island' => $name, 'data-props' => wp_json_encode( (object) $props, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ) + $extra ),
		$slot
	);
}

/*
 * Props as plain HTML, for a data island's slot: text as paragraphs (a title as a heading), images and links as such,
 * lists as lists, in their order. Not the component's look: what it says, until it's drawn.
 */
function marccent_blocks_island_text( $props, int $depth = 0 ): string {
	if ( ! is_array( $props ) ) {
		return '';
	}
	$out  = '';
	$list = array_is_list( $props );
	foreach ( $props as $key => $value ) {
		if ( is_array( $value ) ) {
			$inner = marccent_blocks_island_text( $value, $depth + 1 );
			$out  .= $inner ? ( $list ? "<li>$inner</li>" : ( array_is_list( $value ) ? "<ul>$inner</ul>" : "<div>$inner</div>" ) ) : '';
		} elseif ( is_string( $value ) && '' !== trim( $value ) ) {
			if ( in_array( $key, array( 'src', 'image', 'before', 'after' ), true ) ) {
				$out .= sprintf( '<img src="%s" alt="" loading="lazy">', esc_url( $value ) );
			} elseif ( 'href' === $key ) {
				$out .= sprintf( '<a href="%1$s">%1$s</a>', esc_url( $value ) );
			} elseif ( in_array( $key, array( 'icon', 'overlay', 'align', 'ratio', 'position', 'shape', 'lang', 'kind', 'side', 'format', 'canvas', 'colors', 'variant' ), true ) ) {
				continue; // how it looks, not what it says
			} else {
				$out .= sprintf( 'title' === $key && ! $depth ? '<h3>%s</h3>' : '<p>%s</p>', esc_html( $value ) );
			}
		} elseif ( ( is_int( $value ) || is_float( $value ) ) && in_array( $key, array( 'value', 'fill', 'max' ), true ) ) {
			$out .= sprintf( '<p>%s</p>', esc_html( (string) $value ) );
		}
	}
	return $list && $out && ! $depth ? "<ul>$out</ul>" : $out;
}

/*
 * Props as plain HTML beside an island's blocks in its slot (a card's text before its buttons): marked, so the island
 * leaves it out of the blocks it shows (shared/html.js).
 */
function marccent_blocks_island_stand_in( array $props ): string {
	$text = marccent_blocks_island_text( $props );
	return $text ? '<div data-island-text="">' . $text . '</div>' : '';
}
