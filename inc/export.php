<?php
/**
 * Export and import as JSON: everything the Marccent plugins and theme keep in the database (the starting theme, the
 * business, tracking codes, field groups and post types, site content, header and footer…), in one file, to back a
 * site up, put it in git or start another site from it. Tools → Marccent export, or WP-CLI:
 *
 *   wp marccent export [--parts=theme,business] [--file=site.json]
 *   wp marccent import site.json [--parts=theme]
 *
 * Each plugin adds its own parts with the marccent_export_parts filter, in the order they import in:
 *
 *   $parts['business'] = array( 'label' => 'Business details', 'export' => fn() => array(…), 'import' => fn( array $data ) => … );
 *
 * An import callback sanitizes what it's given as its settings screen would, and may return a WP_Error.
 * The file: { "marccent": 1, "site": "https://…", "exported": "2026-10-30T…", "parts": { "<part>": … } }.
 */

defined( 'ABSPATH' ) || exit;

function marccent_export_parts(): array {
	$parts = apply_filters( 'marccent_export_parts', array() );
	return array_filter( $parts, fn( $p ) => is_callable( $p['export'] ?? null ) && is_callable( $p['import'] ?? null ) );
}

// The starting theme (the marccent_defaults option).
add_filter( 'marccent_export_parts', function ( $parts ) {
	$parts['theme'] = array(
		'label'  => __( 'Starting theme (colors, design, mode)', 'marccent-blocks' ),
		'export' => fn() => marccent_blocks_defaults(),
		'import' => fn( array $data ) => update_option( 'marccent_defaults', wp_json_encode( $data ) ),
	);
	return $parts;
}, 5 );

function marccent_export( ?array $only = null ): array {
	$out = array();
	foreach ( marccent_export_parts() as $key => $part ) {
		if ( null === $only || in_array( $key, $only, true ) ) {
			$out[ $key ] = call_user_func( $part['export'] );
		}
	}
	return array( 'marccent' => 1, 'site' => home_url(), 'exported' => gmdate( 'c' ), 'parts' => $out );
}

/**
 * Puts a file's parts on the site, those in $only if given, in the parts' order. Returns each part's outcome: true,
 * or a WP_Error.
 */
function marccent_import( array $file, ?array $only = null ): array {
	if ( 1 !== ( $file['marccent'] ?? null ) || ! is_array( $file['parts'] ?? null ) ) {
		return array( 'file' => new WP_Error( 'marccent_import', __( 'That isn’t a Marccent export.', 'marccent-blocks' ) ) );
	}
	$done = array();
	foreach ( marccent_export_parts() as $key => $part ) {
		if ( ! array_key_exists( $key, $file['parts'] ) || ( null !== $only && ! in_array( $key, $only, true ) ) ) {
			continue;
		}
		$data = $file['parts'][ $key ];
		if ( ! is_array( $data ) ) {
			$done[ $key ] = new WP_Error( 'marccent_import', __( 'Not in the expected shape.', 'marccent-blocks' ) );
			continue;
		}
		$result       = call_user_func( $part['import'], $data );
		$done[ $key ] = is_wp_error( $result ) ? $result : true;
	}
	return $done;
}

/* Tools → Marccent export */

add_action( 'admin_menu', function () {
	add_management_page( __( 'Marccent export', 'marccent-blocks' ), __( 'Marccent export', 'marccent-blocks' ), 'manage_options', 'marccent-export', 'marccent_export_page' );
} );

add_action( 'admin_post_marccent_export', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'marccent-blocks' ) );
	}
	check_admin_referer( 'marccent_export' );
	$only = array_map( 'sanitize_key', (array) ( $_POST['parts'] ?? array() ) );
	$name = sanitize_file_name( wp_parse_url( home_url(), PHP_URL_HOST ) . '-marccent-' . gmdate( 'Y-m-d' ) . '.json' );
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $name . '"' );
	echo wp_json_encode( marccent_export( $only ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	exit;
} );

add_action( 'admin_post_marccent_import', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Not allowed.', 'marccent-blocks' ) );
	}
	check_admin_referer( 'marccent_import' );
	$back = admin_url( 'tools.php?page=marccent-export' );
	$path = $_FILES['file']['tmp_name'] ?? ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- an uploaded file's temporary path, read below
	$file = $path && is_uploaded_file( $path ) ? json_decode( (string) file_get_contents( $path ), true ) : null;
	$only = array_map( 'sanitize_key', (array) ( $_POST['parts'] ?? array() ) );
	$done = is_array( $file ) ? marccent_import( $file, $only ) : array( 'file' => new WP_Error( 'marccent_import', __( 'Choose a JSON file exported from a Marccent site.', 'marccent-blocks' ) ) );
	set_transient( 'marccent_import_' . get_current_user_id(), array_map( fn( $r ) => is_wp_error( $r ) ? $r->get_error_message() : true, $done ), 300 );
	wp_safe_redirect( add_query_arg( 'imported', 1, $back ) );
	exit;
} );

