<?php
/**
 * WordPress Admin Menu Registry
 *
 * @package KarasuWooPannel
 * @version 1.0.0
 * @date 2026-06-23
 */

namespace WooStoreManager\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Admin_Menu
 */
class WSM_Admin_Menu {

	/**
	 * Register submenu options page.
	 */
	public function add_admin_menu(): void {
		add_submenu_page(
			'woocommerce',
			__( 'تنظیمات KarasuWooPannel', 'karasu-woo-pannel' ),
			__( 'KarasuWooPannel', 'karasu-woo-pannel' ),
			'manage_options',
			'wsm_settings',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Render options page HTML form.
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wsm_settings_group' );
				do_settings_sections( 'wsm_settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
