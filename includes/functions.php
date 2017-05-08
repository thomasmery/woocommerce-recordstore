<?php

use WC_Discogs\Release;

/**
* Global functions
*/

function wc_recordstore_is_music_release( $post_id ) {

	$product = wc_get_product($post_id );

	$is_music_release = false;
	$music_release_category_term = get_term_by('slug', 'music-release', 'product_cat', ARRAY_A);
	$music_release_category_id = intval( $music_release_category_term['term_id'] );
	if( $music_release_category_term ) {
		if( in_array( $music_release_category_id, $product->get_category_ids() ) ) {
			return true;
		}
	}

	return false;

}

function wc_recordstore_artists( $post_id ) {
	$release = new Release( $post_id );
	return apply_filters( 'wc_recordstore_artists', $release->get_artists(), $release );
}

// get archive links for artists
function wc_recordstore_artists_term_links( $post_id, $format = 'string' ) {
    $release = new Release( $post_id );
    $artists_names = $release->get_artists_array();

	if( $format === 'array' ) {
		return array_map(
			function( $artist ) {
				return [
					'name' => $artist,
					'url' => get_term_link( $artist, 'wc_discogs_artist')
				];
			},
			$artists_names
		);
	}

    return implode(
        ', ',
        array_map(
            function($artist) {
                return '<a title="'
                    . __('Browse all of ' . $artist . ' releases', 'wc_recordstore')
                    . '"href="' . esc_url( get_term_link( $artist, 'wc_discogs_artist') )
                    . '">' . $artist . '</a>';
            },
            $artists_names
        )
    );
}

function wc_recordstore_fullname( $post_id ) {
	$release = new Release( $post_id );
	return $release->get_fullname();
}
