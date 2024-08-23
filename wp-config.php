<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'TestWork' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ':48P*Ijqu#gv4ZxEGz*0eYr8=KAe|Q*pno/}%ir15s p<C><c7h{7>NY8%Opq=k$' );
define( 'SECURE_AUTH_KEY',  'RxuPNB6N3=!3_>2pnS@7|Xqr66u6u.zjlvts;z37R/WyqNE|?N+@+Do(l!|h$M.!' );
define( 'LOGGED_IN_KEY',    '@GY}2$vvQ?H)wH5 qQ:oZ{)>6!L;q[dd<I]hN I<Q6<.bZp-[VBR<aC>?T0.*@z[' );
define( 'NONCE_KEY',        'Wm1yqEZhAbjF{%&u>ocT+-by1,22!GurlMXEg3jFc6lts-X83R--}_5Q%+F9;XKm' );
define( 'AUTH_SALT',        'W`v0L;QzL:<V?8ga@yEzn@LWvg&WOYiPMeneh-3#x/IFT;eMbE;NyLVgR38*Ods;' );
define( 'SECURE_AUTH_SALT', 'sJ|aeqkUGejBT|vE`,y;(9@gBjf6b4 sV$:;uuI{*LPyIcuVYtr?hQwsr@1C8 ;*' );
define( 'LOGGED_IN_SALT',   '^T-t!$aN/l(k/M<I97s=8le+EY4kin-j!RMVFU;p]A>,N5kd^y3mxt_=@!*.Hd4M' );
define( 'NONCE_SALT',       'm-ju(0<6`Aoe{R]/}c`OsFqQNwioEa-& NW*`c)t`l^=u=GvwX8q7.+3]4;%>~Pm' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'tblwp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
