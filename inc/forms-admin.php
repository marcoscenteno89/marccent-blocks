<?php
/**
 * The Form block's admin screens (inc/forms.php keeps and emails the entries): Forms → Entries, one entry read only,
 * and Settings → Marccent forms with the Turnstile keys. Loaded in the admin only.
 */

defined( 'ABSPATH' ) || exit;

// The list: the form, its page, the first values, whether Turnstile checked it and whether the email went.
add_filter( 'manage_marccent_entry_posts_columns', fn() => array(
	'cb'       => '<input type="checkbox" />',
	'title'    => __( 'Entry', 'marccent-blocks' ),
	'values'   => __( 'Values', 'marccent-blocks' ),
	'source'   => __( 'Form', 'marccent-blocks' ),
	'verified' => __( 'Checked', 'marccent-blocks' ),
	'mail'     => __( 'Email', 'marccent-blocks' ),
	'date'     => __( 'Date', 'marccent-blocks' ),
) );

add_action( 'manage_marccent_entry_posts_custom_column', function ( $column, $id ) {
	switch ( $column ) {
		case 'values':
			$fields = array_slice( (array) get_post_meta( $id, '_marccent_fields', true ), 0, 3 );
			echo esc_html( implode( ' · ', array_filter( array_map( fn( $f ) => mb_strimwidth( (string) ( $f['value'] ?? '' ), 0, 60, '…' ), $fields ), 'strlen' ) ) );
			break;
		case 'source':
			$post = (int) get_post_meta( $id, '_marccent_post', true );
			$form = (string) get_post_meta( $id, '_marccent_form', true );
			printf( '<a href="%s"><code>%s</code></a><br><a href="%s">%s</a>', esc_url( add_query_arg( 'marccent_form', $form ) ), esc_html( $form ), esc_url( (string) get_permalink( $post ) ), esc_html( get_the_title( $post ) ) );
			break;
		case 'verified':
			echo get_post_meta( $id, '_marccent_verified', true ) ? esc_html__( 'Turnstile', 'marccent-blocks' ) : '<span style="color:#b32d2e">' . esc_html__( 'Not checked', 'marccent-blocks' ) . '</span>';
			break;
		case 'mail':
			$mail = get_post_meta( $id, '_marccent_mail', true );
			echo 'sent' === $mail ? esc_html__( 'Sent', 'marccent-blocks' ) : sprintf( '<span style="color:#b32d2e" title="%s">%s</span>', esc_attr( (string) get_post_meta( $id, '_marccent_mail_error', true ) ), esc_html__( 'Failed', 'marccent-blocks' ) );
			break;
	}
}, 10, 2 );

// Download as CSV: above the list, the entries shown (one form's when the list is filtered by it), a column per field.
add_filter( 'views_edit-marccent_entry', function ( $views ) {
	$form = sanitize_key( $_GET['marccent_form'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification -- a link only
	$views['marccent_csv'] = sprintf( '<a href="%s" class="button button-small" style="margin-left:8px">%s</a>', esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'marccent_entries_csv', 'form' => $form ), admin_url( 'admin-post.php' ) ), 'marccent_entries_csv' ) ), esc_html__( 'Download CSV', 'marccent-blocks' ) );
	return $views;
} );

