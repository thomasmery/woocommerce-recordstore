<?php

/**
* Settings Class
**/

namespace WC_Discogs\Admin;

class Settings {

	/** @vars string Unique menu identifier */
  	private $screen_id;

	static public $options;

	/**
	* Constructor
	*/
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		// get ENV vars from .env file
		$dotenv = new \Dotenv\Dotenv(\WC_Discogs\PLUGIN_PATH);
		if (file_exists(\WC_Discogs\PLUGIN_PATH . '/.env')) {
			$dotenv->load();
		}

		self::$options = [
			'default_record_image_uri' => 'https://s.discogs.com/images/default-release-cd.png',
			'discogs_api_agent' => 'DISCOGS_API_AGENT',
			'discogs_api_scope' => 'https://api.discogs.com',
			'discogs_api_consumer_key' => getenv('DISCOGS_API_CONSUMER_KEY'),
			'discogs_api_consumer_secret' => getenv('DISCOGS_API_CONSUMER_SECRET')
		];

	}

	/**
	* Add admin menu
	*/
	public function admin_menu() {

		$this->screen_id = add_menu_page(
			'Woocommerce Discogs',
			'Woocommerce Discogs',
			'manage_options',
			'wc_discogs',
			[ $this, 'settings' ]
		);

	}

	public function settings() {
		include 'views/settings.php';
	}


}
