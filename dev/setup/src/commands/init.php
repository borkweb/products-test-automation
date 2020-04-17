<?php
/**
 * Handles the `init` command to initialize a plugin to use tric.
 *
 * @var bool     $is_help Whether we're handling an `help` request on this command or not.
 * @var \Closure $args    The argument map closure, as produced by the `args` function.
 */

namespace Tribe\Test;

if ( $is_help ) {
	echo "Initializes a plugin for use in tric.\n";
	echo PHP_EOL;
	echo colorize( "signature: <light_cyan>${argv[0]} init <plugin></light_cyan>\n" );
	echo colorize( "example: <light_cyan>${argv[0]} init the-events-calendar</light_cyan>\n" );
	return;
}

$sub_args = args( [ 'plugin' ], $args( '...' ), 0 );
$plugin   = $sub_args( 'plugin', false );

// If a plugin isn't passed as an argument, the target is the current plugin being used.
if ( empty( $plugin ) ) {
	$plugin = tric_target();
	echo light_cyan( "Using {$plugin}\n" );
}

clone_plugin( $plugin );
setup_plugin_tests( $plugin );

echo light_cyan( "Finished initializing {$plugin}\n" );
