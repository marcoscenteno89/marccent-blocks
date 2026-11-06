<?php
/**
 * Forms: the Form block (src/blocks/form) and its fields (form-field, form-rating, form-tags) post to this server side,
 * which keeps each submission as an entry (Forms → Entries) and emails it.
 *
 * What a form accepts is read from the page it's on, never from the request: the post's blocks are parsed, the Form
 * block with that formId found, and only its fields read, each checked for its type (a select's value one of its
 * options, a rating 1..max…), with a length cap. Before that: the nonce (per post and form), a rate limit per visitor
 * and form, and, when the site has Turnstile keys (Settings → Marccent forms), Cloudflare Turnstile.
 *
 * Two ways in, one code path (marccent_forms_submit): a plain POST to admin-post.php (no JavaScript: back to the page
 * with a flag, errors kept a few minutes under a random token), and the REST route the runtime's enhancer uses
 * (src/runtime/forms.js: JSON back, errors shown beside their fields).
 *
 * A field can show only when another has a value (its showIf: "service=Patios,Driveways", "contact!=Phone", "phone"
 * for filled in); hidden, it isn't required and isn't kept. A "step" field splits the form into steps (the runtime shows
 * one at a time; without JavaScript it's one long form). A file field takes an upload (images, PDFs, office documents,
 * 5 MB by default: the marccent_forms_max_upload filter), kept under uploads/marccent-forms/ at an address no one can
 * guess, and linked from the entry and the email. A form with a webhook posts each entry there as JSON (Zapier, Make,
 * a CRM).
 *
 * The nonce is printed in the page: a page cache keeps it longer than a nonce lives (a day at most), so a cached page's
 * form fails as expired. Cache pages with forms for less than 12 hours, or not at all.
 */

defined( 'ABSPATH' ) || exit;

const MARCCENT_FORM_FIELDS = array( 'marccent/form-field', 'marccent/form-rating', 'marccent/form-tags' );

/* What a form is, from its page */

// The Form block with this formId among blocks, at any depth.
function marccent_forms_find( array $blocks, string $form_id, int $depth = 0 ): ?array {
	foreach ( $blocks as $block ) {
		if ( 'marccent/form' === $block['blockName'] && ( $block['attrs']['formId'] ?? '' ) === $form_id ) {
			return $block;
		}
		// Into a synced pattern (a published one) or a template part, as the page shows them; not round in circles.
		$inner = $block['innerBlocks'] ?? array();
		if ( $depth < 5 && 'core/block' === $block['blockName'] && ! empty( $block['attrs']['ref'] ) ) {
			$pattern = get_post( (int) $block['attrs']['ref'] );
			$inner   = $pattern && 'wp_block' === $pattern->post_type && 'publish' === $pattern->post_status ? parse_blocks( $pattern->post_content ) : array();
		} elseif ( $depth < 5 && 'core/template-part' === $block['blockName'] && ! empty( $block['attrs']['slug'] ) ) {
			$part  = get_block_template( ( $block['attrs']['theme'] ?? get_stylesheet() ) . '//' . $block['attrs']['slug'], 'wp_template_part' );
			$inner = $part ? parse_blocks( $part->content ) : array();
		}
		$found = marccent_forms_find( $inner, $form_id, $depth + 1 );
		if ( $found ) {
			return $found;
		}
	}
	return null;
}

// A block's attributes with its block.json defaults (parse_blocks leaves out the ones at their default).
function marccent_forms_attrs( array $block ): array {
	$type = WP_Block_Type_Registry::get_instance()->get_registered( $block['blockName'] );
	return $type ? $type->prepare_attributes_for_render( $block['attrs'] ?? array() ) : (array) ( $block['attrs'] ?? array() );
}

// A form's fields, name => definition, in their order; a name used twice keeps the first. At most 50.
function marccent_forms_fields( array $blocks, array $fields = array() ): array {
	foreach ( $blocks as $block ) {
		if ( in_array( $block['blockName'], MARCCENT_FORM_FIELDS, true ) ) {
			$a    = marccent_forms_attrs( $block );
			$name = sanitize_key( (string) ( $a['name'] ?? '' ) );
			if ( '' !== $name && ! isset( $fields[ $name ] ) && count( $fields ) < 50 && 'step' !== ( $a['type'] ?? '' ) ) {
				$kind            = substr( $block['blockName'], strlen( 'marccent/form-' ) );
				$fields[ $name ] = array(
					'kind'     => $kind,
					'type'     => 'field' === $kind ? (string) $a['type'] : $kind,
					'label'    => marccent_blocks_text( (string) ( $a['label'] ?? '' ) ) ?: $name,
					'required' => ! empty( $a['required'] ),
					'options'  => 'select' === ( $a['type'] ?? '' ) ? marccent_forms_options( (string) $a['options'] ) : array(),
					'max'      => max( 0, (int) ( $a['max'] ?? 0 ) ),
					'show_if'  => trim( (string) ( $a['showIf'] ?? '' ) ),
				);
			}
		}
		$fields = marccent_forms_fields( $block['innerBlocks'] ?? array(), $fields );
	}
	return $fields;
}

