<?php

if (function_exists('load_textdomain')) {
	load_textdomain( 'mind/types', __DIR__ . '/languages/types-' . get_locale() . '.mo' );
}
