<?php

namespace WC_Discogs;

define( __NAMESPACE__. '\ARTIST_TAXONOMY', sanitize_key(__NAMESPACE__  . '_artist'));
define( __NAMESPACE__ . '\GENRE_TAXONOMY', sanitize_key(__NAMESPACE__  . '_genre'));
define( __NAMESPACE__ . '\STYLE_TAXONOMY', sanitize_key(__NAMESPACE__  . '_style'));

/** Create default Product Categories
* only one mandatory exists: Music Release
*/
function register_product_categories () {
	/** Create default Music Release Product Category */
	$music_release_category_term = get_term_by( 'slug', 'music-release', 'product_cat' );
	if( ! $music_release_category_term ) {
		$term = wp_insert_term('Music Release', 'product_cat');
	}
}

/** the plugin Taxonomies

	- Artist
	- Genre
	- Style

	their name is prepened with the plugin NAMESPACE to prevent conflicts
	with user/plugin created taxonomies

*/


// registration code for artist taxonomy
function register_artist_taxonomy() {

	$object_types = array('product');

	$args = [
		'singular_label' 	=> __('Artist'),
		'public' 			=> true,
		'show_ui' 			=> true,
		'hierarchical' 		=> false,
		'show_tagcloud' 	=> false,
		'show_in_nav_menus' => true,
		'rewrite' 			=> array('slug' => 'genre', 'with_front' => false ),
		'meta_box_cb' 		=> false,
		'sort'				=> true,
	];

	$args['labels'] = [
		'name' 					=> _x( 'Artists', 'taxonomy general name' ),
		'singular_name' 		=> _x( 'Artist', 'taxonomy singular name' ),
		'add_new' 				=> _x( 'Add New Artist', 'Artist'),
		'add_new_item' 			=> __( 'Add New Artist' ),
		'edit_item' 			=> __( 'Edit Artist' ),
		'new_item' 				=> __( 'New Artist' ),
		'view_item' 			=> __( 'View Artist' ),
		'search_items' 			=> __( 'Search Artists' ),
		'not_found' 			=> __( 'No Artist found' ),
		'not_found_in_trash' 	=> __( 'No Artist found in Trash' ),
	];

	register_taxonomy( ARTIST_TAXONOMY , $object_types, $args);
}

// registration code for genre taxonomy
function register_genre_taxonomy() {

	$object_types = array('product');

	$args = [
		'singular_label' 	=> __('Genre'),
		'public' 			=> true,
		'show_ui' 			=> true,
		'hierarchical' 		=> false,
		'show_tagcloud' 	=> false,
		'show_in_nav_menus' => true,
		'rewrite' 			=> array('slug' => 'genre', 'with_front' => false ),
		'meta_box_cb' 		=> false,
		'sort'				=> true,
	];

	$args['labels'] = [
		'name' 					=> _x( 'Genres', 'taxonomy general name' ),
		'singular_name' 		=> _x( 'Genre', 'taxonomy singular name' ),
		'add_new' 				=> _x( 'Add New Genre', 'Genre'),
		'add_new_item' 			=> __( 'Add New Genre' ),
		'edit_item' 			=> __( 'Edit Genre' ),
		'new_item' 				=> __( 'New Genre' ),
		'view_item' 			=> __( 'View Genre' ),
		'search_items' 			=> __( 'Search Genres' ),
		'not_found' 			=> __( 'No Genre found' ),
		'not_found_in_trash' 	=> __( 'No Genre found in Trash' ),
	];

	register_taxonomy( GENRE_TAXONOMY, $object_types, $args);
}

// registration code for genre taxonomy
function register_style_taxonomy() {

	$object_types = array('product');

	$args = [
		'singular_label' 	=> __('Style'),
		'public' 			=> true,
		'show_ui' 			=> true,
		'hierarchical' 		=> false,
		'show_tagcloud' 	=> false,
		'show_in_nav_menus' => true,
		'rewrite' 			=> array('slug' => 'style', 'with_front' => false ),
		'meta_box_cb' 		=> false,
		'sort'				=> true,
	];

	$args['labels'] = [
		'name' 					=> _x( 'Styles', 'taxonomy general name' ),
		'singular_name' 		=> _x( 'Style', 'taxonomy singular name' ),
		'add_new' 				=> _x( 'Add New Style', 'Style'),
		'add_new_item' 			=> __( 'Add New Style' ),
		'edit_item' 			=> __( 'Edit Style' ),
		'new_item' 				=> __( 'New Style' ),
		'view_item' 			=> __( 'View Style' ),
		'search_items' 			=> __( 'Search Styles' ),
		'not_found' 			=> __( 'No Style found' ),
		'not_found_in_trash' 	=> __( 'No Style found in Trash' ),
	];
	register_taxonomy( STYLE_TAXONOMY, $object_types, $args);
}
