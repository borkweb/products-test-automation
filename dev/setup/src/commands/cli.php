<?php

namespace Tribe\Test;

if ( $is_help ) {
	echo "Runs a wp-cli command in the stack.\n";
	echo PHP_EOL;
	echo colorize( "signature: <light_cyan>${argv[0]} cli [...<commands>]</light_cyan>\n" );
	echo colorize( "example: <light_cyan>${argv[0]} cli plugin list --status=active</light_cyan>" );
	return;
}

setup_id();
// Runs a wp-cli command in the stack, using the `cli` service.
$composer_command = $args( '...' );
tric_realtime()( array_merge( [ 'run', '--rm', 'cli', 'wp' ], $composer_command ) );
