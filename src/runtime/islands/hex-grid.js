// A marccent/hex-grid block: the theme's HexGrid of its Hex flips, each one's blocks its front.
import { HexGrid, HexFlip } from 'marccent-theme/components/widgets/layout';
import { Html } from '../../shared/html';

export default function HexGridIsland( { perRow, childBlocks = [] } ) {
	return (
		<HexGrid perRow={ perRow || undefined }>
			{ childBlocks.map( ( { props: { back, frontClass, backClass, label }, html }, i ) => (
				<HexFlip key={ i } front={ <Html html={ html } /> } back={ back } frontClass={ frontClass || undefined } backClass={ backClass || undefined } label={ label || undefined } />
			) ) }
		</HexGrid>
	);
}
