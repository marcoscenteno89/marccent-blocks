// What the editor packages expect from a browser that jsdom lacks.
global.matchMedia ??= () => ( { matches: false, addListener() {}, removeListener() {}, addEventListener() {}, removeEventListener() {} } );
global.ResizeObserver ??= class { observe() {} unobserve() {} disconnect() {} };
global.IntersectionObserver ??= class { observe() {} unobserve() {} disconnect() {} };
window.scrollTo ??= () => {};
const util = require( 'util' );
global.TextEncoder ??= util.TextEncoder;
global.TextDecoder ??= util.TextDecoder;
