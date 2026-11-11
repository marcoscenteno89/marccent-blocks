<?php
/**
 * The theme's markup, written by the server: what the blocks render (build/blocks/<name>/render.php). The Button,
 * Icon, Section, Grid, Card and Menu here are the theme's components in PHP; tools/markup-test.php checks them
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

// The same, for a URL that isn't written into HTML (an island's props, sent as JSON): not HTML-escaped, and its tokens
// left as written, for Marccent Content to fill there. They're set aside while esc_url_raw checks the rest's scheme
// (javascript:{{ x }} comes back empty), then put back.
function marccent_blocks_href_raw( string $href ): string {
	if ( preg_match( '/^\s*(\{\{[^{}]*\}\}|%7B%7B.*?%7D%7D)\s*$/i', $href ) ) {
		return trim( $href );
	}
	$tokens = array();
	$bare   = preg_replace_callback( '/\{\{[^{}]*\}\}|%7B%7B.*?%7D%7D/i', function ( $m ) use ( &$tokens ) {
		$tokens[] = $m[0];
		return 'mcctoken' . ( count( $tokens ) - 1 ) . 'x';
	}, $href );
	return preg_replace_callback( '/mcctoken(\d+)x/', fn( $m ) => $tokens[ (int) $m[1] ] ?? '', esc_url_raw( $bare ) );
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
 * The theme's ToggleButton (components/ui/toggle-button.js) as a disclosure: a button that opens and closes what it
 * controls (aria-expanded, aria-controls), its icon swapping to activeIcon while open (the plain-JS engine does it).
 */
