<?php

namespace Types;

/**
 * Class Taxonomy
 */
class Taxonomy {
	/**
	 * Register taxonomies based on an array definition.
	 *
	 * @param array $taxonomies {
	 *      An array of arrays for taxonomies, where the name of the taxonomy is the key of an array.
	 *
	 *      @type string $name_singular  Singular name for taxonomy.
	 *      @type string $name_plural    Plural name for taxonomy.
	 *      @type array  $for_post_types The array of post types you want to register the taxonomy for.
	 *      @type array  $args           Arguments that get passed to taxonomy registration.
	 * }
	 */
	public static function register( $taxonomies = [] ) {
		foreach ( $taxonomies as $taxonomy => $args ) {
			$args = self::parse_args( $args );
			self::register_extensions( $taxonomy, $args );

			$for_post_types = $args['for_post_types'];

			// Defaults for taxonomy registration.
			$args = wp_parse_args( $args['args'], [
				'public'            => false,
				'hierarchical'      => false,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_tag_cloud'    => false,
			] );

			register_taxonomy( $taxonomy, $for_post_types, $args );
		}
	}

	/**
	 * Updates settings for a taxonomy.
	 *
	 * Here, you use the same settings that you also use for the `register()` funciton.
	 *
	 * Run this function before the `init` hook.
	 *
	 * @see register_taxonomy()
	 * @since 2.2.0
	 *
	 * @param array $taxonomies An associative array of post types and its arguments that should be updated. See the
	 *                          `register()` function for all the arguments that you can use.
	 */
	public static function update( $taxonomies = [] ) {
		foreach ( $taxonomies as $taxonomy => $args ) {
			$args = self::parse_args( $args );
			self::register_extensions( $taxonomy, $args );

			if ( isset( $args['args'] ) ) {
				add_filter( 'register_taxonomy_args', function( $defaults, $name ) use ( $taxonomy, $args ) {
					if ( $taxonomy !== $name ) {
						return $defaults;
					}

					$args = wp_parse_args( $args['args'], $defaults );

					return $args;
				}, 10, 2 );
			}
		}
	}

	/**
	 * Renames a taxonomy.
	 *
	 * Run this function before the `init` hook.
	 *
	 * @since 2.2.0
	 *
	 * @param string $taxonomy      The taxonomy to rename.
	 * @param string $name_singular The new singular name.
	 * @param string $name_plural   The new plural name.
	 */
	public static function rename( $taxonomy, $name_singular, $name_plural ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		( new Taxonomy_Labels( $taxonomy, $name_singular, $name_plural ) )->init();
	}

	/**
	 * Adds missing arguments for taxonomy.
	 *
	 * @since 2.2.0
	 *
	 * @param array $args An array of arguments.
	 *
	 * @return mixed
	 */
	private static function parse_args( $args ) {
		if ( isset( $args['name_singular'] ) && ! isset( $args['name_plural'] ) ) {
			$args['name_plural'] = $args['name_singular'];
		}

		return $args;
	}

	/**
	 * Registers extensions.
	 *
	 * @since 2.2.0
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @param array  $args      Arguments for the taxonomy.
	 */
	private static function register_extensions( $taxonomy, $args ) {
		if ( isset( $args['name_singular'] ) ) {
			( new Taxonomy_Labels( $taxonomy, $args['name_singular'], $args['name_plural'] ) )->init();
		}
	}
}
