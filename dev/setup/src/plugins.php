<?php
/**
 * Plugin related functions for the build PHP scripts.
 */

require_once __DIR__ . '/nightly.php';

/**
 * Returns the list of Modern Tribe .org plugins.
 *
 * @return array<string> A list of Modern Tribe .org hosted WordPress plugin slugs.
 */
function org_plugins() {
	return [
		'the-events-calendar',
		'event-tickets',
		'advanced-post-manager',
		'image-widget',
	];
}

/**
 * Returns the list of Modern Tribe premium plugins.
 *
 * @return array<string> A list of Modern Tribe premium WordPress plugin slugs.
 */
function premium_plugins() {
	return array_keys( premium_plugins_license_keys_map() );
}

/**
 * Returns the list of all Modern Tribe plugins, .org and premium.
 *
 * @return array<string> A list of all Modern Tribe WordPress plugin slugs.
 */
function all_plugins() {
	return array_merge( org_plugins(), premium_plugins() );
}

/**
 * Returns a map relating each premium plugin slug to the expected name of the env var that should contain its license.
 *
 * @return array<string,string> The plugin slug to expected license env var name map.
 */
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

/**
 * Returns a map relating each SaaS slug to the expected name of the env var that should contain its license.
 *
 * @return array<string,string> The SaaS slug to expected license env var name map.
 */
function saas_license_keys_map() {
	return [
		'event-aggregator' => 'EVENT_AGGREGATOR_LICENSE',
		'promoter'         => 'PROMOTER_LICENSE',
	];
}

/**
 * Fetches and returns the last n versions of the specified plugins, both .org and premium plugins.
 *
 * @param callable $args A closure to provide the arguments.
 *
 * @return array<string,array> An array of each plugin slug versions.
 */
function plugin_versions( callable $args ) {
	$plugins         = validate_plugins( parse_list( $args( 'plugins', all_plugins() ) ) );
	$number_versions = $args( 'number_versions' );

	$required_org_plugins     = array_intersect( $plugins, org_plugins() );
	$required_premium_plugins = array_intersect( $plugins, premium_plugins() );
	$org_plugins_versions     = org_plugins_versions( $required_org_plugins, $number_versions );
	$premium_plugins_versions = premium_plugin_versions( $required_premium_plugins, $number_versions );

	return $org_plugins_versions + $premium_plugins_versions;
}

/**
 * Fetches and returns the last n versions of the specified .org plugins.
 *
 * @param array<string> $required_plugins A list of the required .org hosted plugins slugs.
 * @param int           $number_versions  The number of last versions to fetch for each plugin.
 *
 * @return array<string,array> An array of each plugin slug versions.
 */
function org_plugins_versions( array $required_plugins = [], $number_versions = 3 ) {
	if ( count( $required_plugins ) === 0 ) {
		return [];
	}

	echo "\n\nFetching .org plugins versions...";
	$plugin_api_url        = 'https://api.wordpress.org/plugins/info/1.2/';
	$org_plugins_json_info = curl_get( $plugin_api_url, [
		'action'  => 'plugin_information',
		'request' =>
			[
				'slugs' => $required_plugins
			]
	] );

	$decoded_org_info = json_decode( $org_plugins_json_info, true );

	if ( false === $decoded_org_info ) {
		echo '.org plugin(s) information could not be decoded; ' . json_last_error_msg();
		exit( 1 );
	}

	$org_plugins_versions = array_combine(
		$required_plugins,
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
		}, $required_plugins )
	);

	return $org_plugins_versions;
}

/**
 * Fetches and returns the last n versions of the specified premium plugins.
 *
 * @param array<string> $required_plugins A list of the required premium plugins slugs.
 * @param int           $number_versions  The number of last versions to fetch for each plugin.
 *
 * @return array<string,array> An array of each plugin slug versions.
 */
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

