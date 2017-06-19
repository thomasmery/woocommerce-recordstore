<?php
/**
* WC_Dicsogs Post
* a class to operate on Posts / Products ...
*/

namespace WC_Discogs;

use WC_Discogs\API\Discogs\Image;
use WC_Discogs\API\Discogs;

class Release {

	/**
	* a WP Post the record is associated with
	* @var $post
	*/
	public $post;

	public function __construct( $post_id ) {

		$post = get_post( $post_id );

		if( ! $post ) {
			return false;
		}

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

		$this->_artists = $this->get_artists();
		$this->_genres = $this->get_genres();
		$this->_styles = $this->get_styles();
		$this->_release_date_year = $this->get_year();

		return $this->post->ID;
	}


	/**
	* Generate a slug for this release
	*/
	public function generate_slug() {

		$title = $this->post->post_title;
		$artists = $this->get_artists();
		$artists_slug = sanitize_title( $artists );
		$title_slug = sanitize_title( $title );

		if( ! $artists ) {
			return $title_slug;
		}

		return sanitize_title(
			$artists_slug
			. ' - '
			. $title_slug
		);

	}

	/**
	* updates this Release post slug/name
	*/
	public function set_slug() {

		global $wpdb;

		$success = false;

		$slug = $this->generate_slug();

		try {
			$updated_rows = $wpdb->update( $wpdb->posts, array( 'post_name' => $slug), [ 'ID' => $this->post->ID ] );
		}
		catch(Excpetion $e) {
			echo 'Could not update Release #' . $this->post->ID . '. Error: ' . $e->getMessage();
		}

		if( ! $updated_rows ) {
			return false;
		}

		return $slug;

	}

	/**
	* Artists
	*********/

	/**
	* get artists names separated by $separator
	* @param $separator
	* @return string
	*/
	public function get_artists( $separator = ', ' ) {

		$this->_artists = implode(
			$separator,
			$this->get_artists_array()
		);

		return $this->_artists;
	}

	/**
	* get artists names as an array of strings
	* @param $separator
	* @return array
	*/
	public function get_artists_array( $args = [ 'fields' => 'names' ]) {

		return wp_get_object_terms( $this->post->ID, ARTIST_TAXONOMY, $args );

	}


	/** Genres & Styles
	*******************/

	/**
	* @param $separator string
	* @return string a list of Genres separated by $separator
	* an empty string if no genres are found
	*/
	public function get_genres( $separator = ', ') {

		$this->_genres = implode(
			$separator,
			wp_get_object_terms( $this->post->ID, GENRE_TAXONOMY, [ 'fields' => 'names', 'orderby' => 'term_order' ] )
		);

		return $this->_genres;

	}
	public function get_styles( $separator = ', ') {

		$this->_styles = implode(
			$separator,
			wp_get_object_terms( $this->post->ID, STYLE_TAXONOMY, [ 'fields' => 'names', 'orderby' => 'term_order' ] )
		);

		return $this->_styles;
	}

	/**
	* will fetch genres and styles from an external API
	*/
	public function set_genres_and_styles( array $options = [ 'keep' => false, 'refresh' => false ] ) {

		$this->set_genres( $options );
		$this->set_styles( $options );

	}

	public function set_genres( array $options = [] ) {

		$defaults = [ 'keep' => false, 'refresh' => false ];

		$options = wp_parse_args( $options, $defaults );

		if( $this->_genres && ! $options['refresh'] ) {
			error_log("genres exist for # " . $this->post->ID . " - Skipping - " );
			return;
		}

		error_log('Fetching Genres for # ' . $this->post->ID);

		$params = [
			'title' => $this->post->post_title,
			'artist' => $this->get_artists(),
			'type' => 'master',
		];

		$params = wp_parse_args( $options, $params );

		// get from discogs
		$discogs = new Discogs\Database();
		$genres = $discogs->get_genres( $params );

		// keep existing terms
		// keep order (term_order)
		if( $options['keep'] ) {
			$genres = array_merge(
				wp_get_object_terms( $this->post->ID, GENRE_TAXONOMY, [ 'fields' => 'names', 'orderby' => 'term_order' ] ),
				$genres
			);
		}

		wp_set_object_terms( $this->post->ID, $genres, GENRE_TAXONOMY);
	}

