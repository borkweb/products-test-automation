<?php

namespace Tribe\Test;

if ( $is_help ) {
	echo "Returns the current 'use' target.\n";
	echo PHP_EOL;
	echo colorize( "signature: <light_cyan>${argv[0]} using <target>[/<subdir>]</light_cyan>\n" );
	return;
}

$using = tric_target();
if ( empty( $using ) ) {
	echo magenta( "Currently not using any target, commands requiring a target will fail.\n" );
	return;
}
echo light_cyan( "Using {$using}\n" );
