// A marccent/mini-theme-editor block: the theme's MiniThemeEditor for the whole site, packed to one side.
import { MiniThemeEditor } from 'marccent-theme/components/theme/mini-editor';

export default function MiniThemeEditorIsland( { align = 'start' } ) {
	return <div className={ `grid cols-1 justify-${ align }` }><MiniThemeEditor scope="site" /></div>;
}
