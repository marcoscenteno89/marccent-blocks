// A marccent/step-bar block: the theme's StepBar.
import { StepBar } from 'marccent/components/widgets/nav';

export default function StepBarIsland( { steps = [], start = 0 } ) {
	return steps.length ? <StepBar steps={ steps } start={ start } /> : null;
}
