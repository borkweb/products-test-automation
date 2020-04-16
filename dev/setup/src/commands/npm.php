<?php

namespace Tribe\Test;

if ( $is_help ) {
	echo "Runs an npm command in the stack.\n";
	echo PHP_EOL;
	echo colorize( "This command requires a use target set using the <light_cyan>use</light_cyan> command.\n" );
	echo colorize( "usage: <light_cyan>${argv[0]} npm [...<commands>]</light_cyan>\n" );
	echo colorize( "example: <light_cyan>${argv[0]} npm install</light_cyan>" );
	return;
}

$using = tric_target();
echo light_cyan( "Using {$using}\n" );

setup_id();
$npm_command   = $args( '...' );
tric_realtime()( array_merge( [ 'run', '--rm', 'npm' ], $npm_command ) );

if ( ! file_exists( tric_plugins_dir( "{$using}/common" ) ) ) {
	return;
}

if ( ask( "\nWould you like to run that npm command against common?", 'yes' ) ) {
	tric_switch_target( "{$using}/common" );

	echo light_cyan( "Temporarily using " . tric_target() . "\n" );

	tric_realtime()( array_merge( [ 'run', '--rm', 'npm' ], $npm_command ) );

	tric_switch_target( $using );

	echo light_cyan( "Using " . tric_target() ." once again\n" );
}