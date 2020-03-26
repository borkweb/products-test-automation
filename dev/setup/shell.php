<?php
/**
 * Interactive shell dedicated wrappers.
 */

namespace Tribe\Test;

/**
 * Executes a wp-cli command in the stack, echoes and returns its output.
 *
 * @param array $command The command to execute, e.g. `['plugin', 'list', '--status=active']`.
 * @param bool  $quiet   Whether to echo the command output or not.
 *
 * @return string The command output.
 */
function wp_cli( array $command = [ 'version' ], $quiet = false ) {
	$output = cli()( $command )( 'string_output' );
	if ( ! $quiet ) {
		echo $output;
	}

	return $output;
}