function marccent_forms_options( string $options ): array {
	return array_values( array_unique( array_filter( array_map( 'trim', explode( "\n", $options ) ), 'strlen' ) ) );
}

/*
 * A form on a published post anyone can read (not password-protected): its block's attributes and its fields. Null for
 * anything else, so a request can't reach a draft's or a private page's form.
 */
function marccent_forms_definition( int $post_id, string $form_id ): ?array {
	$post = $post_id ? get_post( $post_id ) : null;
	if ( ! $post || '' === $form_id || 'publish' !== $post->post_status || '' !== $post->post_password || ! is_post_publicly_viewable( $post ) ) {
		return null;
	}
	$block = marccent_forms_find( parse_blocks( $post->post_content ), $form_id );
	// Not in the page's own content: the theme's template parts (the header, the footer), around every page.
	foreach ( $block ? array() : get_block_templates( array(), 'wp_template_part' ) as $part ) {
		$block = marccent_forms_find( parse_blocks( $part->content ), $form_id );
		if ( $block ) {
			break;
		}
	}
	return $block ? array( 'post' => $post, 'attrs' => marccent_forms_attrs( $block ), 'fields' => marccent_forms_fields( $block['innerBlocks'] ) ) : null;
}

/* Checking what was sent */

/*
 * The defined fields' values from the request, each checked for its type and capped in length; unknown names are
 * never read. Returns [ values: name => clean value, errors: name => message ].
 */
function marccent_forms_validate( array $fields, array $input, array $files = array() ): array {
	$values = array();
	$errors = array();
	foreach ( $fields as $name => $f ) {
		if ( ! marccent_forms_shown( $f['show_if'] ?? '', $input ) ) {
			$values[ $name ] = ''; // hidden: not asked, so not required and not kept
			continue;
		}
		if ( 'file' === $f['type'] ) {
			[ $value, $error ] = marccent_forms_check_file( $files[ $name ] ?? null, $f );
			if ( $error ) {
				$errors[ $name ] = $error;
			}
			$values[ $name ] = $value;
			continue;
		}
		$raw = $input[ $name ] ?? '';
		if ( ! is_scalar( $raw ) ) {
			$errors[ $name ] = __( 'That isn’t a valid value.', 'marccent-blocks' );
			continue;
		}
		$raw = trim( (string) $raw );
		$cap = 'textarea' === $f['type'] ? 5000 : ( 'tags' === $f['type'] ? 1000 : 500 );
		if ( mb_strlen( $raw ) > $cap ) {
			/* translators: %d: the most characters */
			$errors[ $name ] = sprintf( __( 'Keep it under %d characters.', 'marccent-blocks' ), $cap );
			continue;
		}
		if ( '' === $raw ) {
			if ( $f['required'] ) {
				$errors[ $name ] = 'checkbox' === $f['type'] ? __( 'Check this to go on.', 'marccent-blocks' ) : __( 'Fill this in.', 'marccent-blocks' );
			}
			$values[ $name ] = '';
			continue;
		}
		$value = null;
		switch ( $f['type'] ) {
			case 'email':
				$value = is_email( $raw ) ? sanitize_email( $raw ) : null;
				$error = __( 'Enter an email address, like name@example.com.', 'marccent-blocks' );
				break;
			case 'url':
				$url   = esc_url_raw( $raw, array( 'http', 'https' ) );
				$value = $url && wp_http_validate_url( $url ) ? $url : null;
				$error = __( 'Enter a web address, like https://example.com.', 'marccent-blocks' );
				break;
			case 'number':
				$value = is_numeric( $raw ) ? (string) ( 0 + $raw ) : null;
				$error = __( 'Enter a number.', 'marccent-blocks' );
				break;
			case 'date':
				$value = preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $m ) && checkdate( (int) $m[2], (int) $m[3], (int) $m[1] ) ? $raw : null;
				$error = __( 'Enter a date.', 'marccent-blocks' );
				break;
			case 'tel':
				$value = preg_match( '/^[0-9+().\-\s]{3,30}$/', $raw ) ? $raw : null;
				$error = __( 'Enter a phone number.', 'marccent-blocks' );
				break;
			case 'select':
				$value = in_array( $raw, $f['options'], true ) ? $raw : null;
				$error = __( 'Choose one of the options.', 'marccent-blocks' );
				break;
			case 'checkbox':
				$value = '1' === $raw ? __( 'Yes', 'marccent-blocks' ) : null;
				$error = __( 'That isn’t a valid value.', 'marccent-blocks' );
				break;
			case 'rating':
				$max   = $f['max'] ?: 5;
				$value = ctype_digit( $raw ) && (int) $raw >= 1 && (int) $raw <= $max ? $raw : null;
				/* translators: %d: the most stars */
				$error = sprintf( __( 'Choose 1 to %d stars.', 'marccent-blocks' ), $max );
				break;
			case 'tags':
				$tags  = array_values( array_unique( array_filter( array_map( fn( $t ) => mb_substr( sanitize_text_field( $t ), 0, 50 ), explode( ',', $raw ) ), 'strlen' ) ) );
				$value = ! $f['max'] || count( $tags ) <= $f['max'] ? implode( ', ', $tags ) : null;
				/* translators: %d: the most tags */
				$error = sprintf( __( 'Up to %d tags.', 'marccent-blocks' ), $f['max'] );
				break;
			case 'textarea':
				$value = sanitize_textarea_field( $raw );
				break;
			default:
				$value = sanitize_text_field( $raw );
		}
		if ( null === $value ) {
			$errors[ $name ] = $error;
		} else {
			$values[ $name ] = $value;
		}
	}
	return array( $values, $errors );
}

