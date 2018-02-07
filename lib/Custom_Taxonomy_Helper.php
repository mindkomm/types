<?php

namespace Theme;

/**
 * Class Custom_Taxonomy_Helper
 *
 * @package Theme
 */
class Custom_Taxonomy_Helper {
	/**
	 * Register taxonomies based on an array definition.
	 *
	 * @param array $taxonomies {
	 *      An array of arrays for taxonomies, where the name of the taxonomy is the key of an array.
	 *
	 *      @type string $name_singular Singular name for taxonomy.
	 *      @type string $name_plural   Plural name for taxonomy.
	 *      @type array  $args          Arguments that get passed to taxonomy registration.
	 * }
	 */
	public static function register_taxonomies( $taxonomies ) {
		foreach ( $taxonomies as $name => $taxonomy ) {
			$default_args = [
				'public'            => false,
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_admin_column' => true,
			];

			$args = wp_parse_args( $taxonomy['args'], $default_args );

			$labels = self::get_taxonomy_labels(
				$taxonomy['name_singular'],
				$taxonomy['name_plural']
			);

			add_filter( "taxonomy_labels_{$name}", function() use ( $labels ) {
				return $labels;
			} );

			register_taxonomy( $name, $taxonomy['for_post_types'], $args );
		}
	}

	/**
	 * Get German labels for taxonomy base on singular and plural name.
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_taxonomy_labels/
	 *
	 * @param string $name_singular Singular name for taxonomy.
	 * @param string $name_plural   Plural name for taxonomy.
	 * @return array
	 */
	public static function get_taxonomy_labels( $name_singular, $name_plural ) {
		return [
			'name'                       => $name_plural,
			'singular_name'              => $name_singular,
			'menu_name'                  => $name_plural,
			'search_items'               => $name_plural . ' suchen',
			'all_items'                  => 'Alle ' . $name_plural,
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => $name_singular . ' bearbeiten',
			'view_item'                  => $name_singular . ' anschauen',
			'update_item'                => $name_singular . ' bearbeiten',
			'add_new_item'               => $name_singular . ' hinzufügen',
			'new_item_name'              => 'Neuer Name für ' . $name_singular,
			'separate_items_with_commas' => 'Einträge durch Kommas abtrennen',
			'add_or_remove_items'        => $name_plural . ' hinzufügen oder entfernen',
			'choose_from_most_used'      => 'Wähle aus den meist verwendeten ' . $name_plural,
			'not_found'                  => 'Keine ' . $name_plural . ' gefunden.',
			'no_terms'                   => 'Keine ' . $name_plural,
			'items_list_navigation'      => 'Navigation für ' . $name_plural,
			'items_list'                 => $name_plural,

			// Hide tag cloud
			'popular_items'              => null, // 'Häufig verwendete ' . $name_plural,
		];
	}
}
