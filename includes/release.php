<?php
/**
* WC_Dicsogs Post
* a class to operate on Posts / Products ...
*/

namespace WC_Discogs;

use WC_Discogs\API\Discogs\Image;

class Release {

	/**
	* a WP Post the record is associated with
	* @var $post
	*/
	public $post;

	public function __construct( $post_id ) {

		$post = get_post( $post_id );

		// safeguard
		// a Release is a Parent Product
		if( $post->post_type !== 'product') {
			throw new \Exception(
				__(
					"Warning: You can not create a Release for post ID $post_id of type $post->post_type. A Release can only be of type _product_",
					'wc-record-store'
				)
			);
		}

		$this->post = $post;
	}

	/**
	* get artists names separated by $separator
	* @param $separator
	*/
	public function get_artists( $separator = ', ') {
		return implode(
			$separator,
			wp_get_object_terms( $this->post->ID, __NAMESPACE__ . '_artist', [ 'fields' => 'names' ] )
		);
	}

	/**
	* will fetch genres and styles from an external API
	*/
	public function set_genres_and_styles() {
		// get from discogs
		$discogs_db = new \WC_Discogs\API\Discogs\Database();
		$genres = $discogs_db->get_genres( [ 'title' => $this->post->post_title, 'artist' => $this->get_artists() ] );
		$styles = $discogs_db->get_styles( [ 'title' => $this->post->post_title, 'artist' => $this->get_artists() ] );

		// add to product
		wp_add_object_terms( $this->post->ID, $genres, __NAMESPACE__ . '_genre');
		wp_add_object_terms( $this->post->ID, $styles, __NAMESPACE__ . '_style');
	}

	/**
	* wrapper for getting WC product image
	*/
	public function get_artwork() {

	}

	/**
	* will fetch artwork from a remote source like Discogs
	*
	* @return int the ID of the attachment used as artwork for this Release
	*/
	public function set_artwork( $force = false ) {
		global $wpdb;

		// detach featured image from Release's post
		$thumbnail_id = get_post_thumbnail_id( $this->post->ID );
		if( $thumbnail_id ) {
			$wpdb->update(
				$wpdb->posts,
				[ 'post_parent' => 0 ],
				[ 'ID' => $thumbnail_id ]
			);
		}

		// refresh post data
		if( $force ) {
			$this->post = get_post( $this->post->ID );
		}

		$artist = $this->get_artists();
		$title = $this->post->post_title;

		$attachment_id = null;

		// Attachment title
		// @TODO make this a method
		$artwork_wp_title = "{$artist} - {$title}";

		// we don't want to fetch from external source
		// if we already have an image in the Media Library
		// with a name that corresponds to the artwork we're looking for
		if ( $attachment = Media::get_attachment_by_title( $artwork_wp_title ) ) {
			$attachment_id = $attachment->ID;
			// attach Media to Post
			$wpdb->update(
				$wpdb->posts,
				[ 'post_parent' => $this->post->ID ],
				[ 'ID' => $attachment_id ]
			);
			// Featured image
			set_post_thumbnail( $this->post->ID, $attachment_id );

			// by default we set the parent product image to be the variations image
			$this->set_variations_artwork();

			return $attachment_id;
		}

		// we don't want to fetch unecessarily
		// unless we really insist ...
		if( ! $force ) {
			if ( $attachment_id = $this->has_artwork() ) {
			 	return $attachment_id;
			}
		}

		// Get Artwork URI from external source
		$external_resource = new \WC_Discogs\API\Discogs\Database();
		$artwork_uri = $external_resource->get_artwork_uri( [
			'artist' => $artist,
			'title' => $title,
		] );

		// if the artwork uri is the uri of the default placeholder
		// we don't want to create a new attachment each time
		// so we look for the image filename and use the attachment if it is found
		if ( $artwork_uri === Media::$default_artwork_image_uri ) {
			// we also set a generic name in case the default place holder does not exist and is created
			$artwork_wp_title = "Default Placeholder";

			$default_image_path_parts = explode('/', Media::$default_artwork_image_uri);
			$default_image_filename = $default_image_path_parts[ count($default_image_path_parts) - 1];
			$default_image_basename = explode('.', $default_image_filename)[0];
			$attachment_id = Media::get_attachment_id_by_filename( $default_image_basename );
		}

		// actually attach Media from remote service
		if( ! $attachment_id ) {
			$attachment_id = Media::attach_from_url( $artwork_uri, $this->post->ID, $artwork_wp_title );
		}

		set_post_thumbnail( $this->post->ID, $attachment_id );
		$this->set_variations_artwork();

		return $attachment_id;
	}

	/**
	* will set a variable products
	*/
	public function set_variations_artwork() {

		$attachment_id = get_post_thumbnail_id( $this->post->ID );

		// variations have same image by default
		$wc_product = wc_get_product( $this->post->ID );
		if( $wc_product && 'variable' === $wc_product->get_type() ) {
			$variations_ids = $wc_product->get_children();
			foreach( $variations_ids as $variation_id ) {
				set_post_thumbnail( $variation_id, $attachment_id );
			}
		}
	}

	/**
	* whether a post has an associated artwork
	* the post is considered NOT to have an associated artwork if
	* - it has no featured image
	* - its featured image is the default image
	* @return mixed the attachment ID or
	*/
	public function has_artwork() {
		$has_artwork = false;
		$post_featured_image_id = get_post_thumbnail_id($this->post->ID);
		if ($post_featured_image_id) {
			$has_artwork = $post_featured_image_id;

			$featured_image_infos = wp_get_attachment_image_src($post_featured_image_id);
			$featured_image_src = $featured_image_infos[0];
			// if featured image is the placeholder image we do not consider that the release has artwork
			$default_image_path_parts = explode('/', Media::$default_artwork_image_uri);
			$default_image_filename = $default_image_path_parts[ count($default_image_path_parts) - 1];
			$default_image_basename = explode('.', $default_image_filename)[0];
			if ( preg_match(
				"/$default_image_basename/",
				$featured_image_src
				) ) {
				$has_artwork = false;
			}
		}

		return $has_artwork;
	}

}
