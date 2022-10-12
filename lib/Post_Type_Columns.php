<?php

namespace Types;

/**
 * Class Post_Type_Columns
 */
class Post_Type_Columns {
	/**
	 * Post Type.
	 *
	 * @var null|string A custom post type slug.
	 */
	private $post_type = null;

	/**
	 * Column definitions.
	 *
	 * @var array An array of columns with args.
	 */
	private $columns = [];

	/**
	 * Post_Type_Columns constructor.
	 *
	 * @param string $post_type A post type slug.
	 * @param array  $columns   An array of columns to be edited.
	 */
	public function __construct( $post_type, $columns ) {
		$this->post_type = $post_type;

		foreach ( $columns as $slug => $column ) {
			if ( false !== $column ) {
				// Set defaults for thumbnail.
				if ( 'thumbnail' === $slug ) {
					$column = wp_parse_args( $column, [
						'title'    => __( 'Featured Image', 'mind/types' ),
						'width'    => 80,
						'height'   => 80,
						'sortable' => false,
					] );
				}

				// Set defaults for each field.
				$column = wp_parse_args( $column, [
					'title'        => '',
					'type'         => 'meta',
					'transform'    => null,
					'sortable'     => false,
					'orderby'      => 'meta_value',
					'column_order' => 10,
					'searchable'   => false,
				] );
			}

			$this->columns[ $slug ] = $column;
		}
	}

