/*
 * Clock: the theme's Clock (ui/clock.js), the live time.
 */
import { islandBlock } from '../../shared/island';
import { Clock } from '@marccent/theme/components/ui/clock';
import metadata from './block.json';

islandBlock( metadata, {
	fields: [ { key: 'format', label: 'Format', help: 'dayjs tokens: h:mm:ss A (3:05:09 PM), HH:mm (15:05), dddd, MMMM D (Saturday, October 31).' } ],
	preview: ( p ) => <Clock key={ p.format } format={ p.format || undefined } />,
} );
