/*
 * Content saved with an older settings version (marccent-theme's markup/settings.json, tools/settings.mjs) is read as
 * it was meant then, step by step (settings-upgrade.json): step "0" for a block without `version` (saved before the
 * blocks' settings came from settings.json), step "1" for one saved at version 1, and so on up to today's. Each step
 * renames the old attribute names (pad, cols, fps…), fills in the old defaults that changed, and may turn a list the
 * block kept (events, items) into its child blocks (children: { from, block }). The block is saved again with its
 * version. The server reads such content the same way (inc/upgrade.php).
 */
import { addFilter } from '@wordpress/hooks';
import { createBlock } from '@wordpress/blocks';
import UPGRADES from '../../settings-upgrade.json';

export const currentVersion = ( name ) => UPGRADES[ name ]?.version;

// Saved attributes as today's, and the rows a step turns into child blocks ({ block, rows }), if any.
const upgradeWithChildren = ( name, saved ) => {
	const u = UPGRADES[ name ];
	const from = saved?.version ?? 0;
	if ( ! u || from >= u.version ) {
		return { attributes: saved, children: null };
	}
	const out = { ...saved };
	let children = null;
	for ( let version = from; version < u.version; version++ ) {
		const step = u[ version ] ?? {};
		for ( const [ old, now ] of Object.entries( step.rename ?? {} ) ) {
			if ( old in out ) {
				out[ now ] ??= out[ old ];
				delete out[ old ];
			}
		}
		for ( const [ key, value ] of Object.entries( step.defaults ?? {} ) ) {
			if ( out[ key ] === undefined && value !== null ) {
				out[ key ] = value;
			}
		}
		if ( step.children && step.children.from in out ) {
			const rows = out[ step.children.from ];
			children = { block: step.children.block, rows: Array.isArray( rows ) ? rows.filter( ( r ) => r && typeof r === 'object' ) : [] };
			delete out[ step.children.from ];
		}
	}
	return { attributes: { ...out, version: u.version }, children };
};

export const upgrade = ( name, saved ) => upgradeWithChildren( name, saved ).attributes;

// The attributes as they were before step 0 (the oldest names, the oldest defaults): for a block's deprecated.js,
// whose saved markup used them. Its migrate (upgradeLegacy) brings them to today's.
const withDefault = ( a, value ) => {
	const { default: _, ...rest } = a;
	return value === null ? rest : { ...rest, default: value };
};
export const legacyAttributes = ( metadata ) => {
	const u = UPGRADES[ metadata.name ] ?? {};
	const steps = Array.from( { length: u.version ?? 0 }, ( _, version ) => u[ version ] ?? {} ).reverse();
	return Object.fromEntries( Object.entries( metadata.attributes ).filter( ( [ key ] ) => key !== 'version' ).map( ( [ key, a ] ) => {
		let name = key;
		let attribute = a;
		for ( const step of steps ) {
			if ( name in ( step.defaults ?? {} ) ) {
				attribute = withDefault( a, step.defaults[ name ] );
			}
			name = Object.keys( step.rename ?? {} ).find( ( old ) => step.rename[ old ] === name ) ?? name;
		}
		return [ name, attribute ];
	} ) );
};
export const upgradeLegacy = ( name ) => ( attributes, innerBlocks ) => {
	const { attributes: now, children } = upgradeWithChildren( name, attributes );
	return [ now, children ? children.rows.map( ( row ) => createBlock( children.block, row ) ) : innerBlocks ];
};

// A parent block saved with the list its child blocks hold now (a Timeline's events, T-034; nothing in the comment:
// the list it had by default): a deprecation that takes it even though it parses as valid (isEligible), and makes the
// children (migrate).
export const listToChildren = ( metadata, from ) => ( {
	attributes: { ...metadata.attributes, [ from ]: { type: 'array' } },
	supports: metadata.supports,
	isEligible: ( saved, innerBlocks ) => ( saved?.version ?? 0 ) < currentVersion( metadata.name ) && ! innerBlocks.length,
	save: () => null,
	migrate: upgradeLegacy( metadata.name ),
} );

// While a post is parsed: a block of ours saved at an older version or without one (the current block type; a
// deprecation, which has a migrate, is upgraded by it).
addFilter( 'blocks.getBlockAttributes', 'marccent/upgrade', ( attributes, type, html, saved ) => {
	if ( ! UPGRADES[ type?.name ] || ! type.attributes?.version || type.migrate || ( saved?.version ?? 0 ) >= UPGRADES[ type.name ].version ) {
		return attributes;
	}
	const up = upgrade( type.name, saved ?? {} );
	return { ...attributes, ...Object.fromEntries( Object.entries( up ).filter( ( [ key ] ) => key in type.attributes ) ) };
} );
