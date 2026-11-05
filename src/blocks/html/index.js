/*
 * Custom HTML: the HTML, CSS and JavaScript in the theme's CodeTabs, right in the block, with a Preview tab (the HTML
 * and its CSS; scripts run on the page, not in the editor). Without permission to post unfiltered HTML, it's shown,
 * not edited (inc/html.php empties it on save for them too).
 */
import { useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { CodeTabs } from 'marccent-theme/components/ui/code-tabs';
import { register } from '../../shared/register';
import metadata from './block.json';

// The HTML with its CSS, scoped to the preview the way render.php scopes it to the block (CSS nesting).
const Preview = ( { html, css } ) => (
	<div data-html-preview="">
		{ css && <style>{ `[data-html-preview]{${ css }}` }</style> }
		{ /* The author's own HTML (they may post unfiltered HTML); innerHTML runs no scripts. */ }
		<div dangerouslySetInnerHTML={ { __html: html } } />
	</div>
);

const Edit = ( { attributes: a, setAttributes } ) => {
	// By the store's name: importing @wordpress/editor would tie the block to the post editor (and the content builder's tests).
	const allowed = useSelect( ( select ) => select( 'core/editor' )?.canUserUseUnfilteredHTML?.() ?? true, [] );
	// A theme panel (.bg): its text and surface from the same theme, whatever the canvas around it.
	const blockProps = useBlockProps( { className: 'bg border round pad-sm' } );
	return (
		<div { ...blockProps }>
			{ allowed ? (
				<CodeTabs value={ { html: a.html, css: a.css, js: a.js } } onChange={ ( v ) => setAttributes( v ) }
					preview={ <><Preview html={ a.html } css={ a.css } />{ a.js && <p className="fs-sm opacity-75">The script runs on the page, not here.</p> }</> } />
			) : (
				<>
					<p className="fs-sm opacity-75">Custom HTML: only people who may post unfiltered HTML can edit it.</p>
					<Preview html={ a.html } css={ a.css } />
				</>
			) }
		</div>
	);
};

register( metadata, { edit: Edit, save: () => null } );
