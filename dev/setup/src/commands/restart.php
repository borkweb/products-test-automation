<?php

namespace Tribe\Test;

if ( $is_help ) {
	echo "Restarts a container part of the stack.\n";
	echo PHP_EOL;
	echo colorize( "usage: <light_cyan>{$cli_name} restart [...<service>]</light_cyan>\n" );
	echo colorize( "example: <light_cyan>{$cli_name} restart</light_cyan>" );
	echo colorize( "example: <light_cyan>{$cli_name} restart wordpress</light_cyan>" );
	return;
}

setup_id();
$service = args( [ 'service' ], $args( '...' ), 0 )( 'service', 'wordpress' );
restart_service( $service );
