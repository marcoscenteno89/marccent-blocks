/*
 * Alert: its blocks in a tone's fill (render.php: role alert for danger, else status).
 */
import { register } from '../../shared/register';
import { InnerBlocks, InspectorControls, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import metadata from './block.json';

const TONES = [ [ 'Primary', 'primary' ], [ 'Secondary', 'secondary' ], [ 'Success', 'success' ], [ 'Info', 'info' ], [ 'Danger', 'danger' ] ].map( ( [ label, value ] ) => ( { label, value } ) );

const Edit = ( { attributes: a, setAttributes: set } ) => {
	const props = useInnerBlocksProps( useBlockProps( { className: `pad border round bg-${ a.tone }` } ), { template: [ [ 'core/paragraph', { placeholder: 'The message…' } ] ] } );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Alert">
					<SelectControl label="Tone" help="Danger is announced at once to screen readers; the others politely." value={ a.tone } options={ TONES } onChange={ ( tone ) => set( { tone } ) } />
				</PanelBody>
			</InspectorControls>
			<div { ...props } />
		</>
	);
};

register( metadata, { edit: Edit, save: () => <InnerBlocks.Content /> } );
