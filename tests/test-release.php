<?php
/**
 * Class ReleaseTest
 *
 * @package Woocommerce_Discogs
 */


use WC_Discogs\API\Discogs\Database;
use WC_Discogs\Release;
use WC_Discogs\Media;

/**
 * Release Class test case.
 */
class ReleaseTest extends WP_UnitTestCase {

	static $__NAMESPACE__ = 'WC_Discogs';

	/**
	 * testing if we can accurately tell of a Release has artwork or not
	 */
	function test_get_artists() {

		$taxonomy = self::$__NAMESPACE__ . '_artist';
		$separator = " | ";

		$post_id = $this->factory->post->create();
		$this->assertNotNull($post_id);

		$artists_terms = [];
		$artists_terms[0] = 'Nick Drake';
		wp_set_object_terms( $post_id, $artists_terms, $taxonomy , false );

		$record = new Release( $post_id );
		$this->assertEquals( $artists_terms[0], $record->get_artists() );

		$artists_terms[1] = 'The books';
		wp_set_object_terms( $post_id, $artists_terms, $taxonomy , false );

		$this->assertEquals( implode($separator, $artists_terms), $record->get_artists( $separator ) );

	}

	function test_get_has_associated_post() {

		$post_id = $this->factory->post->create();
		$this->assertNotNull($post_id);

		$record = new Release( $post_id );
		$this->assertNotNull($record->post->ID);

	}

	function test_get_has_artwork() {

		// does not have artwork
		$post_id = $this->factory->post->create();
		$record = new Release( $post_id );
		$this->assertFalse($record->has_artwork());


		// has artwork
		$filename = ( DATA_DIR . '/images/test-artwork.jpg' );
		$contents = file_get_contents($filename);
		$upload = wp_upload_bits(basename($filename), null, $contents);
		$this->assertTrue( empty($upload['error']) );

		$post_id = $this->factory->post->create();
		$record = new Release( $post_id );
		$attachment_id = $this->_make_attachment($upload, $post_id);
		$this->assertNotNull($attachment_id);
		set_post_thumbnail($post_id, $attachment_id);
		$this->assertTrue($record->has_artwork());

		// has featured image but it is the default placeholder
		$post_id = $this->factory->post->create();
		$record = new Release( $post_id );
		$attachment_id = Media::attach_from_url(Media::$default_artwork_image_uri, $post_id);
		$this->assertTrue(is_int($attachment_id));
		$image_infos = wp_get_attachment_image_src($attachment_id, null);
		$image_src = $image_infos[0];
		$default_image_path_parts = explode('/', Media::$default_artwork_image_uri);
		$default_image_filename = $default_image_path_parts[ count($default_image_path_parts) - 1];
		$default_image_basename = explode('.', $default_image_filename)[0];
		$this->assertEquals(
			1,
			preg_match(
				"/$default_image_basename/",
				$image_src
			)
		);
		$this->assertFalse($record->has_artwork());

	}

}
