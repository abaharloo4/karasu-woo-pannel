<?php
/**
 * WooCommerce Hooks Listener for Outbound SMS Notifications
 *
 * @package KarasuWooPannel
 * @version 1.0.7
 * @date 2026-06-23
 */

namespace WooStoreManager\Core;

use WooStoreManager\Services\WSM_Sms_Service;
use WC_Order;
use WC_Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Sms_Hooks
 */
class WSM_Sms_Hooks {

	/**
	 * SMS Service instance.
	 *
	 * @var WSM_Sms_Service
	 */
	private WSM_Sms_Service $sms_service;

	/**
	 * WSM_Sms_Hooks constructor.
	 */
	public function __construct() {
		$this->sms_service = new WSM_Sms_Service();
	}

	/**
	 * Register actions with WooCommerce Hooks.
	 */
	public function register(): void {
		// Customer notification hook
		add_action( 'woocommerce_order_status_changed', [ $this, 'on_order_status_changed' ], 10, 4 );

		// Admin new order notification hook
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'on_new_order' ], 10, 3 );

		// Admin low stock notification hook
		add_action( 'woocommerce_low_stock', [ $this, 'on_low_stock' ], 10, 1 );
	}

	/**
	 * Triggered when order status changes.
	 *
	 * @param int      $order_id   Order ID.
	 * @param string   $old_status Old status slug.
	 * @param string   $new_status New status slug.
	 * @param WC_Order $order      Order instance.
	 */
	public function on_order_status_changed( int $order_id, string $old_status, string $new_status, WC_Order $order ): void {
		$templates = WSM_Sms_Service::get_templates();

		// 1. Customer notification
		if ( isset( $templates[ $new_status ] ) ) {
			$tpl = $templates[ $new_status ];
			if ( ! empty( $tpl['enabled'] ) && ! empty( $tpl['text'] ) ) {
				$phone = $order->get_billing_phone();
				if ( ! empty( $phone ) ) {
					$message = $this->sms_service->parse_variables( $tpl['text'], $order );
					$this->sms_service->send_sms( $phone, $message, $new_status, $order_id );
				}
			}
		}

		// 2. Admin notification
		$admin_key = 'admin_' . $new_status;
		if ( isset( $templates[ $admin_key ] ) ) {
			$tpl = $templates[ $admin_key ];
			if ( ! empty( $tpl['enabled'] ) && ! empty( $tpl['text'] ) ) {
				$admin_phone = get_option( 'wsm_admin_mobile' );
				if ( ! empty( $admin_phone ) ) {
					$message = $this->sms_service->parse_variables( $tpl['text'], $order );
					$this->sms_service->send_sms( $admin_phone, $message, $admin_key, $order_id );
				}
			}
		}
	}

	/**
	 * Triggered when a new checkout is processed.
	 *
	 * @param int      $order_id    Order ID.
	 * @param array    $posted_data Checkout inputs.
	 * @param WC_Order $order       Order instance.
	 */
	public function on_new_order( int $order_id, array $posted_data, WC_Order $order ): void {
		$templates = WSM_Sms_Service::get_templates();
		$tpl       = $templates['admin_new_order'] ?? $templates['new_order'] ?? null;

		if ( $tpl && ! empty( $tpl['enabled'] ) && ! empty( $tpl['text'] ) ) {
			$admin_phone = get_option( 'wsm_admin_mobile' );
			if ( ! empty( $admin_phone ) ) {
				$message = $this->sms_service->parse_variables( $tpl['text'], $order );
				$this->sms_service->send_sms( $admin_phone, $message, 'admin_new_order', $order_id );
			}
		}
	}

	/**
	 * Triggered when stock falls below low threshold.
	 *
	 * @param WC_Product $product WooCommerce product.
	 */
	public function on_low_stock( WC_Product $product ): void {
		$templates = WSM_Sms_Service::get_templates();
		$tpl       = $templates['admin_low_stock'] ?? $templates['low_stock'] ?? null;

		if ( $tpl && ! empty( $tpl['enabled'] ) && ! empty( $tpl['text'] ) ) {
			$admin_phone = get_option( 'wsm_admin_mobile' );
			if ( ! empty( $admin_phone ) ) {
				$message = $this->sms_service->parse_variables( $tpl['text'], $product );
				$this->sms_service->send_sms( $admin_phone, $message, 'admin_low_stock', $product->get_id() );
			}
		}
	}
}
