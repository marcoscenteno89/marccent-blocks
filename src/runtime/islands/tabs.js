/*
 * A marccent/tabs block: the theme's TabBar of its tabs, each one's label and icon on the tab and its blocks the panel.
 */
import { TabBar, Tab } from 'marccent-theme/components/widgets/nav';
import { Html } from '../../shared/html';

export default function TabsIsland( { childBlocks = [] } ) {
	return (
		<TabBar>
			{ childBlocks.map( ( { props: { label = '', icon }, html }, i ) => (
				<Tab key={ i } label={ label } icon={ icon || undefined }><div className="grid cols-1 gap-sm"><Html html={ html } /></div></Tab>
			) ) }
		</TabBar>
	);
}
