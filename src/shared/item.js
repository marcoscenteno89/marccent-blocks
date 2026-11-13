/*
 * A block that is one of a theme component's children (a Timeline item, a Dock item: T-034), inside its parent's
 * block or a Loop there. Its settings are its component's fields as an island's are (shared/island.js: component and
 * overrides), each an attribute and a prop of
 * the child component; render.php writes them for the parent's island (marccent_blocks_item, inc/islands.php). In the
 * editor it shows as a line: its icon and its name.
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { register } from './register';
import { Field, blockFields } from './island';

// A Font Awesome name ("house") or full classes ("fa-brands fa-github"), as the theme's Icon takes them.
const iconClass = ( icon ) => ( icon.startsWith( 'fa-' ) ? icon : `fa-solid fa-${ icon }` );

export const itemBlock = ( metadata, { component, overrides, fields: listed, name, icon = 'icon' } ) => {
	const fields = component ? blockFields( metadata, component, overrides ) : listed;
	const Edit = ( { attributes: a, setAttributes: set } ) => (
		<>
			<InspectorControls>
				<PanelBody title={ metadata.title }>
					{ fields.map( ( f ) => <Field key={ f.key } f={ f } value={ a[ f.key ] } values={ a } onChange={ ( v ) => set( { [ f.key ]: v } ) } /> ) }
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps( { className: 'd-grid auto-cols justify-start items-center gap-sm border round pad-xs pad-x-sm' } ) }>
				{ a[ icon ] && <i className={ iconClass( a[ icon ] ) } aria-hidden="true" /> }
				<span>{ name( a ) || <em style={ { opacity: 0.6 } }>{ metadata.title }: fill it in the sidebar</em> }</span>
			</div>
		</>
	);
	register( metadata, { edit: Edit, save: () => null } );
};
