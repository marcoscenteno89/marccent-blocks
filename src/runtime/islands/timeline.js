// A marccent/timeline block: the theme's Timeline of its Timeline items.
import { Timeline, TimelineItem } from 'marccent-theme/components/widgets/layout';

export default function TimelineIsland( { childBlocks = [] } ) {
	return <Timeline>{ childBlocks.map( ( { props }, i ) => <TimelineItem key={ i } { ...props } /> ) }</Timeline>;
}
