<?php
/**
 * Class ReleaseTest
 *
 * @package Woocommerce_Discogs
 */


use WC_Discogs\Admin\Settings;
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

		$release = $this->_create_release( $artists_terms[0] );
		$this->assertEquals( $artists_terms[0], $release->get_artists() );

		$artists_terms[1] = 'The books';
		wp_set_object_terms( $release->post->ID, $artists_terms, $taxonomy , false );

		$this->assertEquals( implode($separator, $artists_terms), $release->get_artists( $separator ) );

	}

	function test_get_has_associated_post() {

		$release = $this->_create_release();
		$this->assertNotNull($release->post->ID);

	}

	function test_set_genres_and_styles() {

		$genre_taxonomy = self::$__NAMESPACE__ . '_genre';
		$style_taxonomy = self::$__NAMESPACE__ . '_style';

		$release = $this->_create_release( 'The Jesus and Mary Chain', 'Psychocandy' );
		$release->set_genres_and_styles();
		$genres_names = wp_get_object_terms( $release->post->ID, $genre_taxonomy, [ 'fields' => 'names' ]);
		$this->assertEquals( [ 'Rock' ], $genres_names );
		$styles_names = wp_get_object_terms( $release->post->ID, $style_taxonomy, [ 'fields' => 'names' ]);
		$this->assertEquals( [ 'Noise' ], $styles_names );

	}

	function test_set_artwork()  {

		// we need to setup a few settings like the default place holder uri
		new Settings();

		$release = $this->_create_release();

		// test that we don't want to proceed if Release already has had an artwork set
		// relying on has_artwork to tell us what to do
		$filename = ( DATA_DIR . '/images/test-artwork.jpg' );
		$contents = file_get_contents($filename);
		$upload = wp_upload_bits(basename($filename), null, $contents);
		$this->assertTrue( empty($upload['error']) );

		$attachment_id = $this->_make_attachment($upload, $release->post->ID);
		$this->assertNotNull($attachment_id);
		set_post_thumbnail($release->post->ID, $attachment_id);
		$this->assertEquals($attachment_id, $release->has_artwork());

		$already_set_attachment_id = $release->set_artwork();
		$image_infos = wp_get_attachment_image_src($attachment_id, null);
		$this->assertEquals( 1, preg_match("/test-artwork/", $image_infos[0]) );
		$this->assertEquals( $attachment_id, $already_set_attachment_id );


		// TODO
		// test product/post has parent w/ correct title


		// test not fetching artwork if an image named "Artist - Title"
		// exists in the Media Library
		$attachment_id = $this->_make_attachment( $upload );
		$attachment_title = $release->get_artists() . ' - ' . $release->post->post_title;
		wp_update_post( [ 'ID' => $attachment_id, 'post_title' => $attachment_title ]);
		$attachment = get_post( $attachment_id );
		$this->assertEquals( $attachment_title, $attachment->post_title );
		$this->assertEquals( $attachment_id, $release->set_artwork() );
		$post_thumbnail_id = get_post_thumbnail_id( $release->post->ID );
		$this->assertEquals( $attachment_id, $post_thumbnail_id );


		// test using the default placeholder image
		// first pass : it does not exist in Media Library
		$release = $this->_create_release( "Some Unknown Artist", "Some Unknown Title");
		$first_attachment_id = $release->set_artwork();
		$default_image_path_parts = explode('/', Media::$default_artwork_image_uri);
		$default_image_filename = $default_image_path_parts[ count($default_image_path_parts) - 1];
		$default_image_basename = explode('.', $default_image_filename)[0];
		$attachment_url = wp_get_attachment_url( $first_attachment_id );
		$this->assertEquals(
			1,
			preg_match("/$default_image_basename/", $attachment_url )
		);

		// second pass : it has been created before and we want to re-use it
		$release = $this->_create_release( "Some Other Unknown Artist", "Some Other Unknown Title");
		$second_attachment_id = $release->set_artwork();
		$this->assertEquals( $first_attachment_id, $second_attachment_id);



		// test that correct artwork has been attached to Release
		// the filename is transformed with the default_media_file_rename function
		$release = $this->_create_release( '16 Horsepower', 'Hoarse' );
		$first_attachment_id = $release->set_artwork();
		$attachment_url = wp_get_attachment_image_url( $first_attachment_id );
		$this->assertEquals(
			1,
			preg_match("/16-horsepower-hoarse/", $attachment_url)
		);
		$post_thumbnail_id = get_post_thumbnail_id( $release->post->ID );

		// remove file rename for the following test
		// so we'll expect the original filemane
		remove_filter( self::$__NAMESPACE__ . '_rename_file_on_attach_from_url', self::$__NAMESPACE__ . '\default_media_file_rename' );

		// test force update artwork
		$update = wp_update_post([ 'ID' => $release->post->ID, 'post_title' => 'Olden'], true);
		if (is_wp_error($update)) {
			$errors = $update->get_error_messages();
			foreach ($errors as $error) {
				echo $error;
			}
		}
		$second_attachment_id = $release->set_artwork( true );
		$attachment_url = wp_get_attachment_image_url( $second_attachment_id );
		// correct url
		$this->assertEquals(
			1,
			preg_match("/R-1308150-1208375805/", $attachment_url)
		);
		// first image has been detached & second has been attached
		$attached_images = get_attached_media( 'image',  $release->post );
		foreach( $attached_images as $attached_image) {
			$this->assertFalse( $attached_image->ID ==  $first_attachment_id, 'Previously attached image should be detached');
			$this->assertTrue( $attached_image->ID ==  $second_attachment_id, 'Newly attached image should be attached');
		}
		// featured image switch
		$post_thumbnail_id = get_post_thumbnail_id( $release->post->ID );
		$this->assertFalse( $post_thumbnail_id == $first_attachment_id, 'Previous image should not be featured image');
		$this->assertTrue( $post_thumbnail_id == $second_attachment_id, 'New image should be featured image');


	}

	function test_has_artwork() {

		// does not have artwork
		$release = $this->_create_release();
		$post_id = $release->post->ID;
		$this->assertFalse($release->has_artwork());

		// has artwork
		$filename = ( DATA_DIR . '/images/test-artwork.jpg' );
		$contents = file_get_contents($filename);
		$upload = wp_upload_bits(basename($filename), null, $contents);
		$this->assertTrue( empty($upload['error']) );

		$release =  $this->_create_release();
		$post_id = $release->post->ID;
		$attachment_id = $this->_make_attachment($upload, $post_id);
		$this->assertNotNull($attachment_id);
		set_post_thumbnail($post_id, $attachment_id);
		$this->assertEquals($attachment_id, $release->has_artwork());

		// has featured image but it is the default placeholder
		$post_id = $this->factory->post->create();
		$release =  $this->_create_release();
		$post_id = $release->post->ID;
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
		$this->assertFalse($release->has_artwork());

	}

	public function test_get_artwork() {

		$filename = ( DATA_DIR . '/images/test-artwork.jpg' );
		$contents = file_get_contents($filename);
		$upload = wp_upload_bits(basename($filename), null, $contents);
		$this->assertTrue( empty($upload['error']) );

		$release = $this->_create_release();
		$post_id = $release->post->ID;
		$attachment_id = $this->_make_attachment($upload, $post_id);
		$this->assertNotNull($attachment_id);
		set_post_thumbnail($post_id, $attachment_id);
		$attachment = get_post( $attachment_id );
		$this->assertEquals($attachment, $release->get_artwork());

	}


	/**
	* HELPERS
	**/
	function _create_release( $artist_name = 'Nick Drake', $release_title = 'Five Leaves Left') {

		$taxonomy = self::$__NAMESPACE__ . '_artist';

		$product = WC_Helper_Product::create_simple_product();

		$post_id = $product->get_id();
		$this->assertNotNull($post_id);

		$artists_terms = [];
		$artists_terms[0] = $artist_name;
		wp_set_object_terms( $post_id, $artists_terms, $taxonomy , false );

		// set title
		wp_update_post(	['ID' => $post_id, 'post_title' => $release_title ] );

		$release = new Release( $post_id );
		$this->assertEquals($release->post->post_title, $release_title );

		return $release;
	}

}
