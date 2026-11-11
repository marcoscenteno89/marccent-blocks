/*
 * What visitors may change of the theme (Appearance → Marccent theme, Visitors), for a header: the theme button that
 * opens the full mini theme editor in a panel, or single controls of it (only light/dark, say) side by side. From a
 * marccent/mini-theme-editor block with "What Visitors allows" on (render.php); it was the Navbar block's end.
 */
import { lazy, Suspense } from 'react';
import { ToggleButton } from 'marccent-theme/components/ui/toggle-button';
import { ThemeControls } from './theme-controls';

// The mini theme editor loads only when the theme button opens it.
const MiniThemeEditor = lazy( () => import( /* webpackChunkName: "mini-theme-editor" */ 'marccent-theme/components/theme/mini-editor' ).then( ( m ) => ( { default: m.MiniThemeEditor } ) ) );

export default function ThemeButton( { themeButton = true, controls = [] } ) {
	return (
		<div className="grid auto-cols items-center gap-sm">
			<ThemeControls controls={ controls } />
			{ themeButton && (
				<ToggleButton data-name="theme" icon="brush" activeIcon="xmark" className="hover-expand"
					panel={ <Suspense fallback={ null }><MiniThemeEditor scope="site" /></Suspense> }>
					Theme
				</ToggleButton>
			) }
		</div>
	);
}
