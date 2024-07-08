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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          'duE39XwM./2?w)D$(-#pAU*5zg|lCmt1l1Mk*eM!=W-a6Q2Q%3{=8LR2qrjja4yB' );
define( 'SECURE_AUTH_KEY',   '_W&)84sk@@IM3`&wfp7otUbAhU]7{;J_]3WflbY0GX%U~X>J:pdU!Rq,QxVmC-v-' );
define( 'LOGGED_IN_KEY',     '0iAN5)xcpP,WZ8F~hmC9|9-H?.2L)9;Q0NX,~*b5$5Vx4Alfny~ET<BFQHa,ikNN' );
define( 'NONCE_KEY',         'E6Bls5Tu|_YNx0C`CqP*p)y^*RlNl7nw{8|(K4[$/Cj+K4fb`_)qu=$_EyJv,/Jy' );
define( 'AUTH_SALT',         '{99vz1J%~=uep9d|MkW^QD(OSNg?/dq:?]23-r+lJVFDK5MUpm)^jqfIHlk>a#Hu' );
define( 'SECURE_AUTH_SALT',  'vviV>gT&n6:~=rMs,S,vXs^:^qth2PmTeTH9Rsd;1M&bo#|P(]VS6_8TSPg?) n?' );
define( 'LOGGED_IN_SALT',    'cPVns|^F3tfmjwP?&vsltjE5J?Yi`~^W3sbblgs2Q%CQNEPXv;iY$GG.>mU$BK.d' );
define( 'NONCE_SALT',        'NChbK&l:j,+Gj]9xYO4{tI%)%ib]5HLmpE3e`-f;[{Q/`uz2b023GtQjEPAo+p@1' );
define( 'WP_CACHE_KEY_SALT', 'I2`7POqtjMov1wdZ3|:aO|p]50%X}S?gqTyc`Z2@Odg[K=&fiA&uYq#dqS%VdODV' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
