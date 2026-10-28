/*
 * Animated band: the Next site's CanvasBand (components/layout/canvas-band.js). A .stack: the canvas host
 * (data-component="animation", which the runtime's CanvasScanner fills with the named animation, only while on
 * screen and not for reduced motion) and the content centered over it. The editor shows the band without the canvas.
 */
import { register } from '../../shared/register';
import { InspectorControls, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, TextControl } from '@wordpress/components';
import { ANIMATIONS, SURFACES, cx } from '../../shared/options';
import metadata from './block.json';

const outer = ( a ) => ( { className: cx( 'wrap wrap-fluid pad-0 stack', a.surface ), style: { minHeight: a.minHeight || undefined } } );
const content = { className: 'col-center pad text-center', style: { position: 'relative', gridTemplateColumns: 'minmax(0, 1fr)' } };

const Canvas = ( { a } ) => (
	<div data-component="animation" data-name={ a.name } data-fps={ a.fps || undefined } data-colors={ a.colors || undefined }
		className="bg w-100 h-100" aria-hidden="true" />
);

const Edit = ( { attributes: a, setAttributes: set } ) => {
	const props = useInnerBlocksProps( content, { template: [ [ 'core/heading', { level: 2, placeholder: 'Over the animation' } ] ] } );
	return (
		<>
			<InspectorControls>
				<PanelBody title="Animation">
					<SelectControl label="Animation" value={ a.name } options={ ANIMATIONS } onChange={ ( name ) => set( { name } ) } />
					<TextControl label="Least height" value={ a.minHeight } onChange={ ( minHeight ) => set( { minHeight } ) } />
					<RangeControl label="Frames per second" help="Empty: the theme's cap for decorations." value={ a.fps } min={ 5 } max={ 60 } allowReset onChange={ ( fps ) => set( { fps } ) } />
					<TextControl label="Colors" help="Comma-separated, replacing the theme's: #fff, #1ecbe1" value={ a.colors } onChange={ ( colors ) => set( { colors } ) } />
					<SelectControl label="Background" value={ a.surface } options={ SURFACES } onChange={ ( surface ) => set( { surface } ) } />
				</PanelBody>
			</InspectorControls>
			<section { ...useBlockProps( outer( a ) ) }>
				<div className="bg w-100 h-100 col-center" aria-hidden="true" style={ { opacity: 0.4 } }><small>{ a.name }</small></div>
				<div { ...props } />
			</section>
		</>
	);
};

const save = ( { attributes: a } ) => (
	<section { ...useBlockProps.save( outer( a ) ) }>
		<Canvas a={ a } />
		<div { ...useInnerBlocksProps.save( content ) } />
	</section>
);

register( metadata, { edit: Edit, save } );
