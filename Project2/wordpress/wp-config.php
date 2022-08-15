<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'project2' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', '' );

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
define( 'AUTH_KEY',         'ul7A^Y*)UF$(D&2#+j7I2U&?,{B>q3n6;fY:7^LB+WV>Hlc7wAsXN71Uo)} .=4k' );
define( 'SECURE_AUTH_KEY',  '.),DN;b!*t>5e,]H-RCetY8b&3[*6;=Xx8BpeZV|Bi;3$F~[3JmJ>Sz:LN:Ie/ku' );
define( 'LOGGED_IN_KEY',    'e6iUZdKl2EL).Y2eZ8}dk=[Y5U]XOl.b}c[[C[WB3YeWgcwj<-fhZoM=layx8W7}' );
define( 'NONCE_KEY',        '@[uQ2P}{Um=%Z_nRvY+({o%||bgnM}Hvr1i~Z#KmNt0/~J`2y~rw^3<UbO#.uHle' );
define( 'AUTH_SALT',        '>U1oi`1e<`=J7F46}|cqGWMZ:7QhI{Yi6]mF4.7S ?N{&amYgY]QpK{O}A<n`J=F' );
define( 'SECURE_AUTH_SALT', '@qQ^,V%p<c+94):i.m{{>z(D1j!rDjfFM4s_jhkNj2O<iLYK/H>a>-}Q[&[w36uR' );
define( 'LOGGED_IN_SALT',   '@-~lX^l|<49yD90WA>9/(n&bE@<E=~[FGe`3V[{Al$AEuf>P]kC3hyW#:/1S9Is/' );
define( 'NONCE_SALT',       '&<u]`{]*VO#nO-}X,vX^z+G(srIrofi|@bj2i=5JIUH>^$ARC5w$;B8q,XFt{^%?' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