// Whether a field with a showIf is shown, given what was sent: "name=a,b" (one of), "name!=a", "name" (filled in).
function marccent_forms_shown( string $if, array $input ): bool {
	if ( '' === $if || ! preg_match( '/^([a-z0-9_\-]+)\s*(?:(!=|=)\s*(.*))?$/i', $if, $m ) ) {
		return true;
	}
	$have = trim( (string) ( is_scalar( $input[ sanitize_key( $m[1] ) ] ?? '' ) ? $input[ sanitize_key( $m[1] ) ] ?? '' : '' ) );
	if ( empty( $m[2] ) ) {
		return '' !== $have;
	}
	$in = in_array( $have, array_map( 'trim', explode( ',', $m[3] ) ), true );
	return '=' === $m[2] ? $in : ! $in;
}

// The kinds of file a form takes: images, PDFs and office documents.
function marccent_forms_mimes(): array {
	return (array) apply_filters( 'marccent_forms_mimes', array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
		'gif'          => 'image/gif',
		'webp'         => 'image/webp',
		'heic'         => 'image/heic',
		'pdf'          => 'application/pdf',
		'doc'          => 'application/msword',
		'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'xls'          => 'application/vnd.ms-excel',
		'xlsx'         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'txt'          => 'text/plain',
		'csv'          => 'text/csv',
	) );
}

// An upload, checked (size, kind) but not kept yet: [ the file to keep, or '' when there's none, error message ].
function marccent_forms_check_file( $file, array $f ): array {
	if ( ! is_array( $file ) || UPLOAD_ERR_NO_FILE === (int) ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) ) {
		return array( '', $f['required'] ? __( 'Choose a file.', 'marccent-blocks' ) : '' );
	}
	$max = (int) apply_filters( 'marccent_forms_max_upload', 5 * MB_IN_BYTES );
	if ( UPLOAD_ERR_OK !== (int) $file['error'] || ! is_uploaded_file( (string) $file['tmp_name'] ) ) {
		return array( '', in_array( (int) $file['error'], array( UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE ), true ) ? sprintf( /* translators: %s: a size, 5 MB */ __( 'Keep it under %s.', 'marccent-blocks' ), size_format( $max ) ) : __( 'The file didn’t arrive. Try again.', 'marccent-blocks' ) );
	}
	if ( (int) $file['size'] > $max ) {
		/* translators: %s: a size, 5 MB */
		return array( '', sprintf( __( 'Keep it under %s.', 'marccent-blocks' ), size_format( $max ) ) );
	}
	$type = wp_check_filetype_and_ext( (string) $file['tmp_name'], (string) $file['name'], marccent_forms_mimes() );
	if ( ! $type['ext'] || ! $type['type'] ) {
		return array( '', __( 'That kind of file isn’t accepted: send an image, a PDF or an office document.', 'marccent-blocks' ) );
	}
	return array( array( 'upload' => $file ), '' );
}

