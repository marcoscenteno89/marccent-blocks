<?php
/**
 * Custom HTML: the HTML as written, its CSS scoped to this block (nested in [data-html="…"]) and its script given the
 * block as root. Saved only by people who may post unfiltered HTML (inc/html.php), like WordPress's own Custom HTML.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$html = (string) $attributes['html'];
$css  = trim( (string) $attributes['css'] );
$js   = trim( (string) $attributes['js'] );
if ( '' === trim( $html ) && '' === $css && '' === $js ) {
	return;
}
$uid = wp_unique_id( 'html-' );
// A closing tag inside the CSS or the script would end its element early: written so it can't.
$safe = fn( string $code, string $tag ) => preg_replace( "#</(?=$tag)#i", '<\/', $code );

echo '<div ' . get_block_wrapper_attributes( array( 'data-html' => $uid ) ) . '>' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- unfiltered_html authors only
if ( '' !== $css ) {
	printf( '<style>[data-html="%s"]{%s}</style>', esc_attr( $uid ), $safe( $css, 'style' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
}
if ( '' !== $js ) {
	printf( "<script>(function (root) {\n%s\n})(document.querySelector('[data-html=\"%s\"]'));</script>", $safe( $js, 'script' ), esc_attr( $uid ) ); // phpcs:ignore WordPress.Security.EscapeOutput
}
