<?php
/**
 * Edit Product Admin Screen
 *
 */

namespace WC_Discogs\Admin;

use WC_Discogs\Release;

class Product {

	public function __construct() {

		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes'] );
		add_action( 'save_post', [ $this, 'save_post_fetch_release_infos'] );

		add_action( 'post_row_actions', [ $this, 'render_product_row_action_link' ], 10, 2);
		add_action( 'admin_action_fetch_release_infos', [ $this, 'admin_action_fetch_release_infos']);

		// a Release slug (post_name) must contain the artist(s) name(s)
		add_filter(
			'wp_unique_post_slug',
			[ __CLASS__, 'generate_release_slug'],
			10,
			6
		);
	}


	/**
	* Generates a slug for a Release
	* by appending artists names to Release title
	*/
	public static function generate_release_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {

		if( 'product' !== $post_type
			|| ! wc_recordstore_is_music_release( $post_ID ) ) {
			return $slug;
		}

		$release = new Release( $post_ID );

		// we only want to change the slug if the original slug is the sanitized title
		// indicating that the slug has not been modified by the user
		// ( as the wp_insert_post function will set the slug to the sanitized title when none has been specified )
		if( $slug !== sanitize_title( $release->post->post_title ) ) {
			return $slug;
		}

		remove_filter(
			'wp_unique_post_slug',
			[ __CLASS__, 'generate_release_slug'],
			10,
			6
		);

		return $release->generate_slug();

	}

	/**
   * Add meta boxes to the edit stores page
   */
  public function add_meta_boxes ( $post ) {
	add_meta_box(
		'fetch-release-infos',
		__( 'Release Infos', 'wc-recordstore' ),
		array( $this, 'render_fetch_release_infos_meta_box' ),
		'product',
		'side',
		'default'
	);
	// add classes to meta box to be able to control it w/ js
	add_filter(
		'postbox_classes_product_fetch-release-infos',
		function( $classes ) {
			global $post;

			$classes[] = 'wc-recordstore-fetch-release-infos-mb';

			if( ! wc_recordstore_is_music_release($post->ID) ) {
				// temporary
				// we'll need our own hiding class
				$classes[] = 'acf-hidden';
			}

			return $classes;
		}
	);
  }

	/**
	* Product List Row action
	*/
	public function render_product_row_action_link( $actions, $post ) {

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
    }

	/*
	*
	*/
	public function admin_action_fetch_release_infos() {
		$product_id = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : '';
		wp_verify_nonce( 'wc-recordstore_fetch_release_infos_' . $product_id, 'admin_action_fetch_release_infos' );
		$this->fetch_release_infos( $product_id );
	}


	/**
	*
	*/
	function render_fetch_release_infos_meta_box( $post, $metabox ) {

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

		$post_title = $post->post_title;
		$post_is_music_release = wc_recordstore_is_music_release( $post->ID );

		include 'views/fetch-release-infos.php';

	}

	/*
	* when a post is saved
	*/
	function save_post_fetch_release_infos() {

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
		// WC will force a post_type = 'product' on the saved artwork (attachment) for instance ...
		// resulting in the attachment being savec with a wrong post_type
		remove_all_filters('wp_insert_attachment_data');

		$skip_master_release_search = isset($_POST['fetch-release-infos-action-skip-master-release-search']);
		$params = $skip_master_release_search ? [ 'type' => 'release' ] : [];

		// search title
		if( isset($_POST['fetch-release-infos-action-search-title'])
			&& $_POST['fetch-release-infos-action-search-title'] !== '') {
			$params['title'] = $_POST['fetch-release-infos-action-search-title'];
		}
		// Discogs Code
		if( isset($_POST['fetch-release-infos-action-discogs-id'])
			&& $_POST['fetch-release-infos-action-discogs-id'] !== '') {
			$params['discogs_id'] = $_POST['fetch-release-infos-action-discogs-id'];
			// extract Discogs ID & type code copied from site for convenience
			// [m3123123] > 'm' & '3123123'' for instance
			$is_usable_discogs_code = preg_match("/^\[?(r|m)?([0-9]+)\]?/", $params['discogs_id'], $discogs_id_parts);
			if( ! $is_usable_discogs_code ) {
				$this->fetch_release_infos( $post_id, $params );
				return;
			}
			// automatically change 'type' if hint found in Discogs ID
			$discogs_id_type = isset($discogs_id_parts[1]) ? $discogs_id_parts[1] : '';
			switch ( $discogs_id_type ) {
				case 'm':
					$params['type'] = 'master';
					break;
				case 'r':
					$params['type'] = 'release';
					break;
			}
			$params['discogs_id'] = isset($discogs_id_parts[2]) ? $discogs_id_parts[2] : '';
		}
		$this->fetch_release_infos( $post_id, $params );
		return;
	}

	/*
	*
	*/
	public function fetch_release_infos( $product_id, $params = [] ) {

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
			$start = time();
			$release->set_artwork( true, $params );
			$release->set_genres_and_styles( $params );
			$release->set_tracklist( $params );
			$release->set_year( $params );
			$end = time();
			$elapsed = $end - $start;
			error_log('Fetching took:' . $elapsed);
		}
		catch( Exception $e ) {
			wp_die( sprintf( __( 'Could not fetch release infos - Operation failed with this message: %s', 'wc-recordstore' ), $e->getMessage() ) );
		}

		wp_redirect( admin_url( 'post.php?action=edit&post=' . $product_id ) );
		exit;
	}

}
