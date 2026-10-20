<?php

namespace Types\Tests;

use Mantle\Testkit\TestCase;
use Types\Post_Type;
use Types\Taxonomy;

/**
 * Tests for registering post types and taxonomies.
 */
class Post_Type_Test extends TestCase {
	public function test_register_post_type(): void {
		Post_Type::register( [
			'book' => [
				'name_singular' => 'Book',
				'name_plural'   => 'Books',
				'args'          => [
					'public' => true,
				],
			],
		] );

		$this->assertTrue( post_type_exists( 'book' ) );

		$post_type = get_post_type_object( 'book' );

		$this->assertTrue( $post_type->public );
		$this->assertSame( 'Books', $post_type->labels->name );
		$this->assertSame( 'Book', $post_type->labels->singular_name );
		$this->assertSame( 'Add New Book', $post_type->labels->add_new_item );
	}

	public function test_register_post_type_defaults(): void {
		Post_Type::register( [
			'note' => [
				'name_singular' => 'Note',
				'args'          => [],
			],
		] );

		$post_type = get_post_type_object( 'note' );

		$this->assertFalse( $post_type->public );
		$this->assertTrue( $post_type->show_ui );
		// The plural name falls back to the singular name.
		$this->assertSame( 'Note', $post_type->labels->name );
	}

	public function test_rename_post_type(): void {
		Post_Type::rename( 'book', 'Novel', 'Novels' );

		register_post_type( 'book', [ 'label' => 'Books' ] );

		$labels = get_post_type_object( 'book' )->labels;

		$this->assertSame( 'Novels', $labels->name );
		$this->assertSame( 'Novel', $labels->singular_name );
	}

	public function test_register_taxonomy(): void {
		register_post_type( 'book' );

		Taxonomy::register( [
			'genre' => [
				'name_singular'  => 'Genre',
				'name_plural'    => 'Genres',
				'for_post_types' => [ 'book' ],
				'args'           => [
					'public'       => true,
					'hierarchical' => true,
				],
			],
		] );

		$this->assertTrue( taxonomy_exists( 'genre' ) );
		$this->assertTrue( is_object_in_taxonomy( 'book', 'genre' ) );

		$taxonomy = get_taxonomy( 'genre' );

		$this->assertTrue( $taxonomy->hierarchical );
		$this->assertSame( 'Genres', $taxonomy->labels->name );
		$this->assertSame( 'Genre', $taxonomy->labels->singular_name );
	}
}
