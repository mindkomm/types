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
					'title'      => '',
					'type'       => 'meta',
					'transform'  => null,
					'sortable'   => false,
					'orderby'    => 'meta_value',
					'searchable' => false,
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
			add_action( 'pre_get_posts', [ $this, 'search_custom_fields' ] );
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
		foreach ( $this->columns as $slug => $column ) {
			// Columns can be removed when they are set to 'false'
			if ( false === $column ) {
				unset( $columns[ $slug ] );
				continue;
			}

			$columns[ $slug ] = $column['title'];
		}

		return $columns;
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
			if ( ! $column['sortable'] ) {
				unset( $columns[ $slug ] );
				continue;
			} elseif ( ! isset( $columns[ $slug ] ) ) {
				if ( 'meta' === $column['type'] ) {
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
		if ( empty( $this->columns )
			|| ! in_array( $column_name, array_keys( $this->columns ), true )
		) {
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

		if ( 'acf' === $column['type'] ) {
			$value = get_field( $column_name, $post_id );
		} elseif ( 'meta' === $column['type'] ) {
			$value = get_post_meta( $post_id, $column_name, true );
		}

		if ( is_callable( $column['transform'] ) ) {
			$value = call_user_func( $column['transform'], $value, $post_id );
		}

		echo $value;
	}

	/**
	 * Includes searchable custom fields in the search.
	 *
	 * @param \WP_Query $query WordPress query object.
	 */
	public function search_custom_fields( \WP_Query $query ) {
		global $typenow;
		$searchterm = $query->query_vars['s'];

		if ( ! $query->is_main_query() || $typenow !== $this->post_type || empty( $searchterm ) ) {
			return;
		}

		$meta_columns = array_filter( $this->columns, function( $column ) {
			return 'meta' === $column['type'] && $column['searchable'];
		} );

		$meta_query = [ 'relation' => 'OR' ];

		foreach ( $meta_columns as $key => $column ) {
			$meta_query[] = [
				'key'     => $key,
				'value'   => $searchterm,
				'compare' => 'LIKE',
			];
		}

		/**
		 * The search parameter needs to be removed from the query, because it will prevent
		 * the proper posts from being found.
		 */
		$query->set( 's', '' );

		$query->set( 'meta_query', $meta_query );
	}
}