function marccent_export_page(): void {
	$parts  = marccent_export_parts();
	$result = isset( $_GET['imported'] ) ? get_transient( 'marccent_import_' . get_current_user_id() ) : false; // phpcs:ignore WordPress.Security.NonceVerification -- a notice only
	delete_transient( 'marccent_import_' . get_current_user_id() );
	$checks = function () use ( $parts ) {
		foreach ( $parts as $key => $part ) {
			printf( '<label style="display:block;margin:.25rem 0"><input type="checkbox" name="parts[]" value="%s" checked> %s</label>', esc_attr( $key ), esc_html( $part['label'] ) );
		}
	};
	echo '<div class="wrap"><h1>' . esc_html__( 'Marccent export', 'marccent-blocks' ) . '</h1>';
	if ( is_array( $result ) ) {
		foreach ( $result as $key => $r ) {
			$label = $parts[ $key ]['label'] ?? $key;
			printf(
				'<div class="notice notice-%s"><p>%s</p></div>',
				true === $r ? 'success' : 'error',
				esc_html( true === $r ? sprintf( /* translators: %s: a part, e.g. Business details */ __( 'Imported: %s.', 'marccent-blocks' ), $label ) : "$label: $r" )
			);
		}
		if ( ! $result ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'The file had none of the parts you chose.', 'marccent-blocks' ) . '</p></div>';
		}
	}
	echo '<p>' . esc_html__( 'Everything the Marccent plugins and theme keep in the database, as one JSON file: to back the site up, keep it in git, or set another site up the same way. Page content, posts and media are WordPress’s own export (Tools → Export).', 'marccent-blocks' ) . '</p>';

	echo '<h2>' . esc_html__( 'Export', 'marccent-blocks' ) . '</h2><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="marccent_export">';
	wp_nonce_field( 'marccent_export' );
	$checks();
	submit_button( __( 'Download JSON', 'marccent-blocks' ), 'primary', 'submit', false );
	echo '</form>';

	echo '<h2 style="margin-top:2rem">' . esc_html__( 'Import', 'marccent-blocks' ) . '</h2><form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	echo '<input type="hidden" name="action" value="marccent_import">';
	wp_nonce_field( 'marccent_import' );
	echo '<p><input type="file" name="file" accept="application/json,.json" required></p>';
	echo '<p class="description">' . esc_html__( 'The parts you tick replace this site’s; the others stay as they are.', 'marccent-blocks' ) . '</p>';
	$checks();
	submit_button( __( 'Import', 'marccent-blocks' ), 'secondary', 'submit', false );
	echo '</form></div>';
}

/* WP-CLI */

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	/**
	 * Exports the Marccent settings as JSON.
	 *
	 * [--parts=<parts>]
	 * : Comma-separated parts (theme, business, tracking…); all by default.
	 *
	 * [--file=<file>]
	 * : Write to this file instead of printing.
	 */
	WP_CLI::add_command( 'marccent export', function ( $args, $assoc ) {
		$only = isset( $assoc['parts'] ) ? array_map( 'trim', explode( ',', $assoc['parts'] ) ) : null;
		$json = wp_json_encode( marccent_export( $only ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		if ( isset( $assoc['file'] ) ) {
			file_put_contents( $assoc['file'], $json . "\n" );
			WP_CLI::success( "Exported to {$assoc['file']}." );
		} else {
			WP_CLI::line( $json );
		}
	} );

	/**
	 * Imports Marccent settings from a JSON export.
	 *
	 * <file>
	 * : The export.
	 *
	 * [--parts=<parts>]
	 * : Comma-separated parts to import; all the file has by default.
	 */
	WP_CLI::add_command( 'marccent import', function ( $args, $assoc ) {
		$file = json_decode( (string) file_get_contents( $args[0] ), true );
		$only = isset( $assoc['parts'] ) ? array_map( 'trim', explode( ',', $assoc['parts'] ) ) : null;
		$fail = false;
		foreach ( marccent_import( is_array( $file ) ? $file : array(), $only ) as $key => $r ) {
			if ( true === $r ) {
				WP_CLI::log( "Imported $key." );
			} else {
				WP_CLI::warning( "$key: " . $r->get_error_message() );
				$fail = true;
			}
		}
		$fail ? WP_CLI::error( 'Some parts were not imported.' ) : WP_CLI::success( 'Done.' );
	} );
}
