// A marccent/date block: the theme's DateTime, set in the visitor's browser (so a relative date stays current).
import { DateTime } from 'marccent-theme/components/ui/date-time';

export default function DateIsland( { value, end, show = 'date', locale = 'en-US', timeZone = 'UTC' } ) {
	return value ? <DateTime value={ value } end={ end || undefined } show={ show } locale={ locale } timeZone={ timeZone } /> : null;
}
