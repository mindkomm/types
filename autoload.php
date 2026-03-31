<?php

use n5s\WpHookKit\Hook;

Hook::addAction('after_setup_theme', static function() {
	require_once 'lib/functions.php';

	load_theme_textdomain( 'mind/types', __DIR__ . '/languages/types-' . determine_locale() . '.mo' );
}, 1);
