<?php
/**
 * Process related files and functions.
 */

namespace Tribe\Test;

/**
 * Runs a process and returns a closure that allows getting the status or output from it.
 *
 * @param string $command The command to run, in string format.
 *
 * @return \Closure A closure that will take will return the process status, output as an array or output as a
 *                 string using the keys 'status', 'output', 'string_output' respectively.
 */
function process( $command ) {
	debug( "Executing command: {$command}\n" );

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
}

/**
 * Runs a process in realtime, displaying its output.
 *
 * @param string $command The command to run.
 *
 * @return int The process exit status, `0` means ok.
 */
function process_realtime( $command ) {
	debug( "\nExecuting command: {$command}\n" );

	passthru( escapeshellcmd( $command ), $status );

	return (int) $status;
}
