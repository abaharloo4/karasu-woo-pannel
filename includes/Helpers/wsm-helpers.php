<?php
/**
 * Global Helper Functions
 *
 * @package KarasuWooPannel
 * @version 1.1.1
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

/**
 * Encrypt a password string using AES-256-CBC.
 *
 * @param string $password Raw password string.
 * @return string Hashed/encrypted base64 string.
 */
function wsm_encrypt_password( string $password ): string {
	if ( empty( $password ) ) {
		return '';
	}
	$key        = hash( 'sha256', wp_salt( 'auth' ) );
	$method     = 'aes-256-cbc';
	$iv_len     = openssl_cipher_iv_length( $method );
	$iv         = openssl_random_pseudo_bytes( $iv_len );
	$ciphertext = openssl_encrypt( $password, $method, $key, OPENSSL_RAW_DATA, $iv );
	return base64_encode( $iv . $ciphertext );
}

/**
 * Decrypt an encrypted password string.
 *
 * @param string $encrypted Encrypted base64 string or legacy plaintext.
 * @return string Decrypted plaintext password.
 */
function wsm_decrypt_password( string $encrypted ): string {
	if ( empty( $encrypted ) ) {
		return '';
	}
	$decoded = base64_decode( $encrypted, true );
	if ( false === $decoded ) {
		return $encrypted; // Assume legacy plaintext if not valid base64.
	}
	$key    = hash( 'sha256', wp_salt( 'auth' ) );
	$method = 'aes-256-cbc';
	$iv_len = openssl_cipher_iv_length( $method );
	if ( strlen( $decoded ) < $iv_len + 1 ) {
		return $encrypted; // Too short, assume legacy plaintext.
	}
	$iv         = substr( $decoded, 0, $iv_len );
	$ciphertext = substr( $decoded, $iv_len );
	$decrypted  = openssl_decrypt( $ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv );
	return false !== $decrypted ? $decrypted : $encrypted;
}
