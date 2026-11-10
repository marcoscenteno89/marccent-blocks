<?php
/**
 * Links (inc/markup.php marccent_blocks_href): a link that is only a {{ token }} stays one, for Marccent Content to
 * fill; anything else is escaped as a URL. The markup itself is checked by the theme's conformance check (npm run
 * test:markup), against its reference.
 *
 *   php tools/href-test.php   (npm run test:href)
 */

require __DIR__ . '/markup-render.php';

// A link that is only a {{ token }} stays one, for Marccent Content to fill; anything else is escaped as a URL.
foreach ( array( '{{link}}' => '{{link}}', '%7B%7B link %7D%7D' => '%7B%7B link %7D%7D', 'javascript:{{link}}' => '', '{{a}}{{b}}x' => '' ) as $in => $want ) {
	$got = marccent_blocks_href( $in );
	$ok  = $got === $want;
	$failed = ( $failed ?? 0 ) + ( $ok ? 0 : 1 );
	printf( "%s href: %s\n", $ok ? 'ok  ' : 'FAIL', $in );
}
exit( $failed ? 1 : 0 );
