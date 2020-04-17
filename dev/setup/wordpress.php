<?php
/**
 * WordPress related functions.
 */

namespace Tribe\Test;

require_once __DIR__ . '/src/utils.php';

/**
 * Returns a WordPress version, picked at random among the last n.
 *
 * @param int $number_versions How many WordPress versions to choose between.
 *                             The "nightly" version is always added.
 *
 * @return string The randomly picked WordPress version.
 */
function random_wordpress_version( $number_versions = 3 ) {
	$versions = wordpress_fetch_versions();

	// Most recent first.
	$all_versions = array_column( $versions, 'version' );
	// First is auto-update, remove.
	$all_versions = array_slice($all_versions,1);
	// Always add "nightly" on top.
	array_unshift( $all_versions, 'nightly' );

	return $versions[ array_rand( array_slice( $all_versions, 0, $number_versions ), 1 ) ]['version'];
}

/**
 * Fetches the last WordPress versions from the WordPress API.
 *
 * @return array<array> An array of WordPress versions.
 */
function wordpress_fetch_versions() {
	echo "\nFetching WordPress version information...";

	$api      = 'https://api.wordpress.org/core/version-check/1.7/';
	$versions = curl_get( $api );

	$decoded = json_decode( $versions, true );

	if ( false === $decoded ) {
		echo "\nCould not fetch WordPress version information.";
		exit( 1 );
	}

	if ( ! isset( $decoded['offers'] ) ) {
		echo "\nWordPress version information is malformed.";
		exit( 1 );
	}

	$offers = $decoded['offers'];

	return $offers;
}

/**
 * Prepares the WordPress installation installing a specific version of WordPress.
 *
 * The command will use the debug version of the WordPress service when not running in CI context.
 *
 * @param string|null The version of WordPress to install, e.g. `5.3.2` or `nightly`.
 */
function prepare_wordpress( $wordpress_version  = 'nightly' ) {
	$stack          = stack();
	$docker_compose = docker_compose( [ '-f', '"' . $stack . '"' ] );
	$cli            = docker_compose( [ '-f', '"' . $stack . '"', 'run', '--rm', 'cli', '--allow-root' ] );
	$waiter         = docker_compose( [ '-f', '"' . $stack . '"', 'run', '--rm', 'waiter' ] );
	$service        = is_ci() ? 'wordpress' : 'wordpress_debug';

	// Start the WordPress container.
	check_status_or_exit( $docker_compose( [ 'up', '-d', $service ] ) );

	// Wait for WordPress container to come up.
	check_status_or_wait( $waiter() );
	// The db is available, but it will lag a bit behind, let's give it some time.
	sleep( 3 );

	// Install WordPress when it's up and running.
	$url = wordpress_url();
	check_status_or_exit( $cli( [
		'core',
		'install',
		'--url=' . escapeshellarg( $url ),
		'--title="Activation Tests"',
		'--admin_user=admin',
		'--admin_password=admin',
		'--admin_email=admin@tribe.test',
		'--skip-email',
	] ) );

	// Force the installation of a random WordPress version.
	check_status_or_exit( $cli ( [
		'core',
		'update',
		'--version=' . $wordpress_version,
		'--force',
	] ) );

	if ( ! is_ci() ) {
		// Set the site URL explicitly in the configuration file to avoid URL mangling.
		check_status_or_exit( $cli( [ 'config', 'set', 'WP_SITEURL', escapeshellarg( $url ) ] ) );
		check_status_or_exit( $cli( [ 'config', 'set', 'WP_HOME', escapeshellarg( $url ) ] ) );
		echo "\033[32mWordPress installation available at ${url}\033[0m";
	}
}
