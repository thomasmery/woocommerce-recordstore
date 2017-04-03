<?php

/**
* Settings Class
**/

namespace WC_Discogs;

class Setup {

	public function __construct() {

		$this->init();

	}

	public function init() {

		/*
		* ACF
		*/
		add_filter( 'acf/settings/path', [ $this, 'acf_path' ] );
		add_filter( 'acf/settings/dir', [ $this, 'acf_dir' ] );
		add_filter('acf/settings/show_admin', '__return_false');

		require_once( PLUGIN_PATH . '/vendor/acf/acf.php' );
		require_once( PLUGIN_PATH . '/includes/custom-fields/acf.php' );

		/*
		* Taxonomonies
		*/
		require_once( PLUGIN_PATH . '/includes/taxonomies.php' );


	}

	public function acf_path( $path ) {
		return PLUGIN_PATH . '/vendor/acf/';
	}

	public function acf_dir( $path ) {
		return PLUGIN_URL . '/vendor/acf/';
	}

}