	/**
	 * Inits hooks.
	 */
	public function init() {
		add_filter( 'manage_edit-' . $this->post_type . '_columns', [ $this, 'columns' ] );
		add_filter( 'manage_edit-' . $this->post_type . '_sortable_columns', [
			$this,
			'columns_sortable',
		] );
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', [
			$this,
			'column_content',
		], 10, 2 );

		if ( is_admin() ) {
			add_action( 'pre_get_posts', [ $this, 'search_meta_or_title' ] );
			add_action( 'pre_get_posts', [ $this, 'sort_by_meta' ] );
		}
	}

	/**
	 * Filters columns for post list view.
	 *
	 * @param array $columns An array of existing columns.
	 *
	 * @return array Filtered array.
	 */
	public function columns( $columns ) {
		$sorted = [];
		$return = [];

		// Move existing columns into sort array.
		foreach ( $columns as $key => $column ) {
			$sorted[ $key ] = [
				'title'        => $column,
				// The checkbox should always be first, hence the order key '-1'.
				'column_order' => 'cb' === $key ? - 1 : 10,
			];
		}

		// Merge in user-defined columns and settings for existing columns.
		foreach ( $this->columns as $key => $column ) {
			if ( isset( $sorted[ $key ] ) ) {
				// Columns can be removed when they are set to 'false'
				if ( false === $column ) {
					unset( $sorted[ $key ] );
					continue;
				}

				if ( ! empty( $column['title'] ) ) {
					$sorted[ $key ]['title'] = $column['title'];
				}

				if ( 10 !== $column['column_order'] ) {
					$sorted[ $key ]['column_order'] = $column['column_order'];
				}
			} else {
				$sorted[ $key ] = $column;
			}
		}

		$sorted = wp_list_sort( $sorted, 'column_order', 'ASC', true );

		foreach ( $sorted as $slug => $column ) {
			$return[ $slug ] = $column['title'];
		}

		return $return;
	}

	/**
	 * Filters sortable columns.
	 *
	 * @param array $columns An array of existing columns.
	 *
	 * @return array Filtered array.
	 */
	public function columns_sortable( $columns ) {
		foreach ( $this->columns as $slug => $column ) {
			// Remove column when it’s not sortable.
			if ( isset( $column['sortable'] ) && ! $column['sortable'] ) {
				unset( $columns[ $slug ] );
				continue;
			} elseif ( ! isset( $columns[ $slug ] ) ) {
				if ( ! empty( $column['type'] ) && 'meta' === $column['type'] ) {
					$columns[ $slug ] = $slug;
				}
			}
		}

		return $columns;
	}

	/**
	 * Includes searchable custom fields in the search.
	 *
	 * @param \WP_Query $query WordPress query object.
	 */
	public function sort_by_meta( \WP_Query $query ) {
		global $typenow;
		$orderby = $query->get( 'orderby' );

		if ( ! $query->is_main_query()
			|| $typenow !== $this->post_type
			|| empty( $orderby )
			|| ! is_string( $orderby )
			|| ! isset( $this->columns[ $orderby ] )
			|| 'meta' !== $this->columns[ $orderby ]['type']
		) {
			return;
		}

		$new_orderby = $this->columns[ $orderby ]['orderby'];

		$query->set( 'orderby', $new_orderby );
		$query->set( 'meta_key', $orderby );
	}

	/**
	 * Update column contents for post list view.
	 *
	 * @param string $column_name The column slug.
	 * @param int    $post_id     The post ID.
	 */
	public function column_content( $column_name, $post_id ) {
		// Bail out.
		if ( ! array_key_exists( $column_name, $this->columns ) ) {
			return;
		}

		$column = $this->columns[ $column_name ];

		if ( 'thumbnail' === $column_name ) {
			$src = get_the_post_thumbnail_url( $post_id, 'thumbnail' );

			if ( empty( $src ) ) {
				return;
			}

			$styles = '';

			foreach ( [ 'width', 'height' ] as $attr ) {
				if ( isset( $column[ $attr ] ) ) {
					$styles .= $attr . ':' . $column[ $attr ] . 'px;';
				}
			}

			if ( ! empty( $styles ) ) {
				$styles = ' style="' . $styles . '"';
			}

			echo '<img src="' . esc_attr( $src ) . '"' . $styles . '>';

			return;
		}

		$value = '';
		if ( 'acf' === $column['type'] ) {
			$value = get_field( $column_name, $post_id );
		} elseif ( 'meta' === $column['type'] ) {
			$value = get_post_meta( $post_id, $column_name, true );
		} elseif ( 'image' === $column['type'] ) {
			$value = get_post_meta( $post_id, $column_name, true );

			if ( is_numeric( $value ) ) {
				$src = wp_get_attachment_image_src(
					$value,
					$column['image_size'] ?? 'thumbnail'
				);

				if ( $src ) {
					echo $this->column_content_image( $src[0], $column );

					return;
				}
			}
		} elseif ( 'custom' === $column['type'] && is_callable( $column['value'] ) ) {
			$value = call_user_func( $column['value'], $post_id );
		}

		if ( in_array( $column['type'], [ 'meta', 'acf' ], true )
			&& is_callable( $column['transform'] )
		) {
			$value = call_user_func( $column['transform'], $value, $post_id );
		}

		echo $value;
	}

	/**
	 * Gets the HTML for the 'image' type.
	 *
	 * @param string $src  An image source.
	 * @param array  $args An array of column arguments.
	 *
	 * @return string
	 */
	protected function column_content_image( $src, $args ) {
		if ( empty( $src ) ) {
			return '';
		}

		$styles = 'max-width: 100%;';

		foreach ( [ 'width', 'height' ] as $attr ) {
			if ( isset( $args[ $attr ] ) ) {
				$styles .= $attr . ':' . esc_attr( $args[ $attr ] ) . 'px;';
			}
		}

		$styles = ' style="' . $styles . '"';

		return '<img src="' . esc_attr( $src ) . '"' . $styles . '>';
	}

	/**
	 * Includes searchable custom fields in the search.
	 *
	 * @param \WP_Query $query WordPress query object.
	 */
	public function search_meta_or_title( \WP_Query $query ) {
		global $typenow;
		$searchterm = $query->query_vars['s'];

		if (
			! $query->is_main_query()
			|| $typenow !== $this->post_type
			|| empty( $searchterm )
		) {
			return;
		}

		$meta_columns = array_filter( $this->columns, function( $column ) {
			return ! empty( $column ) && 'meta' === $column['type'] && $column['searchable'];
		} );

		// Bail out and use default search if no searchable meta columns are defined.
		if ( empty( $meta_columns ) ) {
			return;
		}

		$meta_query = [ 'relation' => 'OR' ];

		foreach ( $meta_columns as $key => $column ) {
			$meta_query[] = [
				'key'     => $key,
				'value'   => $searchterm,
				'compare' => 'LIKE',
			];
		}

		$query->set( 'meta_query', $meta_query );

		/**
		 * Remove search parameter.
		 *
		 * The search parameter needs to be removed from the query, otherwise posts can’t be found.
		 * A disadvantage of this is that all the logic in \WP_Query::parse_search() is lost.
		 *
		 * @see \WP_Query::parse_search()
		 */
		$query->set( 's', '' );

		// Fixes the "Search results for …" label in the post list table.
		add_filter( 'get_search_query', function( $query ) use ( $searchterm ) {
			return $searchterm;
		} );

		/**
		 * Update meta query to include search for title.
		 *
		 * This logic is taken from a StackOverflow answer:
		 *
		 * @link https://wordpress.stackexchange.com/a/178492/22506
		 */
		add_filter( 'get_meta_sql', function( $sql ) use ( $searchterm ) {
			global $wpdb;

			// Run only once.
			static $nr = 0;

			if ( 0 != $nr ++ ) {
				return $sql;
			}

			// Modify the WHERE part.
			$sql['where'] = sprintf(
				" AND ( %s OR %s ) ",
				$wpdb->prepare( "{$wpdb->posts}.post_title like '%%%s%%'", $searchterm ),
				mb_substr( $sql['where'], 5 )
			);

			return $sql;
		} );
	}
}
