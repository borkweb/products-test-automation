<?php
/**
 * docker-compose wrapper functions.
 */

require_once __DIR__ . '/src/utils.php';

/**
 * Curried docker-compose wrapper.
 *
 * @param array<string> $options A list of options to initialize the wrapper.
 *
 * @return Closure A closure to actually call docker-compose with more arguments.
 */
function docker_compose( array $options = [] ) {
	setup_id();

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
