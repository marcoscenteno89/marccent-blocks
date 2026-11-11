// A marccent/dock block: the theme's Dock of its Dock items (each with its icon and name).
import { Dock, DockItem } from 'marccent-theme/components/widgets/nav';

export default function DockIsland( { childBlocks = [], label } ) {
	return (
		<Dock label={ label || undefined }>
			{ childBlocks.filter( ( { props } ) => props.icon && props.label ).map( ( { props }, i ) => <DockItem key={ i } { ...props } /> ) }
		</Dock>
	);
}
