<?php

namespace Types\Tests;

use Mantle\Testing\Concerns\Admin_Screen;
use Mantle\Testkit\TestCase;
use Types\Post_Type_Columns;

/**
 * Tests for admin columns in Post_Type_Columns.
 */
class Post_Type_Columns_Test extends TestCase {
	use Admin_Screen;

	/**
	 * Post IDs by title.
	 *
	 * @var int[]
	 */
	private array $posts = [];

	protected function setUp(): void {
		parent::setUp();

		register_post_type( 'book', [ 'public' => true ] );

		// Makes is_admin() true and sets the global $typenow to 'book'.
		set_current_screen( 'edit-book' );

		( new Post_Type_Columns( 'book', [
			'date'        => false,
			'book_author' => [
				'title'      => 'Author',
				'sortable'   => true,
				'searchable' => true,
			],
			'price'       => [
				'title'     => 'Price',
				'sortable'  => true,
				'orderby'   => 'meta_value_num',
				'transform' => fn( $value ) => 'CHF ' . $value,
			],
			'publisher'   => [
				'title'        => 'Publisher',
				'column_order' => 5,
			],
			'length'      => [
				'title' => 'Pages',
				'type'  => 'custom',
				'value' => fn( $post_id ) => get_post_meta( $post_id, 'page_count', true ) . ' pages',
			],
		] ) )->init();

		$books = [
			'Dune'        => [
				'book_author' => 'Frank Herbert',
				'price'       => '12.9',
				'publisher'   => 'Chilton',
				'page_count'  => '412',
			],
			'Neuromancer' => [
				'book_author' => 'William Gibson',
				'price'       => '9.5',
				'publisher'   => 'Ace',
				'page_count'  => '271',
			],
			'Hyperion'    => [
				'book_author' => 'Dan Simmons',
				'price'       => '105',
				'publisher'   => 'Doubleday',
				'page_count'  => '482',
			],
		];

		$days = 0;

		foreach ( $books as $title => $meta ) {
			$this->posts[ $title ] = static::factory()->post->create( [
				'post_type'  => 'book',
				'post_title' => $title,
				'post_date'  => gmdate( 'Y-m-d H:i:s', strtotime( '-' . $days++ . ' days' ) ),
				'meta_input' => $meta,
			] );
		}
	}

	/**
	 * Runs a query as the main query of the post list in the admin.
	 *
	 * @param array $args Query arguments.
	 * @return string[] Post titles in the order they were returned.
	 */
	protected function query_titles( array $args ): array {
		$query = new \WP_Query();

		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['wp_the_query'] = $query;

		$posts = $query->query( array_merge( [
			'post_type'      => 'book',
			'posts_per_page' => -1,
		], $args ) );

		return wp_list_pluck( $posts, 'post_title' );
	}

	public function test_columns(): void {
		$columns = apply_filters( 'manage_edit-book_columns', [
			'cb'    => '<input type="checkbox" />',
			'title' => 'Title',
			'date'  => 'Date',
		] );

		$this->assertSame(
			[ 'cb', 'publisher', 'title', 'book_author', 'price', 'length' ],
			array_keys( $columns )
		);
		$this->assertSame( 'Author', $columns['book_author'] );
	}

	public function test_sortable_columns(): void {
		$sortable = apply_filters( 'manage_edit-book_sortable_columns', [] );

		$this->assertArrayHasKey( 'book_author', $sortable );
		$this->assertArrayHasKey( 'price', $sortable );
		$this->assertArrayNotHasKey( 'publisher', $sortable );
		$this->assertArrayNotHasKey( 'length', $sortable );
	}

	public function test_sort_by_meta_column(): void {
		$this->assertSame( [ 'Hyperion', 'Dune', 'Neuromancer' ], $this->query_titles( [
			'orderby' => 'book_author',
			'order'   => 'ASC',
		] ) );

		$this->assertSame( [ 'Neuromancer', 'Dune', 'Hyperion' ], $this->query_titles( [
			'orderby' => 'book_author',
			'order'   => 'DESC',
		] ) );
	}

	public function test_sort_by_numeric_meta_column(): void {
		$this->assertSame( [ 'Neuromancer', 'Dune', 'Hyperion' ], $this->query_titles( [
			'orderby' => 'price',
			'order'   => 'ASC',
		] ) );
	}

	public function test_secondary_query_is_not_sorted(): void {
		$query = new \WP_Query( [
			'post_type' => 'book',
			'orderby'   => 'price',
		] );

		$this->assertStringNotContainsString( 'price', $query->request );
	}

	public function test_search_meta_column(): void {
		$this->assertSame( [ 'Neuromancer' ], $this->query_titles( [ 's' => 'Gibson' ] ) );
	}

	public function test_search_also_matches_title(): void {
		$this->assertSame( [ 'Dune' ], $this->query_titles( [ 's' => 'Dune' ] ) );
	}

	public function test_meta_column_content_with_transform(): void {
		$this->expectOutputString( 'CHF 12.9' );

		do_action( 'manage_book_posts_custom_column', 'price', $this->posts['Dune'] );
	}

	public function test_custom_column_content(): void {
		$this->expectOutputString( '271 pages' );

		do_action( 'manage_book_posts_custom_column', 'length', $this->posts['Neuromancer'] );
	}
}
