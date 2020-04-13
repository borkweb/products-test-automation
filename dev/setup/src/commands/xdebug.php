<?php

namespace Tribe\Test;

if ( $is_help ) {
	echo "Activates and deactivated XDebug in the stack, returns the current XDebug status or sets its values.\n";
	echo colorize( "Any change ot XDebug settings will require tearing down the stack with <light_cyan>down</light_cyan> and restarting it!\n" );
	echo PHP_EOL;
	echo colorize( "signature: <light_cyan>${argv[0]} xdebug (on|off|status|port|host|key) [<value>]</light_cyan>\n" );
	echo colorize( "example: <light_cyan>${argv[0]} xdebug on</light_cyan>\n" );
	echo colorize( "example: <light_cyan>${argv[0]} xdebug status</light_cyan>\n" );
	echo colorize( "example: <light_cyan>${argv[0]} xdebug host 192.168.1.2</light_cyan>\n" );
	echo colorize( "example: <light_cyan>${argv[0]} xdebug port 9009</light_cyan>" );
	return;
}

$xdebug_args = args( [ 'toggle', 'value' ], $args( '...' ), 0 );

tric_handle_xdebug( $xdebug_args );
