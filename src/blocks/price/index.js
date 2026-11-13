/*
 * Price: the theme's Price (components/ui/price.js), an amount as price tags set it ($19⁹⁹/mo, an old price struck
 * through). The server writes it on the page (render.php, marccent_blocks_price, with PHP's intl as the theme uses
 * the browser's Intl).
 */
import { register } from '../../shared/register';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { Price } from 'marccent-theme/components/ui/price';
import metadata from './block.json';

const number = ( v ) => ( v === '' || v === undefined ? undefined : Number( v ) );

const Edit = ( { attributes: a, setAttributes: set } ) => (
	<>
		<InspectorControls>
			<PanelBody title="Price">
				<TextControl label="Amount" type="number" step="0.01" value={ a.amount ?? '' } onChange={ ( v ) => set( { amount: number( v ) } ) } />
				<TextControl label="Old price (optional)" help="Struck through before it." type="number" step="0.01" value={ a.was ?? '' } onChange={ ( v ) => set( { was: number( v ) } ) } />
				<TextControl label="Per (optional)" help="The period after a slash: mo, yr, user." value={ a.per } onChange={ ( per ) => set( { per } ) } />
				<TextControl label="Currency" help="An ISO code: USD, EUR, GBP." value={ a.currency } onChange={ ( currency ) => set( { currency: currency.toUpperCase() } ) } />
				<TextControl label="Language and region" help="How it's written: en-US, de-DE, es-MX." value={ a.locale } onChange={ ( locale ) => set( { locale } ) } />
			</PanelBody>
		</InspectorControls>
		<div { ...useBlockProps() }>
			{ a.amount === undefined ? <p style={ { opacity: 0.7 } }>Set an amount in the block's settings.</p>
				: <Price amount={ a.amount } currency={ a.currency || 'USD' } locale={ a.locale || 'en-US' } per={ a.per || undefined } was={ a.was } /> }
		</div>
	</>
);

register( metadata, { edit: Edit, save: () => null } );
