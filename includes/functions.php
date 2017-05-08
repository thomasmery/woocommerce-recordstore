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



/** ADMIN UI **/

// temp - should go in the Admin class

// fetch release infos
function wc_recordstore_admin_fetch_release_infos( $product_id, $params ) {

	$release = new Release( $product_id );
	if ( false === $release ) {
		wp_die( sprintf( __( 'Release creation failed: product ID # %s', 'wc-recordstore' ), $product_id ) );
	}

	$params = wp_parse_args(
		$params,
		[
			'refresh' => true,
			'type' => 'master',
		]
	);

	try {
		$release->set_artwork( true, $params );
		$release->set_genres_and_styles( $params );
		$release->set_tracklist( $params );
		$release->set_year( $params );
	}
	catch( Exception $e ) {
		wp_die( sprintf( __( 'Could not fetch release infos - Operation failed with this message: %s', 'wc-recordstore' ), $e->getMessage() ) );
	}

	wp_redirect( admin_url( 'post.php?action=edit&post=' . $product_id ) );
	exit;
}

// link in post list post mouseover menu
add_action(
    'post_row_actions',
    function( $actions, $post ) {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return $actions;
		}

		if ( 'product' !== $post->post_type
			|| ! wc_recordstore_is_music_release($post->ID) ) {
			return $actions;
		}

		$actions['fetch-release-infos'] =
            '<a href="'
                . wp_nonce_url(
                     admin_url( 'edit.php?post_type=product&action=fetch_release_infos&amp;post=' . $post->ID ),
                     'wc-recordstore_fetch_release_infos_' . $post->ID
                )
                . '" aria-label="' . esc_attr__( 'Fetch infos & Artwork for this release', 'woocommerce' )
			    . '" rel="permalink">' . __( 'Fetch Release Infos', 'wc-recordstore' )
                . '</a>';

		return $actions;
    },
    10,
    2
);


// trigger fetch infos for release action from
add_action(
	'admin_action_fetch_release_infos',
	function () {
		$product_id = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : '';
		wp_verify_nonce( 'wc-recordstore_fetch_release_infos_' . $product_id, 'admin_action_fetch_release_infos' );
		wc_recordstore_admin_fetch_release_infos( $product_id );
	}
);

// meta box in product edit screen
// will allow to specify options
// for now inly whether to first search for 'master' or 'release' on Discogs
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'fetch-release-infos',
			__( 'Release Infos', 'wc-recordstore' ),
			function ( $post, $metabox ) {

				// var_dump($metabox);

				if ( ! current_user_can( 'manage_woocommerce' ) ) {
					return;
				}

				if ( ! is_object( $post ) ) {
					return;
				}

				if ( 'product' !== $post->post_type ) {
					return;
				}

				wp_nonce_field(basename(__FILE__), "fetch-release-infos-nonce");

				$output = '<div id="fetch-release-infos-action">';
				$output .= '<input type="submit" name="fetch-release-infos-action"';
				$output .= ' class="submitfetch-release-infos fetch-release-infos button button-primary button-large"';
				$output .= ' value="' . __( 'Fetch Release Infos', 'wc-recordstore' ) . '" />';
				$output .= '<input type="checkbox" name="fetch-release-infos-action-skip-master-release-search" value="1" />';
				$output .= '</div>';

				echo $output;
			},
			'product',
			'side',
			'default'
		);
	}
);

// when a post is saved
add_action(
	'save_post',
	function () {

		if( ! isset($_POST['fetch-release-infos-action']) ) {
			return;
		}

		$post_id = isset( $_REQUEST['post_ID'] ) ? absint( $_REQUEST['post_ID'] ) : '';

		if( ! $post_id ) {
			return;
		}

		// Checks save status
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce =
			isset( $_POST[ 'fetch-release-infos-nonce' ] )
				&& wp_verify_nonce( $_POST[ 'fetch-release-infos-nonce' ], basename( __FILE__ ) );

		// Exits script depending on save status
		if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
			return;
		}
		// prevent any side effect from other code
		// WC will force a post_type = 'product' for instance ...
		remove_all_filters('wp_insert_attachment_data');
		$skip_master_release_search = isset($_POST['fetch-release-infos-action-skip-master-release-search']);
		$params = $skip_master_release_search ? [ 'type' => 'release' ] : [];
		wc_recordstore_admin_fetch_release_infos( $post_id, $params );
	}
);


