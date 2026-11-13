/*
 * Appearance → Marccent theme (Marccent WP's page, its inc/theme-options.php): the site's theme, drawn from
 * marccent-theme's site settings (markup/site-settings.json, through settingsFields) with WordPress's own controls,
 * beside a preview of the site that changes as you pick. Nothing of the theme's is worked out here (PRODUCT-LINE.md 5.7):
 * the colors a preview paints and the contrast warnings are lib/palette.js's; what's saved is checked on the server
 * against the same settings (marccent_blocks_site_settings_sanitize). Nothing is saved until Save is pressed.
 *
 * window.marccentThemeOptions: { base (lib/theme.defaults.json), options ({ designs, hovers }), saved (the site's,
 * JSON), preview (the site's address with ?marccent_preview) }.
 */
import { createRoot, useEffect, useState } from '@wordpress/element';
import { Button, Notice, PanelBody, TextareaControl } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { settingsFields, SITE_SETTINGS } from 'marccent-theme/components/theme/settings-panel';
import { effectiveTheme, themeVars, contrastChecks } from 'marccent-theme/lib/palette';
import { Field, ListField } from '../shared/island';

const { base, options, saved, preview } = window.marccentThemeOptions;
const parse = ( json ) => {
	try {
		const v = JSON.parse( json || '{}' );
		return v && typeof v === 'object' ? v : {};
	} catch {
		return {};
	}
};

// The CSS variables as lines of text, "--name: value", and back.
const varsText = ( vars ) => Object.entries( vars || {} ).map( ( [ k, v ] ) => `${ k }: ${ v }` ).join( '\n' );
const textVars = ( text ) => Object.fromEntries( text.split( '\n' ).map( ( l ) => l.match( /^\s*(--[a-z][a-z0-9-]*)\s*:\s*([^;{}<>]+?)\s*;?\s*$/ ) ).filter( Boolean ).map( ( m ) => [ m[ 1 ], m[ 2 ] ] ) );
const CssVars = ( { f, value, onChange } ) => {
	const [ text, setText ] = useState( null ); // as typed, while it's edited
	return (
		<TextareaControl label={ f.label } help={ f.help } rows={ 6 } value={ text ?? varsText( value ) }
			onChange={ ( t ) => { setText( t ); const v = textVars( t ); onChange( Object.keys( v ).length ? v : undefined ); } }
			onBlur={ () => setText( null ) } />
	);
};

// A setting of its own fields (what visitors may change), each drawn in turn.
const Group = ( { f, value, onChange } ) => (
	<>
		{ f.help && <p className="description">{ f.help }</p> }
		{ f.fields.map( ( x ) => <Field key={ x.key } f={ x } value={ value?.[ x.key ] ?? x.default } onChange={ ( v ) => onChange( { ...value, [ x.key ]: v } ) } /> ) }
	</>
);

// The site's theme on the preview's <html>, as the server paints it on the page (inc/theme.php), from the same rules.
const paint = ( doc, theme ) => {
	if ( ! doc?.documentElement ) return;
	const html = doc.documentElement;
	html.id = 'site';
	html.dataset.design = theme.design || 'modern';
	html.dataset.theme = theme.isDark ? 'dark' : 'light';
	html.classList.toggle( 'glass', !! theme.glass );
	if ( theme.hover && theme.hover !== 'none' ) html.dataset.hover = theme.hover; else delete html.dataset.hover;
	const vars = themeVars( theme );
	( html.marccentVars || [] ).filter( ( k ) => ! ( k in vars ) ).forEach( ( k ) => html.style.removeProperty( k ) );
	Object.entries( vars ).forEach( ( [ k, v ] ) => html.style.setProperty( k, v ) );
	html.marccentVars = Object.keys( vars );
};

