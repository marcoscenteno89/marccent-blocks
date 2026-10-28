// next/navigation outside Next: the page's own location; navigating is a full page load.
const usePathname = () => ( typeof location === 'undefined' ? '/' : location.pathname );
const useSearchParams = () => new URLSearchParams( typeof location === 'undefined' ? '' : location.search );
const useRouter = () => ( {
	push: ( url ) => location.assign( url ),
	replace: ( url ) => location.replace( url ),
	back: () => history.back(),
	refresh: () => location.reload(),
	prefetch: () => {},
} );

export { usePathname, useSearchParams, useRouter };