// Keeps a checked upload under uploads/marccent-forms/<random>/ and returns its address ('' if it couldn't).
function marccent_forms_store_file( array $file ): string {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	$dir = 'marccent-forms/' . wp_generate_password( 24, false );
	$to  = function ( $d ) use ( $dir ) {
		return array_merge( $d, array( 'subdir' => "/$dir", 'path' => $d['basedir'] . "/$dir", 'url' => $d['baseurl'] . "/$dir" ) );
	};
	add_filter( 'upload_dir', $to );
	$done = wp_handle_upload( $file, array( 'test_form' => false, 'mimes' => marccent_forms_mimes() ) );
	remove_filter( 'upload_dir', $to );
	if ( ! empty( $done['file'] ) ) {
		// No listing of the folder: only who has the address finds the file.
		@file_put_contents( dirname( $done['file'] ) . '/index.html', '' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	}
	return (string) ( $done['url'] ?? '' );
}

// The visitor's address, as the server got it (not X-Forwarded-For, which anyone can write). Behind a proxy, the host
// should set REMOTE_ADDR to the visitor's, or every visitor shares one rate limit.
function marccent_forms_ip(): string {
	return (string) filter_var( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ), FILTER_VALIDATE_IP ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- validated as an IP
}

// Whether this visitor may send this form again: at most marccent_forms_rate_limit (5) tries in 10 minutes, each try
// counted (invalid ones too).
function marccent_forms_rate_ok( int $post_id, string $form_id ): bool {
	$key  = 'marccent_form_rate_' . substr( wp_hash( marccent_forms_ip() . "|$post_id|$form_id" ), 0, 32 );
	$now  = time();
	$seen = get_transient( $key );
	$seen = is_array( $seen ) && $seen['until'] > $now ? $seen : array( 'n' => 0, 'until' => $now + 10 * MINUTE_IN_SECONDS );
	if ( $seen['n'] >= (int) apply_filters( 'marccent_forms_rate_limit', 5 ) ) {
		return false;
	}
	++$seen['n'];
	set_transient( $key, $seen, $seen['until'] - $now );
	return true;
}

/* Turnstile (its keys: Appearance → Marccent settings, inc/keys.php) */

function marccent_forms_turnstile(): array {
	return array( 'site' => marccent_key( 'turnstile_site' ), 'secret' => marccent_key( 'turnstile_secret' ) );
}

function marccent_forms_turnstile_on(): bool {
	$keys = marccent_forms_turnstile();
	return '' !== $keys['site'] && '' !== $keys['secret'];
}

// Cloudflare's answer for the token the widget put in the form.
function marccent_forms_turnstile_ok( string $token ): bool {
	if ( '' === $token || strlen( $token ) > 2048 ) {
		return false;
	}
	$response = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
		'timeout' => 10,
		'body'    => array( 'secret' => marccent_forms_turnstile()['secret'], 'response' => $token, 'remoteip' => marccent_forms_ip() ),
	) );
	$body = is_wp_error( $response ) ? null : json_decode( wp_remote_retrieve_body( $response ), true );
	return ! empty( $body['success'] );
}

/* reCAPTCHA, the v2 checkbox (its keys: Appearance → Marccent settings) */

function marccent_forms_recaptcha(): array {
	return array( 'site' => marccent_key( 'recaptcha_site' ), 'secret' => marccent_key( 'recaptcha_secret' ) );
}

// Google's answer for the token the checkbox put in the form.
function marccent_forms_recaptcha_ok( string $token ): bool {
	if ( '' === $token || strlen( $token ) > 4096 ) {
		return false;
	}
	$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
		'timeout' => 10,
		'body'    => array( 'secret' => marccent_forms_recaptcha()['secret'], 'response' => $token, 'remoteip' => marccent_forms_ip() ),
	) );
	$body = is_wp_error( $response ) ? null : json_decode( wp_remote_retrieve_body( $response ), true );
	return ! empty( $body['success'] );
}

/* The robot check: Turnstile when it has both keys, else reCAPTCHA when it has both, else none ('') */

function marccent_forms_check(): string {
	if ( marccent_forms_turnstile_on() ) {
		return 'turnstile';
	}
	$recaptcha = marccent_forms_recaptcha();
	return '' !== $recaptcha['site'] && '' !== $recaptcha['secret'] ? 'recaptcha' : '';
}

// Whether a submission passes the check (its widget's token in the fields).
function marccent_forms_check_ok( array $input ): bool {
	return 'recaptcha' === marccent_forms_check()
		? marccent_forms_recaptcha_ok( (string) ( $input['g-recaptcha-response'] ?? '' ) )
		: marccent_forms_turnstile_ok( (string) ( $input['cf-turnstile-response'] ?? '' ) );
}

/* A submission */

/*
 * Checks a submission and keeps it: [ ok, status (HTTP), message, errors: name => message, form, post ].
 * $input is the request's fields, unslashed.
 */