const App = () => {
	const [ site, setSite ] = useState( () => parse( saved ) );
	const [ clean, setClean ] = useState( () => JSON.stringify( parse( saved ) ) );
	const [ status, setStatus ] = useState( null );
	const [ saving, setSaving ] = useState( false );
	const [ frame, setFrame ] = useState( null );
	const theme = effectiveTheme( site, base );
	const dirty = JSON.stringify( site ) !== clean;

	const set = ( key, value ) => {
		const next = { ...site, [ key ]: value };
		Object.keys( next ).forEach( ( k ) => next[ k ] === undefined && delete next[ k ] );
		setSite( next );
		setStatus( null );
	};
	const save = () => {
		setSaving( true );
		apiFetch( { path: '/wp/v2/settings', method: 'POST', data: { marccent_defaults: JSON.stringify( site ) } } )
			.then( ( r ) => {
				const back = parse( r.marccent_defaults );
				setSite( back );
				setClean( JSON.stringify( back ) );
				setStatus( { kind: 'success', text: 'Saved. The site uses the new theme now.' } );
				frame?.contentWindow.location.reload(); // the header's visitor buttons are the server's
			} )
			.catch( ( e ) => setStatus( { kind: 'error', text: `Not saved: ${ e.message || 'the server said no.' }` } ) )
			.finally( () => setSaving( false ) );
	};

	useEffect( () => { frame && paint( frame.contentDocument, theme ); } );
	useEffect( () => {
		const warn = ( e ) => { if ( dirty ) { e.preventDefault(); e.returnValue = ''; } };
		window.addEventListener( 'beforeunload', warn );
		return () => window.removeEventListener( 'beforeunload', warn );
	}, [ dirty ] );

	// The theme's settings, with what only this page knows: the lists to choose from and how a field is drawn here.
	const fields = settingsFields( SITE_SETTINGS, {
		design: { label: 'Design style', options: options.designs.map( ( d ) => [ d.title, d.id ] ), help: options.designs.find( ( d ) => d.id === ( theme.design || 'modern' ) )?.description },
		palette: { type: 'select', options: theme.palettes.map( ( p ) => [ p.id, p.id ] ) },
		palettes: { label: 'The site’s own palettes', itemLabel: 'Palette' },
		isDark: { label: 'Dark mode' },
		hover: { label: 'Default hover', options: [ [ 'None', 'none' ], ...options.hovers.map( ( h ) => [ h.replace( /-/g, ' ' ).replace( /^./, ( c ) => c.toUpperCase() ), h ] ) ] },
		vars: { type: 'css', label: 'CSS variables' },
		visitor: { label: 'What visitors may change' },
		expiresDays: { type: 'range', label: 'Visitors’ own theme lasts (days)' },
	} );
	const draw = ( f ) => {
		const value = site[ f.key ] ?? theme[ f.key ];
		const onChange = ( v ) => set( f.key, v );
		if ( f.type === 'css' ) return <CssVars key={ f.key } f={ f } value={ site.vars } onChange={ onChange } />;
		if ( f.type === 'group' ) return <PanelBody key={ f.key } title={ f.label } initialOpen={ false }><Group f={ f } value={ value } onChange={ onChange } /></PanelBody>;
		if ( f.type === 'list' ) return <ListField key={ f.key } f={ f } value={ site[ f.key ] } onChange={ onChange } />;
		return <Field key={ f.key } f={ f } value={ value } values={ site } onChange={ onChange } />;
	};
	const checks = contrastChecks( theme );

	return (
		<div id="poststuff">
			<div id="post-body" className="metabox-holder columns-2">
				<div id="post-body-content">
					<iframe title="Preview of the site" src={ preview } ref={ setFrame } width="100%" height="720"
						onLoad={ ( e ) => paint( e.target.contentDocument, theme ) } />
				</div>
				<div id="postbox-container-1" className="postbox-container">
					<p>
						<Button variant="primary" onClick={ save } disabled={ ! dirty || saving } isBusy={ saving }>{ saving ? 'Saving…' : 'Save' }</Button>{ ' ' }
						<Button variant="tertiary" onClick={ () => { setSite( parse( clean ) ); setStatus( null ); } } disabled={ ! dirty || saving }>Discard changes</Button>{ ' ' }
						{ dirty && <span className="description">Not saved yet</span> }
					</p>
					{ status && <Notice status={ status.kind } onRemove={ () => setStatus( null ) }>{ status.text }</Notice> }
					{ checks.map( ( c ) => (
						<Notice key={ c.what } status="warning" isDismissible={ false }>{ `${ c.what }: ${ c.ratio.toFixed( 2 ) }:1, under WCAG AA's 4.5:1. Shift the color a shade.` }</Notice>
					) ) }
					<div className="components-panel">
						<PanelBody title="Theme">{ fields.map( draw ) }</PanelBody>
					</div>
					<p>
						<Button variant="secondary" isDestructive onClick={ () => setSite( site.palettes ? { palettes: site.palettes } : {} ) }>
							Back to the theme’s defaults (keeps the palettes)
						</Button>
					</p>
				</div>
			</div>
		</div>
	);
};

const root = document.getElementById( 'marccent-theme-options' );
if ( root ) createRoot( root ).render( <App /> );
