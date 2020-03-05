#!/usr/bin/env php
<?php
require_once __DIR__ . '/setup/docker.php';
require_once  __DIR__ . '/setup/wordpress.php';
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

putenv( 'WORDPRESS_VERSION=' . random_wordpress_version( $args( 'wp_number_versions', 5 ) ) );

check_status_or_exit( $docker_compose( [ 'up', '-d', 'wordpress' ] ) );

check_status_or_wait( $cli( [ 'db', 'check' ] ), 60 );

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

randomly_activate_plugins( (int) $args( 'epochs', 3 ) );