function marccent_forms_submit( array $input, array $files = array() ): array {
	$fail    = fn( int $status, string $message, array $errors = array() ) => array( 'ok' => false, 'status' => $status, 'message' => $message, 'errors' => $errors );
	$form_id = sanitize_key( (string) ( $input['marccent_form'] ?? '' ) );
	$post_id = absint( $input['marccent_post'] ?? 0 );
	$nonce   = (string) ( $input['_marccent_nonce'] ?? '' );

	if ( ! wp_verify_nonce( $nonce, "marccent_form_{$post_id}_{$form_id}" ) ) {
		return $fail( 403, __( 'This form has expired. Reload the page and send it again.', 'marccent-blocks' ) );
	}
	$form = marccent_forms_definition( $post_id, $form_id );
	if ( ! $form ) {
		return $fail( 404, __( 'This form isn’t available.', 'marccent-blocks' ) );
	}
	if ( ! marccent_forms_rate_ok( $post_id, $form_id ) ) {
		return $fail( 429, __( 'Too many tries. Wait a few minutes and send it again.', 'marccent-blocks' ) );
	}
	$verified = '' !== marccent_forms_check();
	if ( $verified && ! marccent_forms_check_ok( $input ) ) {
		return $fail( 403, __( 'We couldn’t check that you’re not a robot. Try again.', 'marccent-blocks' ) );
	}
	[ $values, $errors ] = marccent_forms_validate( $form['fields'], $input, $files );
	if ( $errors ) {
		return $fail( 400, __( 'Some fields need another look.', 'marccent-blocks' ), $errors ) + array( 'values' => array_filter( $values, 'is_string' ) );
	}
	// Everything checked: the uploads kept, each value its address.
	foreach ( $values as $name => $value ) {
		if ( is_array( $value ) && isset( $value['upload'] ) ) {
			$values[ $name ] = marccent_forms_store_file( $value['upload'] );
		}
	}

	// Kept first, then emailed and sent on: a failed email loses nothing.
	$entry = marccent_forms_save( $form, $values, $verified );
	marccent_forms_mail( $form, $values, $entry );
	marccent_forms_webhook( $form, $values, $entry );

	$message = marccent_blocks_text( (string) $form['attrs']['successMessage'] ) ?: __( 'Thanks! We got it.', 'marccent-blocks' );
	return array( 'ok' => true, 'status' => 200, 'message' => $message, 'errors' => array() );
}

// The entry: a private post of the type the form names (Save entries to: a type made in Fields, saved by Marccent
// Content), else Forms → Entries' (marccent_entry), the fields (label and value) as one meta value.
function marccent_forms_save( array $form, array $values, bool $verified ): int {
	$type = sanitize_key( (string) ( $form['attrs']['saveTo'] ?? '' ) );
	$id   = $type ? (int) apply_filters( 'marccent_forms_save_entry', 0, $type, $form, $values, $verified ) : 0;
	if ( $id ) {
		update_post_meta( $id, '_marccent_verified', $verified ? 1 : 0 );
		return $id;
	}
	$fields = array();
	foreach ( $form['fields'] as $name => $f ) {
		$fields[ $name ] = array( 'label' => $f['label'], 'value' => (string) ( $values[ $name ] ?? '' ) );
	}
	$id = wp_insert_post( array(
		'post_type'   => 'marccent_entry',
		'post_status' => 'private',
		/* translators: %s: the page the form is on */
		'post_title'  => sprintf( __( 'Entry from “%s”', 'marccent-blocks' ), get_the_title( $form['post'] ) ),
	) );
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_marccent_fields', $fields );
		update_post_meta( $id, '_marccent_form', $form['attrs']['formId'] );
		update_post_meta( $id, '_marccent_post', $form['post']->ID );
		update_post_meta( $id, '_marccent_verified', $verified ? 1 : 0 );
		return $id;
	}
	return 0;
}

// The email: to the form's address (else the site's), the fields as text; Reply-To the sender's address when the
// form asked for one. Nothing the visitor typed goes in a header but that checked address.
function marccent_forms_mail( array $form, array $values, int $entry ): void {
	$to      = is_email( (string) $form['attrs']['emailTo'] ) ? (string) $form['attrs']['emailTo'] : (string) get_option( 'admin_email' );
	$title   = wp_strip_all_tags( get_the_title( $form['post'] ) );
	/* translators: %s: the page the form is on */
	$subject = marccent_blocks_text( (string) $form['attrs']['emailSubject'] ) ?: sprintf( __( 'New submission: %s', 'marccent-blocks' ), $title );
	$subject = str_replace( array( "\r", "\n" ), ' ', $subject );
	$body    = '';
	$reply   = '';
	foreach ( $form['fields'] as $name => $f ) {
		$body .= $f['label'] . ': ' . ( $values[ $name ] ?? '' ) . "\n";
		if ( ! $reply && 'email' === $f['type'] && is_email( (string) ( $values[ $name ] ?? '' ) ) ) {
			$reply = str_replace( array( "\r", "\n", ',', ';' ), '', sanitize_email( $values[ $name ] ) );
		}
	}
	$body   .= "\n" . get_permalink( $form['post'] ) . "\n";
	$failure = '';
	$catch   = function ( WP_Error $error ) use ( &$failure ) {
		$failure = $error->get_error_message();
	};
	add_action( 'wp_mail_failed', $catch );
	$sent = wp_mail( $to, $subject, $body, $reply ? array( "Reply-To: $reply" ) : array() );
	remove_action( 'wp_mail_failed', $catch );
	if ( $entry ) {
		update_post_meta( $entry, '_marccent_mail', $sent ? 'sent' : 'failed' );
		if ( ! $sent ) {
			update_post_meta( $entry, '_marccent_mail_error', mb_substr( $failure ?: __( 'wp_mail() returned false.', 'marccent-blocks' ), 0, 500 ) );
		}
	}
}

