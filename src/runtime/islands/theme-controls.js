/*
 * Single theme controls in the header, when the site lets visitors change only some of the theme (Marccent WP:
 * Appearance → Marccent theme, Visitors): each one the theme's own (the parts of its MiniThemeEditor), side by side,
 * with no theme button and no Start over. Only light/dark ticked: just that toggle.
 */
import { ModeToggle, GlassToggle } from 'marccent-theme/components/theme/toggles';
import { PaletteSwatches } from 'marccent-theme/components/theme/palette-swatches';
import { DesignSelect } from 'marccent-theme/components/theme/designs';

const CONTROLS = {
	design: () => <DesignSelect scope="site" />,
	mode: () => <ModeToggle scope="site" className="hover-expand" />,
	glass: () => <GlassToggle scope="site" className="hover-expand" />,
	palette: () => <PaletteSwatches scope="site" slider />,
};

export function ThemeControls( { controls } ) {
	return controls.filter( ( k ) => CONTROLS[ k ] ).map( ( k ) => {
		const Control = CONTROLS[ k ];
		return <Control key={ k } />;
	} );
}