/**
 * Returns the absolute path, on the host machine, to the directory that contains the downloaded plugins.
 *
 * The function will create the directory first, if it does not exist.
 *
 * @return string The absolute path, on the host machine, to the directory that contains the downloaded plugins.
 */
function plugin_store() {
	$plugin_store = getcwd() . '/dev/test/_plugin_store';
	if ( ! is_dir( $plugin_store ) && ! mkdir( $plugin_store, 0777, true ) && ! is_dir( $plugin_store ) ) {
		printf( "\nPlugin store directory %s could not be created.", $plugin_store );
		exit( 1 );
	}

	return $plugin_store;
}

/**
 * Returns the absolute path, on the host machine, to a plugin zip file provided its name (slug) and version.
 *
 * @param string $name    The plugin slug or name.
 * @param string $version The version to return the zip file path for.
 *
 * @return string The absolute path, on the host machine, to the plugin zip file.
 */
function plugin_zip_file( $name, $version ) {
	$path = plugin_store() . '/' . $name . '-' . $version . '.zip';
	if ( ! file_exists( $path ) ) {
		echo "\nPlugin zip file ${path} does not exist.";
		exit( 1 );
	}

	return $path;
}

/**
 * Installs a plugin in a specific version, overriding any previously installed version, from the plugin store.
 *
 * @param string $name    The name of the plugin to install.
 * @param string $version The version of the plugin to install.
 */
function install_plugin( $name, $version ) {
	$plugin_zip = wordpress_container_root_dir(
		'_plugin_store/' . relative_path( plugin_store(), plugin_zip_file( $name, $version ) )
	);
	check_status_or_exit( cli()( [ 'plugin', 'install', $plugin_zip, '--force', '--debug' ] ) )( 'string_output' );
}

/**
 * Validates the required plugin slugs, to make sure all are either .org or premium valid slugs.
 *
 * @param array<string> $plugins The plugin slugs to validate.
 *
 * @return array<string> The list of plugins, if valid.
 */
function validate_plugins( $plugins ) {
	$not_plugins = array_diff( $plugins, all_plugins() );

	if ( count( $not_plugins ) ) {
		echo "\nThe following plugins are not supported: \n" . json_encode( $not_plugins, JSON_PRETTY_PRINT );
		echo "\nSupported plugins are: \n" . json_encode( all_plugins(), JSON_PRETTY_PRINT );
		exit ( 1 );
	}

	return $plugins;
}

/**
 * Returns a premium plugin license key, reading it from the environment.
 *
 * @param string $plugin_slug The slug of the premium plugin to read the license key for.
 *
 * @return string The premium plugin license key.
 */
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

/**
 * Downloads, in the plugin store directory, the specified plugin versions.
 *
 * @param array<string,array> $plugin_versions A map of plugin slugs and the required versions to download.
 */
