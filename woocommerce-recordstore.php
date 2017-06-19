<?php

/**
 * Plugin Name:     WooCommerce Record Store
 * Plugin URI:      http://www.woocommerce-recordstore.com
 * Description:     A plugin to manage Records in WooCommerce
 * Author:          Aaltomeri
 * Author URI:      http://aaltomeri.net
 * Text Domain:     woocommerce-recordstore
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Woocommerce_Recordstore
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

class Main {

	/**
	* @var options
	*/
	protected $options;

	public static function register() {

		// get ENV vars from specific .env file that we put above the webroot
		$root = realpath($_SERVER['DOCUMENT_ROOT'] . '/../');
		$dotenv = new \Dotenv\Dotenv($root, 'wc-recordstore.env');
		if (file_exists($root . '/wc-recordstore.env')) {
			$dotenv->load();
		}
		else {
			add_action('admin_notices', [ '\WC_Discogs\Main', 'noCredentialsNotice' ] );
			deactivate_plugins(\WC_Discogs\PLUGIN_FILE);
			return false;
		}

		$plugin = new self();

		add_action( 'plugins_loaded', array( $plugin, 'run' ) );

		register_activation_hook( __FILE__, [ $plugin, 'activate'] );
		register_deactivation_hook( __FILE__, [ $plugin, 'deactivate'] );
		register_uninstall_hook( __FILE__, [ 'WC_Discogs::uninstall' ]);

	}

	public function __construct() {}

	public function run() {
		if( is_admin() ) {
			new Admin\Settings();
			new Admin\Product();
		}

		new Setup();
	}

	public function activate() {

	}

	public function deactivate() {}
	static public function uninstall() {}


	public static function noCredentialsNotice() {

		$message = __('WC Recordstore requires that you have a wc-recordstore.env file in the directory <em>above the webroot</em>. <strong>The plugin has been de-activated.</strong>');
		echo <<<EOT
			<div class="notice notice-error">
				<p>{$message}</p>
			</div>
EOT;

	}

}

Main::register();
