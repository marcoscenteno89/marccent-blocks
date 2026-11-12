<?php
/**
 * Map: the theme's Map (marccent_blocks_map, inc/markup.php), drawn by its engine when it nears the screen. Its
 * settings may be {{ tokens }} (latitude {{ business.latitude }}: Marccent Content fills them, marccent_blocks_fill),
 * and the marccent_blocks_map filter may add to them. A link to Google Maps inside shows until the map draws, and stays
 * without scripts; without a Maps key, Google's embed of the place. Nothing without coordinates.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$fill = fn( $text ) => str_contains( (string) $text, '{{' ) ? trim( (string) apply_filters( 'marccent_blocks_fill', (string) $text, $block ) ) : (string) $text;
$map  = apply_filters( 'marccent_blocks_map', array_filter( array(
	'latitude'  => $fill( $attributes['lat'] ),
	'longitude' => $fill( $attributes['lng'] ),
	'title'     => $fill( $attributes['title'] ),
	'link'      => $fill( $attributes['link'] ),
) ) + array( 'zoom' => $attributes['zoom'], 'marker' => $attributes['marker'] ) );
if ( ! is_numeric( $map['latitude'] ?? '' ) || ! is_numeric( $map['longitude'] ?? '' ) || ( 0.0 === (float) $map['latitude'] && 0.0 === (float) $map['longitude'] ) ) {
	return;
}
$map += array( 'embed' => 'https://www.google.com/maps?q=' . rawurlencode( $map['latitude'] . ',' . $map['longitude'] ) . '&output=embed' );
$inner = empty( $map['link'] ) ? '' : sprintf( '<a href="%s">%s</a>', esc_url( $map['link'] ), esc_html( sprintf( __( 'Open %s in Google Maps', 'marccent-blocks' ), $map['title'] ?? __( 'the map', 'marccent-blocks' ) ) ) );
// The pin's data-marker is there with no value; the block wrapper leaves empty values out, so it's added after.
$attributes_of = fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) . ( isset( $attrs['data-marker'] ) ? ' data-marker' : '' );
echo marccent_blocks_map( $map, $inner, $attributes_of ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped as it's built