function download_plugin_versions( array $plugin_versions ) {
	$debug_info = array_map( 'array_keys', $plugin_versions );
	echo "\n\n";
	echo "The following plugin versions will be downloaded: \n"
	     . json_encode( $debug_info, JSON_PRETTY_PRINT );
	echo "\n";

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

/**
 * Returns a list of random plugins versions and files, parsing the content of the plugin store.
 *
 * @param string $dir The plugin store directory path.
 *
 * @return array<string,array> The random list of picked plugins and version information.
 *
 * @throws Exception If there's an issue reading the plugin store contents.
 */
function random_plugins( $dir ) {
	$available_zips_iterator = new CallbackFilterIterator(
		new FilesystemIterator( $dir, FilesystemIterator::SKIP_DOTS ),
		static function ( SplFileInfo $f ) {
			// Either a semantic version or the nightly/dev hash.
			$plugin_zip_pattern = '/[\\w_-]+-([0-9\\.]+|dev-[\\w]+)\\.zip$/uis';

			return $f->isFile() && preg_match( $plugin_zip_pattern, $f->getBasename() );
		}
	);

	$available_zips = iterator_to_array( $available_zips_iterator );

	if ( 0 === count( $available_zips ) ) {
		echo "\nNo plugin zips available";
		exit( 1 );
	}

	$by_slug = array_reduce( $available_zips, static function ( array $map, SplFileInfo $f ) {
		$plugin_zip_pattern = '/(?<slug>[\\w_-]+)-(?<version>([0-9\\.]+|dev-[\\w]+))$/uis';
		preg_match( $plugin_zip_pattern, $f->getBasename( '.zip' ), $m );

		if ( ! isset( $m['slug'], $m['version'] ) ) {
			echo "\nPlugin file {$f->getPathname()} has a malformed name pattern.";
			exit( 1 );
		}

		$slug    = $m['slug'];
		$version = $m['version'];

		if ( isset( $map[ $slug ] ) ) {
			$map[ $slug ][ $version ] = $f->getPathname();
		} else {
			$map[ $slug ] = [ $version => $f->getPathname() ];
		}

		return $map;
	}, [] );

	// Pick a random number of plugins.
	$number = max( 2, random_int( 1, count( array_keys( $by_slug ) ) ) );

	// Pick n random plugins in random order.
	$slugs = array_rand_keys( array_keys( $by_slug ), $number );

	$picks = [];
	foreach ( $slugs as $slug ) {
		$version = array_rand( $by_slug[ $slug ], 1 );
		$picks[] = [
			'slug'    => $slug,
			'version' => $version,
			'zip'     => $by_slug[ $slug ][ $version ],
		];
	}

	return $picks;
}

/**
 * Maps each plugin slug, from the zip/repo, to its WordPress name, this is the name the plugin will be identified with
 * by WordPress.
 *
 * @param string $plugin_slug The slug of the plugin to return the WordPress name for.
 *
 * @return string The WordPress name of the plugin.
 */
function plugin_wordpress_name( $plugin_slug ) {
	$map = [
		'the-events-calendar'      => 'the-events-calendar',
		'event-tickets'            => 'event-tickets',
		'advanced-post-manager'    => 'advanced-post-manager',
		'events-community'         => 'the-events-calendar-community-events',
		'events-community-tickets' => 'the-events-calendar-community-events-tickets',
		'events-elasticsearch'     => 'events-elasticsearch',
		'event-tickets-plus'       => 'event-tickets-plus',
		'tribe-eventbrite'         => 'the-events-calendar-eventbrite-tickets',
		'events-calendar-pro'      => 'events-calendar-pro',
		'events-facebook-importer' => 'the-events-calendar-facebook-importer',
		'tribe-filterbar'          => 'the-events-calendar-filterbar',
		'tribe-ical-importer'      => 'the-events-calendar-ical-importer',
		'image-widget'             => 'image-widget',
		'image-widget-plus'        => 'image-widget-plus',
	];

	return isset( $map[ $plugin_slug ] ) ? $map[ $plugin_slug ] : $plugin_slug;
}

/**
 * Returns a map of the available plugins, and their available nightly builds, for a branch.
 *
 * @param callable $args A closure providing the call arguments.
 *
 * @return array<string,array> A map of each plugin and the available nightly builds.
 */
function plugin_nightly_builds( callable $args ) {
	$branch_nightly_builds = nightly_builds( $args( 'branch' ), $args( 'licenses_file' ) );

	foreach ( $branch_nightly_builds as $branch => $branch_builds ) {
		foreach ( $branch_builds as $plugin_slug => $build_data ) {
			if ( ! empty( $build_data['build_error'] ) ) {
				continue;
			}

			if ( ! isset( $build_data['download_url'], $build_data['build_hash'] ) ) {
				continue;
			}

			$download_url = $build_data['download_url'];
			$version      = 'dev-' . $build_data['build_hash'];

			if ( isset( $map[ $plugin_slug ] ) ) {
				$map[ $plugin_slug ][ $version ] = $download_url;
			} else {
				$map[ $plugin_slug ] = [ $version => $download_url ];
			}
		}
	}

	return $map;
}
