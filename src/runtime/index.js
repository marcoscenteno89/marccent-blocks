/*
 * The Marccent runtime on the front end, on pages with something interactive (marccent-blocks.php decides; the styles
 * and the starting colors come from the server): one React root (React from WordPress) that does what app/layout.tsx
 * does in the Next site: loads the theme and puts it on <html> (SiteTheme, ScopeThemes), pauses off-screen animations
 * and, only where the page has them, the canvas backgrounds (CanvasScanner), the goo filter and, with the theme
 * button, the theme's toasts.
 *
 * Interactive parts of the server-rendered page are islands: an element with data-island="<name>" and its props as
 * JSON in data-props. Each is filled by its component from ISLANDS through a portal of this one root, so they all
 * share one ThemeProvider (and one theme: the header's theme editor and the page always agree). A component loads
 * only once a page has its island.
 */
import { createRoot } from 'react-dom/client';
import { createPortal } from 'react-dom';
import { lazy, Suspense, useLayoutEffect, useState } from 'react';
import { ThemeProvider } from 'marccent-theme/lib/context';
import { SiteTheme, ScopeThemes } from 'marccent-theme/components/theme/core';
import { Goo } from 'marccent-theme/components/ui/goo';
import { PauseOffscreen } from 'marccent-theme/components/pause-offscreen';

const named = ( load, name ) => lazy( () => load().then( ( m ) => ( { default: m[ name ] } ) ) );
const CanvasScanner = named( () => import( /* webpackChunkName: "canvas-scanner" */ 'marccent-theme/components/visuals/canvas-scanner' ), 'CanvasScanner' );
const Toast = named( () => import( /* webpackChunkName: "toast" */ 'marccent-theme/components/ui/toast' ), 'Toast' );

const ISLANDS = {
	navbar: lazy( () => import( /* webpackChunkName: "island-navbar" */ './islands/navbar' ) ),
	carousel: lazy( () => import( /* webpackChunkName: "island-carousel" */ './islands/carousel' ) ),
};

// An island's props, and its server-rendered children as HTML (a carousel's slides: the [data-slot] element's).
const readIsland = ( el ) => {
	let props = {};
	try {
		props = JSON.parse( el.dataset.props || '{}' );
	} catch {}
	const slot = el.querySelector( ':scope [data-slot]' );
	const html = slot ? [ ...slot.children ].map( ( c ) => c.outerHTML ) : [];
	return { el, Component: ISLANDS[ el.dataset.island ], props, html };
};

// The server's markup stays until the component (loaded on demand) has rendered, off the page, into its own box;
// then the box replaces it in one step, so nothing flashes empty.
const Swap = ( { el, box, children } ) => {
	useLayoutEffect( () => el.replaceChildren( box ), [] );
	return children;
};

const Island = ( { el, Component, props, html } ) => {
	const [ box ] = useState( () => Object.assign( document.createElement( 'div' ), { style: 'display: contents' } ) );
	return createPortal(
		<Suspense fallback={ null }>
			<Swap el={ el } box={ box }><Component { ...props } html={ html } /></Swap>
		</Suspense>,
		box
	);
};

const start = () => {
	const islands = [ ...document.querySelectorAll( '[data-island]' ) ].filter( ( el ) => ISLANDS[ el.dataset.island ] ).map( readIsland );
	// The theme button lets visitors change the theme: its messages, and any hover effect (goo's needs its filter).
	const themeButton = islands.some( ( i ) => i.props.themeButton );
	const canvases = !! document.querySelector( '[data-component~="animation"]' );
	const goo = themeButton || !! document.querySelector( '.hover-goo, [data-hover="goo"]' );
	const host = document.createElement( 'div' );
	host.id = 'marccent-runtime';
	document.body.append( host );
	createRoot( host ).render(
		<ThemeProvider path={ location.pathname }>
			<SiteTheme />
			<ScopeThemes />
			{ islands.map( ( island, i ) => <Island key={ i } { ...island } /> ) }
			{ goo && <Goo /> }
			<PauseOffscreen />
			<Suspense fallback={ null }>
				{ themeButton && <Toast /> }
				{ canvases && <CanvasScanner /> }
			</Suspense>
		</ThemeProvider>
	);
};

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', start );
} else {
	start();
}
