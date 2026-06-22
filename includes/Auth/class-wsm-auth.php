<?php
/**
 * Custom Session and Authentication Manager
 *
 * @package KarasuWooPannel
 * @version 1.0.1
 * @date 2026-06-23
 */

namespace WooStoreManager\Auth;

use WP_User;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Auth
 */
class WSM_Auth {

	/**
	 * Authenticate user credentials and open a new session.
	 *
	 * @param string $username Username.
	 * @param string $password Password.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function login( string $username, string $password ): bool|WP_Error {
		// Use standard WordPress authentication engine.
		$user = wp_authenticate( $username, $password );

		if ( is_wp_error( $user ) ) {
			return $user;
		}

		// Verify if user has store admin panel access capabilities.
		if ( ! user_can( $user, 'wsm_access_panel' ) ) {
			return new WP_Error(
				'wsm_access_denied',
				__( 'حساب کاربری شما دسترسی لازم برای ورود به پنل را ندارد.', 'karasu-woo-pannel' )
			);
		}

		// Generate cryptographically secure token.
		$token        = bin2hex( random_bytes( 32 ) );
		$hashed_token = wp_hash( $token );

		// Session lifetime in hours.
		$lifetime_hours = (int) get_option( 'wsm_session_lifetime', 8 );
		$lifetime_secs  = $lifetime_hours * HOUR_IN_SECONDS;

		// Calculate expiration date in GMT.
		$created_at = current_time( 'mysql', true );
		$expires_at = gmdate( 'Y-m-d H:i:s', time() + $lifetime_secs );

		global $wpdb;
		$table = $wpdb->prefix . 'wsm_sessions';

		// Insert session details in custom DB table.
		$inserted = $wpdb->insert(
			$table,
			[
				'session_id' => $hashed_token,
				'user_id'    => $user->ID,
				'created_at' => $created_at,
				'expires_at' => $expires_at,
				'ip_address' => WSM_Rate_Limiter::get_client_ip(),
				'user_agent' => substr( sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ), 0, 255 ),
			],
			[ '%s', '%d', '%s', '%s', '%s', '%s' ]
		);

		if ( ! $inserted ) {
			return new WP_Error(
				'wsm_session_creation_failed',
				__( 'خطا در ایجاد نشست کاربری. لطفا دوباره تلاش کنید.', 'karasu-woo-pannel' )
			);
		}

		// Set HttpOnly secure session cookie.
		setcookie(
			'wsm_session',
			$token,
			[
				'expires'  => time() + $lifetime_secs,
				'path'     => '/',
				'domain'   => '',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Strict',
			]
		);

		return true;
	}

	/**
	 * Close active session and clear cookies.
	 */
	public function logout(): void {
		$token = self::get_instance_token();

		if ( $token ) {
			global $wpdb;
			$table        = $wpdb->prefix . 'wsm_sessions';
			$hashed_token = wp_hash( $token );

			// Remove record from custom DB table.
			$wpdb->delete( $table, [ 'session_id' => $hashed_token ], [ '%s' ] );
		}

		// Clear cookie.
		setcookie(
			'wsm_session',
			'',
			[
				'expires'  => time() - YEAR_IN_SECONDS,
				'path'     => '/',
				'domain'   => '',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Strict',
			]
		);
	}

	/**
	 * Check if current request session is authenticated and valid.
	 *
	 * @return bool True if valid session exists.
	 */
	public static function is_authenticated(): bool {
		$token = self::get_instance_token();
		if ( ! $token ) {
			return false;
		}

		global $wpdb;
		$table        = $wpdb->prefix . 'wsm_sessions';
		$hashed_token = wp_hash( $token );

		$session = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE session_id = %s AND expires_at > %s LIMIT 1",
				$hashed_token,
				current_time( 'mysql', true )
			)
		);

		return (bool) $session;
	}

	/**
	 * WordPress hook to resolve current user ID from session.
	 *
	 * @param int|false $user_id User ID or false.
	 * @return int|false Current user ID or false.
	 */
	public function determine_current_user( $user_id ) {
		if ( $user_id ) {
			return $user_id;
		}

		$token = self::get_instance_token();
		if ( ! $token ) {
			return $user_id;
		}

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
			return (int) $session->user_id;
		}

		return $user_id;
	}

	/**
	 * Helper to retrieve raw session token from Request.
	 *
	 * @return string Raw session token.
	 */
	private static function get_instance_token(): string {
		if ( ! empty( $_COOKIE['wsm_session'] ) ) {
			return sanitize_text_field( wp_unslash( $_COOKIE['wsm_session'] ) );
		}

		if ( function_exists( 'getallheaders' ) ) {
			$headers = getallheaders();
			if ( isset( $headers['X-WSM-Token'] ) ) {
				return sanitize_text_field( wp_unslash( $headers['X-WSM-Token'] ) );
			}
		}

		return '';
	}
}
