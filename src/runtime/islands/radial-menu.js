// A marccent/radial-menu block: the theme's RadialMenu.
import { RadialMenu } from 'marccent-theme/components/widgets/nav';

export default function RadialMenuIsland( { items = [], radius, label } ) {
	return <RadialMenu items={ items } radius={ radius || undefined } label={ label || undefined } />;
}
