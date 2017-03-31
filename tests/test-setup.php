<?php

/**
 * Class SetupTest
 *
 * @package Woocommerce_Discogs
 */
class SetupTest extends WP_UnitTestCase {

	static $__NAMESPACE__ = 'WC_Discogs';

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

}
