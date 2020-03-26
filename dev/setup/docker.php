<?php
/**
 * docker-compose wrapper functions.
 */

namespace Tribe\Test;

require_once __DIR__ . '/src/utils.php';

/**
 * Returns the current Operating System family.
 *
 * @return string The human-readable name of the OS PHP is running on. One of `Linux`, `macOS`, `Windows`, `Solaris`,
 *                `BSD` or `Unknown`.
 */
function os() {
	$map = [
		'win' => 'Windows',
		'dar' => 'macOS',
		'lin' => 'Linux',
		'bsd' => 'BSD',
		'sol' => 'Solaris',
	];

	$key = strtolower( substr( PHP_OS, 0, 3 ) );

	return isset( $map[ $key ] ) ? $map[ $key ] : 'Unknown';
}

/**
 * Curried docker-compose wrapper.
 *
 * @param array<string> $options A list of options to initialize the wrapper.
 *
 * @return \Closure A closure to actually call docker-compose with more arguments.
 */
function docker_compose( array $options = [] ) {
	setup_id();

	$is_ci = is_ci();

	$host_ip = false;
	if ( ! $is_ci && 'Linux' === os() ) {
		$linux_overrides = stack( '-linux-override' );
		if ( file_exists( $linux_overrides ) ) {
			$options = array_merge( [ '-f', $linux_overrides ], $options );
		}
		// If we're running on Linux, then try to fetch the host machine IP using a command.
		$host_ip = host_ip( 'Linux' );
	}

	return static function ( array $command = [] ) use ( $options, $host_ip, $is_ci ) {
		$command = 'docker-compose ' . implode( ' ', $options ) . ' ' . implode( ' ', $command );

		if ( ! empty( $host_ip ) ) {
			// Set the host IP address on Linux machines.
			$command = 'XDH=' . host_ip() . ' ' . $command;
		}

		if ( ! empty( $is_ci ) ) {
			// Disable XDebug in CI context to speed up the builds.
			$command = 'XDE=0 ' . $command;
		}

		return process( $command );
	};
}

/**
 * Returns the file path of the WordPress root directory in the WordPress container.
 *
 * @param string $path The path to append to the WordPress root directory path.
 *
 * @return string The absolute path to a directory or file in the WordPress container.
 */
function wordpress_container_root_dir( $path = '/' ) {
	return '/var/www/html/' . ltrim( $path, '\\/' );
}

/**
 * Sets up and returns a wp-cli pre-process, ready to run wp-cli commands in the stack.
 *
 * @return \Closure The wp-cli pre-process, ready to accept an array of commands to run, the `wp` command is not
 *                 required.
 */
function cli() {
	$service = is_ci() ? 'cli' : 'cli_debug';

	return docker_compose( [ '-f', stack(), 'run', $service, '--allow-root' ] );
}

/**
 * Returns the URL at which the `wordpress` service will be reachable on localhost.
 *
 * Depending on whether the current context is a CI one or not, the URL will vary.
 *
 * @return string The URL at which the `wordpress` service can be reached.
 */
function wordpress_url() {
	if ( is_ci() ) {
		return 'http://tribe.test';
	}

	$config = check_status_or_exit( docker_compose( [ '-f', stack() ] )( [ 'config' ] ) )( 'string_output' );

	preg_match( '/wordpress_debug:.*?ports:.*?(?<port>\\d+):80\\/tcp/us', $config, $m );

	if ( ! isset( $m['port'] ) ) {
		echo "\n\033[31mCould not read the 'wordpress_debug' service localhost port from the stack " .
		     "configuration:\n" . $config;
		exit( 1 );
	}

	return 'http://localhost:' . (int) $m['port'];
}

/**
 * Returns the stack to run depending on the current run context.
 *
 * @param string $postfix      A postfix to use for the stack file, it will be inserted between the file base name and
 *                             the `.yml` file extension.
 *
 * @return string The path to the docker-compose stack file to run, depending on the run context.
 */
function stack( $postfix = '' ) {
	$dev_dir     = dirname( __DIR__ );
	$test_dir    = $dev_dir . '/test';
	$run_context = run_context();
	switch ( $run_context ) {
		case 'tric';
			$stack = $dev_dir . '/tric-stack' . $postfix . '.yml';
			break;
		default:
		case 'default':
		case 'ci':
			$stack = $test_dir . '/activation-stack' . $postfix . '.yml';
			break;
	}

	return $stack;
}

/**
 * Executes a docker-compose command in real time, printing the output as produced by the command.
 *
 * @param array<string> $options A list of options to initialize the wrapper.
 *
 * @return \Closure A closure that will run the process in real time and return the process exit status.
 */
function docker_compose_realtime( array $options = [] ) {
	setup_id();

	$is_ci = is_ci();

	$host_ip = false;
	if ( ! $is_ci && 'Linux' === os() ) {
		$options = array_merge( [ '-f', stack( '-linux-override' ) ], $options );
		// If we're running on Linux, then try to fetch the host machine IP using a command.
		$host_ip = host_ip( 'Linux' );
	}

	return static function ( array $command = [] ) use ( $options, $host_ip, $is_ci ) {
		$command = 'docker-compose ' . implode( ' ', $options ) . ' ' . implode( ' ', $command );

		if ( ! empty( $host_ip ) ) {
			// Set the host IP address on Linux machines.
			$command = 'XDH=' . host_ip() . ' ' . $command;
		}

		if ( ! empty( $is_ci ) ) {
			// Disable XDebug in CI context to speed up the builds.
			$command = 'XDE=0 ' . $command;
		}

		return process_realtime( $command );
	};
}


/**
 * Prints a debug message, if CLI_VERBOSITY is not `0`.
 *
 * @param string $message The debug message to print.
 */
function debug( $message ) {
	$verbosity = getenv( 'CLI_VERBOSITY' );
	if ( empty( $verbosity ) ) {
		return;
	}
	echo '[debug]' . $message;
}
