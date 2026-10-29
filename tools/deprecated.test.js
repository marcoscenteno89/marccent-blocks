/*
 * Posts saved before the blocks rendered on the server (0.3.1 and earlier: static markup) still open as valid blocks
 * (each block's deprecated.js), and save again as attributes and inner blocks only, which render.php turns into the
 * markup. tools/fixtures/0.3.1.json is every block saved by 0.3.1 ({ [key]: content }); MARCCENT_CONTENT points
 * to another such file (a site's posts) instead.
 *
 *   npm run test:blocks                                   # the fixture
 *   MARCCENT_CONTENT=/path/posts.json npm run test:blocks  # a site's posts
 */
import fs from 'fs';
import { parse, serialize } from '@wordpress/blocks';
import { registerCoreBlocks } from '@wordpress/block-library';

registerCoreBlocks();
fs.readdirSync( `${ __dirname }/../src/blocks` ).forEach( ( name ) => require( `../src/blocks/${ name }/index.js` ) );

const file = process.env.MARCCENT_CONTENT || `${ __dirname }/fixtures/0.3.1.json`;
const posts = JSON.parse( fs.readFileSync( file, 'utf8' ) );

const all = ( blocks ) => blocks.flatMap( ( b ) => [ b, ...all( b.innerBlocks ) ] );
const ours = ( blocks ) => all( blocks ).filter( ( b ) => b.name.startsWith( 'marccent/' ) );

test.each( Object.keys( posts ) )( '%s', ( key ) => {
	const blocks = parse( posts[ key ] );
	expect( ours( blocks ).filter( ( b ) => ! b.isValid ).map( ( b ) => b.name ) ).toEqual( [] );
	// Saved again: none of our markup, only the comments and the inner blocks' own.
	const again = serialize( blocks );
	expect( ours( parse( again ) ).filter( ( b ) => ! b.isValid ).map( ( b ) => b.name ) ).toEqual( [] );
	expect( again ).not.toMatch( /class="[^"]*wp-block-marccent-/ );
} );
