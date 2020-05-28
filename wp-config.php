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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'root' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'B/jZUAZqn#1$FItyZ$:Xv=H&+{8_n|~_+PaebHpJf!s[Wnrb[>(-;!]I/qD=izn[' );
define( 'SECURE_AUTH_KEY',  'gb^KR2Q~5WA_qM5OqczBvcuKE?yaHNM,IdB#`5hLuHTs.RLZ8h;gqA&V&p?NhoOm' );
define( 'LOGGED_IN_KEY',    'NZEhjVRam>YAMxcV39FVf8`VmCU4W9H`8l9$j k.SY Mrc)OM1y-}?0S%GA3TwO1' );
define( 'NONCE_KEY',        '6v&d:P-i`oR:BtwX)Vp#dUa9GuGh*GZ7p:qM-F{jg|hbGU?1{hze7q<+=3 Q77)a' );
define( 'AUTH_SALT',        '>~FZl>~  `DQHQr`FIOX|0[QwA3$rUf80BOpCE76ww:~Qup9KJ5#O;AXfXrA%_^^' );
define( 'SECURE_AUTH_SALT', 'J0GzS[o*oxwb@L(whYDe+g&3Z[&<G|`Q/?pa/U{owXn`6TE#C{/s!BX,C?xjh1>N' );
define( 'LOGGED_IN_SALT',   'apIv<mh!Wk#Ta%9tl~3&x7vKnVFSNDv|*0G__rrGox,FlN&]Ym5%E%:CI|8(Y~}-' );
define( 'NONCE_SALT',       '>s3zB?ZTq3`sA!-VS#.E[:#_Mbgn(19iTCXi24!wF<I3(tOY;jX2y8/FTs.DpCB ' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'w_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
