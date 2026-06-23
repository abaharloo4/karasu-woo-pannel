<?php
/**
 * REST Controller for Attributes and Brands
 *
 * @package KarasuWooPannel
 * @version 1.1.1
 * @date 2026-06-23
 */

namespace WooStoreManager\Api;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WooStoreManager\Helpers\WSM_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Attributes_Controller
 */
class WSM_Attributes_Controller extends WSM_REST_Controller {

	/**
	 * Register REST routes.
	 */
	public function register_routes(): void {
		// Global Attributes List/Create
		register_rest_route(
			$this->namespace,
			'/attributes',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_attributes' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'create_attribute' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		// Global Attributes Delete
		register_rest_route(
			$this->namespace,
			'/attributes/(?P<id>\d+)',
			[
				[
					'methods'             => 'DELETE',
					'callback'            => [ $this, 'delete_attribute' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		// Attribute Terms List/Create
		register_rest_route(
			$this->namespace,
			'/attributes/(?P<slug>[a-zA-Z0-9_\-]+)/terms',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_attribute_terms' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'create_attribute_term' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		// Attribute Terms Delete
		register_rest_route(
			$this->namespace,
			'/attributes/(?P<slug>[a-zA-Z0-9_\-]+)/terms/(?P<id>\d+)',
			[
				[
					'methods'             => 'DELETE',
					'callback'            => [ $this, 'delete_attribute_term' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		// Brands List/Create
		register_rest_route(
			$this->namespace,
			'/brands',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'get_brands' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'create_brand' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);

		// Brands Delete
		register_rest_route(
			$this->namespace,
			'/brands/(?P<id>\d+)',
			[
				[
					'methods'             => 'DELETE',
					'callback'            => [ $this, 'delete_brand' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);
	}

	/**
	 * Verify product permissions check.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error True if authorized, else WP_Error.
	 */
	public function check_permission( WP_REST_Request $request ): bool|WP_Error {
		return $this->check_capability_permission( $request, 'wsm_manage_products', __( 'دسترسی غیرمجاز. شما مجوز مدیریت محصولات را ندارید.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Get list of all global WooCommerce attributes.
	 */
	public function get_attributes( WP_REST_Request $request ): WP_REST_Response {
		$taxonomies = wc_get_attribute_taxonomies();
		$formatted  = [];
		foreach ( $taxonomies as $tax ) {
			$formatted[] = [
				'id'   => (int) $tax->attribute_id,
				'name' => $tax->attribute_label,
				'slug' => $tax->attribute_name,
				'type' => $tax->attribute_type,
			];
		}
		return WSM_Response::success( $formatted, __( 'ویژگی‌ها با موفقیت دریافت شدند.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Create a new global WooCommerce attribute.
	 */
	public function create_attribute( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$body = json_decode( $request->get_body(), true );
		$name = sanitize_text_field( $body['name'] ?? '' );
		$slug = sanitize_title( $body['slug'] ?? '' );

		if ( empty( $name ) ) {
			return WSM_Response::error( __( 'نام ویژگی الزامی است.', 'karasu-woo-pannel' ) );
		}

		if ( empty( $slug ) ) {
			$slug = sanitize_title( $name );
		}

		// Max length of taxonomy name in WP is 32 chars. 'pa_' prefix is 3 chars. So slug limit is 28.
		if ( strlen( $slug ) > 28 ) {
			$slug = substr( $slug, 0, 28 );
		}

		$args = [
			'name'         => $name,
			'slug'         => $slug,
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		];

		$id = wc_create_attribute( $args );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		// Register taxonomy dynamically for current request
		$taxonomy_name = wc_attribute_taxonomy_name( $slug );
		register_taxonomy(
			$taxonomy_name,
			'product',
			[
				'hierarchical' => false,
				'show_ui'      => false,
				'query_var'    => true,
				'rewrite'      => false,
			]
		);

		return WSM_Response::success( [ 'id' => $id, 'slug' => $slug ], __( 'ویژگی با موفقیت ایجاد شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Delete a global WooCommerce attribute.
	 */
	public function delete_attribute( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id = (int) $request->get_param( 'id' );
		$deleted = wc_delete_attribute( $id );

		if ( ! $deleted ) {
			return WSM_Response::error( __( 'حذف ویژگی ناموفق بود.', 'karasu-woo-pannel' ) );
		}

		return WSM_Response::success( null, __( 'ویژگی با موفقیت حذف شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Get list of terms for a global attribute.
	 */
	public function get_attribute_terms( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$slug = sanitize_title( $request->get_param( 'slug' ) );
		$taxonomy = wc_attribute_taxonomy_name( $slug );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			// Register on the fly if needed
			register_taxonomy( $taxonomy, 'product', [] );
		}

		$terms = get_terms( [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		] );

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		$formatted = [];
		foreach ( $terms as $term ) {
			$formatted[] = [
				'id'   => $term->term_id,
				'name' => $term->name,
				'slug' => $term->slug,
			];
		}

		return WSM_Response::success( $formatted, __( 'مقادیر ویژگی با موفقیت دریافت شدند.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Create a new term for a global attribute.
	 */
	public function create_attribute_term( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$slug = sanitize_title( $request->get_param( 'slug' ) );
		$taxonomy = wc_attribute_taxonomy_name( $slug );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			register_taxonomy( $taxonomy, 'product', [] );
		}

		$body = json_decode( $request->get_body(), true );
		$name = sanitize_text_field( $body['name'] ?? '' );
		$term_slug = sanitize_title( $body['slug'] ?? '' );

		if ( empty( $name ) ) {
			return WSM_Response::error( __( 'نام مقدار الزامی است.', 'karasu-woo-pannel' ) );
		}

		$args = [];
		if ( ! empty( $term_slug ) ) {
			$args['slug'] = $term_slug;
		}

		$result = wp_insert_term( $name, $taxonomy, $args );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success( [ 'term_id' => $result['term_id'] ], __( 'مقدار ویژگی با موفقیت ایجاد شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Delete a term from a global attribute.
	 */
	public function delete_attribute_term( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$slug = sanitize_title( $request->get_param( 'slug' ) );
		$taxonomy = wc_attribute_taxonomy_name( $slug );
		$id = (int) $request->get_param( 'id' );

		$result = wp_delete_term( $id, $taxonomy );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! $result ) {
			return WSM_Response::error( __( 'حذف مقدار ناموفق بود.', 'karasu-woo-pannel' ) );
		}

		return WSM_Response::success( null, __( 'مقدار ویژگی با موفقیت حذف شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Get list of all brands.
	 */
	public function get_brands( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$terms = get_terms( [
			'taxonomy'   => 'product_brand',
			'hide_empty' => false,
		] );

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		$formatted = [];
		foreach ( $terms as $term ) {
			$formatted[] = [
				'id'          => $term->term_id,
				'name'        => $term->name,
				'slug'        => $term->slug,
				'description' => $term->description,
			];
		}

		return WSM_Response::success( $formatted, __( 'برندها با موفقیت دریافت شدند.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Create a brand.
	 */
	public function create_brand( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$body = json_decode( $request->get_body(), true );
		$name = sanitize_text_field( $body['name'] ?? '' );
		$slug = sanitize_title( $body['slug'] ?? '' );
		$description = sanitize_textarea_field( $body['description'] ?? '' );

		if ( empty( $name ) ) {
			return WSM_Response::error( __( 'نام برند الزامی است.', 'karasu-woo-pannel' ) );
		}

		$args = [
			'description' => $description,
		];
		if ( ! empty( $slug ) ) {
			$args['slug'] = $slug;
		}

		$result = wp_insert_term( $name, 'product_brand', $args );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return WSM_Response::success( [ 'term_id' => $result['term_id'] ], __( 'برند با موفقیت ایجاد شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Delete a brand.
	 */
	public function delete_brand( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$id = (int) $request->get_param( 'id' );

		$result = wp_delete_term( $id, 'product_brand' );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! $result ) {
			return WSM_Response::error( __( 'حذف برند ناموفق بود.', 'karasu-woo-pannel' ) );
		}

		return WSM_Response::success( null, __( 'برند با موفقیت حذف شد.', 'karasu-woo-pannel' ) );
	}
}
