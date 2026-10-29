// Runs serialize.test.js (npm run serialize: blocks to post content with the blocks' own save()) or deprecated.test.js
// (npm run test:blocks: posts saved by older versions still open as valid blocks), in jsdom.
const path = require( 'path' );
const plugin = path.resolve( __dirname, '..' );

module.exports = {
	rootDir: plugin,
	testMatch: [ `<rootDir>/tools/${ process.env.MARCCENT_TEST || 'serialize' }.test.js` ],
	testEnvironment: 'jsdom',
	// CommonJS builds of the dependencies (jsdom's default, the browser condition, picks ES module ones).
	testEnvironmentOptions: { customExportConditions: [ 'node', 'require', 'default' ] },
	transform: { '\\.m?[jt]sx?$': [ 'babel-jest', { presets: [ require.resolve( '@wordpress/babel-preset-default' ) ] } ] },
	// Everything is compiled: marccent-theme ships JSX, and some dependencies ship only ES modules (uuid).
	transformIgnorePatterns: [],
	moduleNameMapper: {
		'\\.css$': '<rootDir>/tools/style-stub.js',
	},
	setupFiles: [ '<rootDir>/tools/setup.js' ],
};
