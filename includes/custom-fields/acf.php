<?php

namespace WC_Discogs;

// add ACF fields group for BS WC Products
function acf_init() {

	/**
	* ARTISTS
	**********/
	if( function_exists('acf_add_local_field_group') ):

		/**
		* MUsic Release fields
		*/

		acf_add_local_field_group(array (
			'key' => 'group_58c27d720d372',
			'title' => 'Music Release Fields',
			'fields' => array (
				array (
					'key' => 'field_58dcff1fda49a',
					'label' => 'Artists, Genres & Styles',
					'name' => '',
					'type' => 'tab',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placement' => 'left',
					'endpoint' => 0,
				),
				array (
					'key' => 'field_58c27d7dec229',
					'label' => 'Artists',
					'name' => 'artist-taxonomy',
					'type' => 'taxonomy',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '33%',
						'class' => '',
						'id' => '',
					),
					'taxonomy' => __NAMESPACE__ . '_artist',
					'field_type' => 'multi_select',
					'allow_null' => 0,
					'add_term' => 1,
					'save_terms' => 1,
					'load_terms' => 1,
					'return_format' => 'id',
					'multiple' => 0,
				),
				array (
					'key' => 'field_58c97937f382d',
					'label' => 'Genres',
					'name' => 'genre-taxonomy',
					'type' => 'taxonomy',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '33%',
						'class' => '',
						'id' => '',
					),
					'taxonomy' => __NAMESPACE__ . '_genre',
					'field_type' => 'multi_select',
					'allow_null' => 0,
					'add_term' => 1,
					'save_terms' => 1,
					'load_terms' => 1,
					'return_format' => 'id',
					'multiple' => 0,
				),
				array (
					'key' => 'field_58c9796df382e',
					'label' => 'Styles',
					'name' => 'style-taxonomy',
					'type' => 'taxonomy',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '33%',
						'class' => '',
						'id' => '',
					),
					'taxonomy' => __NAMESPACE__ . '_style',
					'field_type' => 'multi_select',
					'allow_null' => 0,
					'add_term' => 1,
					'save_terms' => 1,
					'load_terms' => 1,
					'return_format' => 'id',
					'multiple' => 0,
				),
				array (
					'key' => 'field_58dcff5bda49b',
					'label' => 'Tracks',
					'name' => '',
					'type' => 'tab',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'placement' => 'left',
					'endpoint' => 0,
				),
				array (
					'key' => 'field_58c97ee7f0369',
					'label' => 'Tracklist',
					'name' => 'tracklist',
					'type' => 'repeater',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'collapsed' => 'field_58c9800af036a',
					'min' => 0,
					'max' => 0,
					'layout' => 'row',
					'button_label' => 'Add Track',
					'sub_fields' => array (
						array (
							'key' => 'field_58c9800af036a',
							'label' => 'Title',
							'name' => 'title',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '90',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array (
							'key' => 'field_58c98048f036b',
							'label' => 'Duration',
							'name' => 'duration',
							'type' => 'text',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '10',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
							'prepend' => '',
							'append' => '',
							'maxlength' => '',
						),
						array (
							'key' => 'field_58c98090f036c',
							'label' => 'Preview URL',
							'name' => 'preview_url',
							'type' => 'url',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array (
								'width' => '100',
								'class' => '',
								'id' => '',
							),
							'default_value' => '',
							'placeholder' => '',
						),
					),
				),
			),
			'location' => array (
				array (
					array (
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'product',
					),
				),
			),
			'menu_order' => 0,
			'position' => 'acf_after_title',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => 1,
			'description' => '',
		));

	endif;

}

add_action('acf/init', __NAMESPACE__ . '\acf_init');
