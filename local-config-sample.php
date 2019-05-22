<?php

/**
 * Local config file for configuring the local environment
 */

// Use whatever URL is being used
if ( filter_has_var( INPUT_SERVER, 'HTTP_HOST' ) ) {
	if ( ! defined( 'WP_HOME' ) ) {
		define( 'WP_HOME', 'http://' . $_SERVER['HTTP_HOST'] );
	}
	if ( ! defined( 'WP_SITEURL' ) ) {
		define( 'WP_SITEURL', 'http://' . $_SERVER['HTTP_HOST'] );
	}
}

// WP installed as dependency config
define('WP_CONTENT_DIR', __DIR__ . '/wp-content');
define('WP_CONTENT_URL', WP_SITEURL . '/wp-content/');
define('ABSPATH', __DIR__ . '/wp/');

// Debug config
define('WP_DEBUG'           , false );
define('WP_DEBUG_LOG'       , false );
define('WP_DEBUG_DISPLAY'   , false );
define('SAVEQUERIES'        , false );
define('SCRIPT_DEBUG'       , false );
define('CONCATENATE_SCRIPTS', false );
define('COMPRESS_SCRIPTS'   , false );
define('COMPRESS_CSS'       , false );

// ** DB config ** //
/** The name of the database for WordPress */
define('DB_NAME', 'tribe_events_test');

/** MySQL database username */
define('DB_USER', 'user');

/** MySQL database password */
define('DB_PASSWORD', 'password');

/** MySQL hostname */
define('DB_HOST', 'localhost:3306');


