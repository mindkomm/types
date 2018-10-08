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
	private $post_types = [];

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
			$this->post_types[ $post_type ] = [
				'callback' => $callback,
			];
		}
	}

	/**
	 * Registers date suffixes for post slugs.
	 *
	 * @param array $post_types An associative array of post types and their suffix args.
	 */
	public function register_suffix_date( $post_types = [] ) {
		foreach ( $post_types as $post_type => $args ) {
			$this->post_types[ $post_type ] = [
				'callback' => function( $post_slug, $post_data, $post_id ) use ( $args ) {
					$args = wp_parse_args( $args, [
						'meta_key'      => 'date_start',
						'input_format'  => 'Ymd',
						'output_format' => 'Y-m-d',
					] );

					$meta_value = get_post_meta( $post_id, $args['meta_key'], true );

					if ( ! $meta_value ) {
						return $post_slug;
					}

					$date = \DateTime::createFromFormat( $args['input_format'], $meta_value );

					if ( $date ) {
						$post_slug = $post_data['post_title'] . '-'
							. $date->format( $args['output_format'] );
					}

					return $post_slug;
				},
			];
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
		$bailout_states = [ 'auto-draft', 'trash' ];
		$post_status    = $postarr['post_status'];

		// Bailout if it’s not the right state.
		if ( in_array( $post_status, $bailout_states, true ) ) {
			return $data;
		}

		$post_type = $postarr['post_type'];

		// Bailout if no callback could be found.
		if ( ! in_array( $post_type, array_keys( $this->post_types ), true )
			|| ! is_callable( $this->post_types[ $post_type ]['callback'] )
		) {
			return $data;
		}

		$post_id     = $postarr['ID'];
		$post_slug   = $postarr['post_name'];
		$post_parent = $postarr['post_parent'];

		// Filter post slug through user-defined callback.
		$post_slug = call_user_func( $this->post_types[ $post_type ]['callback'], $post_slug, $postarr, $post_id );

		// Make sure the post slug is sanitized and unique.
		$post_slug = sanitize_title( $post_slug );
		$post_slug = wp_unique_post_slug( $post_slug, $post_id, $post_status, $post_type, $post_parent );

		$data['post_name'] = $post_slug;

		return $data;
	}
}