// The form's webhook, if it has one: the entry as JSON, sent without waiting for an answer.
function marccent_forms_webhook( array $form, array $values, int $entry ): void {
	$url = (string) ( $form['attrs']['webhook'] ?? '' );
	if ( '' === $url || ! wp_http_validate_url( $url ) ) {
		return;
	}
	$fields = array();
	foreach ( $form['fields'] as $name => $f ) {
		$fields[ $name ] = (string) ( $values[ $name ] ?? '' );
	}
	wp_safe_remote_post( $url, array(
		'blocking' => false,
		'timeout'  => 5,
		'headers'  => array( 'Content-Type' => 'application/json' ),
		'body'     => wp_json_encode( array(
			'form'      => $form['attrs']['formId'],
			'page'      => get_permalink( $form['post'] ),
			'title'     => get_the_title( $form['post'] ),
			'entry'     => $entry,
			'submitted' => gmdate( 'c' ),
			'fields'    => $fields,
			'labels'    => array_map( fn( $f ) => $f['label'], $form['fields'] ),
		) ),
	) );
}

/* The two ways in */

// Without JavaScript: back to the page, a flag for this form, errors (and what was typed) kept 5 minutes.
function marccent_forms_post(): void {
	$input  = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification -- marccent_forms_submit checks it
	$result = marccent_forms_submit( $input, $_FILES ); // phpcs:ignore WordPress.Security.NonceVerification -- checked there
	$post   = absint( $input['marccent_post'] ?? 0 );
	$form   = sanitize_key( (string) ( $input['marccent_form'] ?? '' ) );
	$page   = $post && 'publish' === get_post_status( $post ) ? get_permalink( $post ) : home_url( '/' );
	if ( $result['ok'] ) {
		$url = add_query_arg( array( 'marccent_form' => $form, 'marccent_sent' => 1 ), $page );
	} else {
		$token = wp_generate_password( 20, false );
		set_transient( "marccent_form_{$token}", array( 'form' => $form, 'message' => $result['message'], 'errors' => $result['errors'], 'values' => $result['values'] ?? array() ), 5 * MINUTE_IN_SECONDS );
		$url = add_query_arg( array( 'marccent_form' => $form, 'marccent_result' => $token ), $page );
	}
	wp_safe_redirect( $url . "#form-$form", 303 );
	exit;
}
add_action( 'admin_post_marccent_form', 'marccent_forms_post' );
add_action( 'admin_post_nopriv_marccent_form', 'marccent_forms_post' );

// With JavaScript (src/runtime/forms.js): the same, as JSON. Open to anyone; the checks are the submission's.
add_action( 'rest_api_init', function () {
	register_rest_route( 'marccent-blocks/v1', '/forms/submit', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => function ( WP_REST_Request $request ) {
			$result = marccent_forms_submit( $request->get_body_params(), $request->get_file_params() );
			return new WP_REST_Response( array( 'ok' => $result['ok'], 'message' => $result['message'], 'errors' => (object) $result['errors'] ), $result['status'] );
		},
	) );
} );

// What a form shows after a no-JS submission: [ sent, message, errors, values ] for this form, else nothing.
function marccent_forms_result( string $form_id ): array {
	// phpcs:disable WordPress.Security.NonceVerification -- reading a flag and a random token back
	if ( sanitize_key( (string) ( $_GET['marccent_form'] ?? '' ) ) !== $form_id ) {
		return array();
	}
	if ( isset( $_GET['marccent_sent'] ) ) {
		return array( 'sent' => true );
	}
	$token = preg_replace( '/[^A-Za-z0-9]/', '', (string) wp_unslash( $_GET['marccent_result'] ?? '' ) );
	// phpcs:enable
	$saved = $token ? get_transient( "marccent_form_{$token}" ) : false;
	return is_array( $saved ) && ( $saved['form'] ?? '' ) === $form_id ? array( 'sent' => false ) + $saved : array();
}

