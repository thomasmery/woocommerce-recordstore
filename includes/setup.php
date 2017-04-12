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

		/**
		* default media file renaming
		*/
		add_filter( __NAMESPACE__ . '_rename_file_on_attach_from_url', __NAMESPACE__ . '\default_media_file_rename', 10, 2 );


	}

	public function acf_path( $path ) {
		return PLUGIN_PATH . '/vendor/acf/';
	}

	public function acf_dir( $path ) {
		return PLUGIN_URL . '/vendor/acf/';
	}

}

function default_media_file_rename( $filename, $post_id ) {
	$path_parts = pathinfo($filename);
	$extension = $path_parts['extension'];
	$post = get_post( $post_id );
	$artist = implode(
		'-',
		wp_get_object_terms( $post_id, __NAMESPACE__ . '_artist', [ 'fields' => 'names' ] )
	);
	$title = $post->post_title;
	$artwork_wp_title = "{$artist} - {$title}";
	return sanitize_title( $artwork_wp_title ) . '.' . $extension;
}
