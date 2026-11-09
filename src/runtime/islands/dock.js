// A marccent/dock block: the theme's Dock.
import { Dock } from 'marccent/components/widgets/nav';

export default function DockIsland( { items = [], label } ) {
	return <Dock items={ items } label={ label || undefined } />;
}
