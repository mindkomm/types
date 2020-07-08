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
		$this->post_type  = $post_type;
		$this->query_args = $this->parse_query_args( $query_args );
	}

	/**
	 * Inits hooks.
	 */
	public function init() {
		add_action( 'pre_get_posts', [ $this, 'pre_get_posts' ], 9 );
	}

	/**
	 * Parses the query args.
	 *
	 * Returns an associative array with key `frontend` and `backend` that each contain query
	 * settings.
	 *
	 * @since 2.2.0
	 *
	 * @param array $args An array of query args.
	 *
	 * @return array An array of query args.
	 */
	public function parse_query_args( $args ) {
		$query_args = [
			'frontend' => $args,
			'backend'  => $args,
		];

		if ( isset( $args['frontend'] ) || isset( $args['backend'] ) ) {
			foreach ( [ 'frontend', 'backend' ] as $query_type ) {
				$query_args[ $query_type ] = isset( $args[ $query_type ] )
					? $args[ $query_type ]
					: [];
			}
		}

		return $query_args;
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
		$query_args = $this->query_args[ is_admin() ? 'backend' : 'frontend' ];

		if ( empty( $query_args ) ) {
			return;
		}

		/**
		 * When certain args are explicitly set through a $_GET parameter, then ignore them by
		 * removing them from the query args. Otherwise, sorting the list table in the admin won’t
		 * work.
		 */
		if ( is_admin() ) {
			foreach( [ 'order', 'orderby' ] as $arg ) {
				if ( ! empty( $_GET[ $arg ] ) && isset( $query_args[ $arg ] ) ) {
					unset( $query_args[$arg ] );
				}
			}
		}

		// Set query args.
		foreach ( $query_args as $key => $arg ) {
			$query->set( $key, $arg );
		}
	}
}
