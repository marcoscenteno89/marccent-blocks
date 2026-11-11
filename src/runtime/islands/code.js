// A marccent/code block: the theme's Code (CodeMirror loads when it comes near the screen).
import { Code } from 'marccent-theme/components/ui/code';

export default function CodeIsland( { code = '', language = 'js' } ) {
	return <Code language={ language }>{ code }</Code>;
}
