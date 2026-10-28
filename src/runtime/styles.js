/*
 * The theme's styles (build/styles/core.css), for the front end and the editor: styles/index.css file by file in its
 * order, without the design styles (build/designs/<id>.css: a page loads only its own). In Next each component imports
 * its own file; here the blocks' markup is saved as static HTML, so all of them load up front (they sit in the
 * components layer, so the order among them doesn't matter). icons.css brings Font Awesome's base only; a page's icons
 * are printed with it (marccent-blocks.php). Then this plugin's own WordPress fixes (wp.css).
 */
import 'marccent-theme/styles/layers.css';
import 'marccent-theme/styles/tokens/defaults.css';
import 'marccent-theme/styles/base/base.css';
import 'marccent-theme/styles/tokens/colors.css';
import 'marccent-theme/styles/layout.css';
import 'marccent-theme/styles/base/animations.css';
import 'marccent-theme/styles/components/icons.css';
import 'marccent-theme/styles/components/header.css';
import 'marccent-theme/styles/hovers.css';
import 'marccent-theme/styles/utilities.css';
import 'marccent-theme/styles/components/accordion.css';
import 'marccent-theme/styles/components/board.css';
import 'marccent-theme/styles/components/button.css';
import 'marccent-theme/styles/components/canvas.css';
import 'marccent-theme/styles/components/clock.css';
import 'marccent-theme/styles/components/close.css';
import 'marccent-theme/styles/components/form.css';
import 'marccent-theme/styles/components/loading.css';
import 'marccent-theme/styles/components/menu.css';
import 'marccent-theme/styles/components/modal.css';
import 'marccent-theme/styles/components/nav.css';
import 'marccent-theme/styles/components/pagination.css';
import 'marccent-theme/styles/components/toast.css';
import './wp.css';
