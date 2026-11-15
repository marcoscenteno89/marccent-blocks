// A marccent/hex-grid block: the theme's HexGrid of its Hex flips.
import { HexGrid, HexFlip } from 'marccent-theme/components/widgets/layout';

export default function HexGridIsland( { perRow, childBlocks = [] } ) {
	return <HexGrid perRow={ perRow }>{ childBlocks.map( ( { props }, i ) => <HexFlip key={ i } { ...props } /> ) }</HexGrid>;
}
