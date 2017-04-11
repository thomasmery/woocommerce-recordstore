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
	* One can set a title and description for the attachment by setting the 3rd param
	*
	* @param string $file    The URL of the image to download.
	* @param int    $post_id The post ID the media is to be associated with.
	* @param string $desc    Optional. Description of the image. This will beused as the attachment title if present
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

	/*
	* find an attachment based on title
	* quite naive and simple but will do for now
	*/
	public static function get_attachment_by_title( $title ) {
		$q = new \WP_Query(
			[
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'title' => $title,
			]
		);

		$attachment = $q->posts ? $q->posts[0] : null;

		return $attachment;
	}

	/**
	* will try to find a media in the WP library
	* based on the media filename
	* @param string a path or url
	*/
	public static function get_attachment_id_by_filename( $path ) {
		global $wpdb;

		$image_path_parts = explode('/', $path );
		$image_filename = $image_path_parts[ count($image_path_parts) - 1];

		$attachments_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE guid LIKE '%s';",
				"%$image_filename%"
			)
		);
		return isset( $attachments_ids[0] ) ? $attachments_ids[0] : null ;
	}

}
