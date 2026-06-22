<?php
/**
 * Main Plugin Coordinator Class (Singleton)
 *
 * @package KarasuWooPannel
 * @version 1.0.0
 * @date 2026-06-23
 */

namespace WooStoreManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Plugin
 */
final class WSM_Plugin {

	/**
	 * Single instance of the class.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Hook registry loader.
	 *
	 * @var WSM_Loader
	 */
	private WSM_Loader $loader;

	/**
	 * Get class instance.
	 *
	 * @return self
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * WSM_Plugin constructor.
	 */
	private function __construct() {
		$this->loader = new WSM_Loader();
		$this->load_dependencies();
	}

	/**
	 * Instantiate active plugin components.
	 */
	private function load_dependencies(): void {
		// Initialization of routing, access control, and admin menus.
		$auth           = new \WooStoreManager\Auth\WSM_Auth();
		$roles          = new \WooStoreManager\Auth\WSM_Roles();
		$rewrite        = new \WooStoreManager\Router\WSM_Rewrite();
		$admin_menu     = new \WooStoreManager\Admin\WSM_Admin_Menu();
		$admin_settings = new \WooStoreManager\Admin\WSM_Admin_Settings();
		$auth_ctrl      = new \WooStoreManager\Api\WSM_Auth_Controller();
		$sms_ctrl       = new \WooStoreManager\Api\WSM_Sms_Controller();

		// Repositories & Services
		$order_repo      = new \WooStoreManager\Repositories\WSM_Order_Repository();
		$order_service   = new \WooStoreManager\Services\WSM_Order_Service( $order_repo );
		$orders_ctrl     = new \WooStoreManager\Api\WSM_Orders_Controller( $order_service );
		$product_repo    = new \WooStoreManager\Repositories\WSM_Product_Repository();
		$product_service = new \WooStoreManager\Services\WSM_Product_Service( $product_repo );
		$products_ctrl   = new \WooStoreManager\Api\WSM_Products_Controller( $product_service );

		$coupon_repo     = new \WooStoreManager\Repositories\WSM_Coupon_Repository();
		$coupon_service  = new \WooStoreManager\Services\WSM_Coupon_Service( $coupon_repo );
		$coupons_ctrl    = new \WooStoreManager\Api\WSM_Coupons_Controller( $coupon_service );
		$reports_ctrl    = new \WooStoreManager\Api\WSM_Reports_Controller();

		// SMS Hooks
		$sms_hooks = new \WooStoreManager\Core\WSM_Sms_Hooks();
		$sms_hooks->register();

		// Register hooks with the Loader.
		$this->loader->add_filter( 'determine_current_user', $auth, 'determine_current_user', 15 );
		$this->loader->add_action( 'admin_init', $roles, 'block_admin_access' );

		$this->loader->add_action( 'init', $rewrite, 'add_rewrite_rules' );
		$this->loader->add_filter( 'query_vars', $rewrite, 'add_query_vars' );
		$this->loader->add_action( 'template_redirect', $rewrite, 'handle_request' );

		$this->loader->add_action( 'admin_menu', $admin_menu, 'add_admin_menu' );
		$this->loader->add_action( 'admin_init', $admin_settings, 'register_settings' );
		$this->loader->add_action( 'rest_api_init', $auth_ctrl, 'register_routes' );
		$this->loader->add_action( 'rest_api_init', $orders_ctrl, 'register_routes' );
		$this->loader->add_action( 'rest_api_init', $products_ctrl, 'register_routes' );
		$this->loader->add_action( 'rest_api_init', $sms_ctrl, 'register_routes' );
		$this->loader->add_action( 'rest_api_init', $coupons_ctrl, 'register_routes' );
		$this->loader->add_action( 'rest_api_init', $reports_ctrl, 'register_routes' );

		// Purge report aggregation caches on checkout or order updates.
		$this->loader->add_action( 'woocommerce_new_order', $reports_ctrl, 'clean_reports_cache' );
		$this->loader->add_action( 'woocommerce_order_status_changed', $reports_ctrl, 'clean_reports_cache' );

		// Elementor Integration
		if ( did_action( 'elementor/loaded' ) ) {
			$elementor = new \WooStoreManager\Core\WSM_Elementor();
			$elementor->register();
		}

		// Initialize GitHub Automatic Updater
		$updater = new \WooStoreManager\Core\WSM_GitHub_Updater( WSM_PLUGIN_FILE );
		$updater->init();
	}

	/**
	 * Run the hook registry loader.
	 */
	public function run(): void {
		$this->loader->run();
	}
}
