/*
 * Hex flip: its blocks are the hexagon's front; its settings (the back's text, both faces' colors, its name) come from
 * the theme's HexFlip (shared/island.js, blockFields).
 */
import { register } from '../../shared/register';
import { Field, blockFields } from '../../shared/island';
import { InnerBlocks, InspectorControls, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import metadata from './block.json';

const FIELDS = blockFields( metadata, 'HexFlip', { back: { label: 'Back', help: 'What it turns over to: a name and role, a line.' } } );

const Edit = ( { attributes: a, setAttributes: set } ) => {
	const inner = useInnerBlocksProps( { className: 'grid cols-1 gap-xs pad-sm text-center' }, { template: [ [ 'core/paragraph', { placeholder: 'The front…' } ] ] } );
	return (
		<>
			<InspectorControls>
				<PanelBody title={ metadata.title }>
					{ FIELDS.map( ( f ) => <Field key={ f.key } f={ f } value={ a[ f.key ] } values={ a } onChange={ ( v ) => set( { [ f.key ]: v } ) } /> ) }
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps( { className: 'border round' } ) }>
				<div { ...inner } />
				<p className="pad-x-sm opacity-75" style={ { margin: 0 } }>Back: { a.back || '—' }</p>
			</div>
		</>
	);
};

register( metadata, { edit: Edit, save: () => <InnerBlocks.Content /> } );
