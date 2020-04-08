<?php
/**
 * tric cli functions.
 */

namespace Tribe\Test;

/**
 * Checks a specified target exists in the `dev/_plugins` directory.
 *
 * @param string $target The target to check in the `dev/_plugins` directory.
 */
function ensure_dev_plugin( $target ) {
	$targets     = array_keys( dev_plugins() );
	$targets_str = implode( PHP_EOL, array_map( static function ( $target ) {
		return "  - {$target}";
	}, $targets ) );

	if ( false === $target ) {
		echo red( "This command needs a target argument; available targets are:\n${targets_str}\n" );
		exit( 1 );
	}

	if ( ! in_array( $target, $targets, true ) ) {
		echo red( "'{$target}' is not a valid target; available targets are:\n${targets_str}\n" );
		exit( 1 );
	}
}

/**
 * Sets up the environment form the cli tool.
 *
 * @param string $root_dir The cli tool root directory.
 */
function setup_tric_env( $root_dir ) {
	// Set the current run context.
	putenv( 'TRIBE_TRIC=1' );

	// Load the distribution version configuration file, the version-controlled one.
	load_env_file( $root_dir . '/.env.tric' );

	// Load the local overrides, this file is not version controlled.
	if ( file_exists( $root_dir . '/.env.tric.local' ) ) {
		load_env_file( $root_dir . '/.env.tric.local' );
	}

	// Load the current session configuration file.
	if ( file_exists( $root_dir . '/.env.tric.run' ) ) {
		load_env_file( $root_dir . '/.env.tric.run' );
	}

	// Most commands are nested shells that should not run with a time limit.
	remove_time_limit();
}

/**
 * Returns the current `use` target.
 *
 * @param bool $require Whether to require a target, and fail if not set, or not.
 *
 * @return string|string Either the current target or `false` if the target is not set. If `$require` is `true` then the
 *                       return value will always be a non empty string.
 */
function tric_target( $require = true ) {
	$using = getenv( 'TRIC_CURRENT_PROJECT' );
	if ( $require ) {
		return $using;
	}
	if ( empty( $using ) ) {
		echo red( "Use target not set; use the 'use' sub-command to set it.\n" );
		exit( 1 );
	}

	return trim( $using );
}

/**
 * Returns a map of the stack PHP services that relates the service to its pretty name.
 *
 * @return array<string,string> A map of the stack PHP services relating each service to its pretty name.
 */
function php_services() {
	return [
		'wordpress'   => 'WordPress',
		'codeception' => 'Codeception',
	];
}

/**
 * Restart the stack PHP services.
 */
function restart_php_services() {
	foreach ( php_services() as $service => $pretty_name ) {
		restart_service( $service, $pretty_name );
	}
}

/**
 * Restarts a stack services if it's running.
 *
 * @param string      $service     The name of the service to restart, e.g. `wordpress`.
 * @param string|null $pretty_name The pretty name to use for the service, or `null` to use the service name.
 */
function restart_service( $service, $pretty_name = null ) {
	$pretty_name   = $pretty_name ?: $service;
	$tric          = docker_compose( [ '-f', stack() ] );
	$tric_realtime = docker_compose_realtime( [ '-f', stack() ] );

	$service_running = $tric( [ 'ps', '-q', $service ] )( 'string_output' );
	if ( ! empty( $service_running ) ) {
		echo colorize( "Restarting {$pretty_name} service...\n" );
		$tric_realtime( [ 'restart', $service ] );
		echo colorize( "<light_cyan>{$pretty_name} service restarted!</light_cyan>\n" );
	} else {
		echo colorize( "{$pretty_name} service was not running.\n" );
	}
}

/**
 * Clones a company plugin in the current plugin root directory.
 *
 * @param string $plugin The plugin name, e.g. `the-events-calendar` or `event-tickets`.
 */
function clone_plugin( $plugin ) {
	$plugin_path = __DIR__ . '/_plugins/' . $plugin;
	$plugin_dir  = dirname( $plugin_path );

	if ( ! file_exists( $plugin_path ) ) {
		if ( ! file_exists( __DIR__ . '/_plugins' ) ) {
			echo "Creating dev/_plugins directory...\n";
			if ( ! mkdir( $plugin_dir ) && ! is_dir( $plugin_dir ) ) {
				echo magenta( "Could not create {$plugin_path} directory; please check the parent directory is writeable." );
				exit( 1 );
			}
		}

		echo "Cloning {$plugin}...\n";

		$repository = github_company_handle() . '/' . escapeshellcmd( $plugin );

		$clone_status = process_realtime(
			'git clone --recursive git@github.com:' . $repository . '.git ' . escapeshellcmd( $plugin_path )
		);

		if ( 0 !== $clone_status ) {
			echo magenta( "Could not clone the {$repository} repository; please check your access rights to the repository." );
			exit( 1 );
		}
	}

}

/**
 * Sets up the files required to run tests in the plugin using tric stack.
 *
 * @param string $plugin The plugin name, e.g. 'the-events-calendar` or `event-tickets`.
 */
function setup_plugin_tests( $plugin ) {
	$plugin_path = dirname( dirname( __DIR__ ) ) . '/_plugins';
	$relative_paths = [ '' ];

	if ( file_exists( "{$plugin_path}/common" ) ) {
		$relative_paths[] = 'common';
	}

	foreach ( $relative_paths as $relative_path ) {
		$target_path   = "{$plugin_path}/{$relative_path}";
		$relative_path = empty( $relative_path ) ? '' : "{$relative_path}/";

		write_tric_test_config( $target_path );
		echo colorize( "Created/updated <light_cyan>{$relative_path}test-config.tric.php</light_cyan> " .
		               "in {$plugin}.\n" );

		write_tric_env_file( $target_path );
		echo colorize( "Created/updated <light_cyan>{$relative_path}.env.testing.tric</light_cyan> " .
		               "in {$plugin}.\n" );


		if ( write_codeception_config( $target_path ) ) {
			echo colorize( "Created <light_cyan>{$relative_path}codeception.yml</light_cyan> in " .
			               "<light_cyan>{$plugin}</light_cyan>.\n" );
		} else {
			echo colorize( "Skipped creating <light_cyan>{$relative_path}codeception.yml</light_cyan>" .
			               " in <light_cyan>{$plugin}</light_cyan>. It already exists (*).\n" );
			echo colorize( "\n(*) A skipped codeception.yml file could be ok. If your tests fail to run, try removing the" .
			               " codeception.yml and running <light_cyan>tric init <plugin></light_cyan> again.\n\n" );
		}
	}
}

/**
 * Returns the handle (username) of the company to clone plugins from.
 *
 * Configured using the `TRIC_GITHUB_COMPANY_HANDLE` env variable.
 *
 * @return string The handle of the company to clone plugins from.
 */
function github_company_handle() {
	$handle = getenv( 'TRIC_GITHUB_COMPANY_HANDLE' );

	return ! empty( $handle ) ? trim( $handle ) : 'moderntribe';
}
