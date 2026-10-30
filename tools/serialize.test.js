/*
 * Post content from a spec, with every block's own save() (core blocks and ours), so the editor opens it as valid
 * blocks. The spec is a module (MARCCENT_SPEC) exporting { [key]: [ block, … ] }, a block being
 * [ name, attributes?, [ inner blocks ]? ]; the result (MARCCENT_OUT) is JSON { [key]: content }. Every block is
 * parsed back and validated, and the test fails on any that the editor would flag.
 *
 *   MARCCENT_SPEC=/path/spec.js MARCCENT_OUT=/path/out.json npm run serialize
 */
import fs from 'fs';
import { createElement } from '@wordpress/element';
import { createBlock, serialize, parse, registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';
import { registerCoreBlocks } from '@wordpress/block-library';

registerCoreBlocks();
const ours = fs.readdirSync( `${ __dirname }/../src/blocks` );
ours.forEach( ( name ) => require( `../src/blocks/${ name }/index.js` ) );

// Marccent Content's blocks (Loop, Field), when it sits beside this plugin: from their block.json, saved as its
// editor saves them (their inner blocks; the server renders the rest).
const content = `${ __dirname }/../../marccent-content/blocks`;
if ( fs.existsSync( content ) ) {
	fs.readdirSync( content ).forEach( ( dir ) => {
		const { name, ...meta } = JSON.parse( fs.readFileSync( `${ content }/${ dir }/block.json`, 'utf8' ) );
		registerBlockType( name, { ...meta, save: () => createElement( InnerBlocks.Content ) } );
	} );
}

const toBlock = ( [ name, attributes = {}, inner = [] ] ) => {
	const block = createBlock( name, attributes, inner.map( toBlock ) );
	// Custom HTML has no save(): it's serialized from its innerContent, as parsed blocks carry it.
	if ( name === 'core/html' ) {
		block.innerContent = [ attributes.content ];
	}
	return block;
};

const invalid = ( blocks, path = [] ) => blocks.flatMap( ( b, i ) => [
	...( b.isValid ? [] : [ `${ [ ...path, i ].join( '.' ) } ${ b.name }` ] ),
	...invalid( b.innerBlocks, [ ...path, i ] ),
] );

test( 'serialize the spec', () => {
	const spec = require( process.env.MARCCENT_SPEC );
	const out = {};
	for ( const [ key, blocks ] of Object.entries( spec.default ?? spec ) ) {
		out[ key ] = serialize( blocks.map( toBlock ) );
	}
	fs.writeFileSync( process.env.MARCCENT_OUT, JSON.stringify( out, null, '\t' ) );
	for ( const [ key, content ] of Object.entries( out ) ) {
		expect( { key, invalid: invalid( parse( content ) ) } ).toEqual( { key, invalid: [] } );
	}
} );