// The list filtered by one form: ?marccent_form=<formId>, the Form column's id linking to it.
add_action( 'pre_get_posts', function ( WP_Query $q ) {
	if ( is_admin() && $q->is_main_query() && 'marccent_entry' === $q->get( 'post_type' ) && ! empty( $_GET['marccent_form'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- a filter
		$q->set( 'meta_query', array( array( 'key' => '_marccent_form', 'value' => sanitize_key( $_GET['marccent_form'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
} );

add_action( 'admin_post_marccent_entries_csv', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'marccent-blocks' ) );
	}
	check_admin_referer( 'marccent_entries_csv' );
	$form    = sanitize_key( $_GET['form'] ?? '' );
	$entries = get_posts( array( 'post_type' => 'marccent_entry', 'post_status' => 'any', 'numberposts' => -1, 'orderby' => 'date', 'order' => 'DESC', 'meta_query' => $form ? array( array( 'key' => '_marccent_form', 'value' => $form ) ) : array() ) );
	$columns = array();
	foreach ( $entries as $e ) {
		foreach ( (array) get_post_meta( $e->ID, '_marccent_fields', true ) as $name => $f ) {
			$columns[ $name ] = $columns[ $name ] ?? (string) ( $f['label'] ?? $name );
		}
	}
	// A value a spreadsheet would run as a formula (=, +, -, @) starts with a quote instead.
	$cell = fn( $v ) => preg_match( '/^[=+\-@\t\r]/', (string) $v ) ? "'" . $v : (string) $v;
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="entries-' . ( $form ?: 'all' ) . '-' . gmdate( 'Y-m-d' ) . '.csv"' );
	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // UTF-8, as Excel reads it
	fputcsv( $out, array_merge( array( __( 'Date', 'marccent-blocks' ), __( 'Form', 'marccent-blocks' ), __( 'Page', 'marccent-blocks' ) ), array_values( $columns ) ) );
	foreach ( $entries as $e ) {
		$fields = (array) get_post_meta( $e->ID, '_marccent_fields', true );
		$row    = array( get_date_from_gmt( $e->post_date_gmt, 'Y-m-d H:i' ), (string) get_post_meta( $e->ID, '_marccent_form', true ), get_the_title( (int) get_post_meta( $e->ID, '_marccent_post', true ) ) );
		foreach ( array_keys( $columns ) as $name ) {
			$row[] = $cell( $fields[ $name ]['value'] ?? '' );
		}
		fputcsv( $out, $row );
	}
	fclose( $out );
	exit;
} );

// One entry: its fields, read only (no editor, no Publish box: an entry is what was sent).
add_action( 'add_meta_boxes_marccent_entry', function ( WP_Post $entry ) {
	remove_meta_box( 'submitdiv', 'marccent_entry', 'side' );
	remove_meta_box( 'slugdiv', 'marccent_entry', 'normal' );
	add_meta_box( 'marccent-entry', __( 'What was sent', 'marccent-blocks' ), function ( WP_Post $entry ) {
		echo '<table class="widefat striped"><tbody>';
		foreach ( (array) get_post_meta( $entry->ID, '_marccent_fields', true ) as $name => $f ) {
			$value = (string) ( $f['value'] ?? '' );
			// An upload's address: a link to it.
			$shown = str_contains( $value, '/marccent-forms/' ) && wp_http_validate_url( $value ) ? sprintf( '<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url( $value ), esc_html( wp_basename( $value ) ) ) : nl2br( esc_html( $value ) );
			printf( '<tr><th scope="row" style="width:12rem">%s <code>%s</code></th><td>%s</td></tr>', esc_html( (string) ( $f['label'] ?? '' ) ), esc_html( (string) $name ), $shown ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above
		}
		echo '</tbody></table>';
		$post = (int) get_post_meta( $entry->ID, '_marccent_post', true );
		printf(
			'<p>%s <a href="%s">%s</a> · %s · %s</p>',
			esc_html__( 'From', 'marccent-blocks' ),
			esc_url( (string) get_permalink( $post ) ),
			esc_html( get_the_title( $post ) ),
			get_post_meta( $entry->ID, '_marccent_verified', true ) ? esc_html__( 'checked by Turnstile', 'marccent-blocks' ) : esc_html__( 'not checked for spam', 'marccent-blocks' ),
			'sent' === get_post_meta( $entry->ID, '_marccent_mail', true ) ? esc_html__( 'emailed', 'marccent-blocks' ) : esc_html( sprintf( /* translators: %s: why */ __( 'email failed: %s', 'marccent-blocks' ), (string) get_post_meta( $entry->ID, '_marccent_mail_error', true ) ) )
		);
	}, 'marccent_entry', 'normal', 'high' );
} );

/* Settings → Marccent forms: the Turnstile keys */

add_action( 'admin_init', function () {
	register_setting( 'marccent_forms', 'marccent_turnstile', array(
		'type'              => 'array',
		'default'           => array(),
		'sanitize_callback' => fn( $value ) => array(
			'site'   => preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) ( $value['site'] ?? '' ) ),
			'secret' => preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) ( $value['secret'] ?? '' ) ),
		),
	) );
	add_settings_section( 'marccent_turnstile', __( 'Cloudflare Turnstile', 'marccent-blocks' ), function () {
		echo '<p>' . esc_html__( 'Checks that a form’s sender is a person before it’s kept. Get a site key and a secret key from Cloudflare (Turnstile → Add widget, with this site’s domain). Without them, forms still work, unchecked for spam.', 'marccent-blocks' ) . '</p>';
	}, 'marccent_forms' );
	foreach ( array( 'site' => __( 'Site key', 'marccent-blocks' ), 'secret' => __( 'Secret key', 'marccent-blocks' ) ) as $key => $label ) {
		add_settings_field( "marccent_turnstile_$key", $label, function () use ( $key ) {
			printf( '<input type="%s" name="marccent_turnstile[%s]" value="%s" class="regular-text" autocomplete="off">', 'secret' === $key ? 'password' : 'text', esc_attr( $key ), esc_attr( marccent_forms_turnstile()[ $key ] ) );
		}, 'marccent_forms', 'marccent_turnstile' );
	}
} );

add_action( 'admin_menu', function () {
	add_options_page( __( 'Marccent forms', 'marccent-blocks' ), __( 'Marccent forms', 'marccent-blocks' ), 'manage_options', 'marccent-forms', function () {
		echo '<div class="wrap"><h1>' . esc_html__( 'Marccent forms', 'marccent-blocks' ) . '</h1><form method="post" action="options.php">';
		settings_fields( 'marccent_forms' );
		do_settings_sections( 'marccent_forms' );
		submit_button();
		echo '</form></div>';
	} );
} );

// On the Forms screens and the settings page, while there are no Turnstile keys: spam protection is off.
add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( marccent_forms_turnstile_on() || ! $screen || ! current_user_can( 'manage_options' ) || ( 'marccent_entry' !== $screen->post_type && 'settings_page_marccent-forms' !== $screen->id ) ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
		esc_html__( 'Spam protection is off: forms accept submissions without a Turnstile check, marked “Not checked”.', 'marccent-blocks' ),
		esc_url( admin_url( 'options-general.php?page=marccent-forms' ) ),
		esc_html__( 'Add the Turnstile keys', 'marccent-blocks' )
	);
} );
