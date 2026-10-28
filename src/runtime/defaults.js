/*
 * The theme's starting values (marccent-theme/lib/theme.defaults.json) with this site's on top. webpack.config.js
 * points every import of theme.defaults.json inside the package here. The site's come from the marccent_defaults
 * option, printed as window.marccentDefaults before any script runs. A site's palettes go before the package's.
 */
import base from 'marccent-theme/lib/theme.defaults.json';

const site = ( typeof window !== 'undefined' && window.marccentDefaults ) || {};
const own = site.palettes ?? [];

/*
 * A visitor's saved theme (marccent_theme_v2) holds a copy of the palettes and settings it started from, so once the
 * site changes its defaults (a new brand color) returning visitors would keep the old ones. The defaults it was saved
 * under are stamped beside it; when they differ, the saved theme is dropped and the visitor starts from the new ones.
 * This runs on import, before the theme reads what's saved.
 */
try {
	const stamp = JSON.stringify( site );
	// In development (window.marccentDev, WP_DEBUG on) nothing saved is restored: every load starts from the defaults.
	if ( window.marccentDev || localStorage.getItem( 'marccent_defaults_stamp' ) !== stamp ) {
		localStorage.removeItem( 'marccent_theme_v2' );
		localStorage.setItem( 'marccent_defaults_stamp', stamp );
	}
} catch {}

const defaults = {
	...base,
	...site,
	vars: { ...base.vars, ...site.vars },
	palettes: [ ...own, ...base.palettes.filter( ( p ) => ! own.some( ( o ) => o.id === p.id ) ) ],
};

export default defaults;
