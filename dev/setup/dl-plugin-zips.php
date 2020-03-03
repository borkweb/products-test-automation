#!/usr/bin/env php
<?php

function curl_get( $url, array $query_args = null ) {
	$full_url = $url . ( strpos( $url, '?' ) === false ? '?' : '' ) . http_build_query( $query_args );

	$curl_handle  = curl_init();
	curl_setopt( $curl_handle, CURLOPT_URL, $full_url );
	curl_setopt( $curl_handle, CURLOPT_HEADER, 0 );
	curl_setopt( $curl_handle, CURLOPT_RETURNTRANSFER, true);
	curl_setopt( $curl_handle, CURLOPT_TIMEOUT, 10);

	if ( ! $result = curl_exec( $curl_handle ) ) {
		echo "\nFailed to process curl request.";
		echo "\nError: " . curl_error( $curl_handle );
		exit( 1 );
	}

	curl_close( $curl_handle );

	return $result;
}

function parse_requested_plugins( array $all_plugins ) {
	global $argv;

	$plugins = isset( $argv[2] ) ?
		array_filter( explode( ',', $argv[2] ) )
		: $all_plugins;

	$not_plugins = array_diff( $plugins, $all_plugins );

	if ( count( $not_plugins ) ) {
		echo "\nThe following plugins are not supported: \n" . json_encode( $not_plugins, JSON_PRETTY_PRINT );
		echo "\nSupported plugins are: \n" . json_encode( $all_plugins, JSON_PRETTY_PRINT );
		exit ( 1 );
	}

	return $plugins;
}

function parse_license_file() {
	global $argv;

	$licenses_file = isset( $argv[3] ) ? $argv[3] : null;

	if ( null !== $licenses_file ) {
		if ( ! file_exists( $licenses_file ) ) {
			echo "\nLicenses file ${licenses_file} does not exist.";
			exit( 1 );
		}

		foreach ( explode( "\n", file_get_contents( $licenses_file ) ) as $license_line ) {
			if ( ! preg_match( '/^[^=]=.*$/', $license_line ) ) {
				echo "\nLine '${license_line}' from licenses file is malformed.";
				exit( 1 );
			}
			putenv( $license_line );
		}
	} else {
		echo "\nLicenses file not specified, licenses will be read from environment.";
	}
}

function get_org_plugins_versions( array $required_org_plugins = [], $number_versions = 3 ) {
	$versions = [];
	if ( count( $required_org_plugins ) === 0 ) {
		return $versions;
	}

	echo "\n\nFetching .org plugins versions...";
	$plugin_api_url = 'https://api.wordpress.org/plugins/info/1.2/';
	$org_plugins_json_info = curl_get( $plugin_api_url, [
		'action'  => 'plugin_information',
		'request' =>
			[
				'slugs' => $required_org_plugins
			]
	] );
	$decoded_org_info      = json_decode( $org_plugins_json_info, true );
	if ( false === $decoded_org_info ) {
		echo '.org plugin(s) information could not be decoded; ' . json_last_error_msg();
		exit( 1 );
	}
	$org_plugins_versions = array_combine(
		$required_org_plugins,
		array_map( static function ( $plugin_slug ) use ( $number_versions, $decoded_org_info ) {
			if ( ! isset( $decoded_org_info[ $plugin_slug ] ) ) {
				echo "\n.org plugin information for {$plugin_slug} is missing.";
				exit( 1 );
			}
			$versions = $decoded_org_info[ $plugin_slug ]['versions'];
			// Versions come in older to newer order, let's reverse them.
			$versions = array_reverse( $versions, true );
			// The latest version, now first, is trunk, drop it as it's a duplicate of the last tagged one.
			// Keep only the last n.
			$versions = array_slice( $versions, 1, $number_versions );

			// The version array format is [<version> => <zip_url>].
			return $versions;
		}, $required_org_plugins )
	);

	return $org_plugins_versions;
}

