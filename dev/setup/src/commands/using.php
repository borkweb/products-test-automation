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
	echo colorize( "signature: <light_cyan>{$cli_name} using [<path>]</light_cyan>\n" );
	return;
}

$using = tric_target();
$target_path = tric_plugins_dir( $using );
if ( empty( $using ) ) {
	echo magenta( "Currently not using any target, commands requiring a target will fail.\n" );
	return;
}

echo light_cyan( "Using {$using}\n" );

if ( tric_plugins_dir() !== dev( 'plugins' ) ) {
	echo light_cyan( "\nFull target path: " ) . $target_path;
}

if ( $target_path === getcwd() ) {
	echo light_cyan( "\nThis directory is the current use target." );
} else {
	echo yellow( "\nThis directory is not the current use target." );
}
