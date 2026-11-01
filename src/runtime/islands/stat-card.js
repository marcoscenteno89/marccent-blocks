// A marccent/stat-card block: the theme's StatCard, its blocks (buttons) as its actions.
import { StatCard } from 'marccent-theme/components/widgets/layout';
import { HtmlList, blocksOf } from '../../shared/html';

export default function StatCardIsland( { html = [], ...props } ) {
	return <StatCard { ...props }>{ blocksOf( html ).length > 0 && <HtmlList html={ html } /> }</StatCard>;
}
