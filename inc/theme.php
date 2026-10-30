<?php
/**
 * The site's starting theme: the package's defaults (build/theme-defaults.json, from marccent-theme's
 * lib/theme.defaults.json) with the site's on top (the marccent_defaults option), as the package's lib/defaults.js
 * merges them in the browser (window.marccentDefaults). The server paints it on <html> (colors, mode, design style, hover, glass), so the page is
 * right on the first paint and needs no script for it; the runtime, where a page loads it, then applies a visitor's
 * saved theme.
 */

defined( 'ABSPATH' ) || exit;

/*
 * The site's starting theme (palettes, mode, design style…), over the package's lib/theme.defaults.json. Saved as
 * JSON in the marccent_defaults option, e.g.
 *   {"palette":"brand","isDark":false,"design":"professional","palettes":[{"id":"brand","primary":"#046bd2","secondary":"#1e293b"}]}
 */
/*
 * The marccent-theme release this build was made from (package.json pins it: github:…#v<version>), so the theme,
 * Local and Content can check the one copy every Marccent plugin shares.
 */
function marccent_blocks_theme_version(): string {
	static $version = null;
	$version ??= json_decode( (string) @file_get_contents( MARCCENT_BLOCKS_DIR . '/build/marccent-theme.json' ), true )['version'] ?? '';
	return $version;
}

function marccent_blocks_defaults(): array {
	$defaults = json_decode( (string) get_option( 'marccent_defaults', '{}' ), true );
	return is_array( $defaults ) ? $defaults : array();
}

/*
 * Development (WP_DEBUG on): every page load shows the site as it is now. Pages aren't cached by the browser, and
 * the runtime doesn't restore a visitor's saved theme (window.marccentDev; the package's lib/defaults.js), so a
 * changed default shows at once.
 */
function marccent_blocks_dev(): bool {
	return defined( 'WP_DEBUG' ) && WP_DEBUG;
}

add_action( 'template_redirect', function () {
	if ( marccent_blocks_dev() && ! is_admin() ) {
		nocache_headers();
	}
} );

// The package's defaults with the site's on top; a site's palettes go before the package's (as lib/defaults.js does).
function marccent_blocks_theme(): array {
	static $theme = null;
	if ( null === $theme ) {
		$base  = json_decode( (string) @file_get_contents( MARCCENT_BLOCKS_DIR . '/build/theme-defaults.json' ), true ) ?: array();
		$site  = marccent_blocks_defaults();
		$own   = $site['palettes'] ?? array();
		$ids   = array_column( $own, 'id' );
		$theme = array_merge( $base, $site, array(
			'vars'     => array_merge( $base['vars'] ?? array(), $site['vars'] ?? array() ),
			'palettes' => array_merge( $own, array_values( array_filter( $base['palettes'] ?? array(), fn( $p ) => ! in_array( $p['id'], $ids, true ) ) ) ),
		) );
	}
	return $theme;
}

// The design style the site starts with.
function marccent_blocks_design(): string {
	return (string) ( marccent_blocks_theme()['design'] ?? 'modern' );
}

/*
 * The colors the runtime puts on <html> (marccent-theme's core/apply.js and lib/theme-scope.js paletteVars), worked out
 * the same way: the palette in use, its shades (the color itself while the package's SHADES is off), the text on each
 * fill (dark or light, whichever has the higher WCAG contrast with it) and the page's dark and light; then the
 * defaults' vars,
 * which go with the default design.
 */
function marccent_blocks_theme_vars(): array {
	$t       = marccent_blocks_theme();
	$palette = null;
	foreach ( $t['palettes'] ?? array() as $p ) {
		if ( ( $p['id'] ?? null ) === ( $t['palette'] ?? null ) ) {
			$palette = $p;
		}
	}
	$palette ??= $t['palettes'][0] ?? null;
	$vars      = array();
	if ( $palette ) {
		// WCAG relative luminance of a hex color.
		$luminance = function ( string $hex ): float {
			$hex = ltrim( $hex, '#' );
			if ( strlen( $hex ) <= 4 ) {
				$hex = implode( '', array_map( fn( $c ) => $c . $c, str_split( $hex ) ) );
			}
			$lin = function ( string $pair ): float {
				$v = hexdec( $pair ) / 255;
				return $v <= .03928 ? $v / 12.92 : ( ( $v + .055 ) / 1.055 ) ** 2.4;
			};
			[ $r, $g, $b ] = array_map( $lin, str_split( substr( $hex, 0, 6 ), 2 ) );
			return .2126 * $r + .7152 * $g + .0722 * $b;
		};
		// Dark text when it contrasts more than light with every color given (the gradient: both ends).
		$on = function ( string ...$colors ) use ( $luminance ): string {
			$l = array_map( $luminance, $colors );
			return min( array_map( fn( $x ) => ( $x + .05 ) / .05, $l ) ) >= min( array_map( fn( $x ) => 1.05 / ( $x + .05 ), $l ) ) ? 'var(--dark)' : 'var(--light)';
		};
		$vars = array(
			'--primary'           => $palette['primary'],
			'--primary-lighter'   => $palette['primary'],
			'--primary-darker'    => $palette['primary'],
			'--secondary'         => $palette['secondary'],
			'--secondary-lighter' => $palette['secondary'],
			'--secondary-darker'  => $palette['secondary'],
			'--gradient'          => 'linear-gradient(to right, var(--primary), var(--secondary))',
			'--gradient-lighter'  => 'linear-gradient(to right, var(--primary-lighter), var(--secondary-lighter))',
			'--gradient-darker'   => 'linear-gradient(to right, var(--primary-darker), var(--secondary-darker))',
			'--primary-text'      => $on( $palette['primary'] ),
			'--secondary-text'    => $on( $palette['secondary'] ),
			'--gradient-text'     => $on( $palette['primary'], $palette['secondary'] ),
		);
		// Text on the shades' fills (the colors themselves while SHADES is off).
		foreach ( array( 'primary', 'secondary' ) as $name ) {
			foreach ( array( 'lighter', 'darker' ) as $side ) {
				$vars[ "--$name-$side-text" ] = $vars[ "--$name-text" ];
			}
		}
	}
	foreach ( array( 'dark', 'light' ) as $mode ) {
		if ( ! empty( $t[ $mode ] ) ) {
			$vars[ "--$mode" ] = $t[ $mode ];
		}
	}
	return array_merge( $vars, $t['vars'] ?? array() );
}

/*
 * <html> as the runtime expects it: id="site" (the whole site's theme scope), and the starting theme painted on it:
 * the design style, the mode, the default hover, glass and the colors.
 */
add_filter( 'language_attributes', function ( $output ) {
	if ( is_admin() ) {
		return $output;
	}
	$t     = marccent_blocks_theme();
	$vars  = marccent_blocks_theme_vars();
	$style = implode( '', array_map( fn( $k, $v ) => "$k: $v;", array_keys( $vars ), $vars ) );
	return sprintf(
		'%s id="site" data-design="%s" data-theme="%s"%s%s style="%s"',
		$output,
		esc_attr( marccent_blocks_design() ),
		empty( $t['isDark'] ) ? 'light' : 'dark',
		empty( $t['hover'] ) ? '' : sprintf( ' data-hover="%s"', esc_attr( $t['hover'] ) ),
		empty( $t['glass'] ) ? '' : ' class="glass"',
		esc_attr( $style )
	);
} );
