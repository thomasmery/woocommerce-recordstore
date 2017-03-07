<?php
/**
 * Class MediaTest
 *
 * @package Woocommerce_Discogs
 */

use WC_Discogs\Media;

/**
 * Sample test case.
 */
class MediaTest extends WP_UnitTestCase {

	/**
	 * testing if we can accurately find an attachment by its title
	 */
	function test_get_attachment_by_title() {

		// create attachment
		$title = 'A test attachment';
		$post_id = $this->factory->attachment->create([ 'post_title' => $title, 'post_status' => 'inherit' ]);
		$this->assertNotNull($post_id);
		$this->assertInstanceOf( 'WP_Post', Media::get_attachment_by_title( $title ) );

	}

}
