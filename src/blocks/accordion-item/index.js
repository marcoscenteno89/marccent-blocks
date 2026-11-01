/*
 * Accordion item: its header and, under it, its blocks; open in the editor so they can be edited.
 */
import { register } from '../../shared/register';
import { InnerBlocks, RichText, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import metadata from './block.json';

const Edit = ( { attributes: a, setAttributes: set } ) => {
	const inner = useInnerBlocksProps( { className: 'pad-sm' }, { template: [ [ 'core/paragraph', { placeholder: 'The answer…' } ] ] } );
	return (
		<div { ...useBlockProps() }>
			<RichText tagName="div" className="header pad-sm fw-bold" value={ a.header } onChange={ ( header ) => set( { header } ) } placeholder="A question" allowedFormats={ [] } />
			<div { ...inner } />
		</div>
	);
};

register( metadata, { edit: Edit, save: () => <InnerBlocks.Content /> } );
