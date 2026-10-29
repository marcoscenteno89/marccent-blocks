<?php
/**
 * The server's Button, Icon, Section, Grid, Card and NavBar (inc/markup.php) against the theme's markup reference
 * (node_modules/marccent-theme/markup/reference.json): every case rendered here must be the same HTML as the theme's
 * components render, ignoring the order of attributes and of classes, and whitespace between tags. Without WordPress:
 * the few functions inc/markup.php uses are stood in for below.
 *
 *   php tools/markup-test.php   (npm run test:markup)
 */

const ABSPATH = __DIR__;

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

require __DIR__ . '/../inc/markup.php';

// The extra classes, as a block's wrapper adds its className.
function with_classes( string $classes ): callable {
	return fn( $attrs ) => marccent_blocks_attributes( array( 'class' => marccent_blocks_cx( $attrs['class'] ?? '', $classes ) ) + $attrs );
}

// Each case's fields (the theme's markup/cases.json) as the server takes them (a block's attributes): a button's label
// as the editor stores it (RichText: HTML, so "<" is "&lt;").
$render = array(
	'button'  => fn( $f ) => marccent_blocks_button( array( 'label' => htmlspecialchars( $f['label'] ) ) + $f ),
	'icon'    => fn( $f ) => marccent_blocks_icon( $f['name'], $f['classes'], $f['family'] ),
	'section' => fn( $f ) => marccent_blocks_section( array( 'tagName' => $f['tag'], 'imageUrl' => $f['image'] ) + $f, $f['content'], with_classes( $f['classes'] ) ),
	'grid'    => fn( $f ) => marccent_blocks_grid( $f, $f['content'], with_classes( $f['classes'] ) ),
	'card'    => fn( $f ) => marccent_blocks_card( $f, $f['content'], with_classes( $f['classes'] ) ),
	'nav'     => fn( $f ) => marccent_blocks_navbar( $f['brand'], $f['items'] ),
);

// An element as a comparable tree: its name, attributes sorted (classes too), and its children; blank text dropped.
function tree( DOMNode $node ) {
	if ( XML_TEXT_NODE === $node->nodeType ) {
		return trim( $node->textContent ) === '' ? null : trim( preg_replace( '/\s+/', ' ', $node->textContent ) );
	}
	$attrs = array();
	foreach ( $node->attributes ?? array() as $a ) {
		$value = $a->value;
		if ( 'class' === $a->name ) {
			$classes = preg_split( '/\s+/', trim( $value ), -1, PREG_SPLIT_NO_EMPTY );
			sort( $classes );
			$value = implode( ' ', $classes );
		} elseif ( 'style' === $a->name ) { // "a: b; c: d" as "a:b;c:d"
			$value = implode( ';', array_filter( array_map( fn( $d ) => preg_replace( '/\s*:\s*/', ':', trim( $d ), 1 ), explode( ';', $value ) ) ) );
		}
		$attrs[ strtolower( $a->name ) ] = $value;
	}
	ksort( $attrs );
	$children = array();
	foreach ( $node->childNodes as $child ) {
		$t = tree( $child );
		if ( null !== $t ) {
			$children[] = $t;
		}
	}
	return array( strtolower( $node->nodeName ), $attrs, $children );
}
function parse_html( string $html ) {
	$doc = new DOMDocument();
	libxml_use_internal_errors( true ); // HTML5 elements (svg) are unknown to libxml's HTML 4 parser
	$doc->loadHTML( '<?xml encoding="utf-8"?><body>' . $html . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	return tree( $doc->getElementsByTagName( 'body' )->item( 0 ) );
}

$file      = __DIR__ . '/../node_modules/marccent-theme/markup/reference.json';
$reference = json_decode( (string) file_get_contents( $file ), true ) ?: exit( "No markup reference at $file: npm install first.\n" );
$failed    = 0;
foreach ( $render as $component => $fn ) {
	foreach ( $reference[ $component ] ?? array() as $case ) {
		$html = $fn( $case['fields'] );
		$ok   = parse_html( $html ) === parse_html( $case['html'] );
		$failed += $ok ? 0 : 1;
		printf( "%s %s: %s\n", $ok ? 'ok  ' : 'FAIL', $component, $case['case'] );
		if ( ! $ok ) {
			printf( "     theme:  %s\n     server: %s\n", $case['html'], $html );
		}
	}
}
exit( $failed ? 1 : 0 );
