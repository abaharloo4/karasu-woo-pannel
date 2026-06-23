<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package KarasuWooPannel
 * @version 1.0.3
 * @date 2026-06-23
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// 1. Drop custom database tables.
$tables = [
	$wpdb->prefix . 'wsm_sms_log',
	$wpdb->prefix . 'wsm_login_attempts',
	$wpdb->prefix . 'wsm_sessions',
];

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
}

// 2. Delete options from wp_options.
$options = [
	'wsm_panel_slug',
	'wsm_session_lifetime',
	'wsm_admin_mobile',
	'wsm_sms_username',
	'wsm_sms_password',
	'wsm_sms_from_line',
	'wsm_sms_evt_new_order',
	'wsm_sms_evt_order_status',
	'wsm_sms_evt_low_stock',
	'wsm_low_stock_threshold',
	'wsm_db_version',
];

foreach ( $options as $option ) {
	delete_option( $option );
}

// 3. Remove custom user role.
remove_role( 'shop_manager_custom' );
