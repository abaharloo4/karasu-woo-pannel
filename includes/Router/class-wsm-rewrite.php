<?php
/**
 * Custom Rewrite Rules Manager
 *
 * @package KarasuWooPannel
 * @version 1.0.5
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

		// Authenticate and manually set the current user if custom session is active
		// to bypass early anonymous user caching in WordPress.
		if ( ! is_user_logged_in() ) {
			$token = $_COOKIE['wsm_session'] ?? '';
			if ( ! empty( $token ) ) {
				global $wpdb;
				$table        = $wpdb->prefix . 'wsm_sessions';
				$hashed_token = wp_hash( $token );

				$session = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT user_id FROM {$table} WHERE session_id = %s AND expires_at > %s LIMIT 1",
						$hashed_token,
						current_time( 'mysql', true )
					)
				);

				if ( $session ) {
					wp_set_current_user( (int) $session->user_id );
				}
			}
		}

		$router = new WSM_Router();
		$router->dispatch( get_query_var( 'wsm_path', '' ) );
		exit;
	}
}
