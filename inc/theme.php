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
 * The marccent-theme release this build was made from (package.json pins it: github:…#v<version>), so the theme,
 * Local and Content can check the one copy every Marccent plugin shares.
 */
function marccent_blocks_theme_version(): string {
	static $version = null;
	$version ??= json_decode( (string) @file_get_contents( MARCCENT_BLOCKS_DIR . '/build/marccent-theme.json' ), true )['version'] ?? '';
	return $version;
}

/*
 * One of our options, kept among those WordPress loads with every request (autoload). One that's missing, or saved
 * without autoload, costs a database query on every page that reads it (WordPress remembers a missing option only for
 * the request), so the first read adds it with $default or switches it to autoload.
 */
function marccent_blocks_option( string $name, $default ) {
	global $wpdb;
	if ( wp_installing() || array_key_exists( $name, wp_load_alloptions() ) ) {
		return get_option( $name, $default );
	}
	$value = get_option( $name, null );
	if ( null !== $value ) {
		wp_set_option_autoload( $name, true );
		return $value;
	}
	// INSERT IGNORE, not add_option: add_option overwrites a row that's there, and a read that failed (the database
	// timing out) looks the same as a missing option here.
	$wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO $wpdb->options (option_name, option_value, autoload) VALUES (%s, %s, 'on')", $name, maybe_serialize( $default ) ) );
	if ( wp_using_ext_object_cache() ) {
		wp_cache_delete( 'alloptions', 'options' );
		wp_cache_delete( 'notoptions', 'options' );
	}
	return $default;
}

/*
 * The site's starting theme (palettes, mode, design style…), over the package's lib/theme.defaults.json. Saved as
 * JSON in the marccent_defaults option, e.g.
 *   {"palette":"brand","isDark":false,"design":"professional","palettes":[{"id":"brand","primary":"#046bd2","secondary":"#1e293b"}]}
 */
function marccent_blocks_defaults(): array {
	$defaults = json_decode( (string) marccent_blocks_option( 'marccent_defaults', '{}' ), true );
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

// The theme's own rules for the site's colors (marccent-theme's server/palette.php, copied into build/ by webpack): one
// source with the browser's lib/palette.js (PRODUCT-LINE.md 5.7).
require_once MARCCENT_BLOCKS_DIR . '/build/palette.php';

// The package's defaults with the site's on top; a site's palettes go before the package's (as lib/defaults.js does).
function marccent_blocks_theme(): array {
	static $theme = null;
	$theme ??= \Marccent\effective_theme( marccent_blocks_defaults(), marccent_blocks_theme_base() );
	return $theme;
}

// The package's own theme defaults (build/theme-defaults.json, from marccent-theme's lib/theme.defaults.json).
function marccent_blocks_theme_base(): array {
	static $base = null;
	$base ??= json_decode( (string) @file_get_contents( MARCCENT_BLOCKS_DIR . '/build/theme-defaults.json' ), true ) ?: array();
	return $base;
}

// What the package offers a site's theme (build/theme-options.json): its design styles (id, title, description) and
// hover effects. The theme's options panel (Marccent WP) builds its choices from it.
function marccent_blocks_theme_options(): array {
	static $options = null;
	$options ??= json_decode( (string) @file_get_contents( MARCCENT_BLOCKS_DIR . '/build/theme-options.json' ), true ) ?: array( 'designs' => array(), 'hovers' => array() );
	return $options;
}

// The site's default hover (Marccent WP's theme options): the class a button or card set to Default gets; none for "none".
function marccent_blocks_default_hover(): string {
	$hover = (string) ( marccent_blocks_theme()['hover'] ?? '' );
	return '' === $hover || 'none' === $hover ? '' : 'hover-' . sanitize_html_class( $hover );
}

/*
 * What visitors may change themselves (Marccent WP: Appearance → Marccent theme, Visitors): the full mini theme editor
 * (the brush button: design style, light/dark, glass, the palettes and Start over), or single controls of it right in
 * the header (only light/dark, say). A site that never chose keeps the full editor.
 */
function marccent_blocks_visitor_controls(): array {
	$v    = (array) ( marccent_blocks_theme()['visitor'] ?? array() );
	$full = (bool) ( $v['full'] ?? true );
	return array(
		'full'     => $full,
		// The single controls, in the order they show; none beside the full editor, which has them all.
		'controls' => $full ? array() : array_values( array_filter( array( 'design', 'mode', 'glass', 'palette' ), fn( $k ) => ! empty( $v[ $k ] ) ) ),
	);
}

// The design style the site starts with.
function marccent_blocks_design(): string {
	return (string) ( marccent_blocks_theme()['design'] ?? 'modern' );
}

// The colors the runtime puts on <html>: the theme's (server/palette.php theme_vars, lib/palette.js's twin).
function marccent_blocks_theme_vars(): array {
	return \Marccent\theme_vars( marccent_blocks_theme() );
}

/*
 * A site's theme as saved (the marccent_defaults option), checked against the theme's site settings
 * (build/site-settings.json, marccent-theme's markup/site-settings.json): only what they define, each value of its kind;
 * a design style or hover the package has (build/theme-options.json). Anything else is dropped. Marccent WP's theme
 * options page saves through this (PRODUCT-LINE.md 5.7: the theme defines, the admin draws and saves).
 */
function marccent_blocks_site_settings(): array {
	static $schema = null;
	$schema ??= json_decode( (string) @file_get_contents( MARCCENT_BLOCKS_DIR . '/build/site-settings.json' ), true ) ?: array( 'settings' => array() );
	return $schema;
}

function marccent_blocks_site_settings_sanitize( $value ): array {
	$in      = is_array( $value ) ? $value : json_decode( (string) $value, true );
	$in      = is_array( $in ) ? $in : array();
	$options = marccent_blocks_theme_options();
	$lists   = array( 'designs' => array_column( $options['designs'], 'id' ), 'hovers' => array_merge( array( 'none' ), $options['hovers'] ) );
	$one     = function ( array $s, $v ) use ( &$one, $lists ) {
		$hex = fn( $c ) => is_string( $c ) && preg_match( '/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $c ) ? strtolower( $c ) : null;
		switch ( $s['sanitize'] ?? '' ) {
			case 'boolean':
				return (bool) $v;
			case 'number':
				return is_numeric( $v ) ? max( $s['minimum'] ?? -INF, min( $s['maximum'] ?? INF, 0 + $v ) ) : null;
			case 'color':
				return $hex( $v );
			case 'class':
				$v = is_string( $v ) ? sanitize_key( $v ) : '';
				return '' !== $v && ( ! isset( $s['choicesFrom'] ) || in_array( $v, $lists[ $s['choicesFrom'] ] ?? array(), true ) ) ? $v : null;
			case 'css variables':
				$out = array();
				foreach ( (array) $v as $name => $x ) {
					if ( preg_match( '/^--[a-z][a-z0-9-]{0,63}$/', (string) $name ) && is_scalar( $x ) && '' !== trim( (string) $x ) && ! preg_match( '/[;{}<>]/', (string) $x ) ) {
						$out[ $name ] = trim( (string) $x );
					}
				}
				return $out ?: null;
			case 'each item':
				$item = function ( $row ) use ( $s, $one ) {
					$out = array();
					foreach ( $s['items'] ?? array() as $k => $f ) {
						$x = isset( $row[ $k ] ) ? $one( $f, $row[ $k ] ) : null;
						if ( null === $x && 'required' === ( $f['validate'] ?? '' ) ) {
							return null;
						}
						if ( null !== $x ) {
							$out[ $k ] = $x;
						}
					}
					return $out ?: null;
				};
				if ( 'object' === ( $s['type'] ?? '' ) ) {
					return is_array( $v ) ? $item( $v ) : null;
				}
				$rows = array_values( array_filter( array_map( $item, array_filter( (array) $v, 'is_array' ) ) ) );
				return $rows ? array_slice( $rows, 0, (int) ( $s['maximum'] ?? 100 ) ) : null;
			default:
				return is_string( $v ) ? sanitize_text_field( $v ) : null;
		}
	};
	$out = array();
	foreach ( marccent_blocks_site_settings()['settings'] as $key => $s ) {
		if ( array_key_exists( $key, $in ) ) {
			$v = $one( $s, $in[ $key ] );
			if ( null !== $v ) {
				$out[ $key ] = $v;
			}
		}
	}
	// A palette is the theme's or one of the site's own.
	if ( isset( $out['palette'] ) && ! in_array( $out['palette'], array_column( \Marccent\effective_theme( $out, marccent_blocks_theme_base() )['palettes'], 'id' ), true ) ) {
		unset( $out['palette'] );
	}
	return $out;
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
