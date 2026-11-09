import { Timeline } from 'marccent/components/widgets/layout';

export default function TimelineIsland( { events = [] } ) {
	return <Timeline events={ events } />;
}
