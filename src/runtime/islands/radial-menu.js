// A marccent/radial-menu block: the theme's RadialMenu of its Radial menu items (each with its icon and name).
import { RadialMenu, RadialMenuItem } from 'marccent-theme/components/widgets/nav';

export default function RadialMenuIsland( { childBlocks = [], radius, label } ) {
	return (
		<RadialMenu radius={ radius || undefined } label={ label || undefined }>
			{ childBlocks.filter( ( { props } ) => props.icon && props.label ).map( ( { props }, i ) => <RadialMenuItem key={ i } { ...props } /> ) }
		</RadialMenu>
	);
}
