<?php
/**
 * Tooltip: its text as a link-styled button with the hint beside it (marccent_blocks_tooltip, inc/markup.php).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

printf( '<div %s>%s</div>', marccent_blocks_wrapper( $block, array() ), marccent_blocks_tooltip( array( 'id' => wp_unique_id( 'marccent-tooltip-' ) ) + $attributes ) ); // phpcs:ignore WordPress.Security.EscapeOutput
