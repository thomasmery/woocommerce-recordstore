<?php

/**
* Discogs API base class
*/

namespace WC_Discogs\API\Discogs;

use  \WC_Discogs\Admin\Settings;
use \Discogs\ClientFactory;

abstract class Resource {

	protected $client;

	public function __construct() {

		$this->client = ClientFactory::factory([
			'defaults' => [
				'query' => [
					'key' => getenv('DISCOGS_API_CONSUMER_KEY'),
					'secret' => getenv('DISCOGS_API_CONSUMER_SECRET'),
				]
			]
		]);

	}

}
