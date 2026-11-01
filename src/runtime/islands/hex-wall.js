// A marccent/hex-wall block: the theme's HexWall.
import { HexWall } from 'marccent-theme/components/widgets/layout';

export default function HexWallIsland( { people = [], perRow = 4 } ) {
	return <HexWall people={ people } perRow={ perRow } />;
}
