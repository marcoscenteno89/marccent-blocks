// A marccent/step-bar block: the theme's StepBar of its Step bar steps, the current one within them.
import { StepBar, StepBarStep } from 'marccent-theme/components/widgets/nav';

export default function StepBarIsland( { childBlocks = [], start = 0 } ) {
	const steps = childBlocks.filter( ( { props } ) => props.label );
	return steps.length ? (
		<StepBar start={ Math.min( start, steps.length - 1 ) }>
			{ steps.map( ( { props }, i ) => <StepBarStep key={ i } { ...props } /> ) }
		</StepBar>
	) : null;
}
