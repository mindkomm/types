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

\Mantle\Testing\manager()
	->with_sqlite()
	->install();
