#!/usr/bin/env php
<?php
/**
 * CI script to download the latest nightly builds of the plugins for the specified branch.
 */

namespace Tribe\Test;

require_once __DIR__ . '/src/utils.php';
require_once __DIR__ . '/src/plugins.php';

if ( 1 === $argc ) {
	echo "\033[1mDownload the latest nightly builds of the plugins for the specified branch.\033[0m\n";
	echo "\n";
	echo "\033[1mUsage:\033[0m\n";
	echo "\n";
	echo "\tphp download-plugin-nightly-zips.php <branch> [[<licenses_file>]\n";
	echo "\n";
	echo "\t\033[32m<branch>\033[0m - the branch to download the nightly builds for, e.g. 'release/B20.01'.\n";
	echo "\t\033[32m[<licenses_file>]\033[0m - an optional .env format file that will provide the licenses for each plugin to download, e.g. '.env.licenses'.\n</licenses_file>";
	exit( 0 );
}

$args = args( [
	'branch',
	'licenses_file'
] );

// Either read from the environment, in CI, or read it from the user input.
parse_license_file( $args( 'licenses_file' ) );

download_plugin_versions( plugin_nightly_builds( $args ) );
