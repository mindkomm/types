<?php

namespace Types\Tests;

use Mantle\Testing\Attributes\Environment;
use Mantle\Testkit\TestCase;
use Types\Post_Type_Page;

/**
 * Tests for the redirect of singular views in Post_Type_Page.
 */
class Post_Type_Page_Test extends TestCase {
	private Post_Type_Page $post_type_page;

	private int $page_id;

	protected function setUp(): void {
		parent::setUp();

		$this->page_id = static::factory()->page->create( [ 'post_title' => 'Library' ] );

		$this->post_type_page = new Post_Type_Page( 'book', $this->page_id, [
			'is_singular_public' => false,
		] );
		$this->post_type_page->init();

		register_post_type( 'book', [ 'public' => true ] );
		flush_rewrite_rules();
	}

	public function test_redirects_singular_view_permanently(): void {
		$book_id = static::factory()->post->create( [ 'post_type' => 'book' ] );

		$this->get( get_permalink( $book_id ) )
			->assertStatus( 301 )
			->assertRedirect( get_permalink( $this->page_id ) );
	}

	#[Environment( Environment::DEVELOPMENT )]
	public function test_redirects_singular_view_temporarily_in_development(): void {
		$book_id = static::factory()->post->create( [ 'post_type' => 'book' ] );

		$this->get( get_permalink( $book_id ) )
			->assertStatus( 302 )
			->assertRedirect( get_permalink( $this->page_id ) );
	}

	public function test_redirect_headers_limit_caching_of_permanent_redirect(): void {
		$headers = $this->post_type_page->get_redirect_headers( 301 );

		$this->assertSame( 'max-age=' . HOUR_IN_SECONDS, $headers['Cache-Control'] );
		$this->assertMatchesRegularExpression( '/^\w{3}, \d{2} \w{3} \d{4} \d{2}:\d{2}:\d{2} GMT$/', $headers['Expires'] );
		$this->assertEqualsWithDelta( time() + HOUR_IN_SECONDS, strtotime( $headers['Expires'] ), 5 );
	}

	public function test_no_redirect_headers_for_temporary_redirect(): void {
		$this->assertSame( [], $this->post_type_page->get_redirect_headers( 302 ) );
	}
}
