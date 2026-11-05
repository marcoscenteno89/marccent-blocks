<?php
/**
 * Map: the theme's Map (marccent_blocks_map, inc/markup.php), drawn by its engine when it nears the screen. What the
 * block leaves empty comes from the site (the marccent_blocks_map filter: Marccent Local gives the business's
 * coordinates, name, Google Maps link and embed). A link to Google Maps inside shows until the map draws, and stays
 * without scripts. Nothing without coordinates.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$map = apply_filters( 'marccent_blocks_map', array_filter( array(
	'lat'    => $attributes['lat'],
	'lng'    => $attributes['lng'],
	'title'  => $attributes['title'],
	'link'   => $attributes['link'],
) ) + array( 'zoom' => $attributes['zoom'], 'marker' => $attributes['marker'] ) );
if ( '' === (string) ( $map['lat'] ?? '' ) || '' === (string) ( $map['lng'] ?? '' ) ) {
	return;
}
$inner = empty( $map['link'] ) ? '' : sprintf( '<a href="%s">%s</a>', esc_url( $map['link'] ), esc_html( sprintf( __( 'Open %s in Google Maps', 'marccent-blocks' ), $map['title'] ?? __( 'the map', 'marccent-blocks' ) ) ) );
// The pin's data-marker is there with no value; the block wrapper leaves empty values out, so it's added after.
$attributes_of = fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ) . ( isset( $attrs['data-marker'] ) ? ' data-marker' : '' );
echo marccent_blocks_map( $map, $inner, $attributes_of ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped as it's built
