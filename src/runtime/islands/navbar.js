/*
 * The site's header, as in the Next site (components/layout/header.tsx): the theme's NavBar with the brand, the main
 * menu, the end slot (social icon buttons and a call-to-action button) and, last, the theme button that pulls the mini
 * theme editor down above the bar. Its props come from the marccent/navbar block (render.php): the menu from
 * WordPress, the rest from the block's settings.
 */
import { lazy, Suspense, useEffect, useRef, useState } from 'react';
import { NavBar } from 'marccent-theme/components/widgets/navbar';
import { Button } from 'marccent-theme/components/ui/button';
import { ToggleButton } from 'marccent-theme/components/ui/toggle-button';

// The mini theme editor loads only on sites that show the theme button.
const MiniThemeEditor = lazy( () => import( /* webpackChunkName: "mini-theme-editor" */ 'marccent-theme/components/theme/mini-editor' ).then( ( m ) => ( { default: m.MiniThemeEditor } ) ) );

const external = ( href ) => /^https?:/.test( href ) && ! href.startsWith( location.origin ) ? { target: '_blank', rel: 'noopener noreferrer' } : {};

export default function Navbar( { brand = {}, items = [], social = [], cta, themeButton = true } ) {
	const [ themeOpen, setThemeOpen ] = useState( false );
	const panel = useRef( null );
	useEffect( () => {
		if ( panel.current ) panel.current.inert = ! themeOpen;
	}, [ themeOpen ] );

	return (
		<NavBar
			items={ items }
			path={ location.pathname }
			onEscape={ () => setThemeOpen( false ) }
			className={ themeOpen ? 'divide' : undefined }
			brand={
				<a href={ brand.href ?? '/' } className="menu-link grid auto-cols items-center gap-sm" aria-label={ `${ brand.name }, home` }>
					{ brand.logo && <img src={ brand.logo } alt={ brand.name } width={ brand.logoSize?.[ 0 ] } height={ brand.logoSize?.[ 1 ] } style={ { height: '2.5rem', width: 'auto' } } /> }
					{ brand.showName !== false && <span className="fw-bold">{ brand.name }</span> }
				</a>
			}
			end={ ( social.length > 0 || cta ) && (
				<nav aria-label="Social" className="nav-end grid auto-cols items-center gap-sm">
					{ social.map( ( s ) => (
						<Button key={ s.href } icon={ s.icon } href={ s.href } className="secondary hover-expand" { ...external( s.href ) }>{ s.name }</Button>
					) ) }
					{ cta && <Button icon={ cta.icon } href={ cta.href } className="primary">{ cta.label }</Button> }
				</nav>
			) }
			last={ themeButton && (
				<ToggleButton data-name="theme" icon="brush" activeIcon="xmark" className="hover-expand col-last"
					open={ themeOpen } onToggle={ setThemeOpen } controls="theme-panel">
					Theme
				</ToggleButton>
			) }
			top={ themeButton && (
				<div id="theme-panel" className={ `collapse${ themeOpen ? ' open' : '' }` } ref={ panel }>
					<div>
						<div className="pad-y-sm">
							<div className="wrap pad-0 grid cols-1 justify-end"><Suspense fallback={ null }>{ themeOpen && <MiniThemeEditor scope="site" /> }</Suspense></div>
						</div>
					</div>
				</div>
			) }
		/>
	);
}
