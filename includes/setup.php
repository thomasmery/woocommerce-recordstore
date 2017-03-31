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

		require_once( PLUGIN_PATH . '/includes/taxonomies.php' );

	}

}
