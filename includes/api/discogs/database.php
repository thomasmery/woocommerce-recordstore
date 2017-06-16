<?php

/**
* Discogs Database API class
*/

namespace WC_Discogs\API\Discogs;

use WC_Discogs\Admin\Settings;
use WC_Discogs\API\Discogs\Resource;

class Database extends Resource {

	public function __construct( array $config = [] ) {
		parent::__construct( $config );
	}

	public function get_artwork_uri( array $params = [] ) {
		$release = $this->get_main_release($params);
		$uri = Settings::$options['default_record_image_uri'];
		if ($release) {
			if( isset($release['images'][0]) ) {
				$uri = $release['images'][0]['uri'];
				if( $uri === '' ) {
					if ( isset( $release['main_release'] ) ) {
						$release = $this->client->getRelease( [ 'id' => $release['main_release'] ] );
						$uri = $release['images'][0]['uri'];
					}
				}
			}
		}

		return $uri;
	}

	public function get_genres( array $params ) {
		$release = $this->get_main_release($params);
		$genres = [];
		if ($release) {
			return isset( $release['genres'] ) ? $release['genres'] : [];
		}
		return $genres;
	}

	public function get_styles( array $params ) {
		$release = $this->get_main_release($params);
		$styles = [];
		if ($release) {
			return isset( $release['styles'] ) ? $release['styles'] : [];
		}
		return $styles;
	}

	public function get_tracklist( array $params ) {
		$release = $this->get_main_release($params);
		$tracklist = [];
		if ($release) {
			return $release['tracklist'];
		}
		return $tracklist;
	}

	public function get_year( array $params ) {
		$release = $this->get_main_release($params);
		if ($release) {
			return $release['year'];
		}
		return null;
	}

	/**
	* Gettin' releases
	*******************/

	public function search( array $params = [] ) {

		/**
		* @since 1.0.0
		* allows to modify the query just before it is sent to Discogs
		*/
		$params = apply_filters( __NAMESPACE__ . '\search_params', $params );

        // var_dump($params);

		$cache_key =  "WC_Discogs\API\Discogs_search_" . md5(serialize($params));

		if( $result = wp_cache_get( $cache_key ) ) {
			return $result;
		}

		try {
			$result = $this->client->search( $params );
			wp_cache_set( $cache_key, $result );
		}
		catch (Exception $error) {
			echo $error->getMessage();
		}

		return $result;

	}

	public function get_release( array $params ) {

		$type = isset($params['type']) ? $params['type'] : 'release';

		$response = $this->search( [
			'q' => $params['artist'],
			'title' => $params['title'],
			'type' => $type,
		] );

		if ( empty($response['results']) ) {
			return null;
		}

		$id = $response['results'][0]['id'];

		$cache_key =  "WC_Discogs\API\Discogs_$type-" . md5(serialize($params));

		switch($type) {
			case 'release':
				if( $release = wp_cache_get( $cache_key ) ) {
					break;
				}
				$release = $this->client->getRelease( [ 'id' => $id ] );
				wp_cache_set( $cache_key, $release );
				break;
			case 'master':
				if( $release = wp_cache_get( $cache_key ) ) {
					break;
				}
				$release = $this->client->getMaster( [ 'id' => $id ] );
				wp_cache_set( $cache_key, $release );
				break;
			default:
				$release = null;
		}

		return $release;
	}

	public function get_master_release( array $params ) {
		$params['type'] = 'master';
		return $this->get_release($params);
	}

	/**
	* Try to get a Release - not necessarily a Master Release
	* but try to get a Master Release first
	*/
	public function get_main_release( array $params ) {

		$release = null;

		$defaults = [
			'type' => 'master'
		];

		$params = wp_parse_args( $params, $defaults );

		// try Master Release
		// by default
		if( $params['type'] === 'master') {
			$release = $this->get_master_release( $params );
		}
		// try Main Release if no master found
		// or we want to skip master in certain cases
		// specifying 'type' => 'release' (or anything but 'master' for the moment)
		if( ! $release ) {
			$params['type'] = 'release';
			$release = $this->get_release( $params );
		}
		// try with artist + title as the main query param
		if( ! $release && empty($params['final']) ) {
			add_filter(
				__NAMESPACE__ . '\search_params',
				function( $params ) {
					if( ! isset($params['q']) || ! isset($params['title']) ) {
						return $params;
					}

					$params['q'] .= '+' . $params['title'];
					unset($params['title']);

					return $params;
				},
				999
			);
			// let's go again
			$params['final'] = true;
			$params['type'] = 'master';
			$release = $this->get_main_release( $params );
		}
		return $release;

	}


}
