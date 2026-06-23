<?php
/**
 * Plugin Name: KarasuWooPannel
 * Plugin URI:  https://github.com/abaharloo4/karasu-woo-pannel
 * Description: A completely independent, RTL, TailwindCSS-based store management panel for WooCommerce.
 * Version:     1.0.4
 * Author:      karasu
 * Author URI:  https://github.com/abaharloo4
 * Text Domain: karasu-woo-pannel
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 *
 * @package KarasuWooPannel
 * @version 1.0.4
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define core constants.
define( 'WSM_VERSION', '1.0.4' );
define( 'WSM_PLUGIN_FILE', __FILE__ );
define( 'WSM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WSM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WSM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Load the custom autoloader.
require_once WSM_PLUGIN_DIR . 'includes/Core/class-wsm-autoloader.php';

// Load global helper functions.
require_once WSM_PLUGIN_DIR . 'includes/Helpers/wsm-helpers.php';

/**
 * Register activation hook.
 */
register_activation_hook( __FILE__, [ '\WooStoreManager\Core\WSM_Activator', 'activate' ] );

/**
 * Register deactivation hook.
 */
register_deactivation_hook( __FILE__, [ '\WooStoreManager\Core\WSM_Deactivator', 'deactivate' ] );

/**
 * Bootstraps the plugin.
 */
function wsm_run(): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'wsm_woocommerce_missing_notice' );
		return;
	}
	$plugin = \WooStoreManager\Core\WSM_Plugin::get_instance();
	$plugin->run();
}
add_action( 'plugins_loaded', 'wsm_run' );

/**
 * Display admin notice if WooCommerce is missing.
 */
function wsm_woocommerce_missing_notice(): void {
	?>
	<div class="error notice">
		<p><?php esc_html_e( 'افزونه KarasuWooPannel غیرفعال است زیرا افزونه ووکامرس فعال نیست. لطفا ابتدا ووکامرس را فعال کنید.', 'karasu-woo-pannel' ); ?></p>
	</div>
	<?php
}
