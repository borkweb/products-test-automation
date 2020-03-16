<?php
/**
 * A bootstrap file that will load all the dev and setup functions.
 * Load this file in a PHP interactive shell with this command (from the project root directory):
 * php -a -d auto_prepend_file=dev/bootstrap.php
 */

require_once __DIR__ . '/setup/src/utils.php';
require_once __DIR__ . '/setup/src/pue.php';
require_once __DIR__ . '/setup/src/plugins.php';
require_once __DIR__ . '/setup/src/nightly.php';
require_once __DIR__ . '/setup/docker.php';
require_once __DIR__ . '/setup/wordpress.php';
require_once __DIR__ . '/setup/shell.php';

echo implode( PHP_EOL, [
	'The following source files have been loaded:',
	'* dev/setup/src/utils.php',
	'* dev/setup/src/pue.php',
	'* dev/setup/src/plugins.php',
	'* dev/setup/docker.php',
	'* dev/setup/wordpress.php',
	'* dev/setup/shell.php',
	PHP_EOL,
	'You will be able to use any of the functions defined in these files.',
	PHP_EOL
] );
