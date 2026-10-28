/*
 * Section: a full-width band (.wrap.wrap-fluid, padding the sides) and inside it the content, capped to the page width (.wrap) in a
 * 1-column grid, like the Next site's Full sections. With an image the band is a .stack: the image, a shade over it
 * and the content on top, all in one cell (the theme's replacement for absolute overlays).
 */
import { register } from '../../shared/register';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { Button, PanelBody, RangeControl, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import { SURFACES, WIDTHS, PADS_Y, GAPS, cx } from '../../shared/options';
import metadata from './block.json';

// The band pads the sides (.wrap); the content's .wrap inside it is narrower by that padding (layout.css).
const outer = ( a ) => cx( 'wrap wrap-fluid', a.imageUrl ? 'stack' : a.surface );
const inner = ( a ) => cx( a.width, a.padY, 'grid cols-1', a.gap, a.center && 'text-center justify-center', a.imageUrl && a.surface );
// A least height makes the band a one-cell grid, so its content box can center in it (not .grid: that brings columns).
const outerStyle = ( a ) => ( a.minHeight ? { minHeight: a.minHeight, ...( ! a.imageUrl && { display: 'grid', gridTemplateColumns: 'minmax(0, 1fr)' } ) } : undefined );
// Over an image: the content on top of the image and shade (position), centered in the band, in light text.
const innerStyle = ( a ) => {
	if ( a.imageUrl ) return { position: 'relative', alignContent: 'center', color: '#fff' };
	return a.minHeight ? { alignContent: 'center' } : undefined;
};

const Background = ( { a } ) => a.imageUrl && (
	<>
		<img className="cover-img" src={ a.imageUrl } alt={ a.imageAlt } loading="lazy" decoding="async" />
		<div aria-hidden="true" style={ { background: `rgb(0 0 0 / ${ a.shade }%)` } } />
	</>
);

const Edit = ( { attributes: a, setAttributes: set } ) => {
	const Tag = a.tagName;
	const blockProps = useBlockProps( { className: outer( a ), style: outerStyle( a ) } );
	const innerProps = useInnerBlocksProps( { className: inner( a ), style: innerStyle( a ) }, {
		template: [ [ 'core/heading', { placeholder: 'Section title' } ], [ 'core/paragraph' ] ],
	} );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Layout">
					<SelectControl label="Content width" value={ a.width } options={ WIDTHS } onChange={ ( width ) => set( { width } ) } />
					<SelectControl label="Space above and below" value={ a.padY } options={ PADS_Y } onChange={ ( padY ) => set( { padY } ) } />
					<SelectControl label="Space between items" value={ a.gap } options={ GAPS } onChange={ ( gap ) => set( { gap } ) } />
					<ToggleControl label="Center the content" checked={ a.center } onChange={ ( center ) => set( { center } ) } />
					<TextControl label="Least height" help="Any CSS length: 60vh, 30rem…" value={ a.minHeight } onChange={ ( minHeight ) => set( { minHeight } ) } />
					<SelectControl label="HTML element" value={ a.tagName } options={ [ 'section', 'div', 'header', 'footer', 'aside' ].map( ( t ) => ( { label: t, value: t } ) ) } onChange={ ( tagName ) => set( { tagName } ) } />
				</PanelBody>
				<PanelBody title="Background">
					<SelectControl label="Color" help={ a.imageUrl ? 'Over an image, the color goes on the content box.' : undefined } value={ a.surface } options={ SURFACES } onChange={ ( surface ) => set( { surface } ) } />
					<MediaUploadCheck>
						<MediaUpload allowedTypes={ [ 'image' ] } value={ a.imageId }
							onSelect={ ( m ) => set( { imageId: m.id, imageUrl: m.url, imageAlt: m.alt ?? '' } ) }
							render={ ( { open } ) => <Button variant="secondary" onClick={ open }>{ a.imageUrl ? 'Replace image' : 'Background image' }</Button> } />
					</MediaUploadCheck>
					{ a.imageUrl && (
						<>
							<img src={ a.imageUrl } alt="" style={ { width: '100%', marginBlock: 8 } } />
							<RangeControl label="Shade over the image (%)" value={ a.shade } min={ 0 } max={ 90 } onChange={ ( shade ) => set( { shade } ) } />
							<Button variant="link" isDestructive onClick={ () => set( { imageId: undefined, imageUrl: undefined, imageAlt: '' } ) }>Remove image</Button>
						</>
					) }
				</PanelBody>
			</InspectorControls>
			<Tag { ...blockProps }>
				<Background a={ a } />
				<div { ...innerProps } />
			</Tag>
		</>
	);
};

const save = ( { attributes: a } ) => {
	const Tag = a.tagName;
	// Block props first: the inner blocks' props serialize the children, which resets what useBlockProps.save reads.
	const blockProps = useBlockProps.save( { className: outer( a ), style: outerStyle( a ) } );
	const innerProps = useInnerBlocksProps.save( { className: inner( a ), style: innerStyle( a ) } );
	return (
		<Tag { ...blockProps }>
			<Background a={ a } />
			<div { ...innerProps } />
		</Tag>
	);
};

register( metadata, { edit: Edit, save } );
