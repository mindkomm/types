# Types

Custom Post Types and Taxonomy helper classes for WordPress projects.

- Register Custom Post Types and Taxonomies through an array notation. Labels will be set accordingly. Currently, English and German languages are supported.
- Change the query arguments for posts in the front- and backend via a list of arguments, e.g. to set a custom post order.
- Change the admin columns for posts in the backend via a list of arguments.
- Set post slugs dynamically when posts are saved.

## Installation

You can install the package via Composer:

```bash
composer require mindkomm/types
```

## Register post types

```php
<?php

/**
 * Register post types for your theme.
 *
 * Pass a an array of arrays to the registration function.
 */
add_action( 'init', function() {
    Types\Post_Type::register( [
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

Arguments that are used for quering this post type in the back- and frontend. You can use this to define the sort order. Here’s an example for a post type `event`, where we want to order the posts by the value of a custom field named `date_start`.

```php
'query' => [
    'meta_key' => 'date_start',
    'orderby'  => 'meta_value_num',
    'order'    => 'DESC',
],
```

If you want to have different queries for the front- and the backend, you can use separate `frontend` and `backend` keys:

```php
'query' => [
    'frontend' => [
        'meta_key' => 'date_start',
        'orderby'  => 'meta_value_num',
        'order'    => 'ASC',
    ],
    'backend'  => [
        'meta_key' => 'date_start',
        'orderby'  => 'meta_value_num',
        'order'    => 'DESC',
    ],
],
```

If you only use one key and omit the other, then the query will only be applied to your choice.

#### admin_columns

Arguments that are used to add and remove admin columns in the backend. Pass an associative array of column names with arguments. The column name is the name of the meta field you want to display.

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
        'sortable'  => false,
        'transform' => function( $post_id ) {
            return get_the_title( $post_id );
        },
    ],
],
```

You can pass `false` if you want to disable an existing column. These are the possible arguments:

- **title** – *(string)* The title to use for the column. Default empty.
- **transform** – *(callable)* The function to use on the value that is displayed. The function defined here will get a `$value` parameter that you can transform. E.g., if you have a post ID saved in post meta, you could display the post’s title. Default `null`.
- **type** – *(string)* The type for the column. You can set this to `acf` if you use Advanced Custom Fields and want to apply its filters to the value. Default `default`.
- **sortable** – *(bool)* Whether the column is sortable. Default `true`.

##### thumbnail

Use this key to display the featured image thumbnail for a post.

```php
'location' => [
    'thumbnail' => true,
],
```

You can also set the width and height. The defaults are `80` &times; `80` pixels.

```php
'location' => [
    'thumbnail'    => [
        'width'  => 100,
        'height' => 100,
    ],
],
```

If you need more possibilities for defining admin columns you could use the fantastic [Admin Columns](https://www.admincolumns.com/) plugin.

## Update existing post types

### Change settings for a post type

Use the `update()` function to change the settings for an existing post type. Here’s an example for changing the settings for posts to make them not directly accessible in public.

**functions.php**

```php
Types\Post_Type::update( [
    'post' => [
        'args' => [
            'public'            => false,
            'show_ui'           => true,
            'show_in_nav_menus' => true,
        ],
        'admin_columns' => [
            'date' => false,
        ],
    ],
] );
```

The `update()` function accepts a post type as the first parameter and an array of settings to update as the second parameter. Make sure you use this function before the `init` hook.

### Rename a post type

Sometimes you might want to rename an existing post type to better reflect what it’s used for.

**functions.php**

```php
Types\Post_Type::rename( 'post', 'Beispiel', 'Beispiele' );
```

The `rename()` function accepts a post type as the first parameter, the new singular name of the post type as a second parameter and the plural name of the post type as a third parameter. If you omit the third parameter, the second parameter will be used as the plural form instead. Make sure you use this function before the `init` hook.

This is practically a shorthand function for:

```php
Types\Post_Type::update( [
    'post' => [
        'name_singular' => 'Beispiel',
        'name_plural'   => 'Beispiele,
    ],
] );
```

If you only want to rename one of the labels, e.g. the menu label, you can use the `post_type_labels_{$post_type}` filter. Here’s an example for changing the menu name for posts:

```php
add_filter( 'post_type_labels_post', function( $labels ) {
    $labels->menu_name = 'Aktuelles';

    return $labels;
}, 11 );
```

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

## Change admin column settings for existing post type

To change the admin column settings for existing post types like `post` or `page`, you can use the `admin_columns()` function, which accepts an associative of post types and their admin column settings.

Here’s an example that disables the date, comments and author columns and adds a thumbnail instead:

```php
Types\Post_Type::admin_columns( [
    'page' => [
        'date'         => false,
        'comments'     => false,
        'author'       => false,
        'thumbnail'    => [
            'width'  => 80,
            'height' => 80,
        ],
    ],
] );
```

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
        'meta_key' => 'date_start',
    ],
] );
```

The `register_suffix_date` function is a special function that makes it easier to append a date taken from a post’s meta data and append it to the slug. The function takes an associative array of post types and their args for the function:

- **meta_key** – Used to define the name of the meta key that is used. Default `date_start`.
- **input_format** – Defines the date format of the meta value. Default `Ymd`.
- **output_format** – Defines the output date format that should be used for the suffix. Default `Y-m-d`.

## Support

This is a library that we use at MIND to develop WordPress themes. You’re free to use it, but currently, we don’t provide any support.
