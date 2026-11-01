<?php
/**
 * Modal: its button, and the dialog it opens (data-opens; the theme's engine opens and closes it) with the block's
 * blocks inside (marccent_blocks_modal, inc/markup.php). The dialog's id is the block's (dialogId: any other link or
 * button with data-opens="<it>" opens it too), else one of its own.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a  = $attributes;
$id = sanitize_html_class( $a['dialogId'] ) ?: wp_unique_id( 'marccent-modal-' );
printf(
	'<div %s>%s%s</div>',
	marccent_blocks_wrapper( $block, array() ), // phpcs:ignore WordPress.Security.EscapeOutput
	marccent_blocks_opener( array( 'label' => $a['buttonLabel'], 'icon' => $a['buttonIcon'], 'classes' => $a['buttonColor'] ), $id ), // phpcs:ignore WordPress.Security.EscapeOutput
	marccent_blocks_modal( array( 'id' => $id ) + $a, marccent_blocks_inner( $block ) ) // phpcs:ignore WordPress.Security.EscapeOutput
);
