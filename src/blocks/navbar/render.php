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

// The markup the island's components render (NavBar, Menu, Button, ToggleButton), so swapping it in shifts nothing.
$icon = function ( string $icon ): string {
	$classes = str_starts_with( $icon, 'fa-' ) ? $icon : "fa-solid fa-$icon";
	return sprintf( '<i data-component="icon" class="%s btn-icon" aria-hidden="true"></i>', esc_attr( $classes ) );
};
$button = function ( string $class, string $name, string $label, string $href ) use ( $icon ): string {
	$external = preg_match( '#^https?:#', $href ) && ! str_starts_with( $href, home_url() ) ? ' target="_blank" rel="noopener noreferrer"' : '';
	return sprintf( '<a data-component="button" class="btn %1$s" aria-label="%2$s" title="%2$s" href="%3$s"%4$s>%5$s<span class="btn-text">%6$s</span></a>',
		esc_attr( $class ), esc_attr( $label ), esc_url( $href ), $external, $icon( $name ), esc_html( $label ) );
};
$links = function ( $items ) use ( &$links ) {
	foreach ( $items as $item ) {
		if ( ! empty( $item['children'] ) ) {
			printf( '<details data-component="menu-dropdown" class="menu-dropdown"><summary class="menu-link"><span class="text-truncate">%s</span></summary><div class="menu-panel bg border round shadow pad grid cols-1 gap-sm">', esc_html( $item['name'] ) );
			$links( $item['children'] );
			echo '</div></details>';
		} else {
			printf( '<a data-component="menu-link" class="menu-link" href="%s"><span class="text-truncate">%s</span></a>', esc_url( $item['href'] ), esc_html( $item['name'] ) );
		}
	}
};
?>
<div <?php echo get_block_wrapper_attributes( array( 'data-island' => 'navbar', 'data-props' => wp_json_encode( $props ) ) ); ?>>
	<header data-component="nav-bar" class="nav wrap wrap-fluid pad-y-sm bg border-bottom sticky-top grid cols-1 gap-sm">
		<?php if ( $props['themeButton'] ) : ?>
			<div id="theme-panel" class="collapse"><div><div class="pad-y-sm"><div class="wrap pad-0 grid cols-1 justify-end"></div></div></div></div>
		<?php endif; ?>
		<div class="wrap pad-0 grid layout-ends items-center gap-sm">
			<a href="<?php echo esc_url( $props['brand']['href'] ); ?>" class="menu-link grid auto-cols items-center gap-sm" aria-label="<?php echo esc_attr( $props['brand']['name'] . ', home' ); ?>">
				<?php if ( $props['brand']['logo'] ) : ?>
					<img src="<?php echo esc_url( $props['brand']['logo'] ); ?>" alt="<?php echo esc_attr( $props['brand']['name'] ); ?>" width="<?php echo (int) $logo[1]; ?>" height="<?php echo (int) $logo[2]; ?>" style="height: 2.5rem; width: auto">
				<?php endif; ?>
				<?php if ( $props['brand']['showName'] ) : ?>
					<span class="fw-bold"><?php echo esc_html( $props['brand']['name'] ); ?></span>
				<?php endif; ?>
			</a>
			<button type="button" data-component="toggle-button" class="btn nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="site-nav"><?php echo $icon( 'bars-staggered' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			<div id="site-nav" class="nav-collapse">
				<nav data-component="menu" class="menu grid gap-0 auto-cols divide" style="--cols: <?php echo count( $items ); ?>" aria-label="Main"><?php $links( $items ); ?></nav>
				<?php if ( $social || $props['cta'] ) : ?>
					<nav aria-label="Social" class="nav-end grid auto-cols items-center gap-sm">
						<?php
						foreach ( $social as $s ) {
							echo $button( 'secondary hover-expand', $s['icon'], $s['name'], $s['href'] ); // phpcs:ignore WordPress.Security.EscapeOutput
						}
						if ( $props['cta'] ) {
							echo $button( 'primary', $props['cta']['icon'], $props['cta']['label'], $props['cta']['href'] ); // phpcs:ignore WordPress.Security.EscapeOutput
						}
						?>
					</nav>
				<?php endif; ?>
			</div>
			<?php if ( $props['themeButton'] ) : ?>
				<button type="button" data-component="toggle-button" data-name="theme" class="btn hover-expand col-last" aria-label="Theme" title="Theme" aria-expanded="false" aria-controls="theme-panel"><?php echo $icon( 'brush' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span class="btn-text">Theme</span></button>
			<?php endif; ?>
		</div>
	</header>
</div>
