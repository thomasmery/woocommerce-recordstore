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
		add_action('init', __NAMESPACE__ . '\register_product_categories');
		add_action('init', __NAMESPACE__ . '\register_artist_taxonomy');
		add_action('init', __NAMESPACE__ . '\register_genre_taxonomy');
		add_action('init', __NAMESPACE__ . '\register_style_taxonomy');

		/**
		* WooCommerce filters
		*/

		// when counting the terms for products
		// when a large amount of products is assigned to 1 category
		// the db choke on most system w/ a large catalog
		// Unhook update_count_callback for WooCommerce Product Categories
		// as the _wc_term_recount function will generate a query that will cause this
		// This solution might be temporary ... until WC has a more efficicient way of counting
		add_filter('woocommerce_taxonomy_args_product_cat', function( $args ) {
				$args['update_count_callback'] = '';
				return $args;
			}
		);

		/**
		* default media file renaming
		*/
		add_filter( __NAMESPACE__ . '_rename_file_on_attach_from_url', __NAMESPACE__ . '\default_media_file_rename', 10, 2 );


		/**
		* global helpers
		*/
		require_once( PLUGIN_PATH . '/includes/functions.php' );
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
		wp_get_object_terms( $post_id, ARTIST_TAXONOMY, [ 'fields' => 'names' ] )
	);
	$title = $post->post_title;

	// in case of a variation we need to get title from the parent product
	// because the variation title is of the form 'Variation #nnn for ... '
	// or 'Title, Attribyte 1: value, Atribute 2: value ... ' in WC 3.0
	// and this won't give us a nice filename ...
	// this is needed because in some (rare) cases we will try to get the artwork using the variation first
	if ($post->post_type === 'product_variation') {
		$_parent = get_post($post->post_parent);
		$title = $_parent->post_title;
	}

	$artwork_wp_title = "{$artist} - {$title}";
	return sanitize_title( $artwork_wp_title ) . '.' . $extension;
}
