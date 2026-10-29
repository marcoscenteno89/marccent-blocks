<?php
/**
 * Animated band: a .stack of the canvas host (data-component="animation", which the runtime's CanvasScanner fills with
 * the named animation) and the content centered over it.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$a     = $attributes;
$outer = array(
	'class' => marccent_blocks_cx( 'wrap wrap-fluid pad-0 stack', $a['surface'] ),
	'style' => marccent_blocks_css( array( 'min-height' => $a['minHeight'] ) ),
);
?>
<section <?php echo marccent_blocks_wrapper( $block, $outer ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div <?php echo marccent_blocks_attributes( array( 'data-component' => 'animation', 'data-name' => $a['name'], 'data-fps' => ! empty( $a['fps'] ) ? (int) $a['fps'] : null, 'data-colors' => $a['colors'] ?: null, 'class' => 'bg w-100 h-100', 'aria-hidden' => 'true' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>></div>
	<div class="col-center pad text-center" style="position: relative; grid-template-columns: minmax(0, 1fr)"><?php echo marccent_blocks_inner( $block ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
</section>
