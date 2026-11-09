/*
 * A marccent/tabs block: the theme's TabBar, each tab showing its blocks (the [data-tab-content] of the tab's HTML in
 * the slot; its heading is for the page without JavaScript).
 */
import { TabBar } from 'marccent/components/widgets/nav';
import { Html } from '../../shared/html';

const contentOf = ( html ) => new DOMParser().parseFromString( html, 'text/html' ).querySelector( '[data-tab-content]' )?.innerHTML ?? html;

export default function TabsIsland( { tabs = [], html = [] } ) {
	return <TabBar tabs={ tabs.map( ( t, i ) => ( { label: t.label, icon: t.icon || undefined, content: <div className="grid cols-1 gap-sm"><Html html={ contentOf( html[ i ] ?? '' ) } /></div> } ) ) } />;
}
