<?php
/**
 * Class ApiTest
 *
 * @package Woocommerce_Discogs
 */

use WC_Discogs\API\Discogs\Database;

/**
 * Sample test case.
 */
class APITest extends WP_UnitTestCase {

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

	/**
	 * Master release
	 */
	function test_get_master_release() {

		$discogs_api_db = new Database();

		$results = $discogs_api_db->get_main_release([
			'artist' => '16 Horsepower',
			'title' => 'Hoarse',
		]);
		$this->assertEquals( 'https://api.discogs.com/releases/1823745', $results['main_release_url']);

		$results = $discogs_api_db->get_main_release([
			'artist' => 'Björk',
			'title' => 'Debut',
		]);
		$this->assertEquals( '34486', $results['id']);

	}

	/**
	* Artwork URI
	*/
	function test_get_artwork_uri() {

		$discogs_api_db = new Database();

		$uri = $discogs_api_db->get_artwork_uri([
			'artist' => '16 Horsepower',
			'title' => 'Hoarse',
		]);
		$this->assertEquals(
			'https://api-img.discogs.com/VmUwWPaRh22XrcjmWEdpgHEPIgo=/fit-in/500x500/filters:strip_icc():format(jpeg):mode_rgb():quality(90)/discogs-images/R-1823745-1245810570.jpeg.jpg',
			$uri
		);

		$uri = $discogs_api_db->get_artwork_uri([
			'artist' => 'Thomas Mery',
			'title' => 'I Matter',
		]);
		$this->assertEquals(
			'https://api-img.discogs.com/f6dHeYEBbxwRrEhly06Q5coDuio=/fit-in/174x174/filters:strip_icc():format(jpeg):mode_rgb():quality(90)/discogs-images/R-1774021-1329680830.jpeg.jpg',
			$uri
		);

		// master release has no artwork
		// we should get the main_release test_get_artwork_uri
		$uri = $discogs_api_db->get_artwork_uri([
			'artist' => 'Nick Drake',
			'title' => 'Five Leaves Left',
		]);
		$this->assertEquals(
			'https://api-img.discogs.com/tg4I1j1F2lPRu-9Pt_uwjcO1Cxg=/fit-in/394x394/filters:strip_icc():format(jpeg):mode_rgb():quality(90)/discogs-images/R-467112-1258034866.jpeg.jpg',
			$uri
		);
	}

	/**
	* Genres & Styles
	*/
	function test_get_genres() {

		$discogs_api_db = new Database();
		$expected = [
			'Rock',
		];
		$actual = $discogs_api_db->get_genres([
			'artist' => '16 Horsepower',
			'title' => 'Hoarse',
		]);
		$this->assertEquals( $expected, $actual );
	}

	function test_get_styles() {
		$discogs_api_db = new Database();
		$expected = [
			'Alternative Rock',
			'Country Rock',
		];
		$actual = $discogs_api_db->get_styles([
			'artist' => '16 Horsepower',
			'title' => 'Hoarse',
		]);
		$this->assertEquals( $expected, $actual );

		$expected = [
			'Art Rock',
        	'Chanson'
		];
		$actual = $discogs_api_db->get_styles([
			'artist' => 'Alain bashung',
			'title' => 'Fantaisie militaire',
		]);
		$this->assertEquals( $expected, $actual );

	}

	function test_get_tracklist() {
		$discogs_api_db = new Database();
		$tracklist = $discogs_api_db->get_tracklist([
			'artist' => '16 Horsepower',
			'title' => 'Hoarse',
		]);

		$expected = [
			'duration' => '5:45',
			'position' => '8',
			'type_' => 'track',
			'title' => 'South Pennsylvania Waltz'
		];
		$actual = $tracklist[ 7 ];
		$this->assertEquals( $expected, $actual );

		$expected = '11';
		$actual = $tracklist[ 10 ][ 'position' ];
		$this->assertEquals( $expected, $actual );

		$expected = 'Joy Division';
		$actual = $tracklist[ 10 ][ 'extraartists' ][ 0 ][ 'name' ];
		$this->assertEquals( $expected, $actual );
	}

}

