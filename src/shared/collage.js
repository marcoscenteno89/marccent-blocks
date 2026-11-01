/*
 * A collage's tiles as the theme's ImageCards on its dense grid (widgets/collage.js). The theme's Collage takes tiles
 * only as names of its own photos (/img/<name>.webp), so the tiles are drawn here and given to it as children, with
 * its sizes and borders (copied: the theme doesn't export them).
 */
import { Collage } from 'marccent-theme/components/widgets/collage';
import { ImageCard } from 'marccent-theme/components/widgets/layout';

const SIZE = {
	square: {},
	circle: { shape: 'circle', ratio: '1 / 1', style: { width: 'min(100%, var(--row))', justifySelf: 'center', alignSelf: 'center' } },
	portrait: { style: { gridRow: 'span 2' } },
	landscape: { className: 'span-2' },
	wide: { className: 'span-3' },
	feature: { className: 'span-2', style: { gridRow: 'span 2' }, big: true },
	panorama: { className: 'span-4', big: true },
};
const BORDER = {
	line: { className: 'border' },
	frame: { style: { border: '.4rem solid color-mix(in srgb, var(--light) 92%, transparent)' } },
	accent: { style: { border: '2px solid var(--primary)', boxShadow: '0 0 0 4px color-mix(in srgb, var(--primary) 25%, transparent), var(--shadow)' } },
};

export const CollageOf = ( { tiles = [], cols = 6, row = '11rem' } ) => (
	<Collage cols={ cols } row={ row }>
		{ tiles.map( ( { size, border, src, canvas, ...card }, i ) => {
			const { style, className, ...shape } = SIZE[ size ] ?? {};
			const edge = BORDER[ border ] ?? {};
			return <ImageCard key={ i } src={ src || undefined } canvas={ canvas || undefined } ratio={ null } { ...shape } { ...card }
				className={ [ className, edge.className ].filter( Boolean ).join( ' ' ) || undefined } style={ { ...style, ...edge.style } } />;
		} ) }
	</Collage>
);
