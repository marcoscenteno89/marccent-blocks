// next/image outside Next: a plain lazy <img>. fill stretches it over its (positioned) parent, like Next's.
const Image = ( { src, alt = '', fill, priority, quality, placeholder, blurDataURL, loader, unoptimized, style, ...rest } ) => (
	<img
		src={ typeof src === 'object' ? src.src : src }
		alt={ alt }
		loading={ priority ? 'eager' : 'lazy' }
		decoding="async"
		style={ fill ? { position: 'absolute', inset: 0, width: '100%', height: '100%', objectFit: 'cover', ...style } : style }
		{ ...rest }
	/>
);

export default Image;
