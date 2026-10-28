// css-loader resolves the theme's @imports itself (each file's font urls stay relative to that file), so no
// postcss-import here: inlining them first, as @wordpress/scripts' default config does, breaks those urls. The
// production build is minified (cssnano), comments and all.
module.exports = ( { mode, env } ) => ( {
	plugins: [ require( 'autoprefixer' ), ...( ( mode ?? env ) === 'production' ? [ require( 'cssnano' )( { preset: 'default' } ) ] : [] ) ],
} );
