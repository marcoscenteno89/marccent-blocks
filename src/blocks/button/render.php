<?php
/**
 * Button: the theme's Button, written by the server (marccent_blocks_button, inc/markup.php): a link when it has a URL.
 * Hover at Default takes the site's default hover (the theme's options panel, Marccent WP).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_button( // phpcs:ignore WordPress.Security.EscapeOutput -- escaped as it's built
	array(
		'label'   => $attributes['label'],
		'href'    => $attributes['href'],
		'icon'    => $attributes['icon'],
		'classes' => marccent_blocks_cx( $attributes['color'], $attributes['hover'] ?: marccent_blocks_default_hover() ),
		'newTab'  => $attributes['newTab'],
		'trace'   => $attributes['trace'],
		'align'   => $attributes['align'],
	),
	fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs )
);
