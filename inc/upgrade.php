<?php
/**
 * Content saved before the blocks' settings came from marccent-theme's markup/settings.json (tools/settings.mjs): a
 * block without `version` is read as it was meant then, with its old attribute names (pad, cols, padY…) and the old
 * defaults that changed (settings-upgrade.json), on the page and wherever the server reads saved blocks (forms). The
 * editor does the same (src/shared/upgrade.js) and saves the block with its version.
 *
 * @package marccent-blocks
 */

// marccent-theme's markup/settings.json (copied to build/ by webpack.config.js): every component's settings, for a
// block of another plugin that draws one (Marccent Content's Loop is a Grid).
function marccent_blocks_settings(): array {
	static $settings;
	return $settings ??= json_decode( (string) @file_get_contents( MARCCENT_BLOCKS_DIR . '/build/settings.json' ), true ) ?: array();
}

function marccent_blocks_upgrades(): array {
	static $upgrades;
	return $upgrades ??= json_decode( (string) @file_get_contents( MARCCENT_BLOCKS_DIR . '/settings-upgrade.json' ), true ) ?: array();
}

// A block's saved attributes as today's: old names renamed, the old defaults where nothing was saved, the version.
function marccent_blocks_upgrade( string $name, array $attrs ): array {
	$u = marccent_blocks_upgrades()[ $name ] ?? null;
	if ( ! $u || isset( $attrs['version'] ) ) {
		return $attrs;
	}
	$step = $u['0'] ?? array();
	foreach ( $step['rename'] ?? array() as $old => $new ) {
		if ( array_key_exists( $old, $attrs ) ) {
			$attrs[ $new ] ??= $attrs[ $old ];
			unset( $attrs[ $old ] );
		}
	}
	foreach ( $step['defaults'] ?? array() as $key => $value ) {
		if ( ! array_key_exists( $key, $attrs ) && null !== $value ) { // null: it had no default then
			$attrs[ $key ] = $value;
		}
	}
	$attrs['version'] = $u['version'];
	return $attrs;
}

// Every render of a block of ours (render.php), however it's reached (a page, a container's or a Loop's inner blocks,
// which WordPress hands over already prepared): its attributes from what was saved, upgraded.
add_filter(
	'register_block_type_args',
	function ( $args, $name ) {
		if ( ! isset( marccent_blocks_upgrades()[ $name ] ) || empty( $args['render_callback'] ) ) {
			return $args;
		}
		$render                  = $args['render_callback'];
		$args['render_callback'] = function ( $attributes, $content, $block ) use ( $render, $name ) {
			$saved = (array) ( $block->parsed_block['attrs'] ?? array() );
			if ( ! isset( $saved['version'] ) && $block->block_type ) {
				$attributes = $block->block_type->prepare_attributes_for_render( marccent_blocks_upgrade( $name, $saved ) );
			}
			return $render( $attributes, $content, $block );
		};
		return $args;
	},
	10,
	2
);
