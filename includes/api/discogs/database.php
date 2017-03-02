<?php

/**
* Discogs Database API class
*/

namespace WC_Discogs\API\Discogs;

use  \WC_Discogs\Admin\Settings;
use WC_Discogs\Api\Discogs\API;

class Database extends API {

	public function __construct() {
		parent::__construct();
	}

	public function search( $params = [] ) {

		try {

			$result = $this->client->search( $params );

		}
		catch (Exception $error) {
			echo $error->getMessage();
		}

		return $result;

	}

	public function get_release($title, $artist = '', $type = 'release') {

		$body = $this->search( [
			'title' => $title,
			'artist' => $artist,
			'type' => $type,
		] );

		if ( empty($body['results']) ) {
			return null;
		}

		$id = $body['results'][0]['id'];

		switch($type) {
			case 'release':
				$release = $this->client->getRelease( [ 'id' => $id ] );
				break;
			case 'master':
				$release = $this->client->getMaster( [ 'id' => $id ] );
				break;
			default:
				$release = null;
		}

		return $release;
	}

	public function get_master_release($title, $artist = '') {
		return $this->get_release($title, $artist, 'master');
	}

	/**
	* Try to get a Release - not necessarily a Master Release
	* but try to get a Master Release first
	*/
	public function get_main_release($title, $artist = '') {

		$release = $this->get_master_release($title, $artist);
		if( ! $release ) {
			$release = $this->get_release($title, $artist);
		}
		return $release;

	}

	public function get_artwork_uri( $artist, $title ) {
		$release = $this->get_main_release($artist, $title);
		if ($release) {
			return $release['images'][0]['uri'];
		}
		return Settings::$options['default_record_image_uri'];
	}


}
