<?php

namespace Types;

/**
 * Class Post_Type_Page
 *
 * This class is partially inspired by the Page for Post Type plugin.
 *
 * @link https://github.com/humanmade/page-for-post-type
 *
 * @since 2.3
 */
class Post_Type_Page {
	/**
	 * Post Type.
	 *
	 * @var null|string A custom post type slug.
	 */
	private $post_type = null;

	/**
	 * Post ID
	 *
	 * @var null|int ID of the page for the archive.
	 */
	private $post_id = null;

	/**
	 * Args.
	 *
	 * @var array An array of args.
	 */
	private $args = [];

	/**
	 * Post_Type_Page constructor.
	 *
	 * @param string $post_type A post type slug.
	 * @param int    $post_id   The ID of the post to use as the archive page.
	 * @param array  $args      An array of arguments for the post type archive page.
	 */
	public function __construct( $post_type, $post_id, $args ) {
		$this->post_type = $post_type;
		$this->post_id   = (int) $post_id;

		$this->args = wp_parse_args( $args, [
			'is_singular_public' => true,
		] );
	}

	/**
	 * Inits hooks.
	 */
	public function init() {
		add_filter( 'register_post_type_args', [ $this, 'update_archive_slug' ], 10, 2 );
		add_filter( 'wp_nav_menu_objects', [ $this, 'filter_wp_nav_menu_objects' ], 1 );

		if ( ! $this->args['is_singular_public'] ) {
			add_action( 'template_redirect', [ $this, 'template_redirect' ] );
		}
	}

	/**
	 * Update the archive slug to be the same as the page that should be used for the archive.
	 *
	 * @param array  $args      Post type registration arguments.
	 * @param string $post_type Post type name.
	 *
	 * @return mixed
	 */
	public function update_archive_slug( $args, $post_type ) {
		if ( $post_type !== $this->post_type ) {
			return $args;
		}

		$args['has_archive'] = trim(
			wp_make_link_relative( get_permalink( $this->post_id ) ),
			'/'
		);

		return $args;
	}

	/**
	 * Redirects singular page views to the post type archive page.
	 */
	public function template_redirect() {
		if ( is_singular( $this->post_type ) ) {
			wp_safe_redirect( get_post_type_archive_link( $this->post_type ), 301 );
			exit;
		}
	}

	/**
	 * Make sure menu items for our pages get the correct classes assigned
	 *
	 * @param array $menu_items Array of menu items.
	 *
	 * @return array
	 */
	public function filter_wp_nav_menu_objects( $menu_items ) {
		foreach ( $menu_items as &$item ) {
			if ( 'page' !== $item->object || (int) $item->object_id !== $this->post_id ) {
				continue;
			}

			if ( is_singular( $this->post_type ) ) {
				$item->current_item_parent = true;
				$item->classes[]           = 'current-menu-item-parent';

				$menu_items = $this->add_ancestor_class( $item, $menu_items );
			}

			if ( is_post_type_archive( $this->post_type ) ) {
				$item->classes[] = 'current-menu-item';
				$item->current   = true;

				$menu_items = $this->add_ancestor_class( $item, $menu_items );
			}
		}

		return $menu_items;
	}

	/**
	 * Recursively add ancestor class and properties.
	 *
	 * @param object $child       Child menu item.
	 * @param array  $menu_items  All menu items.
	 * @param bool   $with_parent Whether to add the direct parent property and class.
	 *
	 * @return array Update menu items.
	 */
	protected function add_ancestor_class( $child, $menu_items, $with_parent = true ) {
		// Bailout if menu item has no parent.
		if ( ! (int) $child->menu_item_parent ) {
			return $menu_items;
		}

		foreach ( $menu_items as $item ) {
			if ( (int) $item->ID === (int) $child->menu_item_parent ) {
				if ( $with_parent ) {
					$item->current_item_parent = true;
					$item->classes[]           = 'current-menu-item-parent';
				}

				$item->current_item_ancestor = true;
				$item->classes[]             = 'current-menu-item-ancestor';

				if ( (int) $item->menu_item_parent ) {
					$menu_items = $this->add_ancestor_class( $item, $menu_items, false );
				}

				break;
			}
		}

		return $menu_items;
	}
}
