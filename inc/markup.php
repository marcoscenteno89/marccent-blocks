<?php
/**
 * The theme's markup, written by the server: what the blocks render (build/blocks/<name>/render.php). The Button,
 * Icon, Section, Grid, Card and NavBar here are the theme's components in PHP; tools/markup-test.php checks them
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

/*
 * Section, Grid and Card: the theme's components (components/widgets/section.js) in PHP, from a block's attributes,
 * around $inner (HTML). $attributes turns the root's attributes into HTML, as for Button.
 */
function marccent_blocks_section( array $a, string $inner, ?callable $attributes = null ): string {
	$attributes ??= 'marccent_blocks_attributes';
	$tag   = in_array( $a['tagName'] ?? '', array( 'section', 'div', 'header', 'footer', 'aside' ), true ) ? $a['tagName'] : 'section';
	$image = (string) ( $a['imageUrl'] ?? '' );
	$min   = (string) ( $a['minHeight'] ?? '' );
	// A least height makes the band a one-cell grid, so its content box can center in it (not .grid: that brings columns).
	$outer = array(
		'data-theme' => in_array( $a['mode'] ?? '', array( 'light', 'dark' ), true ) ? $a['mode'] : null,
		'class'      => marccent_blocks_cx( 'wrap wrap-fluid', $image ? 'stack' : $a['surface'] ?? '' ),
		'style'      => $min ? marccent_blocks_css( array( 'min-height' => $min ) + ( $image ? array() : array( 'display' => 'grid', 'grid-template-columns' => 'minmax(0, 1fr)' ) ) ) : null,
	);
	// Over an image: the content on top of the image and shade, centered in the band, in light text.
	$content = array(
		'class' => marccent_blocks_cx( $a['width'] ?? 'wrap', $a['padY'] ?? 'pad-y-lg', 'grid cols-1', $a['gap'] ?? '', ! empty( $a['center'] ) ? 'text-center justify-center' : '', $image ? $a['surface'] ?? '' : '' ),
		'style' => $image ? 'position: relative; align-content: center; color: #fff' : ( $min ? 'align-content: center' : null ),
	);
	$background = $image ? sprintf(
		'<img class="cover-img" src="%s" alt="%s" loading="lazy" decoding="async"><div aria-hidden="true" style="background: rgb(0 0 0 / %d%%)"></div>',
		esc_url( $image ),
		esc_attr( (string) ( $a['imageAlt'] ?? '' ) ),
		(int) ( $a['shade'] ?? 50 )
	) : '';
	return sprintf( '<%1$s %2$s>%3$s<div %4$s>%5$s</div></%1$s>', $tag, $attributes( $outer ), $background, marccent_blocks_attributes( $content ), $inner );
}

function marccent_blocks_grid( array $a, string $inner, ?callable $attributes = null ): string {
	$attributes ??= 'marccent_blocks_attributes';
	$layout = (string) ( $a['layout'] ?? 'cols' );
	$equal  = in_array( $layout, array( 'cols', 'fixed' ), true );
	$cols   = 'cols-' . (int) ( $a['cols'] ?? 3 );
	$shape  = $equal ? marccent_blocks_cx( 'grid', 'fixed' === $layout ? 'fixed' : '', $cols ) : ( 'row-wrap' === $layout ? 'row-wrap' : marccent_blocks_cx( 'grid', $layout ) );
	$attrs  = array(
		'class' => marccent_blocks_cx( $shape, $a['gap'] ?? '', $a['alignItems'] ?? '', ! empty( $a['center'] ) ? 'cols-center justify-center text-center' : '' ),
		'style' => $equal ? marccent_blocks_css( array( '--min' => $a['min'] ?? '' ) ) : null,
	);
	return sprintf( '<div %s>%s</div>', $attributes( $attrs ), $inner );
}

