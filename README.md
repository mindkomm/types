# Types

Custom Post Types and Taxonomy helper classes for WordPress themes.

- Register Custom Post Types and Taxonomies through an array notation. Labels will be set accordingly. Currently, only German labels are supported.
- Change the order of posts in the front- and backend via arguments passed in the registration.
- Change the admin columns for posts in the backend via arguments passed in the registration.
- Make it possible to set post slugs dynamically.

## Installation

You can install the package via Composer:

```bash
composer require mindkomm/types
```

## Register post types

```php
<?php

use Types\Post_Type;

/**
 * Register post types for your theme.
 *
 * Pass a an array of arrays to the registration function.
 */
add_action( 'init', function() {
    Post_Type::register( [
        // Always use an English lowercase singular name to name a post type.
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
            'admin_columns' => [
                'date' => false,
            ],
        ],
    ] );
} );
```

### Options

The `args` parameter is used for the arguments that are passed to `register_post_type`. The `name_singular` and `name_plural` parameters are used for the generating the labels in the backend.

You can use more options:

#### query

Arguments that are used for quering this post type in the back- and frontend. Use this to define the sort order. Here’s an example for a post type `event`, where we want to order the posts by the value of a custom field named `date_start`.

```php
'query' => [
    'meta_key' => 'date_start',
    'orderby'  => 'meta_value_num',
    'order'    => 'DESC',
],
```

#### admin_columns

Arguments that are used to add and remove admin columns in the backend. Pass an associative array of column names with arguments. The column name is the name of the meta field you want to display. You can pass `false` if you want to disable an existing column.

- **title** – The title to use for the column.
- **transform** – The function to use on the value that is displayed. The function defined here will get a `$value` parameter that you can transform. E.g., if you have a post ID saved in post meta, you could display the post’s title.

Here’s an example for a Custom Post Type `event`.

```php
'admin_columns' => [
    'date'       => false,
    'date_start' => [
        'title'     => 'Start Date',
        'transform' => function( $value ) {
            return date_i18n(
                get_option( 'date_format' ),
                DateTime::createFromFormat( 'Ymd', $value )->getTimeStamp()
            );
        },
    ],
    'location'   => [
        'title'     => 'Ort',
        'transform' => function( $value ) {
            return get_the_title( $value );
        },
    ],
],
```

If you need more possibilities for defining admin columns you could use the fantastic [Admin Columns](https://www.admincolumns.com/) plugin.

## Register taxonomies

```php
<?php

use Types\Taxonomy;

/**
 * Register taxonomies for your theme.
 *
 * Pass a an array of arrays to the registration function.
 */
add_action( 'init', function() {
    Taxonomy::register( [
        // Always use an English lowercase singular name to name a taxonomy.
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

### Options

The `args` parameter is used for the arguments that are passed to `register_taxonomy`. Use the `for_post_types` parameter to assign taxonomies to certain post types. The `name_singular` and `name_plural` parameters are used for the generating the labels in the backend.

## Customize a post slug

Sometimes you want to overwrite the post slug when a post is saved. Possible use cases:

- Common post duplication plugin often only add `-copy` or `-2` as a suffix to the post slug. You want to use your own pattern.
- Multiple posts have the same name, but differ in the meta data they display. For example, event posts could have a different date. You want to add the date of the event to the post slug automatically.

To initalize, you’ll need to create an instance of `Post_Slug` and call the `init()` function:

```php
$post_slugs = new Types\Post_Slug();
$post_slugs->init();
```

Here’s an example for a custom post slug for a post type `course`, where you want the permalink to be built from the post title. In this scenario, the post title would be a course number.

```php
$post_slugs = new Types\Post_Slug();
$post_slugs->init();

$post_slugs->register( [
    'course' => function( $post_slug, $post_data, $post_id ) {
        return $post_data['post_title'];
    },
] );
```

You don’t have to use `sanitize_title` in the callback, because the class uses that function internally.

Here’s another example for the event post mentioned earlier:

```php
$post_slugs->register_suffix_date( [
	'event' => [
		'meta_key'     => 'date_start',
	],
] );
```

The `register_suffix_date` function is a special function that makes it easier to append a date taken from a post’s meta data and append it to the slug. The function takes an associative array of post types and their args for the function:

- **meta_key** – Used to define the name of the meta key that is used. Default `date_start`.
- **input_format** – Defines the date format of the meta value. Default `Ymd`.
- **output_format** – Defines the output date format that should be used for the suffix. Default `Y-m-d`.

## Support

This is a library that we use at MIND to develop WordPress themes. You’re free to use it, but currently, we don’t provide any support.
