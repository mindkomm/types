# Types

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
