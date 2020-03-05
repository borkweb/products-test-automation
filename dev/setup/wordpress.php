<?php
/**
 * WordPress related functions.
 */

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
