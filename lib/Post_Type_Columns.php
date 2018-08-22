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
	 * Custom_Post_Type_Columns constructor.
	 *
	 * @param string $post_type A post type slug.
	 * @param array  $columns   An array of columns to be edited.
	 */
	public function __construct( $post_type, $columns ) {
		$this->post_type = $post_type;

		foreach ( $columns as $slug => $column ) {
			if ( false !== $column ) {
				$column = wp_parse_args( $column, [
					'title'     => '',
					'type'      => 'default',
					'transform' => null,
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
		add_filter( 'manage_edit-' . $this->post_type . '_sortable_columns', [ $this, 'columns' ] );
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', [
			$this,
			'column_content',
		], 10, 2 );
	}

	/**
	 * Adapt columns for post list view.
	 *
	 * @param array $columns An array of existing columns.
	 *
	 * @return array
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

		if ( 'acf' === $column['type'] ) {
			$value = get_field( $column_name, $post_id );
		} else {
			$value = get_post_meta( $post_id, $column_name, true );
		}

		if ( is_callable( $column['transform'] ) ) {
			$value = call_user_func( $column['transform'], $value, $post_id );
		}

		echo esc_html( $value );
	}
}
