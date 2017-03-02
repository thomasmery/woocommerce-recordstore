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

	public function get_artwork() {
		// get Artwork URI
	}

	/**
	* whether a post has an associated artwork
	* the post is considered NOT to have an associted artwork if
	* - it has no featured image
	* - its featured image is the default image
	* @return boolean
	*/
	public function has_artwork() {
		$has_artwork = false;
		$post_featured_image_id = get_post_thumbnail_id($this->post->ID);

		if ($post_featured_image_id) {
			$has_artwork = true;

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
