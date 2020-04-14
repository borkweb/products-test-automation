<?php

namespace Tribe\Test;

if ( $is_help ) {
	echo "Starts a container part of the stack.\n";
	echo PHP_EOL;
	echo colorize( "signature: <light_cyan>${argv[0]} up <service></service></light_cyan>\n" );
	echo colorize( "example: <light_cyan>${argv[0]} up adminer</light_cyan>" );
	return;
}

$service       = args( [ 'service' ], $args( '...' ), 0 )( 'service', 'wordpress' );
tric_realtime()( [ 'up', '-d', $service ] );
