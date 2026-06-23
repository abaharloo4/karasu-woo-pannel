<?php
/**
 * View Router and Dispatcher
 *
 * @package KarasuWooPannel
 * @version 1.0.9
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

		$this->render( $view );
	}

	/**
	 * Render the view template within the base layout.
	 *
	 * @param string $view View template file path base.
	 */
	private function render( string $view ): void {
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
