// A marccent/atom block: the theme's Atom.
import { Atom } from 'marccent-theme/components/visuals/effects';

export default function AtomIsland( { speed, label } ) {
	return <Atom speed={ speed || undefined } label={ label || undefined } />;
}
