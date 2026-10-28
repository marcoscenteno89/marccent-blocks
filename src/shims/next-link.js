// next/link outside Next: a plain <a>. Next-only props (prefetch, replace, scroll…) are dropped.
import { forwardRef } from 'react';

const Link = forwardRef( ( { href, prefetch, replace, scroll, shallow, locale, legacyBehavior, passHref, ...rest }, ref ) => (
	<a ref={ ref } href={ typeof href === 'object' ? href.pathname : href } { ...rest } />
) );

export default Link;
