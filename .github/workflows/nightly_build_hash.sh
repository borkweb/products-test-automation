#!/usr/bin/env php
<?php
/**
 * Fires a request to the nightly build service to get the last build timestamp.
 */

require_once __DIR__ . '/../../dev/setup/src/utils.php';
require_once __DIR__ . '/../../dev/setup/src/nightly.php';

if ( 0 === $argc ) {
	echo "\033[1mGet the last nightly build timestamp for a branch.\033[0m\n";
	echo "\n";
	echo "\033[1mUsage:\033[0m\n";
	echo "\n";
	echo "\tphp night_build_hash.php <branch> [<licenses_file>]\n";
	echo "\n";
	echo "\t\033[32m<branch>\033[0m - The branch to get the nightly build timestamp for.\n";
	echo "\t\033[32m[<licenses_file>]\033[0m - an optional .env format file that will provide the licenses for each plugin to download, e.g. '.env.licenses'.\n</licenses_file>";
	exit( 0 );
}

$args = args( [
	'branch',
	'licenses_file'
] );

echo nightly_build_timestamp( $args( 'branch' ), $args( 'licenses_file' ) );
