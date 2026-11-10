// A marccent/hex-wall block: a team wall, the theme's HexGrid with a HexFlip for each person (HexWall is gone from the
// theme: the grid with its children is the wall). Retires for a Loop of HexFlip in a HexGrid (the 1.0 checklist).
import { Icon } from 'marccent-theme/components/ui/icon';
import { HexGrid, HexFlip } from 'marccent-theme/components/widgets/layout';

const FACES = [ 'bg-gradient', 'bg-primary', 'bg-secondary', 'bg-gradient-darker' ];

export default function HexWallIsland( { people = [], perRow = 4 } ) {
	return (
		<HexGrid data-name="hex-wall" perRow={ perRow }>
			{ people.map( ( p, i ) => (
				<HexFlip key={ p.name } label={ `${ p.name }${ p.role ? `, ${ p.role }` : '' }` } frontClass={ FACES[ i % 4 ] }
					front={ p.icon ? <Icon name={ p.icon } className="fs-lg" /> : <strong className="fs-lg">{ p.initials }</strong> }
					back={ <><strong>{ p.name }</strong>{ p.role && <small>{ p.role }</small> }</> } />
			) ) }
		</HexGrid>
	);
}
