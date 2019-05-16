<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

include __DIR__ . '/local-config.php';

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '~zimIL`89n]b:a7kIJ#65FoV<J]2,;:GkMT]n]aBPqbN0gw*q9N# 4`(Ib`N.Eju');
define('SECURE_AUTH_KEY',  '>7<RDriF&H^@a=KJ.L]2U8AJW>p#KI$<W?7:lW9bOuE/tP]qPs[jv:O0xT;uP~G`');
define('LOGGED_IN_KEY',    'r37Pr$17rJvKt[9^Y<.Zk6XcJJqI9n} U<X$@B}2E{W|xL^|y(SsNyl)vLn&;Hb9');
define('NONCE_KEY',        'MiS}kV)<Mbr7|b~LUIHN3d{eYi]&sQ)E60:`tN|[_NVV>LF !zV[-Vy#O(8(DK:i');
define('AUTH_SALT',        '5kG+es5/f[!DYnKoTxAMT)rNB*GoF|6671vTqDR/Y{z:qw9p{LAKF0%H#4*LxOM^');
define('SECURE_AUTH_SALT', '9|,3hgX}=O5*X.@biWzGJmb=/1AD/<)oSef )cgtu0tRSSEp`d.n;-#x|]m(qoB4');
define('LOGGED_IN_SALT',   'Y)%7 X(_oy]VXbx0{4[v,%v@`!y&K]7<$k:(OJxY3qA1zmhB|BV`i[B[HIJs)TV/');
define('NONCE_SALT',       'Wh+72?N-IYA?%s VlZTO06sZo}G1{kI=+o1QWcGiOm^b5Ls,#wh7@r7D,)9k>kii');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
