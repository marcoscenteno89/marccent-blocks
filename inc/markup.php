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

/*
 * A link's URL, escaped: empty for a scheme that isn't allowed (javascript:…). A link that is only a {{ token }}
 * ({{ link }}, as the editor saves it, %7B%7B link %7D%7D, too) is kept as it is: Marccent Content fills it as the
 * block renders, through esc_url_raw, and esc_url here would mangle it into http://…. Tokens inside a URL
 * (https://maps…?q={{ lat }},{{ lng }}) are URL-encoded first, as the editor saves them, so esc_url keeps them.
 */
function marccent_blocks_href( string $href ): string {
	if ( preg_match( '/^\s*(\{\{[^{}]*\}\}|%7B%7B.*?%7D%7D)\s*$/i', $href ) ) {
		return esc_attr( trim( $href ) );
	}
	return esc_url( preg_replace_callback( '/\{\{([^{}]*)\}\}/', fn( $m ) => '%7B%7B' . rawurlencode( trim( $m[1] ) ) . '%7D%7D', $href ) );
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
	$label = marccent_blocks_text( (string) ( $b['label'] ?? '' ) );
	$href  = marccent_blocks_href( (string) ( $b['href'] ?? '' ) );
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
		'class'      => marccent_blocks_cx( 'wrap wrap-fluid', $image ? 'stack' : $a['surface'] ?? '', marccent_blocks_divider_classes( $a ) ),
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
	$page     = '' === (string) ( $a['surface'] ?? '' );
	$dividers = marccent_blocks_divider( (array) ( $a['dividerTop'] ?? array() ), 'top', $page ) . marccent_blocks_divider( (array) ( $a['dividerBottom'] ?? array() ), 'bottom', $page );
	return sprintf( '<%1$s %2$s>%3$s%4$s<div %5$s>%6$s</div></%1$s>', $tag, $attributes( $outer ), $dividers, $background, marccent_blocks_attributes( $content ), $inner );
}

/*
 * Section dividers (Divider in marccent-theme's components/widgets/divider.js): a shape on the section's top or bottom
 * edge, in its color, reaching into the neighbor. The shapes, as their layers' CSS masks, are the package's
 * markup/dividers.json (copied to build/ by webpack.config.js). A divider is { shape, size, motion, scroll, flip };
 * a motion or scroll effect the shape doesn't take is left out.
 */
function marccent_blocks_dividers(): array {
	static $shapes;
	return $shapes ??= json_decode( (string) @file_get_contents( MARCCENT_BLOCKS_DIR . '/build/dividers.json' ), true ) ?: array();
}

function marccent_blocks_divider_size( array $d ): string {
	return in_array( $d['size'] ?? '', array( 'sm', 'lg' ), true ) ? $d['size'] : 'md';
}

// The section's classes for its dividers: it goes over its neighbors, which make room (divider.css).
function marccent_blocks_divider_classes( array $a ): string {
	$classes = array();
	foreach ( array( 'top' => 'dividerTop', 'bottom' => 'dividerBottom' ) as $edge => $key ) {
		$d = (array) ( $a[ $key ] ?? array() );
		if ( isset( marccent_blocks_dividers()[ $d['shape'] ?? '' ] ) ) {
			$classes[] = "has-divider-$edge-" . marccent_blocks_divider_size( $d );
		}
	}
	return implode( ' ', $classes );
}

function marccent_blocks_divider( array $d, string $edge, bool $page ): string {
	$def = marccent_blocks_dividers()[ $d['shape'] ?? '' ] ?? null;
	if ( ! $def ) {
		return '';
	}
	$size   = marccent_blocks_divider_size( $d );
	$motion = in_array( $d['motion'] ?? '', $def['motions'], true ) ? $d['motion'] : null;
	$scroll = in_array( $d['scroll'] ?? '', $def['scrolls'], true ) ? $d['scroll'] : null;
	$layers = '';
	foreach ( $def['layers'] as $l ) {
		$layers .= sprintf( '<i %s></i>', marccent_blocks_attributes( array(
			'style'  => '--mask:' . $l['mask'] . ( $l['opacity'] < 1 ? ';opacity:' . $l['opacity'] : '' ),
			'data-x' => 'parallax' === $scroll ? $l['x'] : null,
			'data-y' => 'parallax' === $scroll ? $l['y'] : null,
		) ) );
	}
	return sprintf( '<div %s>%s</div>', marccent_blocks_attributes( array(
		'aria-hidden'    => 'true',
		'class'          => marccent_blocks_cx( 'divider', "divider-$edge", 'md' === $size ? '' : "divider-$size", $page ? 'divider-page' : '' ),
		'data-component' => 'divider',
		'data-shape'     => $d['shape'],
		'data-motion'    => $motion,
		'data-scroll'    => $scroll,
		'data-flip'      => empty( $d['flip'] ) ? null : '',
	) ), $layers );
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
		. '<button type="button" data-component="toggle-button" data-icon="bars" data-active-icon="bars" class="btn nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="site-nav">' . marccent_blocks_icon( 'bars', 'btn-icon' ) . '</button>'
		. sprintf( '<div id="site-nav" class="nav-collapse"><nav data-component="menu" class="menu grid gap-0 auto-cols" style="--cols: %d" aria-label="Main">%s</nav>%s</div>', count( $items ), marccent_blocks_menu_items( $items ), $end )
		. $last . '</div></header>';
}