function marccent_blocks_toggle_button( array $t ): string {
	$label  = marccent_blocks_text( (string) ( $t['label'] ?? '' ) );
	$icon   = (string) ( $t['icon'] ?? '' );
	$active = (string) ( ( $t['activeIcon'] ?? '' ) ?: $icon );
	$open   = ! empty( $t['open'] );
	return sprintf(
		'<button %s>%s%s</button>',
		marccent_blocks_attributes( array_filter( array(
			'type'             => 'button',
			'class'            => marccent_blocks_cx( 'btn', $t['classes'] ?? '' ),
			'aria-label'       => $label,
			'title'            => $label,
			'data-component'   => 'toggle-button',
			'data-icon'        => $icon,
			'data-active-icon' => $active,
			'aria-expanded'    => $open ? 'true' : 'false',
			'aria-controls'    => (string) ( $t['controls'] ?? '' ),
		), fn( $v ) => '' !== $v ) ),
		marccent_blocks_icon( $open ? $active : $icon, 'btn-icon' ),
		'' !== $label ? '<span class="btn-text">' . esc_html( $label ) . '</span>' : ''
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
 * Section, Grid and Card: the theme's components (components/widgets/section.js) in PHP, from a block's attributes
 * (the settings' full-word names: verticalPadding, columns, padding; markup/settings.json), around $inner (HTML). $attributes turns the root's attributes into HTML, as for Button.
 */
function marccent_blocks_section( array $a, string $inner, ?callable $attributes = null ): string {
	$attributes ??= 'marccent_blocks_attributes';
	$tag   = in_array( $a['as'] ?? '', array( 'section', 'div', 'header', 'footer', 'aside' ), true ) ? $a['as'] : 'section';
	$image = (string) ( $a['image'] ?? '' );
	$min   = (string) ( $a['minHeight'] ?? '' );
	// A least height makes the band a one-cell grid, so its content box can center in it (not .grid: that brings columns).
	$outer = array(
		'data-theme' => in_array( $a['mode'] ?? '', array( 'light', 'dark' ), true ) ? $a['mode'] : null,
		'class'      => marccent_blocks_cx( 'wrap wrap-fluid', $image ? 'stack' : $a['surface'] ?? '', marccent_blocks_divider_classes( $a ) ),
		'style'      => $min ? marccent_blocks_css( array( 'min-height' => $min ) + ( $image ? array() : array( 'display' => 'grid', 'grid-template-columns' => 'minmax(0, 1fr)' ) ) ) : null,
	);
	// Over an image: the content on top of the image and shade, centered in the band, in light text.
	$content = array(
		'class' => marccent_blocks_cx( $a['width'] ?? 'wrap', $a['verticalPadding'] ?? 'pad-y-lg', 'grid cols-1', $a['gap'] ?? '', ! empty( $a['center'] ) ? 'text-center justify-center' : '', $image ? $a['surface'] ?? '' : '' ),
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
	$cols   = 'cols-' . (int) ( $a['columns'] ?? 3 );
	$shape  = $equal ? marccent_blocks_cx( 'grid', 'fixed' === $layout ? 'fixed' : '', $cols ) : ( 'row-wrap' === $layout ? 'row-wrap' : marccent_blocks_cx( 'grid', $layout ) );
	$attrs  = array(
		'class' => marccent_blocks_cx( $shape, $a['gap'] ?? '', $a['alignItems'] ?? '', ! empty( $a['center'] ) ? 'cols-center justify-center text-center' : '', ! empty( $a['dense'] ) ? 'dense' : '', ! empty( $a['minimumRowHeight'] ) ? 'rows-min' : '' ),
		'style' => marccent_blocks_css( array( '--min' => $equal ? ( $a['minimumColumnWidth'] ?? '' ) : '', '--row' => $a['minimumRowHeight'] ?? '' ) ) ?: null,
	);
	return sprintf( '<div %s>%s</div>', $attributes( $attrs ), $inner );
}

function marccent_blocks_card( array $a, string $inner, ?callable $attributes = null ): string {
	$attributes ??= 'marccent_blocks_attributes';
	$layout = ! empty( $a['rows'] ) ? 'sub rows-' . (int) $a['rows'] : marccent_blocks_cx( 'grid cols-1 content-start', $a['gap'] ?? 'gap-sm' );
	$class  = marccent_blocks_cx( $a['surface'] ?? 'bg border', ( $a['round'] ?? true ) ? 'round' : '', $a['padding'] ?? 'pad', $layout, $a['hover'] ?? '', ! empty( $a['center'] ) ? 'text-center justify-center' : '', ! empty( $a['variant'] ) ? 'variant-' . sanitize_html_class( $a['variant'] ) : '' );
	return sprintf( '<article %s>%s</article>', $attributes( array( 'class' => $class ) ), $inner );
}

/*
 * The theme's Menu (components/ui/menu.js): its <nav> (a label for screen readers; layout replaces "fixed", equal
 * columns, as "auto-cols cols-center" or "cols-1"; icon only: the links show only their icons, named by aria-label)
 * around its items. Folded (fold): behind a menu button where there's no room, ToggleButton's fold (.toggle-fold,
 * button.css), the menu (.menu-fold, menu.css) in its panel; id: the panel's (the reference writes id-1).
 */
function marccent_blocks_menu( array $p ): string {
	if ( ! empty( $p['fold'] ) ) {
		$id    = $p['id'] ?? wp_unique_id( 'menu-fold-' );
		$label = ( $p['label'] ?? '' ) . ' menu';
		return sprintf(
			'<div class="toggle-fold"><button type="button" class="btn menu-fold-toggle" data-component="toggle-button" data-icon="bars" data-active-icon="xmark" aria-expanded="false" aria-controls="%1$s" aria-label="%2$s" id="%1$s-toggle" popovertarget="%1$s">%3$s</button><div id="%1$s" data-component="toggle-panel" popover="auto" role="group" aria-label="%2$s" class="toggle-panel bg border round shadow pad-sm">%4$s</div></div>',
			esc_attr( $id ),
			esc_attr( $label ),
			marccent_blocks_icon( 'bars', 'btn-icon' ),
			marccent_blocks_menu( array( 'fold' => false, 'classes' => marccent_blocks_cx( 'menu-fold', $p['classes'] ?? '' ) ) + $p )
		);
	}
	// --cols only for the fixed layout (one column per item); any other layout sets its own columns (.cols-1, auto-cols).
	$layout = ( $p['layout'] ?? '' ) ?: 'fixed';
	return sprintf(
		'<nav data-component="menu" class="%s"%s aria-label="%s">%s</nav>',
		esc_attr( marccent_blocks_cx( 'menu grid', $p['gap'] ?? 'gap-sm', $layout, $p['classes'] ?? '' ) ),
		preg_match( '/\bfixed\b/', $layout ) ? sprintf( ' style="--cols:%d"', count( $p['items'] ) ) : '',
		esc_attr( $p['label'] ?? '' ),
		marccent_blocks_menu_items( $p['items'], false, ! empty( $p['iconOnly'] ) )
	);
}

/*
 * The theme's Menu's items ({ name, href, icon?, label?, external?, children? }, as marccent_blocks_menu_tree gives
 * them): a link, or a dropdown for an item with children, a submenu inside another.
 */
function marccent_blocks_menu_items( array $items, bool $sub = false, bool $icon_only = false ): string {
	$html = '';
	foreach ( $items as $item ) {
		$icon = ! empty( $item['icon'] ) ? sprintf( '<i class="%s" aria-hidden="true"></i>', esc_attr( $item['icon'] ) ) : '';
		if ( ! empty( $item['children'] ) ) {
			$html .= sprintf(
				'<details data-component="menu-dropdown" class="%s"><summary class="menu-link caret">%s<span class="text-truncate">%s</span></summary><div class="menu-panel bg border round shadow pad grid cols-1 gap-sm">%s</div></details>',
				$sub ? 'menu-dropdown menu-sub' : 'menu-dropdown',
				$icon,
				esc_html( $item['name'] ),
				marccent_blocks_menu_items( $item['children'], true )
			);
		} else {
			$html .= sprintf(
				'<a data-component="menu-link" class="menu-link" href="%s"%s%s>%s%s</a>',
				esc_url( $item['href'] ?? '' ),
				$icon_only ? sprintf( ' aria-label="%s" title="%s"', esc_attr( $item['label'] ?? $item['name'] ), esc_attr( $item['name'] ) ) : '',
				! empty( $item['external'] ) ? ' target="_blank" rel="noopener noreferrer"' : '',
				$icon,
				$icon_only ? '' : sprintf( '<span class="text-truncate">%s</span>', esc_html( $item['name'] ) )
			);
		}
	}
	return $html;
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
		'<details %s><summary class="header pad-sm caret hover-muted focus-inset">%s</summary><div class="%s">%s</div></details>',
		marccent_blocks_attributes( array( 'data-component' => 'accordion-item', 'name' => $name, 'open' => $open ? '' : null ) ),
		esc_html( marccent_blocks_text( $header ) ),
		esc_attr( marccent_blocks_cx( 'pad-sm border-top', $class ) ),
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
			'<div data-component="modal-panel" class="grid cols-1 gap-0"><div class="header pad bg-gradient text-center"><h2 id="%1$s" class="h4">%2$s</h2></div><div class="notch-bottom bg-gradient"></div><div class="notch-top bg"></div><div class="bg pad-x grid cols-1">%3$s</div><div class="bg pad grid auto-cols cols-center gap-sm">%4$s</div></div>',
			esc_attr( $title_id ), $title, $content, marccent_blocks_close_button( true, 'gradient' )
		)
		: sprintf(
			'<div data-component="modal-panel" class="grid cols-1 gap-0 divide"><div class="header pad"><h2 id="%1$s" class="h5">%2$s</h2>%3$s</div><div class="pad grid cols-1">%4$s</div><div class="grid auto-cols cols-end items-center gap-sm pad">%5$s</div></div>',
			esc_attr( $title_id ), $title, marccent_blocks_close_button(), $content, marccent_blocks_close_button( true )
		);
	return sprintf( '<dialog %s>%s</dialog>', $attributes( array( 'id' => $id, 'data-component' => 'modal', 'class' => marccent_blocks_cx( 'modal', $ticket ? 'ticket' : 'bg-page border round shadow' ), 'aria-labelledby' => $title_id ) ), $panel );
}

// Drawer: a <dialog> that slides in from the start or the end, its title and ✕ on top and its content scrolling.
function marccent_blocks_drawer( array $d, string $content, ?callable $attributes = null ): string {
	$attributes ??= 'marccent_blocks_attributes';
	$id          = $d['id'] ?? null;
	$title_id    = (string) ( $d['titleId'] ?? "$id-title" );
	$start       = 'start' === ( $d['side'] ?? '' );
	return sprintf(
		'<dialog %s><div class="grid cols-1 rows-body gap-0 divide h-100"><div class="header pad"><h2 id="%s" class="h5">%s</h2>%s</div><div class="pad grid cols-1 content-start overflow-auto">%s</div></div></dialog>',
		$attributes( array( 'id' => $id, 'data-component' => 'drawer', 'data-side' => $start ? 'start' : 'end', 'class' => marccent_blocks_cx( 'modal drawer bg-page shadow', $start ? 'border-end' : 'border-start' ), 'aria-labelledby' => $title_id ) ),
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
		'<button type="button" class="btn-link" id="%1$s-anchor" aria-describedby="%1$s">%2$s</button><div id="%1$s" role="tooltip" popover="manual" data-component="tooltip" data-placement="%3$s" class="float bg-inverse round fs-sm pad-xs pad-x-sm pe-none shadow-sm">%4$s</div>',
		esc_attr( $id ),
		esc_html( marccent_blocks_text( (string) ( $t['trigger'] ?? '' ) ) ),
		esc_attr( marccent_blocks_side( $t['side'] ?? 'top' ) ),
		esc_html( marccent_blocks_text( (string) ( $t['content'] ?? '' ) ) )
	);
}

/*
 * Map: the theme's Map as its markup engine draws it (<div data-component="map" class="map col-center round
 * overflow-hidden" …>, core/components.js): a Google map in the theme's colors once it nears the screen, a pin on the
 * center with marker (its title, and a Google Maps link its click opens), many pins with markers ([{ lat, lng, title,
 * link }], clustered), Google's plain embed without a key. The key is data-key, or the page's
 * window.marccentMapsKey. What's inside (a no-script link) shows until the map draws. Only http(s) addresses.
 */
function marccent_blocks_map( array $m, string $inner = '', ?callable $attributes = null ): string {
	$attributes ??= 'marccent_blocks_attributes';
	$url          = fn( $u ) => preg_match( '#^https?://#i', (string) $u ) ? (string) $u : '';
	// The pins with a place, each with only what it has.
	$pins = array();
	foreach ( (array) ( $m['markers'] ?? array() ) as $p ) {
		if ( is_array( $p ) && is_numeric( $p['lat'] ?? null ) && is_numeric( $p['lng'] ?? null ) ) {
			$pins[] = array_filter( array( 'lat' => (float) $p['lat'], 'lng' => (float) $p['lng'], 'title' => (string) ( $p['title'] ?? '' ), 'link' => $url( $p['link'] ?? '' ) ), fn( $v ) => '' !== $v );
		}
	}
	return sprintf( '<div %s>%s</div>', $attributes( array(
		'data-component' => 'map',
		'class'          => 'map col-center round overflow-hidden',
		'data-key'       => (string) ( $m['key'] ?? '' ) ?: null,
		'data-lat'       => (string) (float) ( $m['lat'] ?? 0 ),
		'data-lng'       => (string) (float) ( $m['lng'] ?? 0 ),
		'data-zoom'      => (string) (int) ( $m['zoom'] ?? 10 ),
		'data-marker'    => empty( $m['marker'] ) ? null : '',
		'data-title'     => (string) ( $m['title'] ?? '' ) ?: null,
		'data-link'      => $url( $m['link'] ?? '' ) ?: null,
		'data-embed'     => $url( $m['embed'] ?? '' ) ?: null,
		'data-markers'   => $pins ? wp_json_encode( $pins, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) : null,
	) ), $inner );
}

// Popover: a panel its button opens and closes (the browser's popover), beside it.
function marccent_blocks_popover( array $p, string $content ): string {
	$id = (string) $p['id'];
	return sprintf(
		'<button type="button" class="btn" id="%1$s-anchor" popovertarget="%1$s">%2$s</button><div id="%1$s" popover="auto" role="group" aria-label="%3$s" data-component="popover" data-placement="%4$s" class="float bg border round shadow pad-sm">%5$s</div>',
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

// WordPress's Table block in the theme's table (.table: full width, a shaded header, lines between rows): the class
// on the <table> itself, which the block's own class setting can't reach (it goes on the <figure> around it); that
// figure scrolls sideways on a phone (.overflow-auto). marccent-theme T-008.
add_filter( 'render_block_core/table', function ( $html ) {
	$tags = new WP_HTML_Tag_Processor( (string) $html );
	if ( $tags->next_tag( 'figure' ) ) {
		$tags->add_class( 'overflow-auto' );
	}
	if ( $tags->next_tag( 'table' ) ) {
		$tags->add_class( 'table' );
	}
	return $tags->get_updated_html();
} );
