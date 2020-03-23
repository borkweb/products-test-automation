<?php
/**
 * Provides functions to run automated tests.
 */

require_once __DIR__ . '/../setup/src/utils.php';
require_once __DIR__ . '/../setup/src/plugins.php';

/**
 * Randomly activate some plugins found in the plugin store, in random order and versions.
 *
 * @param int $epochs The number of times to run the random activation.
 *
 * @throws Exception If there's an issue reading the plugin store contents.
 */
function randomly_activate_plugins( $epochs ) {
	$cli               = cli();
	$wordpress_version = check_status_or_exit( $cli( [ 'core', 'version' ] ) )( 'string_output' );

	for ( $i = 1; $i <= $epochs; $i ++ ) {
		$plugins = random_plugins( plugin_store() );

		echo "\nThe following plugins will be activated:\n" . json_encode( $plugins, JSON_PRETTY_PRINT );

		$list = $cli( array_merge( [ 'plugin', 'list', '--format=csv', '--status=active', '--fields=name,version' ] ) );
		check_status_or_exit( $list );

		check_status_or_exit(
			$cli( [ 'plugin', 'deactivate', '--all' ] ),
			"\n\nFatality!\n"
			. the_fatality()
			."\nWordPress version: {$wordpress_version}\n"
			. "\nThe following deactivation context has issues: \n" . implode( "\n", $list( 'output' ) )
		);

		foreach ( $plugins as $plugin ) {
			$plugin_zip = wordpress_container_root_dir(
				'_plugin_store/' . relative_path( plugin_store(), $plugin['zip'] )
			);
			check_status_or_exit( $cli( [ 'plugin', 'install', $plugin_zip, '--force' ] ) );
		}

		$list = $cli( array_merge( [ 'plugin', 'list','--format=csv', '--fields=name,version' ] ) );
		check_status_or_exit( $list );
		the_process_output( $list );

		$activated = [];
		$debug = "\n\nFatality!\n"
		         . the_fatality()
		         ."\nWordPress version: {$wordpress_version}\n"
		         . "\nThe following activation path has issues: \n";

		foreach ( $plugins as $plugin ) {
			$activated[ $plugin['slug'] ] = $plugin['version'];
			$plugin_slug                  = plugin_wordpress_name( $plugin['slug'] );
			$activate                     = $cli( [ 'plugin', 'activate', $plugin_slug ] );
			check_status_or_exit( $activate, $debug . json_encode( $activated, JSON_PRETTY_PRINT ) );
		}
	}
}
