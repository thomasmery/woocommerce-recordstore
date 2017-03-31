<?php

namespace WC_Discogs;

/** Registering the plugin Taxonomies

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

	register_taxonomy(__NAMESPACE__  . '_artist', $object_types, $args);
}
add_action('init', __NAMESPACE__ . '\register_artist_taxonomy');

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

	register_taxonomy(__NAMESPACE__  . '_genre', $object_types, $args);
}
add_action('init', __NAMESPACE__ . '\register_genre_taxonomy');

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
	register_taxonomy(__NAMESPACE__  . '_style', $object_types, $args);
}
add_action('init', __NAMESPACE__ . '\register_style_taxonomy');
