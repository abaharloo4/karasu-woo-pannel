<?php
/**
 * Custom Rewrite Rules Manager
 *
 * @package KarasuWooPannel
 * @version 1.0.0
 * @date 2026-06-23
 */

namespace WooStoreManager\Router;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Rewrite
 */
class WSM_Rewrite {

	/**
	 * Register custom rewrite rules with WordPress.
	 */
	public function add_rewrite_rules(): void {
		$slug = get_option( 'wsm_panel_slug', 'store-admin' );
		add_rewrite_rule(
			'^' . $slug . '(/(.*))?/?$',
			'index.php?wsm_panel=1&wsm_path=$matches[2]',
			'top'
		);
	}

	/**
	 * Register query variables with WordPress.
	 *
	 * @param array $vars Array of registered query variables.
	 * @return array Modified query variables.
	 */
	public function add_query_vars( array $vars ): array {
		$vars[] = 'wsm_panel';
		$vars[] = 'wsm_path';
		return $vars;
	}

	/**
	 * Intercept the request if custom query parameter is present.
	 */
	public function handle_request(): void {
		if ( ! get_query_var( 'wsm_panel' ) ) {
			return;
		}

		$router = new WSM_Router();
		$router->dispatch( get_query_var( 'wsm_path', '' ) );
		exit;
	}
}
