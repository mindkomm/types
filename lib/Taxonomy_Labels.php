<?php

namespace Types;

/**
 * Class Taxonomy_Labels
 */
class Taxonomy_Labels {
	/**
	 * Taxonomy.
	 *
	 * @var null|string The taxonomy slug.
	 */
	private $taxonomy = null;

	/**
	 * Singular name.
	 *
	 * @var string The singular name of the taxonomy.
	 */
	private $name_singular = '';

	/**
	 * Plural name.
	 *
	 * @var string The plural name of the taxonomy.
	 */
	private $name_plural = '';

	/**
	 * Taxonomy labels.
	 *
	 * @var array|null
	 */
	private $labels = null;

	/**
	 * Taxonomy_Labels constructor.
	 *
	 * @param string $taxonomy      The post type slug.
	 * @param string $name_singular The singular name of the post type.
	 * @param string $name_plural   The plural name of the post type.
	 */
	public function __construct( $taxonomy, $name_singular, $name_plural = '' ) {
		if ( empty( $name_plural ) ) {
			$name_plural = $name_singular;
		}

		$this->taxonomy      = $taxonomy;
		$this->name_singular = $name_singular;
		$this->name_plural   = $name_plural;
		$this->labels        = $this->get_labels( $name_singular, $name_plural );
	}

	/**
	 * Inits hooks.
	 */
	public function init() {
		add_filter( "taxonomy_labels_{$this->taxonomy}", function() {
			return $this->labels;
		} );

		if ( is_admin() ) {
			// Update messages in backend.
			add_filter( 'term_updated_messages', [ $this, 'add_term_updated_messages' ] );
		}
	}

	/**
	 * Get labels for taxonomy base on singular and plural name.
	 *
	 * The following labels are not translated, because they don’t contain the post type name:
	 * most_used
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_taxonomy_labels/
	 *
	 * @param string $name_singular Singular name for taxonomy.
	 * @param string $name_plural   Plural name for taxonomy.
	 *
	 * @return array The translated labels.
	 */
	public function get_labels( $name_singular, $name_plural ) {
		$labels = [
			'name'                       => $name_plural,
			'singular_name'              => $name_singular,
			/* translators: %s: Singular taxonomy name */
			'add_new_item'               => sprintf( __( 'Add New %s', 'mind/types' ), $name_singular ),
			/* translators: %s: Plural taxonomy name */
			'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'mind/types' ), $name_plural ),
			/* translators: %s: Plural taxonomy name */
			'all_items'                  => sprintf( __( 'All %s', 'mind/types' ), $name_plural ),
			/* translators: %s: Singular post type name */
			'archives'                   => sprintf( __( '%s Archives', 'mind/types' ), $name_singular ),
			/* translators: %s: Plural taxonomy name */
			'back_to_items'              => sprintf( __( '&larr; Back to %s', 'mind/types' ), $name_plural ),
			/* translators: %s: Plural taxonomy name */
			'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'mind/types' ), $name_plural ),
			/* translators: %s: Singular taxonomy name */
			'edit_item'                  => sprintf( __( 'Edit %s', 'mind/types' ), $name_singular ),
			/* translators: %s: Plural taxonomy name */
			'items_list'                 => sprintf( __( '%s list', 'mind/types' ), $name_plural ),
			/* translators: %s: Plural taxonomy name */
			'items_list_navigation'      => sprintf( __( '%s list navigation', 'mind/types' ), $name_plural ),
			/* translators: %s: Singular taxonomy name */
			'new_item_name'              => sprintf( __( 'New %s Name', 'mind/types' ), $name_singular ),
			/* translators: %s: Plural taxonomy name */
			'no_terms'                   => sprintf( __( 'No %s', 'mind/types' ), $name_plural ),
			/* translators: %s: Plural taxonomy name */
			'not_found'                  => sprintf( __( 'No %s found.', 'mind/types' ), $name_plural ),
			/* translators: %s: Singular taxonomy name */
			'parent_item'                => sprintf( __( 'Parent %s', 'mind/types' ), $name_singular ),
			/* translators: %s: Singular taxonomy name */
			'parent_item_colon'          => sprintf( __( 'Parent %s:', 'mind/types' ), $name_singular ),
			/* translators: %s: Plural taxonomy name */
			'popular_items'              => sprintf( __( 'Popular %s', 'mind/types' ), $name_plural ),
			/* translators: %s: Plural taxonomy name */
			'search_items'               => sprintf( __( 'Search %s', 'mind/types' ), $name_plural ),
			/* translators: %s: Plural taxonomy name */
			'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'mind/types' ), $name_plural ),
			/* translators: %s: Singular taxonomy name */
			'update_item'                => sprintf( __( 'Update %s', 'mind/types' ), $name_singular ),
			/* translators: %s: Singular taxonomy name */
			'view_item'                  => sprintf( __( 'View %s', 'mind/types' ), $name_singular ),
			'name_admin_bar'             => $name_singular,
			'menu_name'                  => $name_plural,
		];

		return $labels;
	}

	/**
	 * Sets term updated messages for custom taxonomies.
	 *
	 * Check out the `term_updated_messages` in wp-admin/includes/edit-tag-messages.php.
	 *
	 * @param array $messages An associative array of taxonomies and their messages.
	 *
	 * @return array The filtered messages.
	 */
	public function add_term_updated_messages( $messages ) {
		$messages[ $this->taxonomy ] = [
			0 => '',
			/* translators: %s: Singular taxonomy name */
			1 => sprintf( __( '%s added.', 'mind/types' ), $this->name_singular ),
			/* translators: %s: Singular taxonomy name */
			2 => sprintf( __( '%s deleted.', 'mind/types' ), $this->name_singular ),
			/* translators: %s: Singular taxonomy name */
			3 => sprintf( __( '%s updated.', 'mind/types' ), $this->name_singular ),
			/* translators: %s: Singular taxonomy name */
			4 => sprintf( __( '%s not added.', 'mind/types' ), $this->name_singular ),
			/* translators: %s: Singular taxonomy name */
			5 => sprintf( __( '%s not updated.', 'mind/types' ), $this->name_singular ),
			/* translators: %s: Plural taxonomy name */
			6 => sprintf( __( '%s deleted.', 'mind/types' ), $this->name_plural ),
		];

		return $messages;
	}
}
