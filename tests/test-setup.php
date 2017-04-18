<?php
/**
 * Class SetupTest
 *
 * @package Woocommerce_Discogs
*/


class SetupTest extends WP_UnitTestCase {

	static $__NAMESPACE__ = 'WC_Discogs';

	function setUp(){
		call_user_func( self::$__NAMESPACE__ . '\register_product_categories');
	}

	/** should pass always
		set up to make sure the wp functions used in the tests
		are used the correct way
	**/
	function test_category_taxonomy() {
		$taxonomy = 'category';

		// is registered
		$this->assertEquals(
			true,
			taxonomy_exists($taxonomy),
			'category is not registered'
		);

		// is associated with the correct post type
		$this->assertEquals(
			true,
			is_object_in_taxonomy( 'post', $taxonomy )
		);
	}

	// Music Release Product category
	function test_music_release_product_category() {
		$taxonomy = 'product_cat';
		$term_name = 'Music Release';
		$term_slug = 'music-release';

		$this->assertEquals(
			true,
			taxonomy_exists($taxonomy),
			$taxonomy . ' is not registered'
		);

		$term = term_exists( $term_slug, $taxonomy );
		$this->assertEquals( true, $term !== 0 && $term !== null );

		$term = get_term( $term['term_id'], $taxonomy );
		$this->assertEquals( 'Music Release', $term->name );
	}

	// Artist
	function test_artist_taxonomy() {
		$taxonomy = self::$__NAMESPACE__  . '_artist';

		// is registered
		$this->assertEquals(
			true,
			taxonomy_exists(self::$__NAMESPACE__  . '_artist'),
			self::$__NAMESPACE__  . '_artist is not registered'
		);

		// is associated with the correct post type
		$this->assertEquals(
			true,
			is_object_in_taxonomy( 'product', $taxonomy )
		);
	}

	// Genre
	function test_genre_taxonomy() {
		$taxonomy = self::$__NAMESPACE__  . '_genre';

		// is registered
		$this->assertEquals(
			true,
			taxonomy_exists(self::$__NAMESPACE__  . '_genre'),
			self::$__NAMESPACE__  . '_genre is not registered'
		);

		// is associated with the correct post type
		$this->assertEquals(
			true,
			is_object_in_taxonomy( 'product', $taxonomy )
		);
	}

	// Style
	function test_style_taxonomy() {
		$taxonomy = self::$__NAMESPACE__  . '_style';

		// is registered
		$this->assertEquals(
			true,
			taxonomy_exists(self::$__NAMESPACE__  . '_style'),
			self::$__NAMESPACE__  . '_style is not registered'
		);

		// is associated with the correct post type
		$this->assertEquals(
			true,
			is_object_in_taxonomy( 'product', $taxonomy )
		);
	}


	// default media rename
	function test_default_media_rename() {

		$taxonomy = SetupTest::$__NAMESPACE__ . '_artist';

		$artist = 'Nick Drake';
		$title = 'Five Leaves Left';

		$post_id = $this->factory->post->create();

		$artists_terms = [];
		$artists_terms[0] = 'Nick Drake';
		wp_set_object_terms( $post_id, $artists_terms, $taxonomy , false );

		wp_update_post( [ 'ID' => $post_id, 'post_title' => $title ]);
		$post = get_post( $post_id );
		$this->assertEquals( $title, $post->post_title);

		$original_filename = '8437506-RFDFD.jpg';
		$expected_filename = 'nick-drake-five-leaves-left.jpg';
		$renamed_filename = call_user_func( SetupTest::$__NAMESPACE__ . '\default_media_file_rename', $original_filename, $post_id);
		$this->assertEquals( $expected_filename, $renamed_filename );
	}

}
