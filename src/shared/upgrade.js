/*
 * Content saved before the blocks' settings came from marccent-theme's markup/settings.json (tools/settings.mjs): a
 * block without `version` is read as it was meant then, its old attribute names (pad, cols, padY…) renamed and the old
 * defaults that changed filled in (settings-upgrade.json), and saved again with its version. The server reads such
 * content the same way (inc/upgrade.php).
 */
import { addFilter } from '@wordpress/hooks';
import UPGRADES from '../../settings-upgrade.json';

export const currentVersion = ( name ) => UPGRADES[ name ]?.version;

// Saved attributes as today's.
export const upgrade = ( name, saved ) => {
	const u = UPGRADES[ name ];
	if ( ! u || saved?.version !== undefined ) {
		return saved;
	}
	const step = u[ 0 ] ?? {};
	const out = { ...saved };
	for ( const [ old, now ] of Object.entries( step.rename ?? {} ) ) {
		if ( old in out ) {
			out[ now ] ??= out[ old ];
			delete out[ old ];
		}
	}
	for ( const [ key, value ] of Object.entries( step.defaults ?? {} ) ) {
		if ( ! ( key in out ) && value !== null ) {
			out[ key ] = value;
		}
	}
	return { ...out, version: u.version };
};

// The attributes as they were before (old names, old defaults): for a block's deprecated.js, whose saved markup
// used them. Its migrate (upgradeLegacy) brings them to today's.
const withDefault = ( a, value ) => {
	const { default: _, ...rest } = a;
	return value === null ? rest : { ...rest, default: value };
};
export const legacyAttributes = ( metadata ) => {
	const step = UPGRADES[ metadata.name ]?.[ 0 ] ?? {};
	const oldName = Object.fromEntries( Object.entries( step.rename ?? {} ).map( ( [ old, now ] ) => [ now, old ] ) );
	return Object.fromEntries( Object.entries( metadata.attributes ).filter( ( [ key ] ) => key !== 'version' ).map( ( [ key, a ] ) => [
		oldName[ key ] ?? key,
		key in ( step.defaults ?? {} ) ? withDefault( a, step.defaults[ key ] ) : a,
	] ) );
};
export const upgradeLegacy = ( name ) => ( attributes, innerBlocks ) => [ upgrade( name, attributes ), innerBlocks ];

// While a post is parsed: a block of ours saved without a version (the current block type; a deprecation's has no
// version attribute and is upgraded by its migrate).
addFilter( 'blocks.getBlockAttributes', 'marccent/upgrade', ( attributes, type, html, saved ) => {
	if ( ! UPGRADES[ type?.name ] || ! type.attributes?.version || saved?.version !== undefined ) {
		return attributes;
	}
	const up = upgrade( type.name, saved ?? {} );
	return { ...attributes, ...Object.fromEntries( Object.entries( up ).filter( ( [ key ] ) => key in type.attributes ) ) };
} );
