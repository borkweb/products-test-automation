<?php
/**
 * docker-compose wrapper functions.
 */

require_once __DIR__ . '/src/utils.php';

/**
 * Returns the current Operating System family.
 *
 * @return string The human-readable name of the OS PHP is running on. One of `Linux`, `macOS`, `Windows`, `Solaris`,
 *                `BSD` or `Unknown`.
 */
function os() {
	$map    = [
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
 * @return Closure A closure to actually call docker-compose with more arguments.
 */
function docker_compose( array $options = [] ) {
	setup_id();

	$is_ci = is_ci();

	$host_ip = false;
	if ( ! $is_ci && 'Linux' === os() ) {
		$options = array_merge( [ '-f', 'dev/test/activation-stack-linux-override.yml' ], $options );
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

		echo "\nExecuting command: {$command}\n";

		exec( escapeshellcmd( $command ), $output, $status );

		return static function ( $what = null ) use ( $output, $status ) {
			if ( null === $what || 'status' === $what ) {
				return (int) $status;
			}

			if ( 'string_output' === $what ) {
				return trim( implode( PHP_EOL, $output ) );
			}

			return $output;
		};
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
 * @return Closure The wp-cli pre-process, ready to accept an array of commands to run, the `wp` command is not required.
 */
function cli() {
	$service = is_ci() ? 'cli' : 'cli_debug';

	return docker_compose( [ '-f', 'dev/test/activation-stack.yml', 'run', $service, '--allow-root' ] );
}
