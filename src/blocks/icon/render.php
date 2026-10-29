<?php
/**
 * Icon: the theme's Icon (marccent_blocks_icon, inc/markup.php), in a shape of the given width or alone at that size.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a   = $attributes;
$box = $a['shape']
	? array( 'class' => marccent_blocks_cx( $a['shape'], $a['surface'], 'col-center' ), 'style' => marccent_blocks_css( array( 'width' => $a['size'] ) ) )
	: array( 'class' => 'grid', 'style' => marccent_blocks_css( array( '--icon-size' => $a['size'] ) ) );
?>
<div <?php echo marccent_blocks_wrapper( $block, $box ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php
	echo marccent_blocks_icon( (string) $a['icon'], '', (string) $a['family'] ); // phpcs:ignore WordPress.Security.EscapeOutput
	if ( $a['label'] ) {
		printf( '<span class="screen-reader-text">%s</span>', esc_html( $a['label'] ) );
	}
?></div>
