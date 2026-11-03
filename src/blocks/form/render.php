<?php
/**
 * Form: a real <form> posting to admin-post.php (works without JavaScript; the runtime's enhancer sends it in place,
 * runtime/forms.js), its fields being its inner blocks. What it accepts is read back from this page by the server
 * (inc/forms.php), so nothing here is trusted: the hidden fields only say which form on which page, with a nonce.
 * After a no-JS submission the page shows the result: the success message, or the errors beside their fields.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a       = $attributes;
$form_id = sanitize_key( $a['formId'] );
$post_id = (int) get_the_ID();
if ( '' === $form_id || ! $post_id ) {
	return;
}
$result = marccent_forms_result( $form_id );

$GLOBALS['marccent_form'][] = array( 'id' => $form_id, 'errors' => (array) ( $result['errors'] ?? array() ), 'values' => (array) ( $result['values'] ?? array() ) );
$fields                     = marccent_blocks_inner( $block );
array_pop( $GLOBALS['marccent_form'] );

$status = '';
if ( $result ) {
	$status = $result['sent']
		? marccent_blocks_alert( 'success', esc_html( marccent_blocks_text( $a['successMessage'] ) ?: __( 'Thanks! We got it.', 'marccent-blocks' ) ) )
		: marccent_blocks_alert( 'danger', esc_html( (string) ( $result['message'] ?? '' ) ) );
}

$turnstile = marccent_forms_turnstile();
$widget    = '';
if ( '' !== $turnstile['site'] ) {
	wp_enqueue_script( 'cloudflare-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, array( 'strategy' => 'defer', 'in_footer' => true ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Cloudflare's, unversioned
	$widget = sprintf( '<div class="cf-turnstile" data-sitekey="%s"></div>', esc_attr( $turnstile['site'] ) );
}

$hidden = array(
	'action'          => 'marccent_form',
	'marccent_form'   => $form_id,
	'marccent_post'   => $post_id,
	'_marccent_nonce' => wp_create_nonce( "marccent_form_{$post_id}_{$form_id}" ),
);
$inputs = '';
foreach ( $hidden as $name => $value ) {
	$inputs .= sprintf( '<input type="hidden" name="%s" value="%s">', esc_attr( $name ), esc_attr( (string) $value ) );
}

$submit = marccent_blocks_button(
	array( 'label' => $a['submitLabel'] ?: __( 'Send', 'marccent-blocks' ), 'icon' => $a['submitIcon'] ?: 'paper-plane', 'classes' => 'primary' ),
	fn( $attrs ) => marccent_blocks_attributes( array_merge( $attrs, array( 'type' => 'submit' ) ) )
);

printf(
	'<form %s>%s<div data-form-status="" role="status">%s</div>%s%s%s</form>',
	marccent_blocks_wrapper( $block, array( // phpcs:ignore WordPress.Security.EscapeOutput
		'id'                 => "form-$form_id",
		'data-component'     => 'form',
		'data-marccent-form' => $form_id,
		'class'              => in_array( $a['layout'], array( 'grid gap-sm pairs', 'grid cols-1 gap-sm' ), true ) ? $a['layout'] : 'grid gap-sm pairs',
		'method'             => 'post',
		'enctype'            => 'multipart/form-data', // a file field's upload, without JavaScript too
		'action'             => admin_url( 'admin-post.php' ),
		'data-rest'          => rest_url( 'marccent-blocks/v1/forms/submit' ),
		'data-rest-nonce'    => wp_create_nonce( 'wp_rest' ),
	) ),
	$inputs, // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above
	$status, // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above
	$fields, // phpcs:ignore WordPress.Security.EscapeOutput -- rendered blocks
	$widget, // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above
	$submit // phpcs:ignore WordPress.Security.EscapeOutput -- the theme's Button
);
