// A marccent/parallax block: its blocks in the theme's Parallax.
import { Parallax } from '@marccent/theme/components/visuals/effects';
import { HtmlList } from '../../shared/html';

export default function ParallaxIsland( { speed = 0.2, html = [] } ) {
	return <Parallax speed={ speed } className="grid cols-1 gap-sm"><HtmlList html={ html } /></Parallax>;
}
