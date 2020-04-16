<?php

namespace Tribe\Test;

if ( $is_help ) {
	echo "Displays the stack logs.\n";
	echo PHP_EOL;
	echo colorize( "usage: <light_cyan>${argv[0]} logs</light_cyan>" );
	return;
}

tric_realtime()( [ 'logs', '--follow' ] );
