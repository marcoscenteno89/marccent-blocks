<?php
/**
 * Date: an island (runtime/islands/date-time.js), the theme's DateTime; until it's drawn, a <time> with the date as
 * stored (machines read its datetime either way).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a     = $attributes;
$value = trim( (string) $a['value'] );
if ( '' === $value ) {
	return;
}
$props = array_filter(
	array(
		'value'    => $value,
		'end'      => trim( (string) $a['end'] ),
		'show'     => in_array( $a['show'], array( 'date', 'date-time', 'time', 'relative' ), true ) ? $a['show'] : 'date',
		'locale'   => (string) $a['locale'] ?: 'en-US',
		'timeZone' => (string) $a['timeZone'] ?: 'UTC',
	),
	fn( $v ) => '' !== $v
);
$stand_in = sprintf( '<time datetime="%1$s">%1$s</time>', esc_attr( $value ) ) . ( isset( $props['end'] ) ? sprintf( ' – <time datetime="%1$s">%1$s</time>', esc_attr( $props['end'] ) ) : '' );
echo marccent_blocks_island( $block, 'date-time', $props, $stand_in ); // phpcs:ignore WordPress.Security.EscapeOutput
