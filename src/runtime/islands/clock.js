// A marccent/clock block: the theme's Clock.
import { Clock } from 'marccent-theme/components/ui/clock';

export default function ClockIsland( { face, format } ) {
	return <Clock face={ face || undefined } format={ format || undefined } />;
}
