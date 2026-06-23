<?php
/**
 * Custom Taxonomy Registration
 *
 * @package KarasuWooPannel
 * @version 1.1.1
 * @date 2026-06-23
 */

namespace WooStoreManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Taxonomy
 */
class WSM_Taxonomy {

	/**
	 * Register brand taxonomy on init hook.
	 */
	public function register(): void {
		if ( ! taxonomy_exists( 'product_brand' ) ) {
			register_taxonomy(
				'product_brand',
				'product',
				[
					'label'             => __( 'Brands', 'karasu-woo-pannel' ),
					'labels'            => [
						'name'              => __( 'Brands', 'karasu-woo-pannel' ),
						'singular_name'     => __( 'Brand', 'karasu-woo-pannel' ),
						'search_items'      => __( 'Search Brands', 'karasu-woo-pannel' ),
						'all_items'         => __( 'All Brands', 'karasu-woo-pannel' ),
						'parent_item'       => __( 'Parent Brand', 'karasu-woo-pannel' ),
						'parent_item_colon' => __( 'Parent Brand:', 'karasu-woo-pannel' ),
						'edit_item'         => __( 'Edit Brand', 'karasu-woo-pannel' ),
						'update_item'       => __( 'Update Brand', 'karasu-woo-pannel' ),
						'add_new_item'      => __( 'Add New Brand', 'karasu-woo-pannel' ),
						'new_item_name'     => __( 'New Brand Name', 'karasu-woo-pannel' ),
						'menu_name'         => __( 'Brands', 'karasu-woo-pannel' ),
					],
					'hierarchical'      => true,
					'public'            => true,
					'show_ui'           => true,
					'show_in_rest'      => true,
					'show_admin_column' => true,
					'query_var'         => true,
					'rewrite'           => [ 'slug' => 'brand' ],
				]
			);
		}
	}
}
