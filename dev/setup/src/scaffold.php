<?php
/**
 * Functions to scaffold plugins for use.
 */

namespace Tribe\Test;

/**
 * Creates an `.env.testing.tric` file, if one exists it will be overwritten.
 *
 * @param string $plugin_path The plugin path.
 *
 * @return bool Whether or not the .env.testing.tric was created.
 */
function write_tric_env_file( $plugin_path ) {
	$mysql_root_password = getenv( 'MYSQL_ROOT_PASSWORD' );
	$wp_http_port        = getenv( 'WORDPRESS_HTTP_PORT');
	$plugin_env          = file_get_contents( $plugin_path . '/.env' );

	$strings = [
		'/WP_ROOT_FOLDER=.*/'      => 'WP_ROOT_FOLDER=/var/www/html',
		'/WP_URL=.*/'              => 'WP_URL=http://localhost:' . $wp_http_port,
		'/WP_DOMAIN=.*/'           => 'WP_DOMAIN=localhost:' . $wp_http_port,
		'/WP_DB_PORT=.*/'          => 'WP_DB_PORT=3306',
		'/WP_DB_HOST=.*/'          => 'WP_DB_HOST=db',
		'/WP_DB_NAME=.*/'          => 'WP_DB_NAME=test',
		'/WP_DB_PASSWORD=.*/'      => 'WP_DB_PASSWORD=' . $mysql_root_password,
		'/WP_TEST_DB_HOST=.*/'     => 'WP_TEST_DB_HOST=db',
		'/WP_TEST_DB_NAME=.*/'     => 'WP_TEST_DB_NAME=test',
		'/WP_TEST_DB_PASSWORD=.*/' => 'WP_TEST_DB_PASSWORD=' . $mysql_root_password,
		'/CHROMEDRIVER_HOST=.*/'   => 'CHROMEDRIVER_HOST=chrome',
		'/WP_CHROMEDRIVER_URL=.*/' => 'WP_CHROMEDRIVER_URL="wordpress.test"',
	];

	$plugin_env = preg_replace( array_keys( $strings ), $strings, $plugin_env );
	$plugin_env .= "\n# We're using Docker to run the tests.\nUSING_CONTAINERS=1\n";

	$file = $plugin_path . '/.env.testing.tric';
	$put =  file_put_contents( $file, $plugin_env );

	if ( false === $put ) {
		echo magenta( "Could not write {$file}; please check the directory exists and is writeable.\n" );
		exit( 1 );
	}
}

/**
 * Returns the lines that should be written to a `tests-config.php` file for tric to work correclty.
 *
 * @param array<string,string> $overrides A map of lines to write, where the key is the type of entry and the value are
 *                                        the lines to write for that entry.
 *                                        E.g. `[ 'define_plugins_dir' => "define( 'WP_PLUGIN_DIR', '/plugins' );" ]`.
 *
 * @return array<string,string> A map of the lines to write.
 */
function get_tric_test_config_lines( array $overrides = [] ) {
	$defaults = [];

	return array_merge( $defaults, $overrides );
}

/**
 * Creates a `test_config.tric.php` file, if one exists it will be overwritten.
 *
 * The function will not write anything if there are no test config lines to write.
 *
 * @param string               $plugin_path  The plugin path.
 * @param array<string,string> $config_lines A map of lines to write, where the key is the type of entry and the value
 *                                           are the lines to write for that entry.
 *                                           E.g. `[ 'define_plugins_dir' => "define( 'WP_PLUGIN_DIR', '/plugins' );" ]`.
 *
 * @return bool Whether or not the test-config.php was created.
 */
function write_tric_test_config( $plugin_path, array $config_lines = [] ) {
	$file = $plugin_path . '/test-config.tric.php';

	$test_config_lines = get_tric_test_config_lines( $config_lines );

	if ( empty( $test_config_lines ) ) {
		// There's no need for a tric test config file, let's skip this.
		return false;
	}

	$put = file_put_contents( $file, "<?php\n" . implode( "\n", $test_config_lines ) );

	if ( false === $put ) {
		echo magenta( "Could not write {$file}; please check the directory exists and is writeable.\n" );
		exit( 1 );
	}

	return true;
}

/**
 * Creates a codeception.yml if needed.
 *
 * @param string $plugin_path The plugin path.
 *
 * @return bool Whether or not the codeception.yml was created.
 */
function write_codeception_config( $plugin_path ) {
	$file = $plugin_path . '/codeception.yml';

	if ( file_exists( $file ) ) {
		return false;
	}

	$codeception = <<< CODECEPTION_LOCAL_CONFIG
params:
  # read dynamic configuration parameters from the .env file
  - .env.testing.tric
CODECEPTION_LOCAL_CONFIG;

	$test_config_lines = get_tric_test_config_lines();

	if ( ! empty( $test_config_lines ) ) {
		// Add a section for a custom test configuration file only if required.
		$wploader_test_config = <<< WPLOADER_TEST_CONFIG
modules:
  config:
    WPLoader:
      configFile: test-config.tric.php
WPLOADER_TEST_CONFIG;

		$codeception .= $wploader_test_config;
	}

	$put =  file_put_contents( $file, $codeception );

	if ( false === $put ) {
		echo magenta( "Could not write {$file}; please check the directory exists and is writeable.\n" );
		exit( 1 );
	}

	return true;
}
