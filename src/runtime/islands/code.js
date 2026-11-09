// A marccent/code block: the theme's Code (CodeMirror loads when it comes near the screen).
import { Code } from 'marccent/components/ui/code';

export default function CodeIsland( { code = '', lang = 'js' } ) {
	return <Code lang={ lang }>{ code }</Code>;
}