	public function set_styles( array $options = [] ) {

		$defaults = [ 'keep' => false, 'refresh' => false ];

		$options = wp_parse_args( $options, $defaults );

		if( $this->_styles && ! $options['refresh'] ) {
			error_log("styles exist for # " . $this->post->ID . " - Skipping - " );
			return;
		}

		error_log('Fetching Styles for # ' . $this->post->ID);

		$params = [
			'title' => $this->post->post_title,
			'artist' => $this->get_artists(),
			'type' => 'master',
		];

		$params = wp_parse_args( $options, $params );

		// get from discogs
		$discogs = new Discogs\Database();
		$styles = $discogs->get_styles( $params );

		// keep existing terms
		// keep order (term_order)
		if( $options['keep'] ) {
			$styles = array_merge(
				wp_get_object_terms( $this->post->ID, STYLE_TAXONOMY, [ 'fields' => 'names', 'orderby' => 'term_order' ] ),
				$styles
			);
		}

		wp_set_object_terms( $this->post->ID, $styles, STYLE_TAXONOMY);
	}


	/* Tracklist
	*************/

	/**
	* @return array an array of tracks, an empty array if there are none for this release
	*/
	function get_tracklist() {
		return get_field('tracklist', $this->post->ID) ?: [];
	}

	/*
	* fetch tracks at Discogs & Spotify
	* uses Discogs tracklist as reference
	* enhance list with preview url
	* & using spotify durations when preview exists
	*/
	function set_tracklist( array $options = [] ) {

		$defaults = [ 'refresh' => false ];

		$options = wp_parse_args( $options, $defaults );

		if( ! empty(  $this->get_tracklist() ) && ! $options['refresh'] ) {
			error_log("Tracklist exists for # " . $this->post->ID . " - Skipping - " );
			return;
		}

		error_log('Fetching Tracklist for # ' . $this->post->ID);

		$title = $this->post->post_title;
		$artist = $this->get_artists();

		$params = [
			'title' => $title,
			'artist' => $artist,
			'type' => 'master',
		];

		$params = wp_parse_args( $options, $params );

		// remove all tracks - acf style
		update_field('tracklist', [], $this->post->ID);

		// get from discogs
		$tracklist_discogs = null;
		try {
			$discogs = new Discogs\Database();
			$tracklist_discogs = $discogs->get_tracklist( $params );
			$tracklist_discogs = array_map(
				function ($track) {
					return [
						'title' => $track['title'],
						'duration' => $track['duration'],
					];
				},
				$tracklist_discogs
			);
		}
		catch(Exception $e) {
			error_log($e->getMessage());
		}

		// get from spotify
		try {
			$tracklist_spotify = $this->get_spotify_tracklist( $params );
			if( $tracklist_spotify ) {
				$tracklist_spotify = array_map(
					function($track) {
						$duration_mn = intval($track->duration_ms) / 1000;
						$mn_in_duration = floor($duration_mn / 60 % 60);
						$s_in_duration = floor($duration_mn % 60);
						$mn_in_duration_str = str_pad($mn_in_duration, 2, '0', STR_PAD_LEFT);
						$s_in_duration_str = str_pad($s_in_duration, 2, '0', STR_PAD_LEFT);
						$formatted_duration =  "$mn_in_duration_str:$s_in_duration_str";
						return [
							'title' => $track->name,
							'duration' => $formatted_duration,
							'preview_url' => $track->preview_url,
						];
					},
					$tracklist_spotify
				);
			}
		}
		catch(Exception $e) {
			error_log($e->getMessage());
		}

		// merge lists
		try {
			$tracklist = [];
			if( $tracklist_discogs && $tracklist_spotify ) {
				$tracklist = array_map(
					function ($discogs_track) use ($tracklist_spotify) {
						foreach($tracklist_spotify as $spotify_track) {

							// we prefer finding a match on title
							// rather than relying on track number (order in both arrays)
							// to avoid errors in case 2 entries have not the same tracks order
							setlocale(LC_ALL, 'en_US.UTF-8');
							$discogs_title = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim(strtolower($discogs_track['title'])));
							$spotify_title = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim(strtolower($spotify_track['title'])));

							// replace characters that could not match
							$chars_map = [ '&' => 'and'];
							$discogs_title = sanitize_title(strtr($discogs_title, $chars_map));
							$spotify_title = sanitize_title(strtr($spotify_title, $chars_map));

							$pattern = preg_quote($discogs_title, '/');
							$exact_match = preg_match("/$pattern/", $spotify_title, $matches);
							similar_text($discogs_title, $spotify_title, $matching_percentage);
							if($exact_match || $matching_percentage > 80) {
								// we don't want to erase spotify's duration
								if($spotify_track['duration']) {
									unset($discogs_track['duration']);
								}
								return array_merge($spotify_track, $discogs_track);
							}
						}

						return $discogs_track;

					},
					$tracklist_discogs
				);
			}
			elseif($tracklist_discogs) {
				$tracklist = $tracklist_discogs;
			}
			elseif($tracklist_spotify) {
				$tracklist = $tracklist_spotify;
			}
		}
		catch(Exception $e) {
			error_log($e->getMessage());
		}

		// add to release
		try {
			foreach( $tracklist as $track ) {
				add_row( 'tracklist',  $track, $this->post->ID);
			}
		}
		catch(Exception $e) {
			error_log($e->getMessage());
		}

		return true;

	}

	/**
	* get a tracklist from spotify
	* @todo this shoudl be moved to API\Spotify
	*/
	function get_spotify_tracklist( $params ) {

		$params = wp_parse_args(
			$params,
			[
				'artist' => $this->get_artists(),
				'title' => $this->post->post_title,
			]
		);

		/**
		* @since 1.0.0
		* allows to modify the query just before it is sent to Spotify
		* @todo this
		*/
		$params = apply_filters( __NAMESPACE__ . '\Release\spotify_tracklist_params', $params );

		$artist = $params['artist'];
		$title = $params['title'];

		try {

			// get a token from the API
			$authorize_url = 'https://accounts.spotify.com/api/token';
			$key = getenv('SPOTIFY_API_CONSUMER_KEY');
			$secret = getenv('SPOTIFY_API_CONSUMER_SECRET');

			// do not proceed if no credentials found
			if( ! $key || ! $secret ) {
				throw new Exception('get_spotify_tracklist: Spotify key and/or secret are missing.');
			}

			$encoded_spotify_credentials = base64_encode("$key:$secret");

			$cache_key =  "WC_Discogs\Release\Spotify_Token_" . substr($encoded_spotify_credentials, 0, 12);
			$access_token = wp_cache_get( $cache_key );

			if( ! $access_token ) {

				$response = wp_safe_remote_post(
					$authorize_url,
					[
						'headers' => [
							// HARD CODED SPOTIFY KEY & SECRET (DEV@AALTOMERI.NET)
							// @TODO MOVE THIS TO SETTINGS !!!!!!
							'Authorization' => 'Basic ' . $encoded_spotify_credentials
						],
						'body' => [
							'grant_type' => 'client_credentials'
						]
					]
				);

				//turn WP_Error into an Exception
				if ( is_wp_error($response) ) {
					throw new Exception($response->get_error_message(), $response->get_error_code());
				}

				$response_body = json_decode($response['body']);

				if( ! isset($response_body->access_token) ) {
					return [];
				}

				$access_token = $response_body->access_token;
				wp_cache_set( $cache_key, $access_token, null, $response_body->expires_in );

			}

			// search for track with title and artist
			// TODO use Guzzle for consistency
			$search_url = "https://api.spotify.com/v1/search?q=album:{$title}+artist:{$artist}&type=album";
			$response = wp_remote_get(
				$search_url,
				[
					'headers' => [
						'Accept' => 'application/json',
						'Authorization' => 'Bearer ' . $access_token
					]
				]
			);

			//turn WP_Error into an Exception
			if ( is_wp_error($response) ) {
				throw new Exception($response->get_error_message(), $response->get_error_code());
			}

			$response_body = json_decode($response['body']);

			if( ! $response_body
				|| ! isset($response_body->albums)
				|| ! isset($response_body->albums->items)
			) {
				return [];
			}

			$tracklist = [];
			if ($response_body->albums->items) {
				$album_url = $response_body->albums->items[0]->href;
				$response = wp_remote_get($album_url,
					[
						'headers' => [
							'Accept' => 'application/json',
							'Authorization' => 'Bearer ' . $access_token
						]
					]
				);

				//turn WP_Error into an Exception
				if ( is_wp_error($response) ) {
					throw new Exception($response->get_error_message(), $response->get_error_code());
				}

				$response_body = json_decode($response['body']);
				return $response_body->tracks->items;
			}
		}
		catch(Exception $e) {
			error_log($e->getMessage());
		}

		return [];
	}


	/** Other infos
	*******************/

	/**
	* Will fetch Year of release from en external API
	* and set the appropriate custom field
	*/
	public function get_year() {

		$this->_release_date_year = get_field('release_date_year', $this->post->ID);

		return $this->_release_date_year;
	}

	/**
	* Will fetch Year of release from en external API
	* and set the appropriate custom field
	*/
	public function set_year( array $options = [] ) {

		$defaults = [ 'refresh' => false ];

		$options = wp_parse_args( $options, $defaults );

		if( $this->_release_date_year && ! $options['refresh'] ) {
			error_log("Year exists for # " . $this->post->ID . " - Skipping - " );
			return;
		}

		error_log('Fetching Year of release for # ' . $this->post->ID);

		$params = [
			'title' => $this->post->post_title,
			'artist' => $this->get_artists(),
			'type' => 'master',
		];

		$params = wp_parse_args( $options, $params );

		// get from discogs
		$discogs = new Discogs\Database();
		$release_date_year = $discogs->get_year( $params );

		update_field('release_date_year', $release_date_year, $this->post->ID);
	}


	/** Artwork
	*******************/

	/**
	* wrapper for getting WC product image
	* return WP_Post a WP attachment
	*/
	public function get_artwork() {
		return get_post( get_post_thumbnail_id( $this->post->ID ) );
	}

	/**
	* will fetch artwork from a remote source like Discogs
	*
	* @return int the ID of the attachment used as artwork for this Release
	*/
	public function set_artwork( $force = false, $params = [] ) {
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

		$defaults = [
			'artist' => $artist,
			'title' => $title,
			'type' => 'master',
		];

		$params = wp_parse_args( $params, $defaults );

		$attachment_id = null;

		// Attachment title
		// @TODO make this a method
		$artwork_wp_title = "{$artist} - {$title}";

		// we don't want to fetch from external source
		// if we already have an image in the Media Library
		// with a name that corresponds to the artwork we're looking for
		$attachment = Media::get_attachment_by_title( $artwork_wp_title );
		if ( $attachment ) {
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

			// normal flow
			// we have an artwork for this artist - title combination, we keep it
			if ( ! $force ) {
				error_log('Artwork found in Media Library for # ' . $this->post->ID . ' - Skipping -');
				return $attachment_id;
			}

			// there is an attachment but as we are 'forcing' the artwork fetch we:
			// remove attachment from Media Library as we will recreate it later
			wp_delete_attachment( $attachment_id, true );
			// state that we don't have an attachment id yet
			$attachment_id = null; //

		}

		// we don't want to fetch unecessarily
		// unless we really insist ...
		if( ! $force ) {
			if ( $attachment_id = $this->has_artwork() ) {
				error_log('Artwork alreay set for # ' . $this->post->ID . ' - Skipping -');
			 	return $attachment_id;
			}
		}

		error_log('Fetching Artwork for # ' . $this->post->ID);

		// Get Artwork URI from external source
		$external_resource = new Discogs\Database();
		$artwork_uri = $external_resource->get_artwork_uri( $params );


		// if the artwork uri is the uri of the default placeholder
		// we don't want to create a new attachment each time
		// so we look for the image filename and use the attachment if it is found
		if ( $artwork_uri === Media::$default_artwork_image_uri ) {
			// we also set a generic name in case the default place holder does not exist and is created
			$artwork_wp_title = "Default Placeholder";
			$attachment_id = Media::get_default_placeholder_attachment_id();
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

			// we make sure we get all variations
			// as some other plugin (WC POS for instance)
			// might hide some variations
			remove_all_filters('woocommerce_get_children');
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
	* @return mixed the attachment ID or false if no artwork
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


	/** UTILITIES **/

	/**
	* get Artists + Release name
	*/
	public function get_fullname() {
		return $this->get_artists() . ' - ' . $this->post->post_title;
	}

}
