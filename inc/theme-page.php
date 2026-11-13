<?php
/**
 * Appearance → Marccent theme: the site's theme on a page of its own (the site's look is changed on purpose, not from an
 * editor), drawn from marccent-theme's site settings (markup/site-settings.json) by build/admin/theme-options.js beside
 * a preview of the site. Saved as the site's setting marccent_defaults (POST /wp/v2/settings), checked against the same
 * settings (marccent_blocks_site_settings_sanitize, inc/theme.php). One source of control (PRODUCT-LINE.md 5.7): the
 * theme defines, this page draws and saves.
 *
 * The page has tabs; another plugin or the theme adds its own with the marccent_theme_tabs filter
 * ( id => [ label, callback ] ): Marccent WP's Tracking and cookies.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_setting( 'marccent', 'marccent_defaults', array(
		'type'              => 'string',
		'description'       => __( 'The site’s theme (Appearance → Marccent theme)', 'marccent-blocks' ),
		'default'           => '{}',
		'sanitize_callback' => fn( $value ) => wp_json_encode( (object) marccent_blocks_site_settings_sanitize( $value ) ),
		'show_in_rest'      => true,
	) );
} );

function marccent_blocks_theme_tabs(): array {
	return apply_filters( 'marccent_theme_tabs', array( 'look' => array( __( 'Look', 'marccent-blocks' ), null ) ) );
}

add_action( 'admin_menu', function () {
	$hook = add_theme_page( __( 'Marccent theme', 'marccent-blocks' ), __( 'Marccent theme', 'marccent-blocks' ), 'manage_options', 'marccent-theme', function () {
		$tabs = marccent_blocks_theme_tabs();
		$tab  = sanitize_key( $_GET['tab'] ?? 'look' ); // phpcs:ignore WordPress.Security.NonceVerification -- only read to pick a tab
		$tab  = isset( $tabs[ $tab ] ) ? $tab : 'look';
		echo '<div class="wrap"><h1>' . esc_html__( 'Marccent theme', 'marccent-blocks' ) . '</h1>';
		if ( count( $tabs ) > 1 ) {
			echo '<nav class="nav-tab-wrapper">';
			foreach ( $tabs as $id => [ $label ] ) {
				printf( '<a href="%s" class="nav-tab%s">%s</a>', esc_url( add_query_arg( 'tab', $id, admin_url( 'themes.php?page=marccent-theme' ) ) ), $tab === $id ? ' nav-tab-active' : '', esc_html( $label ) );
			}
			echo '</nav>';
		}
		if ( 'look' !== $tab && is_callable( $tabs[ $tab ][1] ) ) {
			settings_errors();
			call_user_func( $tabs[ $tab ][1] );
		} else {
			echo '<p>' . esc_html__( 'The whole site’s look. Changes show in the preview; the site changes when you press Save.', 'marccent-blocks' ) . '</p>';
			echo '<div id="marccent-theme-options"></div>';
		}
		echo '</div>';
	} );

	add_action( 'admin_enqueue_scripts', function ( $current ) use ( $hook ) {
		if ( $current !== $hook || 'look' !== sanitize_key( $_GET['tab'] ?? 'look' ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- only read to pick a tab
			return;
		}
		$asset = include MARCCENT_BLOCKS_DIR . '/build/admin/theme-options.asset.php';
		wp_enqueue_style( 'wp-components' );
		wp_enqueue_script( 'marccent-theme-options', plugins_url( 'build/admin/theme-options.js', MARCCENT_BLOCKS_FILE ), $asset['dependencies'], $asset['version'], true );
		wp_add_inline_script( 'marccent-theme-options', 'window.marccentThemeOptions = ' . wp_json_encode( array(
			'base'    => marccent_blocks_theme_base(),
			'options' => marccent_blocks_theme_options(),
			'saved'   => (string) get_option( 'marccent_defaults', '{}' ),
			'preview' => add_query_arg( 'marccent_preview', '1', home_url( '/' ) ),
		) ) . ';', 'before' );
	} );
} );
