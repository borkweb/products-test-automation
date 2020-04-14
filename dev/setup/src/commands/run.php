<?php

namespace Tribe\Test;

if ( $is_help ) {
	echo colorize( "Runs a Codeception test in the stack, the equivalent of <light_cyan>'codecept run ...'</light_cyan>.\n" );
	echo PHP_EOL;
	echo colorize( "This command requires a  set using the <light_cyan>use</light_cyan> command.\n" );
	echo colorize( "usage: <light_cyan>${argv[0]} run [...<commands>]</light_cyan>\n" );
	echo colorize( "example: <light_cyan>${argv[0]} run wpunit</light_cyan>" );
	return;
}

$using = tric_target();
echo light_cyan( "Using {$using}\n" );

setup_id();

// Run the command in the Codeception container, exit the same status as the process.
$status = tric_realtime()( array_merge( [ 'run', '--rm', 'codeception', 'run' ], $args( '...' ) ) );
