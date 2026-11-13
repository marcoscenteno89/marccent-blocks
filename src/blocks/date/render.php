<?php
/**
 * Date: an island (runtime/islands/date.js), the theme's DateTime set in the visitor's browser; until then, the
 * date as written (an ISO date in a <time>). Nothing without a date.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$value = trim( (string) $attributes['value'] );
if ( '' === $value ) {
	return;
}
$props = array(
	'value'    => $value,
	'end'      => trim( (string) $attributes['end'] ),
	'show'     => (string) $attributes['show'] ?: 'date',
	'locale'   => (string) $attributes['locale'] ?: 'en-US',
	'timeZone' => (string) $attributes['timeZone'] ?: 'UTC',
);
$stand_in = sprintf( '<time datetime="%1$s">%1$s</time>', esc_attr( $value ) )
	. ( '' !== $props['end'] ? sprintf( ' – <time datetime="%1$s">%1$s</time>', esc_attr( $props['end'] ) ) : '' );
echo marccent_blocks_island( $block, 'date', $props, $stand_in ); // phpcs:ignore WordPress.Security.EscapeOutput
