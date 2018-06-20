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