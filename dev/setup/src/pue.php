<?php
/**
 * PUE related functions.
 */

/**
 * Return the complete URL to the PUE service for an endpoint and arguments.
 *
 * @param string        $endpoint   The endpoint to fetch the information for.
 * @param array<string> $query_args The list of query arguments to append to the service URL.
 *
 * @return string The complete, and hashed w/ secret, PUE service URL.
 */
function pue_url( $endpoint, array $query_args = [] ) {
	$pue_signed_secret = getenv( 'PUE_SIGNED_SECRET' );
	$pue_url           = getenv( 'PUE_URL' );

	if ( empty( $pue_signed_secret ) ) {
		echo "\nThe PUE_SIGNED_SECRET environment variable is either not defined or empty.";
		exit( 1 );
	}

	if ( empty( $pue_url ) ) {
		echo "\nThe PUE_URL environment variable is either not defined or empty.";
		exit( 1 );
	}

	$query_args = array_map( 'strval', $query_args );

	// Add timestamp (inflate by 4hrs).
	$query_args['timestamp'] = (string) ( time() + ( 60 * 60 * 4 ) );

	// Sign the request
	ksort( $query_args );
	$encoded_data       = json_encode( $query_args );
	$query_args['hash'] = hash( 'sha256', $encoded_data . $pue_signed_secret );

	return rtrim( $pue_url, '/' ) . '/' . ltrim( $endpoint, '/' ) . '?' . http_build_query( $query_args );
}

