<?php

/**
* Discogs API base class
*/

namespace WC_Discogs\API\Discogs;

use  \WC_Discogs\Admin\Settings;
use \Discogs\ClientFactory;

class API {

	protected $client;

	public function __construct() {

		$this->client = ClientFactory::factory([
			'defaults' => [
				'query' => [
					'key' => 'sojGGgMQZXYXgbzhlVJF',//Settings::$options['discogs_api_consumer_key'],
					'secret' => 'mqjWhtNtNjcZXbNZDsAefmaJjbLfUQYx',//Settings::$options['discogs_api_consumer_secret'],
				]
			]
		]);

	}

}
