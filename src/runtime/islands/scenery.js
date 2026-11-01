// A marccent/scenery block: the theme's Scenery from its layers' names, its blocks on top.
import { Scenery } from 'marccent-theme/components/visuals/scenery';
import { HtmlList } from '../../shared/html';

export default function SceneryIsland( { layers = [], height, parallax = true, min, html = [] } ) {
	return (
		<Scenery layers={ layers } height={ height || undefined } parallax={ parallax } min={ min || undefined }>
			{ html.length > 0 && <div className="grid cols-1 gap-sm text-center"><HtmlList html={ html } /></div> }
		</Scenery>
	);
}
