<?php

namespace WC_Discogs;

define( __NAMESPACE__ . '\ARTIST_TAXONOMY', sanitize_key(__NAMESPACE__  . '_artist'));
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
* - Artist
* - Genre
* - Style
* their name is prepended with the plugin NAMESPACE to prevent conflicts
* with user/plugin created taxonomies
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
		'rewrite' 			=> array('slug' => 'artist', 'with_front' => false ),
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




/** CUSTOM SORTING & SEARCHING **/

/** Enable search by Artists **/
// search all taxonomies, based on: http://projects.jesseheap.com/all-projects/wordpress-plugin-tag-search-in-wordpress-23

function enable_artists_search() {
	add_filter(
		'posts_search',
		function ($where, $query){
			global $wpdb;
			if ($query->is_search() && get_search_query()) {

				// add a grouping parenthese
				// we need to group the artist search condition with the search query
				$where = preg_replace('/^(\s+?AND)/', '$1 (', $where);

				$where .= " OR (wc_rs_as_t.name LIKE '%" . get_search_query() . "%'
				 AND {$wpdb->posts}.post_status = 'publish'";

				 // we don't want to get results on all taxonomies
				 $where .= " AND wc_rs_as_tt.taxonomy = '" . ARTIST_TAXONOMY . "'";

				if( isset($query->query['post_type'])) {
					$where .= " AND wp_posts.post_type = '{$query->query['post_type']}'";
				}
				
				// close the groups
				$where .= ') )';

			}
			return $where;
		},
		10,
		2
	);
	add_filter(
		'posts_join',
		function ($join, $query){
			global $wpdb;
			if ($query->is_search() && get_search_query()) {
				$join .= " LEFT JOIN {$wpdb->term_relationships} wc_rs_as_tr
				ON {$wpdb->posts}.ID = wc_rs_as_tr.object_id
				LEFT JOIN {$wpdb->term_taxonomy} wc_rs_as_tt
				ON wc_rs_as_tt.term_taxonomy_id=wc_rs_as_tr.term_taxonomy_id
				LEFT JOIN {$wpdb->terms} wc_rs_as_t
				ON wc_rs_as_t.term_id = wc_rs_as_tt.term_id";
			}
			return $join;
		},
		10,
		2
	);
	add_filter('posts_groupby',
		function($groupby, $query) {
			global $wpdb;

			// we need to group on post ID
			$groupby_id = "{$wpdb->posts}.ID";
			if(!$query->is_search()
				|| strpos($groupby, $groupby_id) !== false) {

					return $groupby;
			}

			// groupby was empty, use ours
			if(!strlen(trim($groupby))) return $groupby_id;

			// wasn't empty, append ours
			return $groupby.", ".$groupby_id;
		},
		10,
		2
	);
}
// trigger
enable_artists_search();

/** enable sorting by artists */

// add custom filters to dropdown
add_filter(
    'woocommerce_catalog_orderby',
    function ( $array ) {

	$array['taxonomy.' . ARTIST_TAXONOMY . '-asc'] = __('Sort by artists - A-Z', 'woocommerce-recordstore');
	$array['taxonomy.' . ARTIST_TAXONOMY . '-desc'] = __('Sort by artists - Z-A', 'woocommerce-recordstore');

	return $array;
}
,
    10,
    1
);

// allow custom filters in WC order args
// WC will try to set its default orderby if it does not recognize what is passed
add_filter(
    'woocommerce_get_catalog_ordering_args',
    function ( $args ) {

	$custom_filters = [
					'taxonomy.' . ARTIST_TAXONOMY . '-asc',
					'taxonomy.' . ARTIST_TAXONOMY . '-desc'
				];

	if( isset($_GET['orderby']) && in_array($_GET['orderby'], $custom_filters)) {

		$orderby_value = $_GET['orderby'];

		// 		Get order + orderby args from string
							$orderby_value = explode( '-', $orderby_value );
		$orderby       = esc_attr( $orderby_value[0] );
		$order         = ! empty( $orderby_value[1] ) ? $orderby_value[1] : 'ASC';

		$args['orderby'] = $orderby;
		$args['order'] = $order;
	}

	return $args;
}
,
    10,
    1
);

// the main function that will modify the WP_Query clauses
// originally found here : https://wordpress.stackexchange.com/questions/137208/order-posts-by-taxonomy-and-meta-value
function orderby_taxonomy_clauses( $clauses, $wp_query ) {

	$orderby_arg = $wp_query->get('orderby');

	// 	only process when orderby
		// 	- has a value
		// 	- is a string
		// 	- contains a 'taxonomy.' string that indicates we want to sort by taxonomy

	// 	when orderby is an array we don't really know where it comes from
	// and what to do with it for now
	if ( ! empty( $orderby_arg )
			&& is_string($orderby_arg)
			&& substr_count( $orderby_arg, 'taxonomy.' )
	) {
		global $wpdb;
		$bytax = "GROUP_CONCAT(orderby_taxonomy_terms.name ORDER BY name ASC)";
		$array = explode( ' ', $orderby_arg );
		if ( ! isset( $array[1] ) ) {
			$array = array( $bytax, "{$wpdb->posts}.post_title" );
			$taxonomy = str_replace( 'taxonomy.', '', $orderby_arg );
		}
		else {
			foreach ( $array as $i => $t ) {
				if ( substr_count( $t, 'taxonomy.' ) )  {
					$taxonomy = str_replace( 'taxonomy.', '', $t );
					$array[$i] = $bytax;
				}
				elseif ( $t === 'meta_value' || $t === 'meta_value_num' ) {
					$cast = ( $t === 'meta_value_num' ) ? 'SIGNED' : 'CHAR';
					$array[$i] = "CAST( {$wpdb->postmeta}.meta_value AS {$cast} )";
				}
				else {
					$array[$i] = "{$wpdb->posts}.{$t}";
				}
			}
		}
		$order = strtoupper( $wp_query->get('order') ) === 'ASC' ? ' ASC' : ' DESC';
		$ot = strtoupper( $wp_query->get('ordertax') );
		$ordertax = $ot === 'DESC' || $ot === 'ASC' ? " $ot" : " $order";
		$clauses['orderby'] = implode(', ',
		      array_map( function($a) use ( $ordertax, $order ) {
			return ( strpos($a, 'GROUP_CONCAT') === 0 ) ? $a . $ordertax : $a . $order;
		}
		, $array )
		    );
		$clauses['join'] .= " LEFT OUTER JOIN {$wpdb->term_relationships} as orderby_taxonomy_tr";
		$clauses['join'] .= " ON {$wpdb->posts}.ID = orderby_taxonomy_tr.object_id";
		$clauses['join'] .= " LEFT OUTER JOIN {$wpdb->term_taxonomy} as orderby_taxonomy_tt";
		$clauses['join'] .= " ON orderby_taxonomy_tr.term_taxonomy_id = orderby_taxonomy_tt.term_taxonomy_id";
		$clauses['join'] .= " LEFT OUTER JOIN {$wpdb->terms} as orderby_taxonomy_terms";
		$clauses['join'] .= " ON orderby_taxonomy_tt.term_id = orderby_taxonomy_terms.term_id";
		$clauses['groupby'] = "orderby_taxonomy_tr.object_id";
		$clauses['where'] .= " AND (orderby_taxonomy_tt.taxonomy = '{$taxonomy}' OR orderby_taxonomy_tt.taxonomy IS NULL)";
	}
	return $clauses;
}
add_filter( 'posts_clauses', __NAMESPACE__ . '\orderby_taxonomy_clauses', 10, 2 );
