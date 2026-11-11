<?php
/**
 * Code: an island (runtime/islands/code.js), the code in a <pre> until the highlighted one is drawn.
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

$lang = in_array( $attributes['language'], array( 'js', 'jsx', 'css', 'html', 'sh' ), true ) ? $attributes['language'] : 'jsx';
echo marccent_blocks_island( $block, 'code', array( 'code' => (string) $attributes['code'], 'language' => $lang ), '<pre><code>' . esc_html( (string) $attributes['code'] ) . '</code></pre>' ); // phpcs:ignore WordPress.Security.EscapeOutput
