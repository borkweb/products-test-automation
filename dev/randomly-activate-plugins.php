#!/usr/bin/env php
<?php
/**
 * CI script to randomly activate plugins previously downloaded using the `dev/setup/dl-plugins-zip.php` script.
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


$docker_compose = docker_compose( [ '-f', 'dev/test/activation-stack.yml' ] );
$cli            = docker_compose( [ '-f', 'dev/test/activation-stack.yml', 'run', '--rm', 'cli', '--allow-root' ] );
$waiter         = docker_compose( [ '-f', 'dev/test/activation-stack.yml', 'run', '--rm', 'waiter' ] );

// Start the WordPress container.
check_status_or_exit( $docker_compose( [ 'up', '-d', 'wordpress' ] ) );

// Wait for WordPress container to come up.
check_status_or_wait( $waiter() );

// Install WordPress when it's up and running.
check_status_or_exit( $cli( [
	'core',
	'install',
	'--url=http://tribe.localhost',
	'--title="Activation Tests"',
	'--admin_user=admin',
	'--admin_password=admin',
	'--admin_email=admin@tribe.localhost',
	'--skip-email',
] ) );

// Force the installation of a random WordPress version.
check_status_or_exit( $cli ( [
	'core',
	'update',
	'--version=' . random_wordpress_version( $args( 'wp_number_versions', 5 ) ),
	'--force',
] ) );

// Spin the wheel.
randomly_activate_plugins( (int) $args( 'epochs', 3 ) );
