<?php
/**
 * Appearance → Marccent settings: every API key the Marccent plugins and theme use, in one place (the marccent_keys
 * option), not each on its own settings screen. A plugin adds its own with the marccent_keys filter; code reads one
 * with marccent_key( 'id' ). A wp-config.php constant MARCCENT_KEY_<ID> (or the environment variable of that name)
 * wins over the saved value, for sites that keep keys out of the database; the page then says it's set there.
 * Secret keys are never printed back into the page: left empty, a save keeps them.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The keys: id => group (a heading), label, help, secret (a password, kept out of the page).
 */
function marccent_keys(): array {
	return apply_filters( 'marccent_keys', array(
		'google_maps'      => array(
			'group' => __( 'Google Maps', 'marccent-blocks' ),
			'label' => __( 'API key', 'marccent-blocks' ),
			'help'  => __( 'A browser key with the Maps JavaScript API, restricted to this site’s domain. Every map uses it (the Map block, the business’s map); without it, Google’s plain embed.', 'marccent-blocks' ),
		),
		'turnstile_site'   => array(
			'group' => __( 'Cloudflare Turnstile', 'marccent-blocks' ),
			'label' => __( 'Site key', 'marccent-blocks' ),
			'help'  => __( 'Checks that a form’s sender is a person before it’s kept. A site key and a secret key from Cloudflare (Turnstile → Add widget, with this site’s domain). Without them, forms still work, unchecked for spam.', 'marccent-blocks' ),
		),
		'turnstile_secret' => array(
			'group'  => __( 'Cloudflare Turnstile', 'marccent-blocks' ),
			'label'  => __( 'Secret key', 'marccent-blocks' ),
			'secret' => true,
		),
	) );
}

// A key set outside the database (wp-config.php or the environment), or null.
function marccent_key_override( string $id ): ?string {
	$name = 'MARCCENT_KEY_' . strtoupper( $id );
	if ( defined( $name ) ) {
		return (string) constant( $name );
	}
	$env = getenv( $name );
	return false === $env || '' === $env ? null : $env;
}

/**
 * One key's value ('' when it isn't set).
 */
function marccent_key( string $id ): string {
	return marccent_key_override( $id ) ?? (string) ( marccent_blocks_option( 'marccent_keys', array() )[ $id ] ?? '' );
}

// Once: the keys from where they used to be (Settings → Marccent forms, Marccent Local's Settings → Business) move
// here, and leave there.
add_action( 'init', function () {
	if ( false !== get_option( 'marccent_keys', false ) ) {
		return;
	}
	$turnstile = (array) get_option( 'marccent_turnstile', array() );
	$local     = get_option( 'marccent_local', array() );
	$keys      = array_filter( array(
		'google_maps'      => (string) ( is_array( $local ) ? $local['maps_key'] ?? '' : '' ),
		'turnstile_site'   => (string) ( $turnstile['site'] ?? '' ),
		'turnstile_secret' => (string) ( $turnstile['secret'] ?? '' ),
	) );
	add_option( 'marccent_keys', $keys );
	delete_option( 'marccent_turnstile' );
	if ( is_array( $local ) && array_key_exists( 'maps_key', $local ) ) {
		unset( $local['maps_key'] );
		update_option( 'marccent_local', $local );
	}
} );

// The maps' key, for the theme's map (window.marccentMapsKey, so it stays out of the content): on pages with a map.
// A block theme renders the blocks before the head; content rendered later still gets it before the footer's scripts.
add_filter( 'render_block', function ( $html ) {
	if ( $html && str_contains( $html, 'data-component="map"' ) ) {
		$GLOBALS['marccent_has_map'] = true;
	}
	return $html;
} );
$marccent_maps_key = function () {
	static $done = false;
	$key = marccent_key( 'google_maps' );
	if ( $done || '' === $key || empty( $GLOBALS['marccent_has_map'] ) ) {
		return;
	}
	$done = true;
	wp_print_inline_script_tag( 'window.marccentMapsKey = ' . wp_json_encode( $key ) . ';' );
};
add_action( 'wp_head', $marccent_maps_key );
add_action( 'wp_footer', $marccent_maps_key, 1 );

/* The page */

add_action( 'admin_init', function () {
	register_setting( 'marccent_settings', 'marccent_keys', array(
		'type'              => 'array',
		'default'           => array(),
		'sanitize_callback' => function ( $value ) {
			$saved = (array) get_option( 'marccent_keys', array() );
			$out   = array();
			foreach ( marccent_keys() as $id => $k ) {
				$v = preg_replace( '/[^A-Za-z0-9_\-.]/', '', (string) ( $value[ $id ] ?? '' ) );
				// A secret left empty keeps what's saved, unless it was asked to go.
				if ( ! empty( $k['secret'] ) && '' === $v && empty( $value[ "$id:clear" ] ) ) {
					$v = (string) ( $saved[ $id ] ?? '' );
				}
				if ( '' !== $v ) {
					$out[ $id ] = $v;
				}
			}
			return $out;
		},
	) );
	$groups = array();
	foreach ( marccent_keys() as $id => $k ) {
		$group = sanitize_title( $k['group'] );
		if ( ! isset( $groups[ $group ] ) ) {
			$groups[ $group ] = true;
			add_settings_section( "marccent_keys_$group", $k['group'], '__return_null', 'marccent_settings' );
		}
		add_settings_field( "marccent_key_$id", $k['label'], function () use ( $id, $k ) {
			$secret   = ! empty( $k['secret'] );
			$override = marccent_key_override( $id );
			$saved    = (string) ( get_option( 'marccent_keys', array() )[ $id ] ?? '' );
			if ( null !== $override ) {
				printf( '<p><code>MARCCENT_KEY_%s</code> %s</p>', esc_html( strtoupper( $id ) ), esc_html__( 'is set in wp-config.php or the environment: it’s the one used.', 'marccent-blocks' ) );
			}
			printf(
				'<input type="%s" name="marccent_keys[%s]" value="%s" placeholder="%s" class="regular-text code" autocomplete="off" spellcheck="false">',
				$secret ? 'password' : 'text',
				esc_attr( $id ),
				$secret ? '' : esc_attr( $saved ),
				$secret && '' !== $saved ? esc_attr__( 'Saved (left empty, it stays)', 'marccent-blocks' ) : ''
			);
			if ( $secret && '' !== $saved ) {
				printf( ' <label><input type="checkbox" name="marccent_keys[%s:clear]" value="1"> %s</label>', esc_attr( $id ), esc_html__( 'Remove', 'marccent-blocks' ) );
			}
			if ( ! empty( $k['help'] ) ) {
				echo '<p class="description">' . esc_html( $k['help'] ) . '</p>';
			}
		}, 'marccent_settings', "marccent_keys_$group" );
	}
} );

// Under Appearance, after Marccent theme (Marccent WP's, added at the default priority).
add_action( 'admin_menu', function () {
	add_theme_page( __( 'Marccent settings', 'marccent-blocks' ), __( 'Marccent settings', 'marccent-blocks' ), 'manage_options', 'marccent-settings', function () {
		echo '<div class="wrap"><h1>' . esc_html__( 'Marccent settings', 'marccent-blocks' ) . '</h1>';
		echo '<p>' . esc_html__( 'Every API key the Marccent plugins and theme use, in one place.', 'marccent-blocks' ) . '</p>';
		echo '<form method="post" action="options.php">';
		settings_fields( 'marccent_settings' );
		do_settings_sections( 'marccent_settings' );
		submit_button();
		echo '</form></div>';
	} );
}, 11 );
