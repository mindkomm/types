# Custom Types

Custom Post Types and Taxonomy registration helper class for WordPress themes. Currently, only German language (in the backend) is supported.

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
            'admin_columns' => [
                'date' => false,
            ],
        ],
    ] );
} );
```

#### Options

The `args` parameter is used for the arguments that are passed to `register_post_type`. The `name_singular` and `name_plural` parameters are used for the generating the labels in the backend.

You can use more options:

##### query

Arguments that are used for quering this post type in the back- and frontend. Use this to define the sort order. Here’s an example for a post type `event`, where we want to order the posts by the value of a custom field named `date_start`.

```php
'query' => [
    'meta_key' => 'date_start',
    'orderby'  => 'meta_value_num',
    'order'    => 'DESC',
],
```

##### admin_columns

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

#### Options

The `args` parameter is used for the arguments that are passed to `register_taxonomy`. Use the `for_post_types` parameter to assign taxonomies to certain post types. The `name_singular` and `name_plural` parameters are used for the generating the labels in the backend.

## Support

This is a library that we use at MIND to develop WordPress themes. You’re free to use it, but currently, we don’t provide any support.
