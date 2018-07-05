<?php

namespace WC_Discogs;

/**
 * in case we don't want to display the Root Music Release category any time we display a Product's categories,
 * we allow to filter out the Music Release category from the returned Categories list in Product page
 * 
*/
add_filter(
    'woocommerce_get_product_terms',
    function($terms) {
        if(is_product()) {
            // by default we do not remove the Root Music Release category
            if( ! apply_filters( __NAMESPACE__ . '\omit_music_release_in_categories_list_on_product_page', false) ) {
                return $terms;
            }

            return array_filter(
                $terms,
                function($term) {
                    if (is_string($term)) {
                        $term = get_term_by('name', $term, 'product_cat');
                    }

                    if(is_a($term, 'WP_Term')) {
                        return $term->slug !== 'music-release';
                    }

                    return $term;
                }
            );
        }
        
        return $terms;
    }
);


/**
 * Customize related products output
 */

add_filter(
    'woocommerce_get_related_product_cat_terms',
    function($terms_ids, $product_id) {
        // exclude Music Release Root Category
        // as by default all Music Releases belong to this parent category
        // and that not excluding it would make the related products mechanism useless
        // as all music releases could potentially be returned
        $terms_ids = array_filter(
            $terms_ids,
            function($term_id) {
                if (is_numeric($term_id)) {
                    $term = get_term_by('term_id', $term_id, 'product_cat');
                }

                if(is_a($term, 'WP_Term')) {
                    return $term->slug !== 'music-release';
                }

                return false;
            }
        );
        return $terms_ids;
    },
    10,
    2
 );


 /**
  * Use the woocommerce_get_related_product_tag_terms filter
  * to add WooCommerce Record Store custom taxonomies terms to be used
  * in the related products fetch mechanism
  * we could also use the filter used for product categories as the related products query
  * does not seem to care for the taxonomy name ...
  */
 add_filter(
    'woocommerce_get_related_product_tag_terms',
    function($terms, $product_id) {
        // get the music release genres ids
        if(wc_recordstore_is_music_release($product_id)) {
            $styles_ids = wp_get_object_terms( $product_id, STYLE_TAXONOMY, [ 'fields' => 'ids' ] );
            $artist_ids = wp_get_object_terms( $product_id, ARTIST_TAXONOMY, [ 'fields' => 'ids' ] );
            return array_merge($terms, $styles_ids, $artist_ids);
        }
        return $terms;
    },
    10,
    2
 );