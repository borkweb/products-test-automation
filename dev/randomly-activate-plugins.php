#!/usr/bin/env php
<?php
/**
 * CI script to randomly activate plugins previously downloaded using the `dev/setup/download-plugins-zip.php` script.
 */

require_once __DIR__ . '/setup/docker.php';
require_once __DIR__ . '/setup/wordpress.php';
require_once __DIR__ . '/test/tests.php';

if ( 0 === $argc ) {
	echo "\033[1mRandomly activate plugins from the plugin store a number of times (epochs).\033[0m\n";
	echo "\n";
	echo "\033[1mUsage:\033[0m\n";
	echo "\n";
	echo "\tphp randomly-activate-plugins.php <epochs> [<wp_number_versions>]\n";
	echo "\n";
	echo "\t\033[32m<epochs>\033[0m - an integer defining how many times to run the random activation test.\n";
	exit( 0 );
}

$args = args( [
	'epochs',
	'wp_number_versions'
] );

prepare_wordpress( random_wordpress_version( $args( 'wp_number_versions', 5 ) ) );

// Spin the wheel.
randomly_activate_plugins( (int) $args( 'epochs', 3 ) );
