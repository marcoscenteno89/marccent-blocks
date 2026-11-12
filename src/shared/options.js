/*
 * The theme's class vocabulary as editor choices: each option's value is the class (or classes) a block saves, so
 * the saved HTML is the same markup the Next site writes by hand. A choice a setting has is marccent-theme's
 * (markup/settings.json, PRODUCT-LINE.md 5): only its label is here. The lists below them (button looks, shapes, grid
 * layouts) are classes, not a setting's choices.
 */
import { __ } from '@wordpress/i18n';
import SETTINGS from 'marccent-theme/markup/settings.json';

const opt = ( label, value ) => ( { label, value } );

// Labels for the settings' choices; a choice without one shows its value (a hover's name).
const LABELS = {
	'': __( 'None' ), bg: 'Surface', 'bg border': 'Surface + border', 'bg-primary': 'Primary', 'bg-primary-darker': 'Primary, darker',
	'bg-secondary': 'Secondary', 'bg-gradient': 'Gradient', 'bg-gradient-darker': 'Gradient, darker', glass: 'Glass',
	'pad-0': 'None', 'pad-xs': 'Extra small', 'pad-sm': 'Small', pad: 'Normal', 'pad-md': 'Medium', 'pad-lg': 'Large', 'pad-xl': 'Extra large',
	'gap-0': 'None', 'gap-xs': 'Extra small', 'gap-sm': 'Small', 'gap-md': 'Medium', 'gap-lg': 'Large', 'gap-xl': 'Extra large',
	'pad-y-section': 'Section', 'pad-y-section-sm': 'Section, small', 'pad-y-section-lg': 'Section, large', 'pad-y-0': 'None',
	'pad-y-sm': 'Small', 'pad-y': 'Normal', 'pad-y-md': 'Medium', 'pad-y-lg': 'Large', 'pad-y-xl': 'Extra large', 'pad-y-2xl': 'Double',
	wrap: 'Normal', 'wrap wrap-sm': 'Narrow', 'wrap wrap-lg': 'Wide', 'wrap wrap-fluid': 'Full',
	'items-start': 'Top', 'items-center': 'Center', 'items-end': 'Bottom',
	'hover-none': 'None', ticket: 'Ticket (notches and a tear line)',
	top: 'Above', bottom: 'Below', start: 'Before (left)', end: 'After (right)',
	js: 'JavaScript', jsx: 'JSX', css: 'CSS', html: 'HTML', sh: 'Shell',
	phone: 'Phone number', email: 'Email address', address: 'Street address',
};
// What the empty choice means, where it isn't None.
const EMPTY = { 'Card.hover': 'Default', 'Card.gap': 'Normal', 'Grid.gap': 'Normal', 'Section.gap': 'Normal', 'Grid.alignItems': 'Stretch', 'Menu.gap': 'Normal' };

// A setting's choices as options: choices( 'Card', 'surface' ). none: a None first, for a setting whose choices
// don't include the empty one (Card's variant).
export const choices = ( component, key, { none = false } = {} ) => {
	const list = SETTINGS[ component ]?.settings?.[ key ]?.choices;
	if ( ! list ) {
		throw new Error( `markup/settings.json has no choices for ${ component }.${ key }` );
	}
	const label = ( v ) => ( v === '' ? EMPTY[ `${ component }.${ key }` ] ?? LABELS[ '' ] : LABELS[ v ] ?? v.replace( /^hover-/, '' ) );
	return [ ...( none && ! list.includes( '' ) ? [ opt( LABELS[ '' ], '' ) ] : [] ), ...list.map( ( v ) => opt( label( v ), v ) ) ];
};

export const SURFACES = choices( 'Card', 'surface' );
export const HOVERS = choices( 'Card', 'hover' );
export const PADS = choices( 'Card', 'padding' );
export const GAPS = choices( 'Grid', 'gap' );
export const ALIGN_ITEMS = choices( 'Grid', 'alignItems' );
export const WIDTHS = choices( 'Section', 'width' );
export const PADS_Y = choices( 'Section', 'verticalPadding' );
export const ANIMATIONS = choices( 'Animation', 'name' );

// Button looks (.btn modifiers in colors.css).
export const BUTTON_COLORS = [
	opt( 'Default', '' ),
	opt( 'Primary', 'primary' ),
	opt( 'Secondary', 'secondary' ),
	opt( 'Gradient', 'gradient' ),
	opt( 'Outline gradient', 'outline-gradient' ),
	opt( 'Light', 'light' ),
	opt( 'Dark', 'dark' ),
	opt( 'Success', 'success' ),
	opt( 'Info', 'info' ),
	opt( 'Danger', 'danger' ),
];

// Shapes for an icon's box (utilities.css .shape-*).
export const SHAPES = [ opt( 'None', '' ), ...[
	'hexagon', 'hexagon-flat', 'circle', 'squircle', 'diamond', 'octagon', 'pentagon', 'blob', 'star', 'bevel', 'leaf', 'pill', 'egg',
].map( ( s ) => opt( s, `shape-${ s }` ) ) ];

// Grid shapes (layout.css): equal columns, or the uneven layouts that go to one column on narrow screens.
export const LAYOUTS = [
	opt( 'Equal columns', 'cols' ),
	opt( 'Equal columns, always', 'fixed' ),
	opt( 'Row of items, content width', 'auto-cols' ),
	opt( 'Wrapping row', 'row-wrap' ),
	opt( '2 : 1', 'layout-2-1' ),
	opt( '1 : 2', 'layout-1-2' ),
	opt( '3 : 1', 'layout-3-1' ),
	opt( '1 : 3', 'layout-1-3' ),
	opt( '1 : 2 : 1', 'layout-1-2-1' ),
	opt( 'Ends (start … end)', 'layout-ends' ),
];

// Joins class names, skipping empty ones (lib/cx.js does the same in the package).
export const cx = ( ...c ) => c.filter( Boolean ).join( ' ' );
