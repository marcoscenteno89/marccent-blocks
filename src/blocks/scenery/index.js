/*
 * Scenery: the theme's Scenery (visuals/scenery.js) from its layers' names, back to front; its blocks sit on top.
 */
import { islandBlock } from '../../shared/island';
import { Scenery } from '@marccent/theme/components/visuals/scenery';
import metadata from './block.json';

const KINDS = 'stars moon sun clouds birds mountains hills dunes canyon buttes forest trees city waves surf palms farm cliffs wisps balloons ranges treeline farcity mesas street meadow cactus beach rim'.split( ' ' );

islandBlock( metadata, {
	fields: [
		{ key: 'layers', label: 'Layers, back to front', type: 'textarea', help: `Comma-separated; sky first: ${ KINDS.join( ', ' ) }.` },
		{ key: 'height', label: 'Height', help: 'e.g. 22rem' },
		{ key: 'min', label: 'Least height of the ground', help: 'e.g. 40vh, for a full-screen scene. Empty: from the width.' },
		{ key: 'parallax', label: 'Parallax (layers drift with the scroll)', type: 'toggle' },
	],
	toProps: ( a ) => ( { layers: ( a.layers || '' ).split( ',' ).map( ( l ) => l.trim() ).filter( ( l ) => KINDS.includes( l ) ), height: a.height || undefined, min: a.min || undefined, parallax: false } ),
	preview: ( p ) => <Scenery { ...p } />,
	inner: { template: [ [ 'core/heading', { level: 2, placeholder: 'A headline over the landscape' } ] ] },
} );
