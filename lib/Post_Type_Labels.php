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
	}

	/**
	 * Inits hooks.
	 */
	public function init() {
		add_filter( "post_type_labels_{$this->post_type}", function() {
			return (object) $this->get_labels();
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
	 * @return array The translated labels.
	 */
	public function get_labels() {
		return [
			'name'                     => $this->name_plural,
			'singular_name'            => $this->name_singular,
			'add_new'                  => __( 'Add New', 'mind/types' ),
			'add_new_item'             => sprintf(
				/* translators: %s: Singular post type name */
				__( 'Add New %s', 'mind/types' ),
				$this->name_singular
			),
			'all_items'                => sprintf(
				/* translators: %s: Plural post type name */
				__( 'All %s', 'mind/types' ),
				$this->name_plural
			),
			'archives'                 => sprintf(
				/* translators: %s: Singular post type name */
				__( '%s Archives', 'mind/types' ),
				$this->name_singular
			),
			'attributes'               => sprintf(
				/* translators: %s: Singular post type name */
				__( '%s Attributes', 'mind/types' ),
				$this->name_singular
			),
			'edit_item'                => sprintf(
				/* translators: %s: Singular post type name */
				__( 'Edit %s', 'mind/types' ),
				$this->name_singular
			),
			'featured_image'           => sprintf(
				/* translators: %s: Singular post type name */
				__( 'Featured Image for %s', 'mind/types' ),
				$this->name_singular
			),
			'filter_items_list'        => sprintf(
				/* translators: %s: Plural post type name */
				__( 'Filter %s list', 'mind/types' ),
				$this->name_plural
			),
			'insert_into_item'         => sprintf(
				/* translators: %s: Singular post type name */
				__( 'Insert into %s', 'mind/types' ),
				$this->name_singular
			),
			'items_list'               => sprintf(
				/* translators: %s: Plural post type name */
				__( '%s list', 'mind/types' ),
				$this->name_plural
			),
			'items_list_navigation'    => sprintf(
				/* translators: %s: Plural post type name */
				__( '%s list navigation', 'mind/types' ),
				$this->name_plural
			),
			'item_published'           => sprintf(
				/* translators: %s: Singular post type name */
				__( '%s published.', 'mind/types' ),
				$this->name_singular
			),
			'item_published_privately' => sprintf(
				/* translators: %s: Singular post type name */
				__( '%s published privately.', 'mind/types' ),
				$this->name_singular
			),
			'item_reverted_to_draft'   => sprintf(
				/* translators: %s: Singular post type name */
				__( '%s reverted to draft.', 'mind/types' ),
				$this->name_singular
			),
			'item_scheduled'           => sprintf(
				/* translators: %s: Singular post type name */
				__( '%s scheduled.', 'mind/types' ),
				$this->name_singular
			),
			'item_updated'             => sprintf(
				/* translators: %s: Singular post type name */
				__( '%s updated.', 'mind/types' ),
				$this->name_singular
			),
			'new_item'                 => sprintf(
				/* translators: %s: Singular post type name */
				__( 'New %s', 'mind/types' ),
				$this->name_singular
			),
			'not_found'                => sprintf(
				/* translators: %s: Plural post type name */
				__( 'No %s found.', 'mind/types' ),
				$this->name_plural
			),
			'not_found_in_trash'       => sprintf(
				/* translators: %s: Plural post type name */
				__( 'No %s found in Trash.', 'mind/types' ),
				$this->name_plural
			),
			'parent_item'              => sprintf(
				/* translators: %s: Singular post type name */
				__( 'Parent %s', 'mind/types' ),
				$this->name_singular
			),
			'parent_item_colon'        => sprintf(
				/* translators: %s: Singular post type name */
				__( 'Parent %s:', 'mind/types' ),
				$this->name_singular
			),
			'search_items'             => sprintf(
				/* translators: %s: Plural post type name */
				__( 'Search %s', 'mind/types' ),
				$this->name_plural
			),
			'uploaded_to_this_item'    => sprintf(
				/* translators: %s: Singular post type name */
				__( 'Uploaded to this %s', 'mind/types' ),
				$this->name_singular
			),
			'view_item'                => sprintf(
				/* translators: %s: Singular post type name */
				__( 'View %s', 'mind/types' ),
				$this->name_singular
			),
			'view_items'               => sprintf(
				/* translators: %s: Plural post type name */
				__( 'View %s', 'mind/types' ),
				$this->name_plural
			),
			'menu_name'                => $this->name_plural,
			'name_admin_bar'           => $this->name_singular,
		];
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
