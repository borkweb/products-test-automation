#!/usr/bin/env php
<?php

require_once __DIR__ . '/src/utils.php';
require_once __DIR__ . '/src/plugins.php';

if ( 1 === $argc ) {
	echo "\033[1mDownload .org and premium plugins in the last n versions.\033[0m\n";
	echo "\n";
	echo "\033[1mUsage:\033[0m\n";
	echo "\n";
	echo "\tphp dl-plugin-zips.php <number_versions> [<plugins>] [<licenses_file>]\n";
	echo "\n";
	echo "\t\033[32m<number_versions>\033[0m - an integer defining how many versions to download, e.g. 3.\n";
	echo "\t\033[32m[<plugins>]\033[0m - a comma-separated list of plugins to download, e.g. 'the-events-calendar,events-pro' or 'all'.\n";
	echo "\t\033[32m[<licenses_file>]\033[0m - an optional .env format file that will provide the licenses for each plugin to download, e.g. '.env.licenses'.\n";
	exit( 0 );
}

$args = args( [
	'number_versions',
	'plugins',
	'licenses_file'
] );

parse_license_file( $args( 'licenses_file' ) );

download_plugin_versions( plugin_versions( $args ) );
