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

	if ( 'Linux' === os() ) {
		$options = array_merge( [ '-f', 'dev/test/activation-stack-linux-override.yml' ], $options );
	}

	return static function ( array $command = [] ) use ( $options ) {
		$command = 'docker-compose ' . implode( ' ', $options ) . ' ' . implode( ' ', $command );

		echo "\nExecuting command: {$command}\n";

		exec( escapeshellcmd( $command ), $output, $status );

		return static function ( $what = null ) use ( $output, $status ) {
			if ( null === $what || 'status' === $what ) {
				return (int) $status;
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
