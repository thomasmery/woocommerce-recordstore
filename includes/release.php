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
		$this->post = get_post( $post_id );
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

		// refresh post data
		if( $force ) {
			$this->post = get_post( $this->post->ID );
		}

		$discogs_db = new \WC_Discogs\API\Discogs\Database();

		$artist = $this->get_artists();
		$title = $this->post->post_title;

		$attachment_id = null;

		// in case of a variation we need to get title from the parent product
		// because the variation title is of the form 'Variation #nnn for ... '
		// and this won't work to get the artwork
		if ($this->post->post_type === 'product_variation') {
			$_parent = get_post($this->post->post_parent);
			$title = $_parent->post_title;
			$artist = $release->get_artists();
		}

		// Attachment title
		$artwork_wp_title = "{$artist} - {$title}";

		// we don't want to fetch from external source
		// if we already have an image in the Media Library
		// with a name that corresponds to the artwork we're looking for
		if ( ( $attachment = Media::get_attachment_by_title( $artwork_wp_title ) )
			&& ! $force ) {
			set_post_thumbnail( $this->post->ID, $attachment->ID );
			return $attachment->ID;
		}

		// we don't want to fetch unecessarily
		// unless we really insist ...
		if( ! $force ) {
			if ( $attachment_id = $this->has_artwork() ) {
			 	return $attachment_id;
			}
		}

		// Get Artwork URI from external source
		$artwork_uri = $discogs_db->get_artwork_uri( [
			'artist' => $artist,
			'title' => $title,
		] );

		// if the artwork uri is the uri of the default placeholder
		// we don't want to create a new attachment each time
		// so we look for the image filenmame and use the attachment if it is found
		if ( $artwork_uri === Media::$default_artwork_image_uri ) {
			// we also set a generic name in case the default place holder does not exist and is created
			$artwork_wp_title = "Default Placeholder";

			$default_image_path_parts = explode('/', Media::$default_artwork_image_uri);
			$default_image_filename = $default_image_path_parts[ count($default_image_path_parts) - 1];
			$default_image_basename = explode('.', $default_image_filename)[0];
			$attachment_id = Media::get_attachment_id_by_filename( $default_image_basename );
		}

		// actually attach Media
		if( ! $attachment_id ) {
			$attachment_id = Media::attach_from_url( $artwork_uri, $this->post->ID, $artwork_wp_title );
		}
		return $attachment_id;
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
