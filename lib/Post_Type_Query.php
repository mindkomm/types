<?php

namespace Types;

/**
 * Class Post_Type_Query
 */
class Post_Type_Query {
	/**
	 * Post Type.
	 *
	 * @var null|string A custom post type slug.
	 */
	private $post_type = null;

	/**
	 * Query args.
	 *
	 * @var array An array of query args.
	 */
	private $query_args = [];

	/**
	 * Custom_Post_Type_Query constructor.
	 *
	 * @param string $post_type  The custom post type.
	 * @param array  $query_args Arguments used for WP_Query.
	 */
	public function __construct( $post_type, $query_args ) {
		$this->post_type = $post_type;

		foreach ( [ 'frontend', 'backend' ] as $query_type ) {
			$this->query_args[ $query_type ] = isset( $query_args[ $query_type ] )
				? $query_args[ $query_type ]
				: $query_args;
		}
	}

	/**
	 * Inits hooks.
	 */
	public function init() {
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ] );
	}

	/**
	 * Alters the query.
	 *
	 * @param \WP_Query $query A WP_Query object.
	 */
	public function pre_get_posts( $query ) {
		global $typenow;

		/**
		 * Check if we should modify the query.
		 *
		 * As a hint for for future condition updates: We can’t use $query->is_post_type_archive(),
		 * because some post_types have 'has_archive' set to false.
		 */
		if ( ! is_admin() ) {
			if (
				// Special case for post in a page_for_posts setting.
				( 'post' === $this->post_type && ! $query->is_home() )
				// All other post types.
				|| ( 'post' !== $this->post_type && $this->post_type !== $query->get( 'post_type' ) )
			) {
				return;
			}
		} elseif ( ! $query->is_main_query() || $typenow !== $this->post_type ) {
			return;
		}

		// Differ between frontend and backend queries.
		if ( is_admin() ) {
			$query_args = $this->query_args['backend'];
		} else {
			$query_args = $this->query_args['frontend'];
		}

		// Set query args.
		foreach ( $query_args as $key => $arg ) {
			$query->set( $key, $arg );
		}
	}
}
