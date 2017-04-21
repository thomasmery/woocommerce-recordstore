<?php

/**
* Discogs API base class
*/

namespace WC_Discogs\API\Discogs;

use  \WC_Discogs\Admin\Settings;
use \Discogs\ClientFactory;

abstract class Resource {

	protected $client;

	public function __construct( array $config = [] ) {

		$defaultConfig = [
			'defaults' => [
				'debug' => false,
				'query' => [
					'key' => getenv('DISCOGS_API_CONSUMER_KEY'),
					'secret' => getenv('DISCOGS_API_CONSUMER_SECRET'),
				]
			]
		];

		$config = ClientFactory::mergeRecursive(
			$defaultConfig,
			$config
		);

		$this->client = ClientFactory::factory($config);

	}

}
