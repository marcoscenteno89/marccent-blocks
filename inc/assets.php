<?php
/**
 * The styles and scripts, only what each page uses. Every block's markup is read as it renders: the icons it shows,
 * the design styles it asks for (data-design), its islands, its canvas backgrounds and whether the header has the
 * theme button. A block theme renders the whole template before wp_head, so by the time the head's styles and
 * scripts print all of it is known; anything found later (a classic theme's content) is added in the footer.
 *
 * - build/styles/core.css on every page, with the page's Font Awesome icons printed after it (build/icons.php has
 *   every icon's rule; core.css has none).
 * - The site's design style and any a section asks for (build/designs/<id>.css), or all of them where the theme button
 *   lets visitors switch.
 * - The runtime (React and build/runtime/index.js) only on pages with an island, a canvas background or the theme
 *   button; a page without them has no script of ours.
 * In the editor: the core styles, every design style and every icon.
 */

defined( 'ABSPATH' ) || exit;

// What the page uses so far.
function &marccent_blocks_page(): array {
	static $page = array( 'icons' => array(), 'designs' => array(), 'islands' => array(), 'canvas' => false, 'themeButton' => false );
	return $page;
}

// Every icon's rule body by name, and the icons the runtime's components can render (build/icons.php).
function marccent_blocks_icons(): array {
	static $icons = null;
	return $icons ??= ( @include MARCCENT_BLOCKS_DIR . '/build/icons.php' ) ?: array( 'all' => array(), 'runtime' => array() );
}

add_filter( 'render_block', function ( $html, $block ) {
	if ( is_admin() || ! $html ) {
		return $html;
	}
	$page = &marccent_blocks_page();
	// Icons: their classes (fa-snowflake), and the names an island's props carry ("icon":"phone", "fa-brands fa-x").
	preg_match_all( '/\bfa-([a-z0-9-]+)/', $html, $classes );
	preg_match_all( '/(?:"|&quot;)(?:icon|activeIcon)(?:"|&quot;):(?:"|&quot;)([a-z0-9 -]+)(?:"|&quot;)/', $html, $props );
	foreach ( array_merge( $classes[1], ...array_map( fn( $v ) => explode( ' ', $v ), $props[1] ) ) as $name ) {
		$page['icons'][ preg_replace( '/^fa-/', '', $name ) ] = true;
	}
	preg_match_all( '/\bdata-design="([a-z0-9-]+)"/', $html, $designs );
	preg_match_all( '/\bdata-island="([a-z0-9-]+)"/', $html, $islands );
	$page['designs'] += array_fill_keys( $designs[1], true );
	$page['islands'] += array_fill_keys( $islands[1], true );
	$page['canvas']   = $page['canvas'] || preg_match( '/\bdata-component="[^"]*\banimation\b/', $html );
	if ( 'marccent/navbar' === $block['blockName'] && ( $block['attrs']['themeButton'] ?? true ) ) {
		$page['themeButton'] = true;
	}
	return $html;
}, 10, 2 );

// A built stylesheet (build/<entry>.css), versioned by its build.
function marccent_blocks_style( string $handle, string $entry, array $deps = array() ): void {
	$asset = @include MARCCENT_BLOCKS_DIR . "/build/$entry.asset.php";
	wp_register_style( $handle, plugins_url( "build/$entry.css", MARCCENT_BLOCKS_FILE ), $deps, $asset['version'] ?? null );
	wp_enqueue_style( $handle );
}

// The core styles' handle exists from the start, so other plugins can add their inline styles to it at any point.
add_action( 'init', function () {
	$asset = @include MARCCENT_BLOCKS_DIR . '/build/styles/core.asset.php';
	wp_register_style( 'marccent', plugins_url( 'build/styles/core.css', MARCCENT_BLOCKS_FILE ), array(), $asset['version'] ?? null );
} );

/*
 * What the page needs that isn't on it yet: run in the head (everything a block theme rendered) and again in the
 * footer (whatever rendered after the head). Icons already printed aren't printed again.
 */
function marccent_blocks_enqueue( bool $late = false ): void {
	static $printed = array();
	$page    = marccent_blocks_page();
	$runtime = $page['islands'] || $page['canvas'] || $page['themeButton'];

	wp_enqueue_style( 'marccent' );

	$designs = $page['themeButton'] ? array( 'all' ) : array_keys( array( marccent_blocks_design() => true ) + $page['designs'] );
	foreach ( $designs as $id ) {
		if ( 'none' !== $id && ! wp_style_is( "marccent-design-$id" ) && file_exists( MARCCENT_BLOCKS_DIR . "/build/designs/$id.css" ) ) {
			marccent_blocks_style( "marccent-design-$id", "designs/$id", array( 'marccent' ) );
		}
	}

	$icons = marccent_blocks_icons();
	$names = array_keys( $page['icons'] );
	if ( $runtime ) {
		$names = array_merge( $names, $icons['runtime'] );
	}
	$rules = '';
	foreach ( array_unique( $names ) as $name ) {
		if ( isset( $icons['all'][ $name ] ) && ! isset( $printed[ $name ] ) ) {
			$printed[ $name ] = true;
			$rules           .= ".fa-$name{--fa:{$icons['all'][ $name ]}}";
		}
	}
	if ( $rules && ! $late ) {
		wp_add_inline_style( 'marccent', $rules );
	} elseif ( $rules ) {
		printf( '<style id="marccent-icons-late">%s</style>', $rules ); // phpcs:ignore WordPress.Security.EscapeOutput -- built from build/icons.php
	}

	// The script at the end of the body (with React), so nothing blocks the first paint: <html> already carries the
	// starting theme (inc/theme.php).
	if ( $runtime && ! wp_script_is( 'marccent' ) ) {
		$asset = include MARCCENT_BLOCKS_DIR . '/build/runtime/index.asset.php';
		wp_enqueue_script( 'marccent', plugins_url( 'build/runtime/index.js', MARCCENT_BLOCKS_FILE ), $asset['dependencies'], $asset['version'], array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_add_inline_script( 'marccent', 'window.marccentDefaults = ' . wp_json_encode( (object) marccent_blocks_defaults() ) . ';' . ( marccent_blocks_dev() ? ' window.marccentDev = true;' : '' ), 'before' );
	}
}

add_action( 'wp_enqueue_scripts', fn() => marccent_blocks_enqueue(), 20 );
add_action( 'wp_footer', fn() => marccent_blocks_enqueue( true ), 1 );

// The editor's canvas: every style, design style and icon, since any block can be added there.
add_action( 'enqueue_block_assets', function () {
	if ( is_admin() ) {
		wp_enqueue_style( 'marccent' );
		marccent_blocks_style( 'marccent-design-all', 'designs/all', array( 'marccent' ) );
		marccent_blocks_style( 'marccent-icons', 'styles/icons', array( 'marccent' ) );
	}
} );
