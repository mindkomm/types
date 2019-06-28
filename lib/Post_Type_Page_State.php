<?php

namespace Types;

/**
 * Class Post_Type_Page_State
 */
class Post_Type_Page_State {
	/**
	 * Post type.
	 *
	 * @var string
	 */
	private $post_type;

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private $option_name;

	/**
	 * Post_Type_Page_State constructor.
	 *
	 * @param string $post_type The post type to display the post state for.
	 */
	public function __construct( $post_type ) {
		$this->post_type   = $post_type;
		$this->option_name = "page_for_{$this->post_type}";
	}

	/**
	 * Inits hooks.
	 */
	public function init() {
		if ( ! is_admin() ) {
			return;
		}

		add_filter( 'display_post_states', [ $this, 'update_post_states' ], 10, 2 );
	}

	/**
	 * Updates post states with page for event.
	 *
	 * @param string[] $post_states An array of post display states.
	 * @param \WP_Post $post        The current post object.
	 *
	 * @return string[] Updates post states.
	 */
	public function update_post_states( $post_states, $post ) {
		$post_type_object = get_post_type_object( $this->post_type );

		if ( 'page' === $post->post_type
			&& (int) get_option( $this->option_name ) === $post->ID
		) {
			$post_states[ $this->option_name ] = sprintf(
				/* translators: Post type label. */
				__( 'Page for %s', 'mind/types' ),
				$post_type_object->label
			);
		}

		return $post_states;
	}
}
