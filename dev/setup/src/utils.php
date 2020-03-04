<?php
/**
 * Utility functions for the build PHP scripts.
 */

require_once __DIR__ . '/pue.php';

function args( array $map = [] ) {
	global $argv;

	$full_map = [];
	foreach ( $map as $position => $key ) {
		$full_map[ $key ] = isset( $argv[ $position + 1 ] ) ? $argv[ $position + 1 ] : null;
	}

	return static function ( $key, $default = null ) use ( $full_map ) {
		return null !== $full_map[ $key ] ? $full_map[ $key ] : $default;
	};
}

function curl_get( $url, array $query_args = [] ) {
	$full_url = $url . ( strpos( $url, '?' ) === false ? '?' : '' ) . http_build_query( $query_args );

	$curl_handle = curl_init();
	curl_setopt( $curl_handle, CURLOPT_URL, $full_url );
	curl_setopt( $curl_handle, CURLOPT_HEADER, 0 );
	curl_setopt( $curl_handle, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $curl_handle, CURLOPT_TIMEOUT, 10 );
	curl_setopt( $curl_handle, CURLOPT_FOLLOWLOCATION, true );

	if ( ! $result = curl_exec( $curl_handle ) ) {
		echo "\nFailed to process curl request.";
		echo "\nError: " . curl_error( $curl_handle );
		exit( 1 );
	}

	curl_close( $curl_handle );

	return $result;
}

function parse_license_file( $licenses_file ) {
	if ( null !== $licenses_file ) {
		load_env_file( $licenses_file );
	} else {
		echo "\nLicenses file not specified, licenses will be read from environment.";
	}
}

function load_env_file( $env_file ) {
	if ( ! file_exists( $env_file ) ) {
		echo "\nenv file ${env_file} does not exist.";
		exit( 1 );
	}

	$lines = array_filter( explode( "\n", file_get_contents( $env_file ) ) );
	foreach ( $lines as $env_line ) {
		if ( ! preg_match( '/^[^=]+=.*$/', $env_line ) ) {
			echo "\nLine '${env_line}' from env file is malformed.";
			exit( 1 );
		}
		putenv( $env_line );
	}
}

function parse_list( $list, $sep = ',' ) {
	if ( is_string( $list ) ) {
		$list = array_filter( preg_split( '/\\s*' . preg_quote( $sep ) . '\\s*/', $list ) );
	}

	return $list;
}