/* Entries (Forms → Entries; their screens and Settings → Marccent forms are in inc/forms-admin.php) */

/*
 * Entries hold what visitors typed (names, emails…), so only administrators (manage_options) see them; nobody adds or
 * publishes one by hand (create_posts / publish_posts: do_not_allow). Not public, not in search, not in the REST API.
 */
add_action( 'init', function () {
	$admin = 'manage_options';
	register_post_type( 'marccent_entry', array(
		'labels'              => array(
			'name'          => __( 'Entries', 'marccent-blocks' ),
			'singular_name' => __( 'Entry', 'marccent-blocks' ),
			'menu_name'     => __( 'Forms', 'marccent-blocks' ),
			'all_items'     => __( 'Entries', 'marccent-blocks' ),
			'edit_item'     => __( 'Entry', 'marccent-blocks' ),
			'search_items'  => __( 'Search entries', 'marccent-blocks' ),
			'not_found'     => __( 'No entries yet.', 'marccent-blocks' ),
		),
		'public'              => false,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => false,
		'show_in_nav_menus'   => false,
		'menu_icon'           => 'dashicons-feedback',
		'supports'            => array( 'title' ),
		'rewrite'             => false,
		'query_var'           => false,
		'map_meta_cap'        => true,
		'capabilities'        => array(
			'create_posts'           => 'do_not_allow',
			'publish_posts'          => 'do_not_allow',
			'edit_posts'             => $admin,
			'edit_others_posts'      => $admin,
			'edit_private_posts'     => $admin,
			'edit_published_posts'   => $admin,
			'read_private_posts'     => $admin,
			'delete_posts'           => $admin,
			'delete_others_posts'    => $admin,
			'delete_private_posts'   => $admin,
			'delete_published_posts' => $admin,
			'read'                   => $admin,
		),
	) );
} );

/* The blocks' markup (their render.php): the theme's Field, Rating and TagInput, as its React components render them */

// The form being rendered (form/render.php sets it): its id, and after a no-JS submission its errors and values.
function marccent_forms_current(): array {
	$stack = $GLOBALS['marccent_form'] ?? array();
	return $stack ? end( $stack ) : array( 'id' => 'form', 'errors' => array(), 'values' => array() );
}

/*
 * What every field block starts from: its name (empty: the block shows nothing), its id on the page, its label, and
 * after a no-JS submission its error and what was sent.
 */
function marccent_forms_field_base( array $a ): array {
	$form = marccent_forms_current();
	$name = sanitize_key( (string) ( $a['name'] ?? '' ) );
	return array(
		'name'  => $name,
		'id'    => "marccent-{$form['id']}-$name",
		'label' => marccent_blocks_text( (string) ( $a['label'] ?? '' ) ) ?: $name,
		'error' => (string) ( $form['errors'][ $name ] ?? '' ),
		'value' => $form['values'][ $name ] ?? '',
	);
}

// A field's help and error lines (under it, a row of their own in the form's grid) and the ids that point to them.
function marccent_forms_notes( string $id, string $help, string $error ): array {
	$ids  = array();
	$html = '';
	if ( '' !== $help ) {
		$ids[] = "$id-help";
		$html .= sprintf( '<p id="%s-help" class="fs-sm opacity-75">%s</p>', esc_attr( $id ), esc_html( $help ) );
	}
	if ( '' !== $error ) {
		$ids[] = "$id-error";
		$html .= sprintf( '<p id="%s-error" class="fs-sm text-secondary" data-field-error="">%s</p>', esc_attr( $id ), esc_html( $error ) );
	}
	return array( implode( ' ', $ids ) ?: null, $html );
}

// A label beside a control, as the theme's Field (.field: the label and the input joined in one box).
function marccent_forms_row( string $label_html, string $control ): string {
	return '<div data-component="field" class="field joined sub-x">' . $label_html . $control . '</div>';
}

