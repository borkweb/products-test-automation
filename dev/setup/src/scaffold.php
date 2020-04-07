<?php
/**
 * Functions to scaffold plugins for use.
 */

namespace Tribe\Test;

/**
 * Creates an .env.testing.tric if needed.
 *
 * @param string $plugin_path The plugin path.
 *
 * @return bool Whether or not the .env.testing.tric was created.
 */
function generate_tric_env( $plugin_path ) {
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

	return (bool) file_put_contents( $plugin_path . '/.env.testing.tric', $plugin_env );
}

/**
 * Creates a test_config.tric.php if needed.
 *
 * @param string $plugin_path The plugin path.
 *
 * @return bool Whether or not the test-config.php was created.
 */
function generate_test_config( $plugin_path ) {
	return (bool) file_put_contents( $plugin_path . '/test-config.tric.php', "<?php\ndefine( 'WP_PLUGIN_DIR', '/plugins' );" );
}

/**
 * Creates a codeception.yml if needed.
 *
 * @param string $plugin_path The plugin path.
 *
 * @return bool Whether or not the codeception.yml was created.
 */
function maybe_generate_codeception_yml( $plugin_path ) {
	if ( file_exists( $plugin_path . '/codeception.yml' ) ) {
		return false;
	}

	$codeception = <<< CODECEPTION
params:
  # read dynamic configuration parameters from the .env file
  - .env.testing.tric
modules:
  config:
    WPLoader:
      configFile: test-config.tric.php
CODECEPTION;

	return (bool) file_put_contents( $plugin_path . '/codeception.yml', $codeception );
}
