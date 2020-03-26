<?php
/**
 * tric cli functions.
 */

namespace Tribe\Test;

/**
 * Returns the content of the .using file in the root directory.
 *
 * @param string $root_dir The directory to search the .using file into.
 *
 * @return string The using file contents.
 */
function using( $root_dir ) {
	$using_file = using_file( $root_dir );
	$using      = getenv( 'TRIC_CURRENT_PROJECT' );
	if ( empty( $using ) ) {
		if ( ! file_exists( $using_file ) ) {
			echo "\033[31mNo target set; call the 'use' sub-command to set one.\033[0m\n";
			exit( 1 );
		}

		$using = file_get_contents( $using_file );

		if ( false === $using ) {
			echo "\033[31mCannot read {$using_file}; check the file permissions.\033[0m\n";
			exit( 1 );
		}

		$using = trim( $using );
		putenv( 'TRIC_CURRENT_PROJECT=' . $using );
	}

	return $using;
}

/**
 * Writes the .using file.
 *
 * @param string $root_dir The directory to write the .using file to.
 * @param string $target   The target to write in the .using file.
 */
function write_using( $root_dir, $target ) {
	$using_file = using_file( $root_dir );
	$put        = file_put_contents( $using_file, $target );
	if ( false === $put ) {
		echo "\033[31mCould not write {$using_file}!\033[0m\n";
		exit( 1 );
	}
}

/**
 * Returns the path of the .using file.
 *
 * @param string $root_dir The directory to search the .using file into.
 *
 * @return string The absolute path to the .using file.
 */
function using_file( $root_dir ) {
	$using_file = $root_dir . '/.using';

	return $using_file;
}

/**
 * Checks a specified target exists in the `dev/_plugins` directory.
 *
 * @param string $target The target to check in the `dev/_plugins` directory.
 */
function ensure_dev_plugin( $target ) {
	$targets     = array_keys( dev_plugins() );
	$targets_str = implode( ', ', $targets );

	if ( false === $target ) {
		echo "\033[31mThis command needs a target argument; available targets are: ${targets_str}\033[0m\n";
		exit( 1 );
	}

	if ( ! in_array( $target, $targets, true ) ) {
		echo "\033[31m'{$target}' is not a valid target; available targets are: ${targets_str}\033[0m\n";
		exit( 1 );
	}
}