function get_premium_plugins_versions( array $required_premium_plugins = [], $number_versions = 3 ) {
	return [];
}

function make_plugin_store( $plugin_store ) {
	if ( ! is_dir( $plugin_store ) && ! mkdir( $plugin_store, 0777, true ) && ! is_dir( $plugin_store ) ) {
		printf( "\nPlugin store directory %s could not be created.", $plugin_store );
		exit( 1 );
	}
}

if ( 1 === $argc ) {
	echo "\033[1mDownload .org and premium plugins in the last n versions.\033[0m\n";
	echo "\n";
	echo "\033[1mUsage:\033[0m\n";
	echo "\n";
	echo "\tphp dl-plugin-zips.php <number_versions> [<plugins>] [<licenses_file>]\n";
	echo "\n";
	echo "\t\033[32m<number_versions>\033[0m - an integer defining how many versions to download, e.g. 3.\n";
	echo "\t\033[32m[<plugins>]\033[0m - a comma-separated list of plugins to download, e.g. 'the-events-calendar,events-pro'.\n";
	echo "\t\033[32m[<licenses_file>]\033[0m - an optional .env format file that will provide the licenses for each plugin to download, e.g. '.env.licenses'.\n";
	exit( 0 );
}

# How many versions to download?
$number_versions = (int) $argv[1];
$org_plugins     = [
	'the-events-calendar',
	'event-tickets',
	'advanced-post-manager',
];
$premium_plugins = [
	'events-calendar-pro',
	'event-tickets-plus',
	'events-community',
	'events-community-tickets',
	'tribe-eventbrite',
	'events-filterbar',
	'image-widget-plus',
];


$all_plugins = array_merge( $org_plugins, $premium_plugins );
$plugins     = parse_requested_plugins( $all_plugins );
parse_license_file();

$license_to_plugin_map = [
	'events-calendar-pro'      => 'EVENTS_CALENDAR_PRO_LICENSE',
	'event-tickets-plus'       => 'EVENT_TICKETS_PLUS_LICENSE',
	'events-community'         => 'COMMUNITY_EVENTS_LICENSE',
	'events-community-tickets' => 'COMMUNITY_TICKETS_LICENSE',
	'tribe-eventbrite'         => 'EVENTBRITE_TICKETS_LICENSE',
	'events-filterbar'         => 'FILTER_BAR_LICENSE',
	'image-widget-plus'        => 'IMAGE_WIDGET_PLUS_LICENSE',
];

$required_org_plugins     = array_intersect( $plugins, $org_plugins );
$required_premium_plugins = array_intersect( $plugins, $premium_plugins );
$org_plugins_versions     = get_org_plugins_versions( $required_org_plugins, $number_versions );
$premium_plugins_versions = get_premium_plugins_versions( $required_premium_plugins, $number_versions );
$all_required_plugins = array_merge( $required_org_plugins, $required_premium_plugins );
$all_plugins_versions     = $org_plugins_versions + $premium_plugins_versions;
$plugin_store             = getcwd() . '/_plugin_store';
make_plugin_store( $plugin_store );

echo "\n\n";
echo "The following plugin versions will be downloaded: \n"
     . json_encode( $all_plugins_versions, JSON_PRETTY_PRINT );
echo "\n";

foreach ( $plugins as $plugin ) {
	$versions = $all_plugins_versions[ $plugin ];
	foreach ( $versions as $version => $archive_url ) {
		$dest = $plugin_store . '/' . $plugin . '-' . $version . '.zip';
		echo "\nDownloading ${plugin} v{$version} to {$dest}...\n";
		exec( "curl \"{$archive_url}\" > \"{$dest}\"", $output, $status );

		if ( 0 !== (int) $status ) {
			echo "\nFailed download: {$archive_url}; \n" . implode( "\n", $output );
			exit( 1 );
		}
	}
}
