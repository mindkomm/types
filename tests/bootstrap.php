<?php
/**
 * Testing using Mantle Testkit.
 *
 * @link https://mantle.alley.com/docs/testing/testkit
 */

// Install WordPress for the tests inside the package instead of the shared /tmp/wordpress.
putenv( 'WP_CORE_DIR=' . dirname( __DIR__ ) . '/tmp/wordpress' );

/*
 * When this package is installed inside a WordPress project (e.g. in a theme's vendor folder),
 * Mantle would run the tests against the surrounding WordPress installation and load the
 * project's Composer autoloader in its installation subprocess. Use a separate WordPress
 * installation and the package's own autoloader instead.
 */
if ( str_contains( __DIR__, '/wp-content/' ) ) {
	putenv( 'WP_CONTENT_DIR_NAME=__types-tests__' );

	define(
		'WP_PHP_BINARY',
		escapeshellarg( PHP_BINARY ) . ' -d auto_prepend_file=' . escapeshellarg( dirname( __DIR__ ) . '/vendor/autoload.php' )
	);
}

/*
 * Mantle installs the SQLite Database Integration plugin from its outdated main branch, which
 * causes deprecation notices on PHP 8.5. Put the latest stable release into the download cache of
 * the installation script, so that it’s used instead.
 *
 * @link https://github.com/alleyinteractive/mantle-ci/blob/HEAD/install-wp-tests.sh
 */
if ( ! file_exists( dirname( __DIR__ ) . '/tmp/wordpress/wp-load.php' ) ) {
	$cache_dir = dirname( __DIR__ ) . '/tmp/cache';

	if ( ! is_dir( $cache_dir ) ) {
		mkdir( $cache_dir, 0777, true );
	}

	$sqlite_plugin = file_get_contents( 'https://downloads.wordpress.org/plugin/sqlite-database-integration.latest-stable.zip' );

	if ( false !== $sqlite_plugin ) {
		file_put_contents( $cache_dir . '/sqlite-database-integration-main.zip', $sqlite_plugin );
		putenv( 'CACHEDIR=' . $cache_dir );
	}
}

\Mantle\Testing\manager()
	->with_sqlite()
	->install();