function marccent_blocks_card( array $a, string $inner, ?callable $attributes = null ): string {
	$attributes ??= 'marccent_blocks_attributes';
	$layout = ! empty( $a['rows'] ) ? 'sub rows-' . (int) $a['rows'] : marccent_blocks_cx( 'grid cols-1 content-start', $a['gap'] ?? 'gap-sm' );
	$class  = marccent_blocks_cx( $a['surface'] ?? 'bg border', ( $a['round'] ?? true ) ? 'round' : '', $a['pad'] ?? 'pad', $layout, $a['hover'] ?? '', ! empty( $a['center'] ) ? 'text-center justify-center' : '' );
	return sprintf( '<article %s>%s</article>', $attributes( array( 'class' => $class ) ), $inner );
}

/*
 * The theme's Menu (components/ui/menu.js) from menu items ({ name, href, children }, as marccent_blocks_menu_tree
 * gives them): a link, or a dropdown for an item with children, a submenu inside another.
 */
function marccent_blocks_menu_items( array $items, bool $sub = false ): string {
	$html = '';
	foreach ( $items as $item ) {
		if ( ! empty( $item['children'] ) ) {
			$html .= sprintf(
				'<details data-component="menu-dropdown" class="%s"><summary class="menu-link"><span class="text-truncate">%s</span></summary><div class="menu-panel bg border round shadow pad grid cols-1 gap-sm">%s</div></details>',
				$sub ? 'menu-dropdown menu-sub' : 'menu-dropdown',
				esc_html( $item['name'] ),
				marccent_blocks_menu_items( $item['children'], true )
			);
		} else {
			$html .= sprintf( '<a data-component="menu-link" class="menu-link" href="%s"><span class="text-truncate">%s</span></a>', esc_url( $item['href'] ?? '' ), esc_html( $item['name'] ) );
		}
	}
	return $html;
}

/**
 * NavBar: the theme's (components/widgets/navbar.js) header bar, its main menu folding behind a toggle.
 *
 * @param array  $brand name, href, logo (a URL, or empty), logoSize ([ width, height ], optional), showName (default true).
 * @param array  $items The main menu (marccent_blocks_menu_items).
 * @param string $end   HTML that folds with the menu (its root .nav-end).
 * @param string $last  HTML always at the row's end (its root .col-last).
 * @param string $top   HTML in a row above the bar.
 */
function marccent_blocks_navbar( array $brand, array $items, string $end = '', string $last = '', string $top = '' ): string {
	$size = $brand['logoSize'] ?? null;
	$logo = empty( $brand['logo'] ) ? '' : sprintf(
		'<img src="%s" alt="%s"%s style="height: 2.5rem; width: auto">',
		esc_url( $brand['logo'] ),
		esc_attr( $brand['name'] ),
		$size ? sprintf( ' width="%d" height="%d"', $size[0], $size[1] ) : ''
	);
	$name = ( $brand['showName'] ?? true ) ? '<span class="fw-bold">' . esc_html( $brand['name'] ) . '</span>' : '';
	return '<header data-component="nav-bar" class="nav wrap wrap-fluid pad-y-sm bg border-bottom sticky-top grid cols-1 gap-sm">' . $top
		. '<div class="wrap pad-0 grid layout-ends items-center gap-sm">'
		. sprintf( '<a href="%s" class="menu-link grid auto-cols items-center gap-sm" aria-label="%s">%s%s</a>', esc_url( $brand['href'] ), esc_attr( $brand['name'] . ', home' ), $logo, $name )
		. '<button type="button" data-component="toggle-button" data-icon="bars-staggered" data-active-icon="xmark" class="btn nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="site-nav">' . marccent_blocks_icon( 'bars-staggered', 'btn-icon' ) . '</button>'
		. sprintf( '<div id="site-nav" class="nav-collapse"><nav data-component="menu" class="menu grid gap-0 auto-cols divide" style="--cols: %d" aria-label="Main">%s</nav>%s</div>', count( $items ), marccent_blocks_menu_items( $items ), $end )
		. $last . '</div></header>';
}
