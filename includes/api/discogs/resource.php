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
				'headers' => ['User-Agent' => 'wc-record-store/1.0.0 +https://github.com/aaltomeri/wc-record-store'],
				'debug' =>
					Settings::$options && isset(Settings::$options['debug'])
						? Settings::$options['debug']
						: false,
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
