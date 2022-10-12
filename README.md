# Types

Custom Post Types and Taxonomy helper classes for WordPress projects.

- Register Custom Post Types and Taxonomies through an array notation. Labels will be set accordingly. Currently, English, German and Dutch are supported.
- Change the query arguments for posts in the front- and backend via a list of arguments, e.g. to set a custom post order wit the [`query`](#query) option.
- Change the admin columns for posts in the backend via a list of arguments.
- Set a specific page as the archive page for a custom post type with the [`page_for_archive`](#page_for_archive) option.
- Set post slugs dynamically when posts are saved.

## Table of Contents

<!-- TOC -->

- [Table of Contents](#table-of-contents)
- [Installation](#installation)
- [Register post types](#register-post-types)
    - [query](#query)
    - [admin_columns](#admin_columns)
        - [Type](#type)
        - [The `meta` and `acf` types](#the-meta-and-acf-types)
        - [The `thumbnail` type](#the-thumbnail-type)
        - [The `image` type](#the-image-type)
        - [Existing columns and column order](#existing-columns-and-column-order)
    - [page_for_archive](#page_for_archive)
        - [is_singular_public](#is_singular_public)
        - [customizer_section](#customizer_section)
        - [show_post_state](#show_post_state)
        - [Use page in template](#use-page-in-template)
- [Update existing post types](#update-existing-post-types)
    - [Change settings for a post type](#change-settings-for-a-post-type)
    - [Change post type support](#change-post-type-support)
    - [Rename a post type](#rename-a-post-type)
- [Change admin column settings for existing post type](#change-admin-column-settings-for-existing-post-type)
- [Register taxonomies](#register-taxonomies)
- [Update existing taxonomies](#update-existing-taxonomies)
    - [Change settings for a taxonomy](#change-settings-for-a-taxonomy)
    - [Rename a taxonomy](#rename-a-taxonomy)
    - [Unregister taxonomies](#unregister-taxonomies)
- [Customize a post slug](#customize-a-post-slug)
- [Support](#support)

<!-- /TOC -->

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

The `args` parameter is used for the arguments that are passed to `register_post_type`. The `name_singular` and `name_plural` parameters are used for the generating the labels in the backend.

You can use more options:

### query

Arguments that are used for querying this post type in the back- and frontend. You can use this to define the sort order. Here’s an example for a post type `event`, where we want to order the posts by the value of a custom field named `date_start`.

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

### admin_columns

Arguments that are used to add and remove admin columns in the backend. Pass an associative array of column names with arguments.

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
        'title'      => 'Location',
        'sortable'   => true,
        'searchable' => true,
        'transform'  => function( $post_id ) {
            return get_the_title( $post_id );
        },
    ],
    'width' => [
        'title'    => 'Width',
        'sortable' => true,
        'orderby'  => 'meta_value_num',
    ],
],
```

These are the possible arguments:

- **title** – *(string)* The title to use for the column. Default empty.
- **transform** – *(callable)* The function to use on the value that is displayed. The function defined here will get a `$value` parameter that you can transform. E.g., if you have a post ID saved in post meta, you could display the post’s title. Default `null`.
- **type** – *(string)* The type for the column. One of `meta`, `acf`, `thumbnail`, `image` or `custom`. Default `meta`.
- **sortable** – *(bool)* Whether the column is sortable. Default `false`.
- **orderby** – *(string)* What to order by when the `sortable` argument is used. You don’t need to provide a `meta_key` parameter, because it is automatically set. Default `meta_value`.
- **searchable** – *(bool)* Whether the column is searchable. Will include the meta values when searching the post list. Only applied if using the default type `meta`. Default `false`.
- **column_order** – *(int)* An order number to sort by. You can use this to change the order of your columns. Default `10`.

If you need more possibilities for defining admin columns you could use the fantastic [Admin Columns](https://www.admincolumns.com/) plugin.

#### Type

The `type` argument defines how your column is interpreted. The following types exist:

- `meta`
- `acf`
- `thumbnail`
- `image`
- `custom`

#### The `meta` and `acf` types

With the `meta` type, the column name is the name of the meta field you want to display.

You can also use `acf` as a type you use Advanced Custom Fields and want to apply its filters to the value.

#### The `thumbnail` type

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

#### The `image` type

The `type` allows you to display an image other than the featured image. This type will also use the key of the column to get an **attachment ID** from the post’s meta values.

```php
'admin_columns' => [
    'profile_image' => [
        'title'  => 'Profile image',
        'type'   => 'image',
    ],
],
```

By default, it will display the `thumbnail` size. If you want to request a different size, use the `image_size` parameter.

```php
'admin_columns' => [
    'profile_image' => [
        'title'      => 'Profile image',
        'type'       => 'image',
        'image_size' => 'medium',
    ],
],
```

And if you want to restrict the width and the height of the image, you can provide pixel values for `width` and `height`.

```php
'admin_columns' => [
    'profile_image' => [
        'title'  => 'Profile image',
        'type'   => 'image',
        'width'  => 100,
        'height' => 100,
    ],
],
```

#### The `custom` type

If you want to do something custom, you can use the `custom` type. If you use the `custom` type, you need to provide a `value`, which is a callback function that receives the post’s ID as a single parameter.

In this example, we call a function that extracts an attribute value from a certain block of the post.

```php
'email' => [
    'title' => __( 'Email', 'theme-module-teammember' ),
    'type'  => 'custom',
    'value' => function( $post_id ) {
        return $this->get_block_attribute( $post_id, 'email' );
    },
],
```

#### Existing columns and column order

You can also update or hide existing columns. Existing columns can be updated with the `title` and the `column_order` argument.

If you want to the change the title of an existing column, use the `title` attribute.

```php
'admin_columns' => [
    'title' => [
        'title' => 'Event title',
    ],
],
```

If you want to move the column, you can use the `column_order` argument. In this example, we would move the `date` column to the end.

```php
'admin_columns' => [
    'date' => [
        'column_order' => 100,
    ],
],
```

The default order is `10`. So if you wanted to move a column like the thumbnail to the start, you could use `5`.

```php
'admin_columns' => [
    'thumbnail' => [
        'column_order' => 5,
    ],
],
```

To hide an existing column, you can use `false`.

```php
'admin_columns' => [
    'date' => false,
],
```

### page_for_archive

The `page_for_archive` option allows you to set a specific page as the archive page for a custom post type:

```php
'event' => [
    'args' => [
        'public' => true,
    ],
    'page_for_archive' => [
        'post_id'            => get_option( 'page_for_event' ),
        'is_singular_public' => false,
    ],
],
```

In this example, the ID for the page that’s saved in the `page_for_event` option will act as the archive page for the `event` post type.

You need to **flush your permalinks** whenever you make changes to this option.

Behind the curtains, Types uses the `has_archive` option when registering a post type and sets the slug of the page you passed in the `page_for_archive` option.

#### is_singular_public

The `is_singular_public` option allows you to set, whether singular templates for this post type should be accessible in the frontend. Singular template requests will then be redirected to the archive page. We can’t use the `public` or `publicly_queryable` option for this, because then the archive page wouldn’t work either.

#### customizer_section

If you want Types to register an option to select the page you want to use as your archive in the Customizer, you can use the `customizer_section` argument:

```php
'event' => [
    'page_for_archive' => [
        'post_id'            => get_option( 'page_for_event' ),
        'customizer_section' => 'event',
    ],
],
```

With `customizer_section`, you can define in which Customizer section the option should be displayed. This needs to be an existing section. This way, you can decide yourself whether you want to have a separate Customizer section for each custom post type, or whether you want to list all of your custom post type pages in the same section.

#### show_post_state

Types will display a post state in the pages overview for the page that you selected. If you want to disable this functionality, use the `show_post_state` option.

```php
'event' => [
    'page_for_archive' => [
        'post_id'    => get_option( 'page_for_event' ),
        'show_post_state' => false,
    ],
],
```

#### Use page in template

To make use of that page, you will use it in your archive page where you can now use your page as the main post.

**archive-event.php**

```php
$post = get_post( get_option( 'page_for_event' ) );
```

## Update existing post types

### Change settings for a post type

Use the `update()` function to change the settings for an existing post type. Here’s an example for changing the settings for posts to make them not directly accessible in the frontend.

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

The `update()` function accepts an associative array of post types and their arguments. Make sure you use this function before the `init` hook.

### Change post type support

Please be aware that it’s not possible to change post type support features through the `update()` function. To remove support for an existing feature, you will have to use the `remove_post_type_support` function.

```php
add_action( 'init', function() {
    remove_post_type_support( 'post', 'thumbnail' );
} );
```

In the same manner, if you want to add features, you should do it through the `add_post_type_support` function:

```php
add_post_type_support( 'page', 'excerpt' );
```

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
        'name_plural'   => 'Beispiele',
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

## Change admin column settings for existing post type

To change the admin column settings for existing post types like `post` or `page`, you can use the `admin_columns()` function, which accepts an associative array of post types and their admin column settings.

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

The `args` parameter is used for the arguments that are passed to `register_taxonomy`. Use the `for_post_types` parameter to assign taxonomies to certain post types. The `name_singular` and `name_plural` parameters are used for the generating the labels in the backend.

## Update existing taxonomies

### Change settings for a taxonomy

Use the `update()` function to change the settings for an existing taxonomy. Here’s an example for changing the settings for categories to make them not directly accessible in the frontend.

**functions.php**

```php

Types\Taxonomy::update( [
    'category' => [
        'args' => [
            'public'            => false,
            'show_ui'           => true,
            'show_in_nav_menus' => true,
        ],
    ],
] );
```

The `update()` function accepts an associative array of taxonomies and their arguments. Make sure you use this function before the `init` hook.

### Rename a taxonomy

Sometimes you might want to rename an existing taxonomy to better reflect what it’s used for.

**functions.php**

```php
Types\Taxonomy::rename( 'category', 'Topic', 'Topics' );
```

The `rename()` function accepts a taxonomy as the first parameter, the new singular name of the taxonomy as a second parameter and the plural name of the taxonomy as a third parameter. If you omit the third parameter, the second parameter will be used as the plural form instead. Make sure you use this function before the `init` hook.

This is practically a shorthand function for:

```php
Types\Taxonomy::update( [
    'category' => [
        'name_singular' => 'Topic',
        'name_plural'   => 'Topics,
    ],
] );
```

If you only want to rename one of the labels, e.g. the menu label, you can use the `taxonomy_labels_{$post_type}` filter. Here’s an example for changing the menu name for posts:

```php
add_filter( 'taxonomy_labels_category', function( $labels ) {
    $labels->menu_name = 'Topics';

    return $labels;
}, 11 );
```

### Unregister taxonomies

If you want to unregister taxonomies for certain post types, it’s best to do it through the `unregister_taxonomy_for_object_type()` function.

```php
/**
 * Unregister post categories and post tags.
 */
add_action( 'init', function() {
    unregister_taxonomy_for_object_type( 'category', 'post' );
    unregister_taxonomy_for_object_type( 'post_tag', 'post' );
} );
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