/*
 * The theme's interactive components (marccent-theme components/ui: accordion.js, alert.js, modal.js, popover.js), as
 * its markup reference has them; the plain-JS engine (core/components.js) wires them up on the page: a dialog opens
 * from anything with data-opens="<its id>", a popover from its popovertarget button, a tooltip from its trigger.
 * Text the editor stores as rich text (a header, a title, a trigger's label) is shown as plain text.
 */
function marccent_blocks_text( string $html ): string {
	return trim( html_entity_decode( wp_strip_all_tags( $html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

// An icon button's ✕ that closes its dialog ([data-close]).
function marccent_blocks_close_button( bool $labelled = false, string $classes = '' ): string {
	$label = $labelled ? 'Close' : '';
	return marccent_blocks_button( array( 'label' => $label, 'icon' => 'xmark', 'classes' => $classes ), fn( $attrs ) => marccent_blocks_attributes( $attrs + array( 'aria-label' => 'Close', 'data-close' => 'true' ) ) );
}

// Accordion: its items, already rendered (marccent_blocks_accordion_item).
function marccent_blocks_accordion( string $items, ?callable $attributes = null ): string {
	$attributes ??= 'marccent_blocks_attributes';
	return sprintf( '<div %s>%s</div>', $attributes( array( 'data-component' => 'accordion', 'class' => 'accordion grid cols-1 gap-0 divide border round' ) ), $items );
}

// An accordion's item: a <details> its header opens. Items that share a name open one at a time (none: several).
function marccent_blocks_accordion_item( string $header, string $content, ?string $name, bool $open, string $class = '' ): string {
	return sprintf(
		'<details %s><summary class="header pad-sm">%s</summary><div class="%s">%s</div></details>',
		marccent_blocks_attributes( array( 'data-component' => 'accordion-item', 'name' => $name, 'open' => $open ? '' : null ) ),
		esc_html( marccent_blocks_text( $header ) ),
		esc_attr( marccent_blocks_cx( 'pad-sm', $class ) ),
		$content
	);
}

// Alert: a message in a tone's fill (none when the classes already give one); danger interrupts (role alert).
function marccent_blocks_alert( string $tone, string $content, ?callable $attributes = null, string $classes = '' ): string {
	$attributes ??= 'marccent_blocks_attributes';
	$tone        = $tone ?: 'primary';
	$fill        = preg_match( '/\bbg-/', $classes ) ? '' : "bg-$tone";
	return sprintf( '<div %s>%s</div>', $attributes( array( 'data-component' => 'alert', 'role' => 'danger' === $tone ? 'alert' : 'status', 'class' => marccent_blocks_cx( 'pad border round', $fill ) ) ), $content );
}

// Modal: a <dialog> with its title, content and a Close button; the default look or the ticket one. Its id (which
// data-opens names; the theme's React Modal opens from state and has none) and its title's id (titleId, else
// "<id>-title").
function marccent_blocks_modal( array $m, string $content, ?callable $attributes = null ): string {
	$attributes ??= 'marccent_blocks_attributes';
	$id          = $m['id'] ?? null;
	$title_id    = (string) ( $m['titleId'] ?? "$id-title" );
	$title       = esc_html( marccent_blocks_text( (string) ( $m['title'] ?? '' ) ) );
	$ticket      = 'ticket' === ( $m['variant'] ?? '' );
	$panel       = $ticket
		? sprintf(
			'<div data-component="modal-panel" class="grid cols-1 gap-0"><div class="header pad bg-gradient text-center"><h2 id="%1$s" class="h4">%2$s</h2></div><div class="modal-neck bg-gradient"></div><div class="modal-flare bg"></div><div class="bg pad-x grid cols-1">%3$s</div><div class="bg pad grid auto-cols cols-center gap-sm">%4$s</div></div>',
			esc_attr( $title_id ), $title, $content, marccent_blocks_close_button( true, 'gradient' )
		)
		: sprintf(
			'<div data-component="modal-panel" class="grid cols-1 gap-0 divide"><div class="header pad"><h2 id="%1$s" class="h5">%2$s</h2>%3$s</div><div class="pad grid cols-1">%4$s</div><div class="grid auto-cols items-center gap-sm pad" style="justify-content:end">%5$s</div></div>',
			esc_attr( $title_id ), $title, marccent_blocks_close_button(), $content, marccent_blocks_close_button( true )
		);
	return sprintf( '<dialog %s>%s</dialog>', $attributes( array( 'id' => $id, 'data-component' => 'modal', 'class' => marccent_blocks_cx( 'modal', $ticket ? 'ticket' : '' ), 'aria-labelledby' => $title_id ) ), $panel );
}

// Drawer: a <dialog> that slides in from the start or the end, its title and ✕ on top and its content scrolling.
function marccent_blocks_drawer( array $d, string $content, ?callable $attributes = null ): string {
	$attributes ??= 'marccent_blocks_attributes';
	$id          = $d['id'] ?? null;
	$title_id    = (string) ( $d['titleId'] ?? "$id-title" );
	return sprintf(
		'<dialog %s><div class="grid cols-1 gap-0 divide h-100" style="grid-template-rows:auto minmax(0, 1fr);grid-auto-rows:auto"><div class="header pad"><h2 id="%s" class="h5">%s</h2>%s</div><div class="pad grid cols-1 content-start overflow-auto">%s</div></div></dialog>',
		$attributes( array( 'id' => $id, 'data-component' => 'drawer', 'data-side' => 'start' === ( $d['side'] ?? '' ) ? 'start' : 'end', 'class' => 'modal drawer', 'aria-labelledby' => $title_id ) ),
		esc_attr( $title_id ),
		esc_html( marccent_blocks_text( (string) ( $d['title'] ?? '' ) ) ),
		marccent_blocks_close_button(),
		$content
	);
}

// The button that opens a dialog: the theme's Button with data-opens naming it.
function marccent_blocks_opener( array $b, string $dialog ): string {
	return marccent_blocks_button( $b, fn( $attrs ) => marccent_blocks_attributes( $attrs + array( 'data-opens' => $dialog, 'aria-haspopup' => 'dialog' ) ) );
}

// Tooltip: a hint beside its trigger (a text link) on hover or focus.
function marccent_blocks_tooltip( array $t ): string {
	$id = (string) $t['id'];
	return sprintf(
		'<button type="button" class="btn-link" id="%1$s-anchor" aria-describedby="%1$s">%2$s</button><div id="%1$s" role="tooltip" popover="manual" data-component="tooltip" data-placement="%3$s" class="float tooltip">%4$s</div>',
		esc_attr( $id ),
		esc_html( marccent_blocks_text( (string) ( $t['trigger'] ?? '' ) ) ),
		esc_attr( marccent_blocks_side( $t['side'] ?? 'top' ) ),
		esc_html( marccent_blocks_text( (string) ( $t['content'] ?? '' ) ) )
	);
}

// Popover: a panel its button opens and closes (the browser's popover), beside it.
function marccent_blocks_popover( array $p, string $content ): string {
	$id = (string) $p['id'];
	return sprintf(
		'<button type="button" class="btn" id="%1$s-anchor" popovertarget="%1$s">%2$s</button><div id="%1$s" popover="auto" role="group" aria-label="%3$s" data-component="popover" data-placement="%4$s" class="float popover bg border round shadow pad-sm">%5$s</div>',
		esc_attr( $id ),
		esc_html( marccent_blocks_text( (string) ( $p['trigger'] ?? '' ) ) ),
		esc_attr( marccent_blocks_text( (string) ( $p['label'] ?? '' ) ?: (string) ( $p['trigger'] ?? '' ) ) ),
		esc_attr( marccent_blocks_side( $p['side'] ?? 'bottom' ) ),
		$content
	);
}

function marccent_blocks_side( string $side ): string {
	return in_array( $side, array( 'top', 'bottom', 'start', 'end' ), true ) ? $side : 'top';
}
