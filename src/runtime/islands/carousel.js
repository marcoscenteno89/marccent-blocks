/*
 * A marccent/carousel block on the page: the theme's Carousel (Swiper, with its pager) around the slides the block
 * saved. Each slide is the saved HTML of one inner block, so anything can be a slide (an image, a card…).
 */
import { Carousel } from 'marccent-theme/components/widgets/carousel';

export default function CarouselIsland( { slideWidth, label, html = [] } ) {
	return (
		<Carousel slideWidth={ slideWidth } label={ label }>
			{ html.map( ( h, i ) => <div key={ i } className="h-100" dangerouslySetInnerHTML={ { __html: h } } /> ) }
		</Carousel>
	);
}
