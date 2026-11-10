/*
 * Content saved before the settings came from settings.json opens as it was meant (src/shared/upgrade.js): old names
 * renamed, the old defaults kept, and the version saved with it; a block saved today keeps its own values.
 *
 *   MARCCENT_TEST=upgrade npm run serialize
 */
import fs from 'fs';
import { parse, serialize, createBlock } from '@wordpress/blocks';
import { registerCoreBlocks } from '@wordpress/block-library';

registerCoreBlocks();
fs.readdirSync( `${ __dirname }/../src/blocks` ).forEach( ( name ) => require( `../src/blocks/${ name }/index.js` ) );

test( 'an old card, grid and section', () => {
	const [ card, grid, section ] = parse( [
		'<!-- wp:marccent/card {"pad":"pad-lg"} /-->',
		'<!-- wp:marccent/grid {"cols":2,"min":"12rem","row":"9rem"} /-->',
		'<!-- wp:marccent/section {"tagName":"header","padY":"pad-y-sm","imageUrl":"/a.jpg"} /-->',
	].join( '\n' ) );
	expect( card.attributes ).toMatchObject( { padding: 'pad-lg', hover: 'hover-holographic', version: 1 } );
	expect( card.attributes ).not.toHaveProperty( 'pad' );
	expect( grid.attributes ).toMatchObject( { columns: 2, minimumColumnWidth: '12rem', minimumRowHeight: '9rem', version: 2 } );
	expect( section.attributes ).toMatchObject( { as: 'header', verticalPadding: 'pad-y-sm', image: '/a.jpg', version: 1 } );
	expect( serialize( [ card ] ) ).toContain( '"padding":"pad-lg"' );
} );

test( 'a card saved today keeps its default hover', () => {
	const again = parse( serialize( [ createBlock( 'marccent/card', { version: 1 } ) ] ) )[ 0 ];
	expect( again.attributes.hover ).toBe( '' );
	expect( again.attributes.version ).toBe( 1 );
} );
