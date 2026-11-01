<?php
/**
 * Data table: an island (runtime/islands/data-table.js). Its rows are typed as CSV, the first line the headers: the
 * server reads them into columns and rows, and writes them as a plain table until the searchable one is drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$lines = array_values( array_filter( preg_split( '/\r\n|\r|\n/', (string) $attributes['csv'] ), fn( $l ) => '' !== trim( $l ) ) );
$head  = $lines ? array_map( 'trim', str_getcsv( array_shift( $lines ) ) ) : array();
$keys  = array_map( fn( $h, $i ) => 'c' . $i, $head, array_keys( $head ) );
$rows  = array_map( fn( $l, $i ) => array( 'id' => $i ) + array_combine( $keys, array_pad( array_slice( array_map( 'trim', str_getcsv( $l ) ), 0, count( $keys ) ), count( $keys ), '' ) ), $lines, array_keys( $lines ) );
$props = array(
	'columns'  => array_map( fn( $k, $h ) => array( 'key' => $k, 'label' => $h ), $keys, $head ),
	'rows'     => $rows,
	'label'    => (string) $attributes['label'],
	'pageSize' => max( 0, (int) $attributes['pageSize'] ),
	'search'   => (bool) $attributes['search'],
);
$table = '<table><caption>' . esc_html( $props['label'] ) . '</caption><thead><tr>' . implode( '', array_map( fn( $h ) => '<th scope="col">' . esc_html( $h ) . '</th>', $head ) ) . '</tr></thead><tbody>'
	. implode( '', array_map( fn( $r ) => '<tr>' . implode( '', array_map( fn( $k ) => '<td>' . esc_html( $r[ $k ] ) . '</td>', $keys ) ) . '</tr>', $rows ) ) . '</tbody></table>';
echo marccent_blocks_island( $block, 'data-table', $props, $table ); // phpcs:ignore WordPress.Security.EscapeOutput
