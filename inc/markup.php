<?php
/**
 * The theme's markup, written by the server: what the blocks render (build/blocks/<name>/render.php). The Button and
 * Icon here are the theme's components (components/ui/button.js, icon.js) in PHP; tools/markup-test.php checks them
 * against the theme's markup reference (marccent-theme/markup/reference.json), so they can't drift apart unnoticed.
 */

defined( 'ABSPATH' ) || exit;

// Joins class names, skipping empty ones (the theme's lib/cx.js).
function marccent_blocks_cx( ...$classes ): string {
	return implode( ' ', array_filter( array_map( 'trim', array_map( 'strval', $classes ) ) ) );
}

// Attributes from an array, escaped; null and false leave one out (what a block gets from get_block_wrapper_attributes).
function marccent_blocks_attributes( array $attributes ): string {
	$out = array();
	foreach ( $attributes as $name => $value ) {
		if ( null !== $value && false !== $value ) {
			$out[] = sprintf( '%s="%s"', $name, esc_attr( (string) $value ) );
		}
	}
	return implode( ' ', $out );
}

/**
 * Icon: a Font Awesome name ("phone", solid) or full classes ("fa-brands fa-github"), like Button's icon prop; or
 * name and family apart, like the Icon component.
 */
function marccent_blocks_icon( string $icon, string $class = '', string $family = 'fa-solid' ): string {
	$classes = str_starts_with( $icon, 'fa-' ) ? $icon : marccent_blocks_cx( $family, $icon ? "fa-$icon" : '' );
	return sprintf( '<i data-component="icon" class="%s" aria-hidden="true"></i>', esc_attr( marccent_blocks_cx( $classes, $class ) ) );
}

/**
 * Button: a link (or a <button> without an href) with an icon and its label, which is also its accessible name, since
 * it hides when the button is narrow; without an icon a text link.
 *
 * @param array    $b          label, href, icon (a name or full classes; empty: a text link), classes, newTab, trace.
 * @param callable $attributes Turns the element's attributes into HTML (default marccent_blocks_attributes): a block
 *                            passes its wrapper's (marccent_blocks_wrapper).
 */
function marccent_blocks_button( array $b, ?callable $attributes = null ): string {
	$attributes ??= 'marccent_blocks_attributes';
	// The label may come from a RichText (HTML): its plain text.
	$label = trim( html_entity_decode( wp_strip_all_tags( (string) ( $b['label'] ?? '' ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
	$href  = esc_url( (string) ( $b['href'] ?? '' ) ); // empty for a scheme that isn't allowed (javascript:…)
	$tag   = $href ? 'a' : 'button';
	$attrs = array_merge(
		$href ? array( 'href' => $href ) : array( 'type' => 'button' ),
		array( 'class' => marccent_blocks_cx( empty( $b['icon'] ) ? 'btn-link' : 'btn', $b['classes'] ?? '' ) ),
		! empty( $b['icon'] ) && '' !== $label ? array( 'aria-label' => $label, 'title' => $label ) : array(),
		array( 'data-component' => 'button' ),
		! empty( $b['newTab'] ) && $href ? array( 'target' => '_blank', 'rel' => 'noopener noreferrer' ) : array()
	);
	if ( empty( $b['icon'] ) ) {
		return sprintf( '<%1$s %2$s>%3$s</%1$s>', $tag, $attributes( $attrs ), esc_html( $label ) );
	}
	return sprintf(
		'<%1$s %2$s>%3$s%4$s%5$s</%1$s>',
		$tag,
		$attributes( $attrs ),
		marccent_blocks_icon( (string) $b['icon'], 'btn-icon' ),
		'' !== $label ? '<span class="btn-text">' . esc_html( $label ) . '</span>' : '',
		! empty( $b['trace'] ) ? '<svg data-component="trace" class="trace" aria-hidden="true"><rect pathLength="100"></rect></svg>' : ''
	);
}

/*
 * A block's wrapper attributes (get_block_wrapper_attributes: its class, anchor…) with these; empty ones left out. A
 * post saved before the block rendered on the server keeps its anchor in the old markup (the block's own HTML), not in
 * its attributes: it's read from there.
 */
function marccent_blocks_wrapper( WP_Block $block, array $extra ): string {
	$extra = array_filter( $extra, fn( $v ) => null !== $v && false !== $v && '' !== $v );
	if ( empty( $block->attributes['anchor'] ) && trim( $block->inner_html ) ) {
		$tags = new WP_HTML_Tag_Processor( $block->inner_html );
		if ( $tags->next_tag() && is_string( $tags->get_attribute( 'id' ) ) ) {
			$extra['id'] = $tags->get_attribute( 'id' );
		}
	}
	return get_block_wrapper_attributes( $extra );
}

// A block's inner blocks, rendered (not $content: a post saved before the block rendered on the server still has
// the old static markup around them).
function marccent_blocks_inner( WP_Block $block ): string {
	$html = '';
	foreach ( $block->inner_blocks as $inner ) {
		$html .= $inner->render();
	}
	return $html;
}

// Inline styles from an array of property => value; empty values left out, the rest filtered as WordPress does.
function marccent_blocks_css( array $style ): ?string {
	$css = array();
	foreach ( $style as $prop => $value ) {
		$rule = '' === (string) $value ? '' : safecss_filter_attr( "$prop: $value" );
		if ( $rule ) {
			$css[] = $rule;
		}
	}
	return $css ? implode( '; ', $css ) : null;
}
