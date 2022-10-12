# Types

## 2.5.6 - 2022-10-13

- Fixed return type for label filter.
- Fixed bugs when trying to access column data on columns that are not arrays (e.g. when column is set to `false`).
- Improved default compatibility with custom permalink structure when using the `page_for_archive` option.

## 2.5.5 - 2021-11-30

- Fixed a bug with orderby query parameters that are arrays.

## 2.5.4 - 2020-08-19

- Fixed error notice.

## 2.5.3 - 2020-08-18

- Added a new `column_order` argument for the `admin_columns` option.
- Added a new `image` type for the `admin_columns` option.
- Added a new `custom` type for the `admin_columns` option.
- Fixed a bug when updating the label for a custom post type didn’t work properly.
- Fixed a bug when search for meta for title.

## 2.5.2 - 2020-07-29

- Fixed a bug when wrong translation loaded in admin.
- Fixed a bug when searching for post titles didn’t work in combination with meta value searches.
- Fixed some code style issues (thanks, @szepeviktor).

## 2.5.1 - 2020-07-08

- Fixed a PHP notice.

## 2.5.0 - 2020-07-08

- Fixed `sortable` parameter for the `admin_columns` option. Sorting didn’t work properly before. It should work now.
- Changed default for `sortable` parameter for the `admin_columns` option from `true` to `false`.
- Added new `orderby` parameter for the `admin_columns` option that will be used in combination with the `sortable` parameter.
- Fixed a bug when the `query` option was interfering with sorting queries in post list tables.

## 2.4.4 - 2020-02-11

- Added Dutch translations.
- Added `searchable` option as an option for admin columns to make meta columns searchable.
- Changed default column type for admin columns from `default` to `meta` to be more explicit.

## 2.4.3 - 2019-11-13

This release improves compatibility with multisite environments, especially when working with [MultilingualPress](https://multilingualpress.de/).

- Fixed bug when page archives didn’t work in multilingual multisite environments.
- Fixed bug when proper archive link couldn’t be selected in multisite environment.

## 2.4.2 - 2019-10-17

- Fixed bug when no page was selected for a custom post type archive in `Post_Type_Page_Option`.

## 2.4.1 - 2019-08-05

- Added filter to update the title for the post type archive when using `post_type_archive_title()`.

## 2.4.0 - 2019-06-28

- Added `Post_Type_Page_Option` class, which registers an option to select the page to act as the custom post type archive in the Customizer for you.
- Added `Post_Type_Page_State` class, which adds a post state label to the the pages overview in the admin to recognize pages that act as custom post type archives quicker.

Read more about these functionalities in the [`page_for_archive`](https://github.com/mindkomm/types#page_for_archive) section in the README.

## 2.3.2 - 2019-06-07

- Added edit page link to admin bar for archive pages.

## 2.3.1 - 2019-03-18

- Fixed CSS classes added to parent and ancestor menu items.

## 2.3.0 - 2019-03-06

- Added new `Types\Post_Type_Page` class. This class allows you to define a page that should act as the archive for a Custom Post Type [using the `page_for_archive` option](https://github.com/mindkomm/types#page_for_archive).
- Fixed a bug with an undefined function #2 (Thanks @roylodder). 

## 2.2.3 - 2019-01-17

- Added new post type labels (<https://make.wordpress.org/core/2018/12/05/new-post-type-labels-in-5-0/>).
- Fixed wrong label assignment.

## 2.2.2 - 2018-10-08

- Updated post slug feature to not run when a post is being trashed.
- Fixed bug when a post slug couldn’t be set when a date couldn’t be parsed. When the date can’t be parsed, the post slug won’t be changed.

## 2.2.1 - 2018-09-20

- Fixed bug when certain values didn’t exist yet for a post.

## 2.2.0 - 2018-08-29

- Added better handling of labels by updating the messages displayed in the backend and making it possible to properly translate the labels. This will open up the repository for additional languages.
- Added new function `update()` to update the settings for existing post types and taxonomies.
- Added new function `rename()` to rename existing post types and taxonomies.
- Added option for post types to have separate queries with a `frontend` and `backend` argument for `query`.
- Fixed when a post slug wasn’t updated on first save.

## 2.1.0 - 2018-08-27

- Added new function `admin_columns()` to customize admin columns for already registered post types.
- Added special `thumbnail` column key to display the featured image.
- Added `sortable` option for a column to define whether it’s sortable.

## 2.0.1 - 2018-08-23

- Fixed undefined index notices.

## 2.0.0 - 2018-08-22

- Renamed package from «Custom Types» to just «Types».
- Renamed classes.
- Renamed registration methods.
- Added helper class to customize post slugs when posts are saved.

## 1.1.0 - 2018-08-21

- Added `query` argument to define how posts are queried in the front- and backend.
- Added `admin_columns` argument to define the admin columns that should be displayed.

## 1.0.0 - 2018-02-13

Initial release.
