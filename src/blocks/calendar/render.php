<?php
/**
 * Calendar: an island (runtime/islands/calendar.js), the theme's Calendar. Its events as a list until it's drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a      = $attributes;
$events = array_values( array_filter( (array) $a['events'], fn( $e ) => is_array( $e ) && '' !== trim( (string) ( $e['date'] ?? '' ) ) ) );
$props  = array(
	'mode'        => 'range' === $a['mode'] ? 'range' : 'single',
	'weekStart'   => 1 === (int) $a['weekStart'] ? 1 : 0,
	'weekNumbers' => (bool) $a['weekNumbers'],
	'details'     => (bool) $a['details'],
	'events'      => array_map( fn( $e ) => array( 'date' => (string) $e['date'], 'title' => (string) ( $e['title'] ?? '' ), 'color' => sanitize_html_class( (string) ( $e['color'] ?? '' ) ) ?: 'bg-secondary' ), $events ),
);
foreach ( array( 'minimum', 'maximum' ) as $limit ) {
	if ( '' !== (string) $a[ $limit ] ) {
		$props[ $limit ] = (string) $a[ $limit ];
	}
}
echo marccent_blocks_island( $block, 'calendar', $props, marccent_blocks_island_text( array( 'events' => $props['events'] ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput
