<?php

namespace WC_Discogs;

class Media {

	public static $default_artwork_image_uri = 'https://s.discogs.com/images/default-release-cd.png';

	/**
	* Downloads an image from the specified URL and attaches it to a post.
	*
	* This is a modified version of the WP function media_sideload_image
	* allowing to return an attachment $id
	*
	* @param string $file    The URL of the image to download.
	* @param int    $post_id The post ID the media is to be associated with.
	* @param string $desc    Optional. Description of the image.
	* @return int|WP_Error attachment id on success, WP_Error object otherwise.
	*/
	public static function attach_from_url( $file, $post_id, $desc = null) {
		if ( ! empty( $file ) ) {

			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
			if ( ! $matches ) {
				return new WP_Error( 'attach_from_url failed', __( 'Invalid image URL' ) );
			}

			$file_array = array();
			$file_array['name'] = basename( $matches[0] );

			// Download file to temp location.
			$file_array['tmp_name'] = download_url( $file );

			// If error storing temporarily, return the error.
			if ( is_wp_error( $file_array['tmp_name'] ) ) {
				return $file_array['tmp_name'];
			}

			// Do the validation and storage stuff.
			$id = media_handle_sideload( $file_array, $post_id, $desc );

			// If error storing permanently, unlink.
			if ( is_wp_error( $id ) ) {
				@unlink( $file_array['tmp_name'] );
				return $id;
			}

			return $id;
		}

	}

}
