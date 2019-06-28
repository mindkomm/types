<?php

namespace Types;

/**
 * Class Post_Type
 */
class Post_Type {
	/**
	 * Registers post types based on an array definition.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post_types    {
	 *     An associative array of post types and the arguments used for registering the post type.
	 *
	 *     @type string $name_singular Singular name for post type.
	 *     @type string $name_plural   Optional. Plural name for post type. If not set, will be the
	 *                                 same as $name_singular.
	 *     @type array  $args          Arguments that get passed to post type registration.
	 *     @type array  $query         Custom query parameters for frontend and backend query.
	 *     @type array  $admin_columns An array of admin_column definitions.
	 * }
	 */
	public static function register( $post_types = [] ) {
		foreach ( $post_types as $post_type => $args ) {
			$args = self::parse_args( $args );

			self::register_extensions( $post_type, $args );

			// Defaults for post registration.
			$args = wp_parse_args( $args['args'], [
				'description'       => $args['name_plural'],
				'public'            => false,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
			] );

			register_post_type( $post_type, $args );
		}
	}

	/**
	 * Updates settings for a post type.
	 *
	 * Here, you use the same settings that you also use for the `register()` function.
	 * Run this function before the `init` hook.
	 *
	 * @see   register_post_type()
	 * @since 2.2.0
	 *
	 * @param array $post_types An associative array of post types and its arguments that should be
	 *                          updated. See the `register()` function for all the arguments that
	 *                          you can use.
	 */
	public static function update( $post_types = [] ) {
		foreach ( $post_types as $post_type => $args ) {
			$args = self::parse_args( $args );

			self::register_extensions( $post_type, $args );

			if ( isset( $args['args'] ) ) {
				add_filter( 'register_post_type_args', function( $defaults, $name ) use ( $post_type, $args ) {
					if ( $post_type !== $name ) {
						return $defaults;
					}

					$args = wp_parse_args( $args['args'], $defaults );

					return $args;
				}, 10, 2 );
			}
		}
	}

	/**
	 * Renames a post type.
	 *
	 * Run this function before the `init` hook.
	 *
	 * @since 2.1.1
	 *
	 * @param string $post_type     The post type to rename.
	 * @param string $name_singular The new singular name.
	 * @param string $name_plural   The new plural name.
	 */
	public static function rename( $post_type, $name_singular, $name_plural ) {
		if ( ! post_type_exists( $post_type ) ) {
			return;
		}

		( new Post_Type_Labels( $post_type, $name_singular, $name_plural ) )->init();
	}

	/**
	 * Registers admin column settings for a post type.
	 *
	 * @since 2.1.0
	 *
	 * @param array $post_types An associative array of post types, where the name of the post type
	 *                          is the key of an array that defines the admin column settings for
	 *                          this post type.
	 */
	public static function admin_columns( $post_types = [] ) {
		foreach ( $post_types as $name => $column_settings ) {
			( new Post_Type_Columns( $name, $column_settings ) )->init();
		}
	}

	/**
	 * Adds missing arguments for post type.
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
	 * @param string $post_type The post type name.
	 * @param array  $args      Arguments for the post type.
	 */
	private static function register_extensions( $post_type, $args ) {
		if ( isset( $args['name_singular'] ) ) {
			( new Post_Type_Labels( $post_type, $args['name_singular'], $args['name_plural'] ) )->init();
		}

		if ( isset( $args['query'] ) ) {
			( new Post_Type_Query( $post_type, $args['query'] ) )->init();
		}

		if ( isset( $args['admin_columns'] ) ) {
			( new Post_Type_Columns( $post_type, $args['admin_columns'] ) )->init();
		}

		if ( isset( $args['page_for_archive'] ) ) {
			$page_for_archive = wp_parse_args( $args['page_for_archive'], [
				'post_id'            => null,
				'is_singular_public' => true,
				'customizer_section' => '',
				'show_post_state'    => true,
			] );

			( new Post_Type_Page(
				$post_type,
				$page_for_archive['post_id'],
				$page_for_archive
			) )->init();

			if ( ! empty( $page_for_archive['customizer_section'] ) ) {
				( new Post_Type_Page_Option(
					$post_type,
					$page_for_archive['customizer_section']
				) )->init();
			}

			if ( $page_for_archive['show_post_state'] ) {
				( new Post_Type_Page_State( $post_type ) )->init();
			}
		}
	}
}
