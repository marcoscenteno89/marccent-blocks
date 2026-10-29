/*
 * The theme's styles (build/styles/core.css), for the front end and the editor: marccent-theme's core.css and every
 * component's stylesheet (components.css), without the design styles (build/designs/<id>.css: a page loads only its
 * own). In Next each component imports its own file; here the server writes the blocks' markup, so all of them load
 * up front (they sit in the components layer, so the order among them doesn't matter). Font Awesome is its base only
 * (styles/fontawesome.css, webpack.config.js); a page's icons are printed with it (inc/assets.php). Then this
 * plugin's own WordPress fixes (wp.css).
 */
import 'marccent-theme/styles/core.css';
import 'marccent-theme/styles/components.css';
import './wp.css';
