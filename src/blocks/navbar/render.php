<?php
/**
 * Navbar: the header bar's markup as the NavBar component renders it (so the page looks right before scripts run and
 * nothing moves when the component takes over), inside the navbar island, with everything the component needs in data-props. The menu is the one in the Primary
 * location (Appearance → Menus), as a tree; without one, the top-level pages.
 *
 * @var array $attributes
 */

$items = marccent_blocks_menu_tree( 'primary' );

$social = array();
foreach ( preg_split( '/\R/', (string) $attributes['social'] ) as $line ) {
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
		'href'  => esc_url_raw( $attributes['ctaHref'] ),
		'icon'  => $attributes['ctaIcon'],
	) : null,
	'themeButton' => (bool) $attributes['themeButton'],
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
$last = $props['themeButton'] ? '<button type="button" data-component="toggle-button" data-name="theme" class="btn hover-expand col-last" aria-label="Theme" title="Theme" aria-expanded="false" aria-controls="theme-panel">' . marccent_blocks_icon( 'brush', 'btn-icon' ) . '<span class="btn-text">Theme</span></button>' : '';
?>
<div <?php echo get_block_wrapper_attributes( array( 'data-island' => 'navbar', 'data-props' => wp_json_encode( $props ) ) ); ?>>
	<?php echo marccent_blocks_navbar( $props['brand'], $items, $end, $last, $top ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>
