<?php
/**
 * Global Helper Functions
 *
 * @package KarasuWooPannel
 * @version 1.0.4
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get the custom panel URL.
 *
 * @param string $path Optional path to append.
 * @return string Panel URL.
 */
function wsm_panel_url( string $path = '' ): string {
	$slug = get_option( 'wsm_panel_slug', 'store-admin' );
	$url  = home_url( '/' . $slug );
	if ( ! empty( $path ) ) {
		$url = rtrim( $url, '/' ) . '/' . ltrim( $path, '/' );
	}
	return user_trailingslashit( $url );
}

/**
 * Get the custom login URL.
 *
 * @return string Login URL.
 */
function wsm_login_url(): string {
	return wsm_panel_url( 'login' );
}

/**
 * Check if the current manager is authenticated.
 *
 * @return bool True if authenticated.
 */
function wsm_is_authenticated(): bool {
	return \WooStoreManager\Auth\WSM_Auth::is_authenticated();
}

/**
 * Get plugin settings option value.
 *
 * @param string $key Settings key.
 * @param mixed  $default Default value if option is not set.
 * @return mixed Option value.
 */
function wsm_get_setting( string $key, mixed $default = null ): mixed {
	return get_option( 'wsm_' . $key, $default );
}
