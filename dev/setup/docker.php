<?php
/**
 * docker-compose wrapper functions.
 */

require_once __DIR__ . '/src/utils.php';

function docker_compose( array $options = [] ) {
	setup_id();

	return static function ( array $command = [] ) use ( $options ) {
		$command = 'docker-compose ' . implode( ' ', $options ) . ' ' . implode( ' ', $command );

		echo "\nExecuting command: {$command}";

		exec( escapeshellcmd( $command ), $output, $status );

		return static function ( $what = null ) use ( $output, $status ) {
			if ( null === $what || 'status' === $what ) {
				return (int) $status;
			}

			return $output;
		};
	};
}

function wordpress_container_root_dir( $path = '/' ) {
	return '/var/www/html/' . ltrim( $path, '\\/' );
}
