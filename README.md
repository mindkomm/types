# Custom Types

Custom Post Types and Taxonomy registration helper class for WordPress themes. Currently, only German language is supported.

## Installation

You can install the package via Composer:

```bash
composer require mindkomm/theme-lib-custom-types
```

## Usage

### Register a post type

```php
<?php

use Theme\Custom_Post_Type_Helper;

/**
 * Register post types for your theme.
 *
 * Pass a an array of arrays to the registration function.
 */
add_action( 'init', function() {
	Custom_Post_Type_Helper::register_post_types( [
		/**
		 * Always use an English lowercase singular name to name a post type.
		 */
		'example' => [
			'name_singular' => 'Example',
			'name_plural'   => 'Examples',
			'args'          => [
				/**
				 * For a list of possible menu-icons see
				 * https://developer.wordpress.org/resource/dashicons/
				 */
				'menu_icon'    => 'dashicons-building',
				'hierarchical' => false,
				'has_archive'  => false,
				'supports'     => [
					'title',
					'editor',
				],
				// Whether post is accessible in the frontend
				'public'       => false,
			],
		],
	] );
} );
```

### Register a taxonomy

```php
<?php

use Theme\Custom_Taxonomy_Helper;

/**
 * Register taxonomies for your theme.
 *
 * Pass a an array of arrays to the registration function.
 */
add_action( 'init', function() {
	Custom_Taxonomy_Helper::register_taxonomies( [
		/**
		 * Always use an English lowercase singular name to name a taxonomy.
		 */
		'example_tax' => [
			'name_singular'  => 'Example Category',
			'name_plural'    => 'Example Categories',
			// For which post types do you want to register this taxonomy?
			'for_post_types' => [ 'example' ],
			'args'           => [
				// Hide the meta box from the edit view
				// 'meta_box_cb' => false,
				//
				// Make it selectable in the navigation menus
				// 'show_in_nav_menus' => true,
			],
		],
	] );
} );
```

## Support

This is a library that we use at MIND to develop WordPress themes. You’re free to use it, but currently, we don’t provide any support. 
