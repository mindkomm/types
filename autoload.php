<?php

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

require_once 'lib/functions.php';

load_textdomain( 'mind/types', __DIR__ . '/languages/types-' . determine_locale() . '.mo' );
