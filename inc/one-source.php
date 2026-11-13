<?php
/**
 * One source of control (marccent-theme's PRODUCT-LINE.md 5.7): the site's look is set on Appearance → Marccent theme
 * only, from the theme's settings, whatever WordPress theme is active. WordPress's own places to set it are closed:
 * - Global Styles (the site editor's Styles, saved as the wp_global_styles post): left out of every page;
 * - the editor's color, gradient, duotone, font size, line height and spacing pickers: only the theme's tokens, no
 *   custom values (the active theme's theme.json can't turn them back on).
 * WordPress's Fonts page (Appearance → Fonts) stays: it's where fonts are installed, not where they're assigned.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'wp_theme_json_data_user', fn() => new WP_Theme_JSON_Data( array( 'version' => 3 ), 'custom' ) );

add_filter(
	'wp_theme_json_data_theme',
	fn( $data ) => $data->update_with( array(
		'version'  => 3,
		'settings' => array(
			'color'      => array( 'custom' => false, 'customGradient' => false, 'customDuotone' => false, 'defaultPalette' => false, 'defaultGradients' => false, 'defaultDuotone' => false ),
			'typography' => array( 'customFontSize' => false, 'lineHeight' => false, 'defaultFontSizes' => false ),
			'spacing'    => array( 'customSpacingSize' => false ),
		),
	) )
);
