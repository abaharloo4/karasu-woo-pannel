<?php
/**
 * View Router and Dispatcher
 *
 * @package KarasuWooPannel
 * @version 1.1.1
 * @date 2026-06-23
 */

namespace WooStoreManager\Router;

use WooStoreManager\Auth\WSM_Auth;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Router
 */
class WSM_Router {

	/**
	 * Route mapping table.
	 *
	 * @var array
	 */
	private array $routes = [
		''                  => 'dashboard',
		'login'             => 'login',
		'orders'            => 'orders/list',
		'orders/view'       => 'orders/detail',
		'products'          => 'products/list',
		'products/new'      => 'products/edit',
		'products/edit'     => 'products/edit',
		'categories'        => 'categories/list',
		'coupons'           => 'coupons/list',
		'coupons/new'       => 'coupons/edit',
		'coupons/edit'      => 'coupons/edit',
		'attributes'        => 'attributes/list',
		'brands'            => 'brands/list',
		'reports'           => 'reports/dashboard',
		'reports/sales'     => 'reports/sales',
		'reports/products'  => 'reports/products',
		'reports/customers' => 'reports/customers',
	];

	/**
	 * Dispatch the request path to a view template.
	 *
	 * @param string $path Path request parameter.
	 */
	public function dispatch( string $path ): void {
		$path = trim( $path, '/' );

		// 1. If route is login, allow guests, redirect authenticated store managers to dashboard.
		if ( 'login' === $path ) {
			if ( WSM_Auth::is_authenticated() ) {
				wp_safe_redirect( wsm_panel_url() );
				exit;
			}
			$this->render( 'login' );
			return;
		}

		// 2. Check if authenticated.
		if ( ! WSM_Auth::is_authenticated() ) {
			wp_safe_redirect( wsm_login_url() );
			exit;
		}

		// 3. Verify user has capability to access the panel.
		if ( ! current_user_can( 'wsm_access_panel' ) && ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			// Redirect unauthorized logged-in users (like subscribers) to site home.
			wp_safe_redirect( home_url() );
			exit;
		}

		// 4. Dispatch path to view.
		$view = $this->routes[ $path ] ?? null;
		if ( ! $view ) {
			$this->render( '404' );
			return;
		}

		// Verify global settings and capability for this specific view
		$view_permissions = [
			'dashboard' => [
				'enable_key' => 'wsm_enable_dashboard',
				'capability' => 'wsm_access_panel',
			],
			'orders/list' => [
				'enable_key' => 'wsm_enable_orders',
				'capability' => 'wsm_manage_orders',
			],
			'orders/detail' => [
				'enable_key' => 'wsm_enable_orders',
				'capability' => 'wsm_manage_orders',
			],
			'products/list' => [
				'enable_key' => 'wsm_enable_products',
				'capability' => 'wsm_manage_products',
			],
			'products/edit' => [
				'enable_key' => 'wsm_enable_products',
				'capability' => 'wsm_manage_products',
			],
			'categories/list' => [
				'enable_key' => 'wsm_enable_categories',
				'capability' => 'wsm_manage_products',
			],
			'coupons/list' => [
				'enable_key' => 'wsm_enable_coupons',
				'capability' => 'wsm_manage_coupons',
			],
			'coupons/edit' => [
				'enable_key' => 'wsm_enable_coupons',
				'capability' => 'wsm_manage_coupons',
			],
			'attributes/list' => [
				'enable_key' => 'wsm_enable_attributes',
				'capability' => 'wsm_manage_products',
			],
			'brands/list' => [
				'enable_key' => 'wsm_enable_brands',
				'capability' => 'wsm_manage_products',
			],
			'reports/dashboard' => [
				'enable_key' => 'wsm_enable_reports',
				'capability' => 'wsm_view_reports',
			],
			'reports/sales' => [
				'enable_key' => 'wsm_enable_reports',
				'capability' => 'wsm_view_reports',
			],
			'reports/products' => [
				'enable_key' => 'wsm_enable_reports',
				'capability' => 'wsm_view_reports',
			],
			'reports/customers' => [
				'enable_key' => 'wsm_enable_reports',
				'capability' => 'wsm_view_reports',
			],
			'sms/settings' => [
				'enable_key' => 'wsm_enable_sms',
				'capability' => 'wsm_manage_sms',
			],
		];

		if ( isset( $view_permissions[ $view ] ) ) {
			$perm = $view_permissions[ $view ];
			
			// Check if the section is globally enabled
			if ( get_option( $perm['enable_key'], 'yes' ) !== 'yes' ) {
				$this->render( '404' );
				return;
			}

			// Check user capability strictly
			if ( ! current_user_can( $perm['capability'] ) ) {
				wp_die( esc_html__( 'دسترسی غیرمجاز. شما مجوز دسترسی به این بخش را ندارید.', 'karasu-woo-pannel' ), esc_html__( 'دسترسی غیرمجاز', 'karasu-woo-pannel' ), [ 'response' => 403 ] );
				return;
			}
		}

		$this->render( $view );
	}

