<?php
/**
 * Authentication REST Controller
 *
 * @package KarasuWooPannel
 * @version 1.0.10
 * @date 2026-06-23
 */

namespace WooStoreManager\Api;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WooStoreManager\Auth\WSM_Auth;
use WooStoreManager\Auth\WSM_Rate_Limiter;
use WooStoreManager\Helpers\WSM_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Auth_Controller
 */
class WSM_Auth_Controller extends WSM_REST_Controller {

	/**
	 * Auth engine.
	 *
	 * @var WSM_Auth
	 */
	private WSM_Auth $auth;

	/**
	 * Rate limiter.
	 *
	 * @var WSM_Rate_Limiter
	 */
	private WSM_Rate_Limiter $rate_limiter;

	/**
	 * WSM_Auth_Controller constructor.
	 */
	public function __construct() {
		$this->auth         = new WSM_Auth();
		$this->rate_limiter = new WSM_Rate_Limiter();
	}

	/**
	 * Register routes with WordPress.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/auth/login',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'login' ],
				'permission_callback' => '__return_true', // Publicly accessible.
			]
		);

		register_rest_route(
			$this->namespace,
			'/auth/logout',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'logout' ],
				'permission_callback' => '__return_true', // Stale cookies can still logout.
			]
		);
	}

	/**
	 * Handle API Login request.
	 *
	 * @param WP_REST_Request $request REST Request parameters.
	 * @return WP_REST_Response|WP_Error Response details.
	 */
	public function login( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$params   = json_decode( $request->get_body(), true );
		$username = sanitize_text_field( $params['username'] ?? '' );
		$password = sanitize_text_field( $params['password'] ?? '' );
		$nonce    = sanitize_text_field( $params['nonce'] ?? '' );
		$ip       = WSM_Rate_Limiter::get_client_ip();

		// 1. Verify CSRF Login Nonce.
		if ( ! wp_verify_nonce( $nonce, 'wsm_login_action' ) ) {
			return WSM_Response::error( __( 'خطای امنیتی. لطفا صفحه را مجددا بارگذاری کنید.', 'karasu-woo-pannel' ), 403 );
		}

		// 2. Verify Rate Limiting.
		if ( $this->rate_limiter->is_blocked( $ip ) ) {
			$lockout = $this->rate_limiter->get_remaining_lockout( $ip );
			return WSM_Response::error(
				sprintf(
					/* translators: %d: minutes remaining */
					__( 'تلاش‌های ناموفق شما بیش از حد مجاز بوده است. لطفا %d دقیقه دیگر تلاش کنید.', 'karasu-woo-pannel' ),
					$lockout
				),
				429
			);
		}

		// 3. Attempt Authentication.
		$result = $this->auth->login( $username, $password );

		if ( is_wp_error( $result ) ) {
			// Record failed attempt in rate limiter.
			$this->rate_limiter->record_attempt( $ip );
			return WSM_Response::error( $result->get_error_message(), 401 );
		}

		// Success: Reset rate limiter history.
		$this->rate_limiter->reset( $ip );

		return WSM_Response::success( null, __( 'ورود با موفقیت انجام شد.', 'karasu-woo-pannel' ) );
	}

	/**
	 * Handle API Logout request.
	 *
	 * @param WP_REST_Request $request REST Request.
	 * @return WP_REST_Response Response details.
	 */
	public function logout( WP_REST_Request $request ): WP_REST_Response {
		$this->auth->logout();
		return WSM_Response::success( null, __( 'خروج با موفقیت انجام شد.', 'karasu-woo-pannel' ) );
	}
}
