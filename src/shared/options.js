/*
 * The theme's class vocabulary as editor choices: each option's value is the class (or classes) a block saves, so
 * the saved HTML is the same markup the Next site writes by hand.
 */
import { __ } from '@wordpress/i18n';

const opt = ( label, value ) => ( { label, value } );

// Box backgrounds (styles/tokens/colors.css; .bg is the page's surface, raised).
export const SURFACES = [
	opt( __( 'None' ), '' ),
	opt( 'Surface', 'bg' ),
	opt( 'Surface + border', 'bg border' ),
	opt( 'Primary', 'bg-primary' ),
	opt( 'Primary, darker', 'bg-primary-darker' ),
	opt( 'Secondary', 'bg-secondary' ),
	opt( 'Gradient', 'bg-gradient' ),
	opt( 'Gradient, darker', 'bg-gradient-darker' ),
	opt( 'Glass', 'glass' ),
];

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

// Hover effects (styles/hovers.css). Empty: the default (buttons take the site's, data-hover on <html>).
export const HOVERS = [ opt( 'Default', '' ), opt( 'None', 'hover-none' ), ...[
	'holographic', 'bloom', 'corners', 'dash', 'double', 'expand', 'fill', 'flip', 'float', 'frame', 'glow', 'goo', 'grow',
	'halo', 'inset-ring', 'morph', 'offset', 'pill', 'pop', 'press', 'rings', 'rise', 'shine', 'slide', 'spin', 'split',
	'spread', 'stripes', 'tabs', 'trace', 'underline', 'wipe', 'x',
].map( ( h ) => opt( h, `hover-${ h }` ) ) ];

// Shapes for an icon's box (utilities.css .shape-*).
export const SHAPES = [ opt( 'None', '' ), ...[
	'hexagon', 'hexagon-flat', 'circle', 'squircle', 'diamond', 'octagon', 'pentagon', 'blob', 'star', 'bevel', 'leaf', 'pill', 'egg',
].map( ( s ) => opt( s, `shape-${ s }` ) ) ];

// Canvas animations (components/visuals/canvas-scanner.js NAMES).
export const ANIMATIONS = [ 'blob', 'fire', 'wave', 'network-loop', 'particle-flowfield', 'orbit', 'snow', 'fireflies', 'leaves', 'rain', 'bubbles' ].map( ( a ) => opt( a, a ) );

// Page widths (.wrap sizes in layout.css).
export const WIDTHS = [ opt( 'Normal', 'wrap' ), opt( 'Narrow', 'wrap wrap-sm' ), opt( 'Wide', 'wrap wrap-lg' ), opt( 'Full', 'wrap wrap-fluid' ) ];

export const PADS_Y = [ opt( 'None', 'pad-y-0' ), opt( 'Small', 'pad-y-sm' ), opt( 'Normal', 'pad-y' ), opt( 'Large', 'pad-y-lg' ) ];
export const PADS = [ opt( 'None', 'pad-0' ), opt( 'Small', 'pad-sm' ), opt( 'Normal', 'pad' ), opt( 'Large', 'pad-lg' ) ];
export const GAPS = [ opt( 'None', 'gap-0' ), opt( 'Small', 'gap-sm' ), opt( 'Normal', '' ), opt( 'Large', 'gap-lg' ) ];

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

export const ALIGN_ITEMS = [ opt( 'Stretch', '' ), opt( 'Top', 'items-start' ), opt( 'Center', 'items-center' ), opt( 'Bottom', 'items-end' ) ];

// Joins class names, skipping empty ones (lib/cx.js does the same in the package).
export const cx = ( ...c ) => c.filter( Boolean ).join( ' ' );