	/**
	 * Register and enqueue panel assets.
	 */
	public function enqueue_panel_assets(): void {
		// Setup filters to inject crossorigin and defer/integrity attributes
		add_filter( 'style_loader_tag', [ $this, 'style_loader_tag_filters' ], 10, 2 );
		add_filter( 'script_loader_tag', [ $this, 'script_loader_tag_filters' ], 10, 2 );

		wp_register_style(
			'wsm-font-vazirmatn',
			'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap',
			[],
			WSM_VERSION
		);

		wp_register_style(
			'wsm-jalalidatepicker-css',
			WSM_PLUGIN_URL . 'assets/lib/jalalidatepicker.min.css',
			[],
			WSM_VERSION
		);

		wp_register_style(
			'wsm-panel-css',
			WSM_PLUGIN_URL . 'assets/css/wsm-panel.css',
			[ 'wsm-jalalidatepicker-css' ],
			WSM_VERSION
		);

		wp_register_script(
			'wsm-panel-js',
			WSM_PLUGIN_URL . 'assets/js/wsm-panel.js',
			[],
			WSM_VERSION,
			true
		);

		wp_register_script(
			'wsm-jalalidatepicker-js',
			WSM_PLUGIN_URL . 'assets/lib/jalalidatepicker.min.js',
			[],
			WSM_VERSION,
			true
		);

		wp_register_script(
			'wsm-attributes-js',
			WSM_PLUGIN_URL . 'assets/js/wsm-attributes.js',
			[],
			WSM_VERSION,
			true
		);

		wp_enqueue_style( 'wsm-font-vazirmatn' );
		wp_enqueue_style( 'wsm-jalalidatepicker-css' );
		wp_enqueue_style( 'wsm-panel-css' );
		wp_enqueue_script( 'wsm-panel-js' );
		wp_enqueue_script( 'wsm-jalalidatepicker-js' );
	}

	/**
	 * Filter style tags to inject integrity/crossorigin.
	 *
	 * @param string $tag HTML link tag.
	 * @param string $handle Script handle.
	 * @return string Filtered tag.
	 */
	public function style_loader_tag_filters( string $tag, string $handle ): string {
		if ( 'wsm-font-vazirmatn' === $handle ) {
			$tag = str_replace( ' href=', ' crossorigin="anonymous" href=', $tag );
		}
		return $tag;
	}

	/**
	 * Filter script tags to inject defer.
	 *
	 * @param string $tag HTML script tag.
	 * @param string $handle Script handle.
	 * @return string Filtered tag.
	 */
	public function script_loader_tag_filters( string $tag, string $handle ): string {
		if ( 'wsm-jalalidatepicker-js' === $handle ) {
			$tag = str_replace( ' src=', ' defer src=', $tag );
		}
		return $tag;
	}

	/**
	 * Render the view template within the base layout.
	 *
	 * @param string $view View template file path base.
	 */
	private function render( string $view ): void {
		$this->enqueue_panel_assets();

		if ( in_array( $view, [ 'attributes/list', 'brands/list' ], true ) ) {
			wp_enqueue_script( 'wsm-attributes-js' );
		}

		// Exposed to layout.php
		$view_file = WSM_PLUGIN_DIR . 'panel/views/' . $view . '.php';

		// Base layout loads the view_file.
		$layout_file = WSM_PLUGIN_DIR . 'panel/layout.php';

		if ( file_exists( $layout_file ) ) {
			require_once $layout_file;
		} else {
			wp_die( esc_html__( 'Layout file not found.', 'karasu-woo-pannel' ) );
		}
	}
}
