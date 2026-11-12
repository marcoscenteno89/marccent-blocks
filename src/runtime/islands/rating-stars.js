// A marccent/rating-stars block: the theme's Stars.
import { Stars } from 'marccent-theme/components/ui/rating';

export default function RatingStarsIsland( { value = 0, maximum = 5, icon = 'star' } ) {
	return <Stars value={ value } maximum={ maximum } icon={ icon } />;
}
