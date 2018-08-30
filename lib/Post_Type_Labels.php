<?php

namespace Types;

/**
 * Class Post_Type_Labels
 *
 * @since 2.2.0
 */
class Post_Type_Labels {
	/**
	 * Post type.
	 *
	 * @var null|string The post type slug.
	 */
	private $post_type = null;

	/**
	 * Singular name.
	 *
	 * @var string The singular name of the post type.
	 */
	private $name_singular = '';

	/**
	 * Plural name.
	 *
	 * @var string The plural name of the post type.
	 */
	private $name_plural = '';

	/**
	 * Post type labels.
	 *
	 * @var array|null
	 */
	private $labels = null;

	/**
	 * Post_Type_Labels constructor.
	 *
	 * @param string $post_type     The post type slug.
	 * @param string $name_singular The singular name of the post type.
	 * @param string $name_plural   The plural name of the post type.
	 */
	public function __construct( $post_type, $name_singular, $name_plural = '' ) {
		if ( empty( $name_plural ) ) {
			$name_plural = $name_singular;
		}

		$this->post_type     = $post_type;
		$this->name_singular = $name_singular;
		$this->name_plural   = $name_plural;
		$this->labels        = $this->get_labels( $name_singular, $name_plural );
	}

	/**
	 * Inits hooks.
	 */
	public function init() {
		add_filter( "post_type_labels_{$this->post_type}", function() {
			return $this->labels;
		} );

		if ( is_admin() ) {
			// Update messages in backend.
			add_filter( 'post_updated_messages', [ $this, 'add_post_updated_messages' ] );
		}
	}

	/**
	 * Gets labels for a post type based on singular and plural name.
	 *
	 * The following labels are not translated, because they don’t contain the post type name:
	 * set_feature_image, remove_featured_image
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_post_type_labels/
	 *
	 * @param string $name_singular Singular name for post type.
	 * @param string $name_plural   Plural name for post type.
	 *
	 * @return array The translated labels.
	 */
	public function get_labels( $name_singular, $name_plural ) {
		$labels = [
			'name'                  => $name_singular,
			'singular_name'         => $name_plural,
			'add_new'               => __( 'Add New', 'mind/types' ),
			/* translators: %s: Singular post type name */
			'add_new_item'          => sprintf( __( 'Add New %s', 'mind/types' ), $name_singular ),
			/* translators: %s: Plural post type name */
			'all_items'             => sprintf( __( 'All %s', 'mind/types' ), $name_plural ),
			/* translators: %s: Singular post type name */
			'archives'              => sprintf( __( '%s Archives', 'mind/types' ), $name_singular ),
			/* translators: %s: Singular post type name */
			'attributes'            => sprintf( __( '%s Attributes', 'mind/types' ), $name_singular ),
			/* translators: %s: Singular post type name */
			'edit_item'             => sprintf( __( 'Edit %s', 'mind/types' ), $name_singular ),
			/* translators: %s: Singular post type name */
			'featured_image'        => sprintf( __( 'Featured Image for %s', 'mind/types' ), $name_singular ),
			/* translators: %s: Plural post type name */
			'filter_items_list'     => sprintf( __( 'Filter %s list', 'mind/types' ), $name_plural ),
			/* translators: %s: Singular post type name */
			'insert_into_item'      => sprintf( __( 'Insert into %s', 'mind/types' ), $name_singular ),
			/* translators: %s: Plural post type name */
			'items_list'            => sprintf( __( '%s list', 'mind/types' ), $name_plural ),
			/* translators: %s: Plural post type name */
			'items_list_navigation' => sprintf( __( '%s list navigation', 'mind/types' ), $name_plural ),
			/* translators: %s: Singular post type name */
			'new_item'              => sprintf( __( 'New %s', 'mind/types' ), $name_singular ),
			/* translators: %s: Plural post type name */
			'not_found'             => sprintf( __( 'No %s found.', 'mind/types' ), $name_plural ),
			/* translators: %s: Plural post type name */
			'not_found_in_trash'    => sprintf( __( 'No %s found in Trash.', 'mind/types' ), $name_plural ),
			/* translators: %s: Singular post type name */
			'parent_item'           => sprintf( __( 'Parent %s', 'mind/types' ), $name_singular ),
			/* translators: %s: Singular post type name */
			'parent_item_colon'     => sprintf( __( 'Parent %s:', 'mind/types' ), $name_singular ),
			/* translators: %s: Plural post type name */
			'search_items'          => sprintf( __( 'Search %s', 'mind/types' ), $name_plural ),
			/* translators: %s: Singular post type name */
			'uploaded_to_this_item' => sprintf( __( 'Uploaded to this %s', 'mind/types' ), $name_singular ),
			/* translators: %s: Singular post type name */
			'view_item'             => sprintf( __( 'View %s', 'mind/types' ), $name_singular ),
			/* translators: %s: Plural post type name */
			'view_items'            => sprintf( __( 'View %s', 'mind/types' ), $name_plural ),
			'menu_name'             => $name_plural,
			'name_admin_bar'        => $name_singular,
		];

		return $labels;
	}

	/**
	 * Sets post updated messages for custom post types.
	 *
	 * Check out the `post_updated_messages` in wp-admin/edit-form-advanced.php.
	 *
	 * @param array $messages An associative array of post types and their messages.
	 *
	 * @return array The filtered messages.
	 */
	public function add_post_updated_messages( $messages ) {
		global $post_id;

		$preview_url = get_preview_post_link( $post_id );
		$permalink   = get_permalink( $post_id );

		// Preview post link.
		$preview_post_link_html = is_post_type_viewable( $this->post_type )
			? sprintf( ' <a target="_blank" href="%1$s">%2$s</a>',
				esc_url( $preview_url ),
				/* translators: %s: Singular post type name */
				sprintf( __( 'Preview %s', 'mind/types' ), $this->name_singular )
			)
			: '';

		// View post link.
		$view_post_link_html = is_post_type_viewable( $this->post_type )
			? sprintf( ' <a href="%1$s">%2$s</a>',
				esc_url( $permalink ),
				/* translators: %s: Singular post type name */
				sprintf( __( 'View %s', 'mind/types' ), $this->name_singular )
			)
			: '';

		/**
		 * Message indices 2, 3, 5 and 9 are not handled, because they are edge cases or they would be too difficult
		 * to reproduce.
		 */
		$messages[ $this->post_type ] = [
			/* translators: %s: Singular post type name */
			1  => sprintf( __( '%s updated.', 'mind/types' ), $this->name_singular ) . $view_post_link_html,
			/* translators: %s: Singular post type name */
			4  => sprintf( __( '%s updated.', 'mind/types' ), $this->name_singular ),
			/* translators: %s: Singular post type name */
			6  => sprintf( __( '%s published.', 'mind/types' ), $this->name_singular ) . $view_post_link_html,
			/* translators: %s: Singular post type name */
			7  => sprintf( __( '%s saved.', 'mind/types' ), $this->name_singular ),
			/* translators: %s: Singular post type name */
			8  => sprintf( __( '%s submitted.', 'mind/types' ), $this->name_singular ) . $preview_post_link_html,
			/* translators: %s: Singular post type name */
			10 => sprintf( __( '%s draft updated.', 'mind/types' ), $this->name_singular ) . $preview_post_link_html,
		];

		return $messages;
	}
}
