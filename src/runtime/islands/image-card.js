// A marccent/image-card block: the theme's ImageCard, its blocks (buttons) in front of the photo.
import { ImageCard } from 'marccent/components/widgets/layout';
import { HtmlList, blocksOf } from '../../shared/html';

export default function ImageCardIsland( { html = [], ...props } ) {
	return <ImageCard { ...props }>{ blocksOf( html ).length > 0 && <HtmlList html={ html } /> }</ImageCard>;
}
