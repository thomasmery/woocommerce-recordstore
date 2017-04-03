<?php

/**
 * Plugin Name:     Woocommerce Discogs
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     A plugin to allow getting Artwork & Data from Discogs for a WC Product
 * Author:          Aaltomeri
 * Author URI:      http://aaltomeri.net
 * Text Domain:     woocommerce-discogs
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Woocommerce_Discogs
 */

namespace WC_Discogs;

require __DIR__ . '/vendor/autoload.php';

/**
 * Define plugin constants.
 */
define( __NAMESPACE__ . '\VERSION', '0.1.0' );
define( __NAMESPACE__ . '\PLUGIN_NAME', 'woocommerce-discogs' );
define( __NAMESPACE__ . '\PLUGIN_FILE', plugin_basename( __FILE__ ) );
define( __NAMESPACE__ . '\PLUGIN_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( __NAMESPACE__ . '\PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

// prevent direct access to file
defined( 'ABSPATH' ) or exit;

class WC_Discogs {

	/**
	* @var options
	*/
	protected $options;

	public static function register() {

		$plugin = new self();

		add_action( 'plugins_loaded', array( $plugin, 'run' ) );

		register_activation_hook( __FILE__, [ $plugin, 'activate'] );
		register_deactivation_hook( __FILE__, [ $plugin, 'deactivate'] );
		register_uninstall_hook( __FILE__, [ 'WC_Discogs::uninstall' ]);

		// get ENV vars from .env file
		$dotenv = new \Dotenv\Dotenv(\WC_Discogs\PLUGIN_PATH);
		if (file_exists(\WC_Discogs\PLUGIN_PATH . '/.env')) {
			$dotenv->load();
		}

	}

	public function __construct() {}

	public function run() {
		if( is_admin() ) {
			new Admin\Settings();
		}

		new Setup();
	}

	public function activate() {

	}

	public function deactivate() {}
	static public function uninstall() {}

}

WC_Discogs::register();
