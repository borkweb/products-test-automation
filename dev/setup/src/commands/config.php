<?php

namespace Tribe\Test;

if ( $is_help ) {
	echo "Prints the stack configuration as interpolated from the environment.\n";
	echo PHP_EOL;
	echo colorize( "usage: <light_cyan>${argv[0]} config</light_cyan>" );
	return;
}

$using = tric_target();
setup_id();
tric_realtime()( [ 'config' ] );
