// A marccent/compare block: the theme's Compare, its two pictures in a box of the block's shape.
import { Compare } from '@marccent/theme/components/ui/compare';

export default function CompareIsland( { before, after, beforeLabel, afterLabel, start = 50, label, ratio = '16 / 9' } ) {
	const img = ( src, alt ) => <img src={ src } alt={ alt } />;
	return <Compare before={ img( before, beforeLabel ) } after={ img( after, afterLabel ) } beforeLabel={ beforeLabel } afterLabel={ afterLabel } start={ start } label={ label } style={ { aspectRatio: ratio } } />;
}
