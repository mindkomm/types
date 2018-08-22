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
		$this->query_args = wp_parse_args( $query_args, [] );
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
		 * We can’t use $query->is_post_type_archive(), because some post_types have 'has_archive'
		 * set to false.
		 */
		if ( ! is_admin() || ( $query->is_main_query() && $typenow === $this->post_type ) ) {
			switch ( $query->get( 'post_type' ) ) {
				case $this->post_type:
					foreach ( $this->query_args as $key => $arg ) {
						$query->set( $key, $arg );
					}
					break;
			}
		}
	}
}
