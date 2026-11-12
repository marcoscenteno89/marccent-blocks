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
	expect( section.attributes ).toMatchObject( { as: 'header', verticalPadding: 'pad-y-sm', image: '/a.jpg', version: 2 } );
	expect( serialize( [ card ] ) ).toContain( '"padding":"pad-lg"' );
} );

test( 'a card saved today keeps its default hover', () => {
	const again = parse( serialize( [ createBlock( 'marccent/card', { version: 1 } ) ] ) )[ 0 ];
	expect( again.attributes.hover ).toBe( '' );
	expect( again.attributes.version ).toBe( 1 );
} );

test( 'a section and an animation saved at version 1', () => {
	const [ section, animation ] = parse( [
		'<!-- wp:marccent/section {"minHeight":"60vh","version":1} /-->',
		'<!-- wp:marccent/animation {"fps":24,"minHeight":"40vh","version":1} /-->',
	].join( '\n' ) );
	expect( section.attributes ).toMatchObject( { minimumHeight: '60vh', version: 2 } );
	expect( animation.attributes ).toMatchObject( { frameRate: 24, minimumHeight: '40vh', version: 2 } );
	expect( animation.attributes ).not.toHaveProperty( 'fps' );
} );

test( 'a timeline saved with its events: its items as blocks', () => {
	const [ saved, plain ] = parse( [
		'<!-- wp:marccent/timeline {"events":[{"date":"2020","title":"Begun","icon":"flag"}],"version":1} /-->',
		'<!-- wp:marccent/dock {"version":1} /-->',
	].join( '\n' ) );
	expect( saved.isValid ).toBe( true );
	expect( saved.attributes ).toMatchObject( { version: 2 } );
	expect( saved.attributes ).not.toHaveProperty( 'events' );
	expect( saved.innerBlocks.map( ( b ) => [ b.name, b.attributes.title, b.attributes.icon ] ) ).toEqual( [ [ 'marccent/timeline-item', 'Begun', 'flag' ] ] );
	// Saved with the list it had by default (nothing in the comment): that list.
	expect( plain.innerBlocks.map( ( b ) => b.attributes.label ) ).toEqual( [ 'Home', 'Contact', 'GitHub' ] );
	const again = parse( serialize( [ saved ] ) )[ 0 ];
	expect( again.innerBlocks ).toHaveLength( 1 );
	expect( serialize( [ again ] ) ).not.toContain( 'events' );
} );

test( 'a timeline saved today keeps its items', () => {
	const timeline = createBlock( 'marccent/timeline', { version: 2 }, [ createBlock( 'marccent/timeline-item', { date: '2026', title: 'Now', version: 1 } ) ] );
	const again = parse( serialize( [ timeline ] ) )[ 0 ];
	expect( again.innerBlocks.map( ( b ) => b.attributes.title ) ).toEqual( [ 'Now' ] );
} );
