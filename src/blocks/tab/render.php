<?php
/**
 * Tab: its label as a heading and its blocks, one item of its Tabs' slot ([data-tab-content] is what the tab shows).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

printf(
	'<div class="grid cols-1 gap-sm"><h3 class="h5">%s</h3><div data-tab-content="" class="grid cols-1 gap-sm">%s</div></div>',
	esc_html( marccent_blocks_text( (string) $attributes['label'] ) ),
	marccent_blocks_inner( $block ) // phpcs:ignore WordPress.Security.EscapeOutput
);
