// A marccent/collage block: its tiles on the theme's Collage (shared/collage.js).
import { CollageOf } from '../../shared/collage';

export default function CollageIsland( props ) {
	return <CollageOf { ...props } />;
}
