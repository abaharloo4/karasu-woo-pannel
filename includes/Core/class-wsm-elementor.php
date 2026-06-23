<?php
/**
 * Elementor Widgets Bootstrapper and Category Ordering
 *
 * @package KarasuWooPannel
 * @version 1.0.7
 * @date 2026-06-23
 */

namespace WooStoreManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Elementor
 */
class WSM_Elementor {

	/**
	 * Register actions.
	 */
	public function register(): void {
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
	}

	/**
	 * Register custom widgets category and place it exactly below woocommerce.
	 *
	 * @param object $elements_manager Elementor elements manager.
	 */
	public function register_category( $elements_manager ): void {
		$elements_manager->add_category(
			'karasu-woo-pannel',
			[
				'title' => esc_html__( 'پنل فروشگاه کاراسو', 'karasu-woo-pannel' ),
				'icon'  => 'fa fa-shopping-bag',
			]
		);

		try {
			$ref = new \ReflectionClass( $elements_manager );
			$prop = $ref->getProperty( 'categories' );
			$prop->setAccessible( true );
			$categories = $prop->getValue( $elements_manager );

			if ( is_array( $categories ) && isset( $categories['karasu-woo-pannel'] ) && isset( $categories['woocommerce'] ) ) {
				$new_categories = [];
				foreach ( $categories as $key => $val ) {
					if ( 'karasu-woo-pannel' === $key ) {
						continue;
					}
					$new_categories[ $key ] = $val;
					if ( 'woocommerce' === $key ) {
						$new_categories['karasu-woo-pannel'] = $categories['karasu-woo-pannel'];
					}
				}
				$prop->setValue( $elements_manager, $new_categories );
			}
		} catch ( \Exception $e ) {
			// Fail silently if Elementor internals change.
		}
	}

	/**
	 * Register login button widget.
	 *
	 * @param object $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ): void {
		require_once WSM_PLUGIN_DIR . 'includes/Core/class-wsm-login-widget.php';
		$widgets_manager->register( new WSM_Login_Widget() );
	}
}
