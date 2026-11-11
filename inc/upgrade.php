<?php
/**
 * Content saved with an older settings version (marccent-theme's markup/settings.json, tools/settings.mjs) is read as
 * it was meant then, step by step (settings-upgrade.json: step "0" for a block without `version`, "1" for one saved at
 * version 1…): its old attribute names renamed (pad, cols, fps…), the old defaults that changed filled in, and a list
 * the block kept (a Timeline's events) as its child blocks; on the page and wherever the server reads saved blocks
 * (forms). The editor does the same (src/shared/upgrade.js) and saves the block with its version.
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
// $children: the rows a step turns into child blocks, as array( 'block' => name, 'rows' => array ), if any.
function marccent_blocks_upgrade( string $name, array $attrs, ?array &$children = null ): array {
	$u    = marccent_blocks_upgrades()[ $name ] ?? null;
	$from = (int) ( $attrs['version'] ?? 0 );
	if ( ! $u || $from >= $u['version'] ) {
		return $attrs;
	}
	for ( $version = $from; $version < $u['version']; $version++ ) {
		$step = $u[ (string) $version ] ?? array();
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
		$from_list = $step['children']['from'] ?? null;
		if ( $from_list && array_key_exists( $from_list, $attrs ) ) {
			$children = array( 'block' => $step['children']['block'], 'rows' => array_values( array_filter( (array) $attrs[ $from_list ], 'is_array' ) ) );
			unset( $attrs[ $from_list ] );
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
			if ( (int) ( $saved['version'] ?? 0 ) < marccent_blocks_upgrades()[ $name ]['version'] && $block->block_type ) {
				$children   = null;
				$attributes = $block->block_type->prepare_attributes_for_render( marccent_blocks_upgrade( $name, $saved, $children ) );
				// The list as child blocks, when none were saved: render.php reads them as it reads saved ones.
				if ( $children && ! count( $block->inner_blocks ) ) {
					$block->inner_blocks = new WP_Block_List(
						array_map( fn( $row ) => array( 'blockName' => $children['block'], 'attrs' => $row + array( 'version' => marccent_blocks_upgrades()[ $children['block'] ]['version'] ?? null ), 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() ), $children['rows'] ),
						(array) $block->context,
						WP_Block_Type_Registry::get_instance()
					);
				}
			}
			return $render( $attributes, $content, $block );
		};
		return $args;
	},
	10,
	2
);
