<?php
/**
 * inc/upgrade.php without WordPress: a block saved before the settings (no version) or at an older version renders
 * with today's names and its old defaults, however it's reached, a list it kept as its child blocks; one saved today
 * as it is.
 *
 *   php tools/upgrade-test.php
 */

const MARCCENT_BLOCKS_DIR = __DIR__ . '/..';
$filters = array();
function add_filter( $hook, $fn ) {
	$GLOBALS['filters'][ $hook ] = $fn;
}
require __DIR__ . '/../inc/upgrade.php';

// Stand-ins: a block type that fills defaults as WordPress does, and a block carrying what was saved.
$type = new class() {
	public function prepare_attributes_for_render( array $a ): array {
		return $a + array( 'padding' => 'pad', 'hover' => '' );
	}
};
$block = fn( array $saved ) => (object) array( 'parsed_block' => array( 'attrs' => $saved ), 'block_type' => $type );
$args  = $filters['register_block_type_args']( array( 'render_callback' => fn( $a ) => $a ), 'marccent/card' );
// A parent saved with its list (before T-034): its attributes, and the rows that become its child blocks.
$with_children = function ( string $name, array $saved ): array {
	$children = null;
	return array( 'attributes' => marccent_blocks_upgrade( $name, $saved, $children ), 'children' => $children );
};
$cases = array(
	'old card'        => array( $args['render_callback']( array(), '', $block( array( 'pad' => 'pad-lg' ) ) ), array( 'padding' => 'pad-lg', 'hover' => 'hover-holographic', 'rows' => 0, 'version' => 1 ) ),
	'card saved now'  => array( $args['render_callback']( array( 'padding' => 'pad', 'version' => 1 ), '', $block( array( 'version' => 1 ) ) ), array( 'padding' => 'pad', 'version' => 1 ) ),
	'old section'     => array( marccent_blocks_upgrade( 'marccent/section', array( 'padY' => 'pad-y-sm', 'tagName' => 'div' ) ), array( 'as' => 'div', 'verticalPadding' => 'pad-y-sm', 'version' => 2 ) ),
	'section at 1'    => array( marccent_blocks_upgrade( 'marccent/section', array( 'minHeight' => '60vh', 'version' => 1 ) ), array( 'minimumHeight' => '60vh', 'version' => 2 ) ),
	'animation at 1'  => array( marccent_blocks_upgrade( 'marccent/animation', array( 'fps' => 24, 'minHeight' => '40vh', 'version' => 1 ) ), array( 'frameRate' => 24, 'minimumHeight' => '40vh', 'version' => 2 ) ),
	'timeline events' => array( $with_children( 'marccent/timeline', array( 'events' => array( array( 'date' => '2020', 'title' => 'Begun' ), 'not a row' ), 'version' => 1 ) ), array( 'attributes' => array( 'version' => 2 ), 'children' => array( 'block' => 'marccent/timeline-item', 'rows' => array( array( 'date' => '2020', 'title' => 'Begun' ) ) ) ) ),
	'dock as it was'  => array( count( $with_children( 'marccent/dock', array( 'version' => 1 ) )['children']['rows'] ), 3 ),
	'timeline now'    => array( $with_children( 'marccent/timeline', array( 'version' => 2 ) ), array( 'attributes' => array( 'version' => 2 ), 'children' => null ) ),
	'not ours'        => array( $filters['register_block_type_args']( array( 'render_callback' => 'x' ), 'core/paragraph' ), array( 'render_callback' => 'x' ) ),
);
$failed = 0;
foreach ( $cases as $name => [ $got, $want ] ) {
	if ( is_array( $got ) && is_array( $want ) ) {
		ksort( $got );
		ksort( $want );
	}
	$ok      = $got === $want;
	$failed += $ok ? 0 : 1;
	echo ( $ok ? 'ok   ' : 'FAIL ' ) . "upgrade: $name" . ( $ok ? '' : ': ' . json_encode( $got ) . ' != ' . json_encode( $want ) ) . "\n";
}
exit( $failed ? 1 : 0 );
