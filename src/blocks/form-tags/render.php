<?php
/**
 * Form tags: the theme's TagInput, an island inside the form (runtime/islands/form-tags.js); its hidden input carries
 * the tags, comma-separated, as the plain text input that stands in for it does without JavaScript.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

[ 'name' => $name, 'id' => $id, 'label' => $label, 'error' => $error, 'value' => $value ] = marccent_forms_field_base( $attributes );
if ( '' === $name ) {
	return;
}
$value       = (string) $value;
$suggestions = marccent_forms_options( str_replace( ',', "\n", (string) $attributes['suggestions'] ) );
[ $described, $notes ] = marccent_forms_notes( $id, '', $error );

$props = array(
	'name'         => $name,
	'label'        => $label,
	'max'          => max( 0, (int) $attributes['max'] ),
	'suggestions'  => $suggestions ?: null,
	'defaultValue' => array_values( array_filter( array_map( 'trim', explode( ',', $value ) ), 'strlen' ) ),
);
$stand_in = sprintf( '<input %s>', marccent_blocks_attributes( array( 'id' => $id, 'name' => $name, 'class' => 'input', 'value' => $value ?: null, 'placeholder' => __( 'Comma-separated', 'marccent-blocks' ), 'aria-describedby' => $described, 'aria-invalid' => $error ? 'true' : null ) ) );
$island   = sprintf( '<div data-island="form-tags" data-props="%s" style="display: contents"><div data-slot="" style="display: contents">%s</div></div>', esc_attr( wp_json_encode( $props ) ), $stand_in );

echo marccent_forms_row( sprintf( '<label class="field-label" for="%s">%s</label>', esc_attr( $id ), esc_html( $label ) ), $island ) . $notes; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped as it's built
