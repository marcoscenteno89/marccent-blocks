// Runs serialize.test.js: blocks to post content with the blocks' own save(), in jsdom (npm run serialize).
const path = require( 'path' );
const plugin = path.resolve( __dirname, '..' );

module.exports = {
	rootDir: plugin,
	testMatch: [ '<rootDir>/tools/serialize.test.js' ],
	testEnvironment: 'jsdom',
	// CommonJS builds of the dependencies (jsdom's default, the browser condition, picks ES module ones).
	testEnvironmentOptions: { customExportConditions: [ 'node', 'require', 'default' ] },
	transform: { '\\.m?[jt]sx?$': [ 'babel-jest', { presets: [ require.resolve( '@wordpress/babel-preset-default' ) ] } ] },
	// Everything is compiled: marccent-theme ships JSX, and some dependencies ship only ES modules (uuid).
	transformIgnorePatterns: [],
	moduleNameMapper: {
		'\\.css$': '<rootDir>/tools/style-stub.js',
		'^next/link$': '<rootDir>/src/shims/next-link.js',
		'^next/navigation$': '<rootDir>/src/shims/next-navigation.js',
		'^next/image$': '<rootDir>/src/shims/next-image.js',
	},
	setupFiles: [ '<rootDir>/tools/setup.js' ],
};