function marccent_forms_field_html( array $a ): string {
	// A step break: where the next step starts, titled by its label (the runtime shows one step at a time).
	if ( 'step' === $a['type'] ) {
		return sprintf( '<div class="form-step-break" data-form-step="%s" style="grid-column:1/-1"></div>', esc_attr( marccent_blocks_text( (string) $a['label'] ) ) );
	}
	[ 'name' => $name, 'id' => $id, 'label' => $label, 'error' => $error, 'value' => $value ] = marccent_forms_field_base( $a );
	if ( '' === $name ) {
		return '';
	}
	$value                 = (string) $value;
	[ $described, $notes ] = marccent_forms_notes( $id, marccent_blocks_text( (string) $a['help'] ), $error );
	$type                  = in_array( $a['type'], array( 'text', 'email', 'tel', 'url', 'number', 'date', 'textarea', 'select', 'checkbox', 'file' ), true ) ? $a['type'] : 'text';
	$common                = array(
		'id'               => $id,
		'name'             => $name,
		'class'            => 'input',
		'required'         => $a['required'] ? '' : null,
		'aria-invalid'     => $error ? 'true' : null,
		'aria-describedby' => $described,
	);
	$placeholder = marccent_blocks_text( (string) $a['placeholder'] );
	switch ( $type ) {
		case 'textarea':
			$control = sprintf( '<textarea %s>%s</textarea>', marccent_blocks_attributes( $common + array( 'rows' => 4, 'placeholder' => $placeholder ?: null, 'maxlength' => 5000 ) ), esc_textarea( $value ) );
			break;
		case 'select':
			$options = '<option value="">' . esc_html__( 'Choose…', 'marccent-blocks' ) . '</option>';
			foreach ( marccent_forms_options( (string) $a['options'] ) as $option ) {
				$options .= sprintf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $option ), selected( $value, $option, false ), esc_html( $option ) );
			}
			$control = sprintf( '<select %s>%s</select>', marccent_blocks_attributes( $common ), $options );
			break;
		case 'file':
			$control = sprintf( '<input %s>', marccent_blocks_attributes( $common + array( 'type' => 'file', 'accept' => (string) ( $a['accept'] ?? '' ) ?: 'image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv' ) ) );
			break;
		case 'checkbox':
			// The box in the input's place, so the row still reads label | control.
			$control = sprintf( '<span class="input"><input %s></span>', marccent_blocks_attributes( array( 'type' => 'checkbox', 'value' => '1', 'checked' => '' !== $value ? '' : null ) + array_diff_key( $common, array( 'class' => 1 ) ) ) );
			break;
		default:
			$control = sprintf( '<input %s>', marccent_blocks_attributes( $common + array( 'type' => $type, 'value' => '' !== $value ? $value : null, 'placeholder' => $placeholder ?: null, 'maxlength' => 'number' === $type || 'date' === $type ? null : 500 ) ) );
	}
	$row = marccent_forms_row( sprintf( '<label class="field-label" for="%s">%s</label>', esc_attr( $id ), esc_html( $label ) ), $control ) . $notes;
	$if  = trim( (string) ( $a['showIf'] ?? '' ) );
	// Shown only when another field has a value: the runtime shows and hides it as the visitor fills the form in.
	return '' === $if ? $row : sprintf( '<div class="form-if" data-show-if="%s" style="display:contents">%s</div>', esc_attr( $if ), $row );
}

// Rating: the theme's Rating (radio stars, no JavaScript needed) beside its label.
function marccent_forms_rating_html( array $a ): string {
	[ 'name' => $name, 'id' => $id, 'label' => $label, 'error' => $error, 'value' => $value ] = marccent_forms_field_base( $a );
	if ( '' === $name ) {
		return '';
	}
	$value                 = (int) $value;
	$max                   = max( 1, min( 10, (int) $a['max'] ) );
	[ $described, $notes ] = marccent_forms_notes( $id, '', $error );
	$stars                 = '';
	for ( $i = 1; $i <= $max; $i++ ) {
		$stars .= sprintf(
			'<label for="%1$s-%2$d" class="rating-star"><input %3$s>%4$s<span class="visually-hidden">%5$s</span></label>',
			esc_attr( $id ),
			$i,
			marccent_blocks_attributes( array( 'id' => "$id-$i", 'type' => 'radio', 'name' => $name, 'value' => $i, 'class' => 'visually-hidden', 'checked' => $value === $i ? '' : null, 'required' => $a['required'] ? '' : null ) ),
			marccent_blocks_icon( (string) ( $a['icon'] ?: 'star' ) ),
			esc_html( 1 === $i ? __( '1 star', 'marccent-blocks' ) : sprintf( /* translators: %d: stars */ __( '%d stars', 'marccent-blocks' ), $i ) )
		);
	}
	$rating = sprintf(
		'<fieldset %s><legend class="visually-hidden">%s</legend>%s</fieldset>',
		marccent_blocks_attributes( array( 'data-component' => 'rating', 'class' => 'rating', 'id' => $id, 'aria-invalid' => $error ? 'true' : null, 'aria-describedby' => $described ) ),
		esc_html( $label ),
		$stars
	);
	return marccent_forms_row( sprintf( '<span class="field-label" aria-hidden="true">%s</span>', esc_html( $label ) ), '<span class="input">' . $rating . '</span>' ) . $notes;
}
