// A marccent/clock block: the theme's Clock.
import { Clock } from 'marccent-theme/components/ui/clock';

export default function ClockIsland( { format } ) {
	return <Clock format={ format || undefined } />;
}
