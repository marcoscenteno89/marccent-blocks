/*
 * Every block's Additional CSS (WordPress's own, block support customCSS: the CSS in its style.css attribute, scoped
 * and printed by WordPress) edited in marccent-theme's Code instead of core's textarea. The same attribute, the same
 * rules: only for people who may edit CSS (canEditCSS), no HTML in it, empty removes it. Core's textarea is hidden in
 * the block sidebar only (the site editor's global Additional CSS keeps its own). Added once, whichever block script
 * loads first (register.js).
 */
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls, store as blockEditorStore } from '@wordpress/block-editor';
import { hasBlockSupport, getBlockType } from '@wordpress/blocks';
import { Notice } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { CodeField } from './code-field';

const HIDE_CORE = '.block-editor-block-inspector .block-editor-global-styles-advanced-panel { display: none; }';

// The style attribute with css set (or gone), and nothing left empty behind it.
const withCss = ( style = {}, css ) => {
	const next = { ...style, css: css.trim() ? css : undefined };
	if ( next.css === undefined ) {
		delete next.css;
	}
	return Object.keys( next ).length ? next : undefined;
};

const CustomCss = ( { name, attributes, setAttributes } ) => {
	const canEditCSS = useSelect( ( select ) => select( blockEditorStore ).getSettings().canEditCSS, [] );
	const [ draft, setDraft ] = useState( null ); // what's typed while it has HTML in it (not saved)
	if ( ! canEditCSS ) {
		return null;
	}
	const saved = attributes.style?.css ?? '';
	const change = ( css ) => {
		if ( css.includes( '<' ) ) {
			return setDraft( css );
		}
		setDraft( null );
		setAttributes( { style: withCss( attributes.style, css ) } );
	};
	return (
		<InspectorControls group="advanced">
			{ draft !== null && <Notice status="error" isDismissible={ false }>{ 'The CSS can’t contain “<” (HTML): it isn’t saved until that’s gone.' }</Notice> }
			<CodeField label="Additional CSS" value={ draft ?? saved } onChange={ change }
				help={ `Your own CSS for this ${ getBlockType( name )?.title ?? 'block' } block: properties and values, no selector needed (color: red;), or nested rules (& a { … }).` } />
		</InspectorControls>
	);
};

const withCustomCss = createHigherOrderComponent( ( BlockEdit ) => ( props ) => (
	<>
		<BlockEdit { ...props } />
		{ props.isSelected && hasBlockSupport( props.name, 'customCSS', true ) && <CustomCss { ...props } /> }
	</>
), 'withMarccentCustomCss' );

if ( ! window.marccentCustomCss ) {
	window.marccentCustomCss = true;
	addFilter( 'editor.BlockEdit', 'marccent/custom-css', withCustomCss );
	document.head.append( Object.assign( document.createElement( 'style' ), { id: 'marccent-custom-css', textContent: HIDE_CORE } ) );
}
