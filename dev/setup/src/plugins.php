<?php

/**
 * Plugin related functions for the build PHP scripts.
 */

function org_plugins() {
	return [
		'the-events-calendar',
		'event-tickets',
		'advanced-post-manager',
	];
}

function premium_plugins() {
	return array_keys( premium_plugins_license_keys_map() );
}

function all_plugins() {
	return array_merge( org_plugins(), premium_plugins() );
}

function premium_plugins_license_keys_map() {
	return [
		'events-community'         => 'COMMUNITY_EVENTS_LICENSE',
		'events-community-tickets' => 'COMMUNITY_TICKETS_LICENSE',
		'tribe-edd-tickets'        => 'EDD_TICKETS_LICENSE',
		'events-elasticsearch'     => 'EVENTS_ELASTICSEARCH_LICENSE',
		'event-tickets-plus'       => 'EVENT_TICKETS_PLUS_LICENSE',
		'tribe-eventbrite'         => 'EVENTBRITE_TICKETS_LICENSE',
		'events-calendar-pro'      => 'EVENTS_CALENDAR_PRO_LICENSE',
		'events-facebook-importer' => 'EVENTS_FACEBOOK_IMPORTER_LICENSE',
		'tribe-filterbar'          => 'FILTERBAR_LICENSE',
		'tribe-ical-importer'      => 'ICAL_IMPORTER_LICENSE',
		'image-widget-plus'        => 'IMAGE_WIDGET_PLUS_LICENSE',
		'tribe-shopptickets'       => 'SHOPPTICKETS_LICENSE',
		'tribe-wootickets'         => 'WOOTICKETS_LICENSE',
		'tribe-wpectickets'        => 'WPECTICKETS_LICENSE'
	];
}

function saas_license_keys_map() {
	return [
		'event-aggregator' => 'EVENT_AGGREGATOR_LICENSE',
		'promoter'         => 'PROMOTER_LICENSE',
	];
}

function plugin_versions( callable $args ) {
	$plugins         = validate_plugins( parse_list( $args( 'plugins', all_plugins() ) ) );
	$number_versions = $args( 'number_versions' );

	$required_org_plugins     = array_intersect( $plugins, org_plugins() );
	$required_premium_plugins = array_intersect( $plugins, premium_plugins() );
	$org_plugins_versions     = org_plugins_versions( $required_org_plugins, $number_versions );
	$premium_plugins_versions = premium_plugin_versions( $required_premium_plugins, $number_versions );

	return $org_plugins_versions + $premium_plugins_versions;
}

function org_plugins_versions( array $required_org_plugins = [], $number_versions = 3 ) {
	if ( count( $required_org_plugins ) === 0 ) {
		return [];
	}

	echo "\n\nFetching .org plugins versions...";
	$plugin_api_url        = 'https://api.wordpress.org/plugins/info/1.2/';
	$org_plugins_json_info = curl_get( $plugin_api_url, [
		'action'  => 'plugin_information',
		'request' =>
			[
				'slugs' => $required_org_plugins
			]
	] );

	$decoded_org_info = json_decode( $org_plugins_json_info, true );

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

function premium_plugin_versions( array $required_plugins = [], $number_versions = 3 ) {
	if ( count( $required_plugins ) === 0 ) {
		return [];
	}

	echo "\n\nFetching premium plugins versions...";

	$plugins_data = curl_get( pue_url( '/plugins' ) );

	$decoded = json_decode( $plugins_data, true );

	if ( false === $decoded || ! isset( $decoded['plugins'] ) ) {
		echo "\nPUE /plugins response was malformed: \n" . $plugins_data;
		exit( 1 );
	}

	$plugins = $decoded['plugins'];

	$required_plugins_data = array_filter( $plugins, static function ( array $plugin_data ) use ( $required_plugins ) {
		return isset( $plugin_data['plugin_slug'] ) && in_array( $plugin_data['plugin_slug'], $required_plugins,
				true );
	} );

	if ( empty( $required_plugins_data ) ) {
		echo "\nFailed to fetch information for all required premium plugins: " .
		     json_encode( $required_plugins, JSON_PRETTY_PRINT );
		exit( 1 );
	}

	$versions     = array_column( $required_plugins_data, 'allowed_versions' );
	$plugin_slugs = array_column( $required_plugins_data, 'plugin_slug' );

	$required_plugins_versions = array_combine(
		$plugin_slugs,
		array_map( static function ( array $plugin_versions, $plugin_slug ) use ( $number_versions ) {
			// Invert to have latest versions first.
			$plugin_versions = array_reverse( $plugin_versions );
			// Keep the last n.
			$plugin_versions = array_slice( $plugin_versions, 0, $number_versions );
			// Walk the versions to build a [ <version> => <url> ] map.
			$plugin_versions = array_combine(
				array_column( $plugin_versions, 'version' ),
				array_column( $plugin_versions, 'plugin_download_url' )
			);

			// Append the correct license key to each plugin download URL.
			array_walk( $plugin_versions, static function ( &$plugin_download_url ) use ( $plugin_slug ) {
				$plugin_download_url .= '&key=' . license_key( $plugin_slug );
			} );

			return $plugin_versions;
		}, $versions, $plugin_slugs )
	);

	return $required_plugins_versions;
}

function plugin_store() {
	$plugin_store = getcwd() . '/_plugin_store';
	if ( ! is_dir( $plugin_store ) && ! mkdir( $plugin_store, 0777, true ) && ! is_dir( $plugin_store ) ) {
		printf( "\nPlugin store directory %s could not be created.", $plugin_store );
		exit( 1 );
	}

	return $plugin_store;
}

function validate_plugins( $plugins ) {
	$not_plugins = array_diff( $plugins, all_plugins() );

	if ( count( $not_plugins ) ) {
		echo "\nThe following plugins are not supported: \n" . json_encode( $not_plugins, JSON_PRETTY_PRINT );
		echo "\nSupported plugins are: \n" . json_encode( all_plugins(), JSON_PRETTY_PRINT );
		exit ( 1 );
	}

	return $plugins;
}

function license_key( $plugin_slug ) {
	$map = premium_plugins_license_keys_map();

	if ( ! isset( $map[ $plugin_slug ] ) ) {
		echo "\nLicense key var for {$plugin_slug} is not supported.";
		exit( 1 );
	}

	$env_var     = $map[ $plugin_slug ];
	$license_key = getenv( $env_var );

	if ( empty( $license_key ) ) {
		echo "\nLicense key for {$plugin_slug} ({$env_var}) is not a set env var or is empty.";
		exit( 1 );
	}

	return $license_key;
}

function download_plugin_versions( array $plugin_versions ) {
	$debug_info = array_map( 'array_keys', $plugin_versions );
	echo "\n\n";
	echo "The following plugin versions will be downloaded: \n"
	     . json_encode( $debug_info, JSON_PRETTY_PRINT );
	echo "\n";

	exit( 0 );

	foreach ( $plugin_versions as $plugin => $versions ) {
		foreach ( $versions as $version => $archive_url ) {
			$dest = plugin_store() . '/' . $plugin . '-' . $version . '.zip';
			echo "\nDownloading ${plugin} v{$version} to {$dest}...\n";
			exec( "curl \"{$archive_url}\" > \"{$dest}\"", $output, $status );

			if ( 0 !== (int) $status ) {
				echo "\nFailed download: {$archive_url}; \n" . implode( "\n", $output );
				exit( 1 );
			}
		}
	}
}
