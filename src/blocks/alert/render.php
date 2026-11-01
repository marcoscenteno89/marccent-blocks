<?php
/**
 * Alert: its blocks in a tone's fill (marccent_blocks_alert, inc/markup.php).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

echo marccent_blocks_alert( $attributes['tone'], marccent_blocks_inner( $block ), fn( $attrs ) => marccent_blocks_wrapper( $block, $attrs ), (string) ( $attributes['className'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
