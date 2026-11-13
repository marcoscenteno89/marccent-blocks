/*
 * Price: the theme's Price (components/ui/price.js).
 */
import { islandBlock } from '../../shared/island';
import { Price } from 'marccent-theme/components/ui/price';
import metadata from './block.json';

islandBlock( metadata, {
	component: 'Price',
	overrides: { per: { label: 'Per', help: 'mo, yr, user; empty: a one-off price' }, was: { label: 'Was (struck through)' } },
	toProps: ( a ) => ( { amount: Number( a.amount ) || 0, currency: a.currency || 'USD', locale: a.locale || 'en-US', per: a.per || undefined, was: a.was ?? undefined } ),
	preview: ( p ) => <Price { ...p } />,
} );
