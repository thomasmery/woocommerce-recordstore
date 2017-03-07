<?php
/**
 * Class ApiTest
 *
 * @package Woocommerce_Discogs
 */

use WC_Discogs\Api\Discogs\Database;

/**
 * Sample test case.
 */
class ApiTest extends WP_UnitTestCase {

	/**
	 * Search
	 */
	function test_search() {
		$discogs_api_db = new Database();
		$response = $discogs_api_db->search(
			[
				'title' => 'Five Leaves Left',
				'artist' => 'Nick Drake',
				'type' => 'release',
			]
		);
		$results = $response['results'];
		$this->assertTrue( ! empty($results) );
		$release = $results[0];
		$this->assertEquals( 'Nick Drake - Five Leaves Left', $release['title']);
	}
	 */
	function test_get_master_release() {

		$discogs_api_db = new Database();
		$results = $discogs_api_db->get_main_release( 'Hoarse', '16 Horsepower');

		$this->assertEquals( 'https://api.discogs.com/releases/1823745', $results['main_release_url']);

	}

	function test_get_artwork_uri() {

		$discogs_api_db = new Database();

		$uri = $discogs_api_db->get_artwork_uri( 'Hoarse', '16 Horsepower' );
		$this->assertEquals(
			'https://api-img.discogs.com/VmUwWPaRh22XrcjmWEdpgHEPIgo=/fit-in/500x500/filters:strip_icc():format(jpeg):mode_rgb():quality(90)/discogs-images/R-1823745-1245810570.jpeg.jpg',
			$uri);

		$uri = $discogs_api_db->get_artwork_uri( 'I Matter', 'Thomas Mery' );
		$this->assertEquals(
			'https://api-img.discogs.com/f6dHeYEBbxwRrEhly06Q5coDuio=/fit-in/174x174/filters:strip_icc():format(jpeg):mode_rgb():quality(90)/discogs-images/R-1774021-1329680830.jpeg.jpg',
			$uri);
	}
}

