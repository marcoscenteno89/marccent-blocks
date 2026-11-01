<?php
/**
 * Popover: its button and the panel of its blocks the button opens (marccent_blocks_popover, inc/markup.php).
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

printf( '<div %s>%s</div>', marccent_blocks_wrapper( $block, array() ), marccent_blocks_popover( array( 'id' => wp_unique_id( 'marccent-popover-' ) ) + $attributes, marccent_blocks_inner( $block ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput
