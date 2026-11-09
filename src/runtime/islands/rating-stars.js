// A marccent/rating-stars block: the theme's Stars.
import { Stars } from 'marccent/components/ui/rating';

export default function RatingStarsIsland( { value = 0, max = 5, icon = 'star' } ) {
	return <Stars value={ value } max={ max } icon={ icon } />;
}
