<?php
/**
 * Dock: an island (runtime/islands/dock.js), its items as links until it's drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$items = array_values( array_filter( (array) $attributes['items'], fn( $i ) => is_array( $i ) && '' !== trim( (string) ( $i['label'] ?? '' ) ) ) );
$links = implode( '', array_map( fn( $i ) => sprintf( '<li><a href="%s">%s</a></li>', esc_url( (string) ( $i['href'] ?? '#' ) ), esc_html( $i['label'] ) ), $items ) );
$props = array( 'items' => $items, 'label' => (string) $attributes['label'] );
echo marccent_blocks_island( $block, 'dock', $props, "<ul>$links</ul>" ); // phpcs:ignore WordPress.Security.EscapeOutput
