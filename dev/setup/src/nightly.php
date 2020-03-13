<?php
/**
 * Utilities and functions to interact w/ the nightly build service.
 */

/**
 * Returns the base nightly build service URL.
 *
 * @return string The base nightly build service URL.
 */
function nightly_api_url() {
	return 'https://utility.tri.be/nightly.php';
}

/**
 * Returns the nightly build secret, fetched from the nightly builds API.
 *
 * @return string|null The nightly build secret string.
 */
function nightly_secret( $licenses_file = null ) {
	parse_license_file( $licenses_file );

	return getenv( 'NIGHTLY_SECRET' );
}

/**
 * Fetches and returns the nightly builds timestamp
 *
 * @param string      $branch        The branch to fetch the build timestamp for.
 * @param string|null $licenses_file The licenses file to use to read the secret, or `null` to read the secret from
 *                                   the env vars.
 *
 * @return string The nightly build timestamp for the branch.
 */
function nightly_build_timestamp( $branch, $licenses_file = null ) {
	$response = curl_get( nightly_api_url(), [
		'timestamp' => '1',
		'branch'    => $branch,
		'key'       => nightly_secret( $licenses_file ),
	] );

	$decoded = json_decode( $response, true );

	if ( false === $decoded || ! isset( $decoded['timestamp'] ) ) {
		echo( 'Could not decode nightly build JSON response: ' . $response );
		exit( 1 );
	}

	return $decoded['timestamp'];
}

/**
 * Fetches and returns the available nightly builds for a branch.
 *
 * @param string      $branch        The name of the branch to fetch the nightly builds information for.
 * @param string|null $licenses_file The licenses file to use to read the secret, or `null` to read the secret from
 *                                   the env vars.
 *
 * @return array The decoded response.
 */
function nightly_builds( $branch, $licenses_file = null ) {
	$key      = nightly_secret( $licenses_file );

	if ( empty( $key ) ) {
		echo "\nNightly secret not found or not defined; is the NIGHTLY_SECRET env var set?";
		exit( 1 );
	}

	$response = curl_get( nightly_api_url(), [
		'branch' => $branch,
		'key'    => $key,
	] );

	$decoded = json_decode( $response, true );

	if ( false === $decoded ) {
		echo( "\nCould not decode nightly builds JSON response: " . $response );
		exit( 1 );
	}

	return $decoded;
}
