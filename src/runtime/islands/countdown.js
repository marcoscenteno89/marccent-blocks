// A marccent/countdown block: the theme's Countdown.
import { Countdown } from 'marccent/components/visuals/effects';

export default function CountdownIsland( { from = 5 } ) {
	return <Countdown from={ from } />;
}
