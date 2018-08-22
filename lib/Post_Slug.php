<?php

namespace Types;

/**
 * Class Post_Slug
 *
 * Allows you to register callback functions to customize post slugs.
 */
class Post_Slug {
	/**
	 * Post type callbacks.
	 *
	 * @var array An associative array of post types and their callbacks.
	 */
	private $callbacks = [];

	/**
	 * Inits hooks.
	 */
	public function init() {
		add_filter( 'wp_insert_post_data', [ $this, 'customize_slug' ], 11, 2 );
	}

	/**
	 * Registers post type callbacks.
	 *
	 * @param array $post_types An associative array of post types and their callbacks.
	 */
	public function register( $post_types = [] ) {
		foreach ( $post_types as $post_type => $callback ) {
			$this->callbacks[ $post_type ] = $callback;
		}
	}

	/**
	 * Customizes the post slug.
	 *
	 * @param array $data    An array of slashed post data.
	 * @param array $postarr An array of sanitized, but otherwise unmodified post data.
	 *
	 * @return array
	 */
	public function customize_slug( $data, $postarr ) {
		$bailout_states = [ 'draft', 'pending', 'auto-draft' ];

		$post_id     = $postarr['ID'];
		$post_type   = $postarr['post_type'];
		$post_status = $postarr['post_status'];
		$post_slug   = $postarr['post_name'];
		$post_parent = $postarr['post_parent'];

		// Bailout if it’s not the right state.
		if ( in_array( $post_status, $bailout_states, true ) ) {
			return $data;
		}

		// Bailout if no callback could be found.
		if ( ! in_array( $post_type, array_keys( $this->callbacks ), true )
			|| ! is_callable( $this->callbacks[ $post_type ] )
		) {
			return $data;
		}

		// Filter post slug through user-defined callback.
		$post_slug = call_user_func( $this->callbacks[ $post_type ], $post_slug, $postarr, $post_id );

		// Make sure the post slug is sanitized and unique.
		$post_slug = sanitize_title( $post_slug );
		$post_slug = wp_unique_post_slug( $post_slug, $post_id, $post_status, $post_type, $post_parent );

		$data['post_name'] = $post_slug;

		return $data;
	}
}
