/*
 * Clock: the theme's Clock (ui/clock.js), the live time: as text, an analog face or flip digits.
 */
import { islandBlock } from '../../shared/island';
import { Clock } from 'marccent-theme/components/ui/clock';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'Clock',
	overrides: {
		face: { label: 'Look', options: [ [ 'Digital (text)', 'digital' ], [ 'Analog', 'analog' ], [ 'Flip digits', 'flip' ] ] },
		format: { help: 'dayjs tokens: h:mm:ss A (3:05:09 PM), HH:mm (15:05), dddd, MMMM D (Saturday, October 31). Flip digits: 24-hour when it has an H.' },
	},
	preview: ( p ) => <Clock key={ p.face + p.format } face={ p.face || undefined } format={ p.format || undefined } />,
} );
