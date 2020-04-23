<?php
/**
 * Handles the `using` command.
 *
 * @var bool     $is_help Whether we're handling an `help` request on this command or not.
 * @var \Closure $args    The argument map closure, as produced by the `args` function.
 */

namespace Tribe\Test;

if ( $is_help ) {
	echo "Returns the current 'use' target.\n";
	echo PHP_EOL;
	echo colorize( "signature: <light_cyan>${argv[0]} using <target>[/<subdir>]</light_cyan>\n" );
	return;
}

$path         = args( [ 'path' ], $args( '...' ), 0 )( 'path', false );
$current_path = tric_plugins_dir( tric_target() );

if ( false !== $path ) {
	// Are we using the path specified?
	if ( $path === $current_path ) {
		echo colorize( "<light_cyan>Yes, using:</light_cyan> {$path}" );
	} else {
		echo colorize( "<magenta>No, using:</magenta> {$current_path}" );
	}

	return;
}

$using = tric_target();
if ( empty( $using ) ) {
	echo magenta( "Currently not using any target, commands requiring a target will fail.\n" );
	return;
}

echo light_cyan( "Using {$using}\n" );

if ( tric_plugins_dir() !== dev( 'plugins' ) ) {
	echo light_cyan( "\nFull target path: " ) . $current_path;
}
