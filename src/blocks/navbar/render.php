<?php
/**
 * Navbar: the header bar's markup as the NavBar component renders it (so the page looks right before scripts run and
 * nothing moves when the component takes over), inside the navbar island, with everything the component needs in data-props. The menu is the one in the Primary
 * location (Appearance → Menus), as a tree; without one, the top-level pages. The social links are the Social menu's.
 *
 * @var array $attributes
 */

$items = marccent_blocks_menu_tree( 'primary' );

// The social links: the Social menu (Appearance → Menus); without one, the block's own list (its older setting).
$social = marccent_blocks_social_links();
foreach ( $social ? array() : preg_split( '/\R/', (string) $attributes['social'] ) as $line ) {
	$parts = array_map( 'trim', explode( '|', $line ) );
	if ( count( $parts ) >= 2 && $parts[1] ) {
		$social[] = array( 'name' => $parts[0], 'href' => esc_url_raw( $parts[1] ), 'icon' => $parts[2] ?? 'fa-solid fa-link' );
	}
}

$logo_id = get_theme_mod( 'custom_logo' );
$logo    = $logo_id ? wp_get_attachment_image_src( $logo_id, 'medium' ) : false;
$props   = array(
	'brand'       => array(
		'name'     => get_bloginfo( 'name' ),
		'href'     => home_url( '/' ),
		'logo'     => $logo ? $logo[0] : '',
		'logoSize' => $logo ? array( $logo[1], $logo[2] ) : null,
		'showName' => (bool) $attributes['showName'],
	),
	'items'       => $items,
	'social'      => $social,
	'cta'         => $attributes['ctaLabel'] ? array(
		'label' => $attributes['ctaLabel'],
		// A {{ token }} (tel:{{ business.telephone }}) as it is, for Marccent Content to fill; the button escapes it.
		'href'  => str_contains( (string) $attributes['ctaHref'], '{{' ) ? (string) $attributes['ctaHref'] : esc_url_raw( $attributes['ctaHref'] ),
		'icon'  => $attributes['ctaIcon'],
	) : null,
	// What visitors may change (Appearance → Marccent theme, Visitors, the one place that decides it; the block's old
	// themeButton setting no longer does): the full mini theme editor behind the theme button, or single controls.
	'themeButton' => marccent_blocks_visitor_controls()['full'],
	'controls'    => marccent_blocks_visitor_controls()['controls'],
);

// The markup the island's components render (NavBar, Menu, Button, ToggleButton), so swapping it in shifts nothing:
// the theme's (inc/markup.php).
$button = fn( string $class, string $name, string $label, string $href ): string => marccent_blocks_button( array(
	'label'   => $label,
	'href'    => $href,
	'icon'    => $name,
	'classes' => $class,
	'newTab'  => preg_match( '#^https?:#', $href ) && ! str_starts_with( $href, home_url() ),
) );
$end = '';
foreach ( $social as $s ) {
	$end .= $button( 'secondary hover-expand', $s['icon'], $s['name'], $s['href'] );
}
if ( $props['cta'] ) {
	$end .= $button( 'primary', $props['cta']['icon'], $props['cta']['label'], $props['cta']['href'] );
}
$end  = $end ? '<nav aria-label="Social" class="nav-end grid auto-cols items-center gap-sm">' . $end . '</nav>' : '';
$top  = $props['themeButton'] ? '<div id="theme-panel" class="collapse"><div><div class="pad-y-sm"><div class="wrap pad-0 grid cols-1 justify-end"></div></div></div></div>' : '';
$last = $props['themeButton'] ? '<button type="button" data-component="toggle-button" data-name="theme" class="btn hover-expand" aria-label="Theme" title="Theme" aria-expanded="false" aria-controls="theme-panel">' . marccent_blocks_icon( 'brush', 'btn-icon' ) . '<span class="btn-text">Theme</span></button>' : '';
// The single controls' light/dark button (the theme's ModeToggle) as it first shows: the mode it switches to, from the
// site's starting mode. The others (design style, glass, the palettes) draw when the island takes over.
$dark = ! empty( marccent_blocks_theme()['isDark'] );
$mode = in_array( 'mode', $props['controls'], true ) ? sprintf( '<button type="button" data-component="toggle-button" data-name="mode" class="btn %1$s hover-expand" aria-label="%2$s" title="%2$s">%3$s<span class="btn-text">%2$s</span></button>', $dark ? 'light' : 'dark', $dark ? 'Light' : 'Dark', marccent_blocks_icon( $dark ? 'sun' : 'moon', 'btn-icon' ) ) : '';
$last = $mode . $last ? '<div class="grid auto-cols items-center gap-sm col-last">' . $mode . $last . '</div>' : '';
?>
<div <?php echo get_block_wrapper_attributes( array( 'data-island' => 'navbar', 'data-props' => wp_json_encode( $props ) ) ); ?>>
	<?php echo marccent_blocks_navbar( $props['brand'], $items, $end, $last, $top ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
