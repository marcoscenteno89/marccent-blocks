// A marccent/ticket-card block: the theme's TicketCard, its blocks (a button) as the action.
import { TicketCard } from 'marccent-theme/components/widgets/layout';
import { HtmlList, blocksOf } from '../../shared/html';

export default function TicketCardIsland( { html = [], ...props } ) {
	return <TicketCard { ...props } ribbon={ props.ribbon || undefined } action={ blocksOf( html ).length > 0 && <div className="row-wrap gap-sm justify-center"><HtmlList html={ html } /></div> } />;
}
