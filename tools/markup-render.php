<?php
/**
 * The server's markup (inc/markup.php) for each case of the theme's markup reference, printed as JSON
 * ({ component: { case: html } }) for the theme's conformance check (marccent-theme scripts/conformance.mjs, the
 * Adapters gate), which compares it with the reference. Without WordPress: the few functions inc/markup.php uses are
 * stood in for below. tools/markup-test.php uses the same renderers.
 *
 *   php tools/markup-render.php [reference.json | -]   (default: node_modules/marccent-theme/markup/reference.json;
 *                                                     -: from stdin, as the conformance check sends it)
 */

const ABSPATH = __DIR__;
const MARCCENT_BLOCKS_DIR = __DIR__ . '/..';

function esc_attr( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8', false );
}
function esc_html( $s ) {
	return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8', false );
}
function esc_url( $s ) {
	return preg_match( '#^(https?:|mailto:|tel:|/|\#|\?|$)#i', (string) $s ) ? esc_attr( $s ) : '';
}
function wp_strip_all_tags( $s ) {
	return trim( strip_tags( (string) $s ) );
}
function safecss_filter_attr( $css ) {
	return (string) $css;
}
function wp_json_encode( $data, $flags = 0 ) {
	return json_encode( $data, $flags );
}
function sanitize_html_class( $class ) {
	return preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $class );
}
function add_filter( ...$args ) { // inc/markup.php's filters (the Table block's classes) don't run here
	return true;
}

require __DIR__ . '/../inc/markup.php';

// The extra classes, as a block's wrapper adds its className.
function with_classes( string $classes ): callable {
	return fn( $attrs ) => marccent_blocks_attributes( array( 'class' => marccent_blocks_cx( $attrs['class'] ?? '', $classes ) ) + $attrs );
}

// A case's fields are the component's props (pad, cols); a block's attributes are the settings' names (padding,
// columns): settings-upgrade.json's props, the other way round.
function settings_names( string $block, array $fields ): array {
	static $upgrade;
	$upgrade ??= json_decode( (string) file_get_contents( __DIR__ . '/../settings-upgrade.json' ), true );
	foreach ( $upgrade[ "marccent/$block" ]['props'] ?? array() as $name => $prop ) {
		if ( array_key_exists( $prop, $fields ) ) {
			$fields[ $name ] = $fields[ $prop ];
			unset( $fields[ $prop ] );
		}
	}
	return $fields;
}

// Each case's fields (the theme's markup/cases.json) as the server takes them (a block's attributes): a button's label
// as the editor stores it (RichText: HTML, so "<" is "&lt;").
$render = array(
	'button'  => fn( $f ) => marccent_blocks_button( array( 'label' => htmlspecialchars( $f['label'] ) ) + $f ),
	'icon'    => fn( $f ) => marccent_blocks_icon( $f['name'], $f['classes'], $f['family'] ),
	'section' => fn( $f ) => marccent_blocks_section( array( 'as' => $f['tag'] ) + settings_names( 'section', $f ), $f['content'], with_classes( $f['classes'] ) ),
	'grid'    => fn( $f ) => marccent_blocks_grid( settings_names( 'grid', $f ), $f['content'], with_classes( $f['classes'] ) ),
	'card'    => fn( $f ) => marccent_blocks_card( settings_names( 'card', $f ), $f['content'], with_classes( $f['classes'] ) ),
	'menu'    => fn( $f ) => marccent_blocks_menu( array( 'id' => 'id-1' ) + $f ),
	'toggleButton' => fn( $f ) => marccent_blocks_toggle_button( $f ),
	// The interactive components: ids as the reference writes them (id-1); a block makes its own.
	'accordion' => fn( $f ) => marccent_blocks_accordion( implode( '', array_map(
		fn( $item, $i ) => marccent_blocks_accordion_item( htmlspecialchars( $item['header'] ), $item['content'], $f['multiple'] ? null : 'id-1', $i === $f['open'] ),
		$f['items'], array_keys( $f['items'] )
	) ) ),
	'alert'     => fn( $f ) => marccent_blocks_alert( $f['tone'], $f['content'] ),
	'modal'     => fn( $f ) => marccent_blocks_modal( array( 'titleId' => 'id-1', 'title' => htmlspecialchars( $f['title'] ), 'variant' => $f['variant'] ), $f['content'] ),
	'drawer'    => fn( $f ) => marccent_blocks_drawer( array( 'titleId' => 'id-1', 'title' => htmlspecialchars( $f['title'] ), 'side' => $f['side'] ), $f['content'] ),
	'tooltip'   => fn( $f ) => marccent_blocks_tooltip( array( 'id' => 'id-1' ) + $f ),
	'popover'   => fn( $f ) => marccent_blocks_popover( array( 'id' => 'id-1' ) + $f, $f['content'] ),
	'map'       => fn( $f ) => marccent_blocks_map( $f ),
);

// Run directly: every case of the reference this server renders, as JSON.
if ( realpath( $_SERVER['SCRIPT_FILENAME'] ?? '' ) === __FILE__ ) {
	$file      = $argv[1] ?? __DIR__ . '/../node_modules/marccent-theme/markup/reference.json';
	$file      = '-' === $file ? 'php://stdin' : $file;
	$reference = json_decode( (string) file_get_contents( $file ), true ) ?: exit( "No markup reference at $file: npm install first.\n" );
	$out       = array();
	foreach ( $render as $component => $fn ) {
		foreach ( $reference[ $component ] ?? array() as $case ) {
			$out[ $component ][ $case['case'] ] = $fn( $case['fields'] );
		}
	}
	echo json_encode( $out, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
}
