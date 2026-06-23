<?php
/**
 * Hook and Filter Registry Loader
 *
 * @package KarasuWooPannel
 * @version 1.0.9
 * @date 2026-06-23
 */

namespace WooStoreManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Loader
 */
class WSM_Loader {

	/**
	 * Array of actions registered with WordPress.
	 *
	 * @var array
	 */
	protected array $actions = [];

	/**
	 * Array of filters registered with WordPress.
	 *
	 * @var array
	 */
	protected array $filters = [];

	/**
	 * Register an action hook.
	 *
	 * @param string $hook          The name of the WordPress action.
	 * @param object $component     Reference to the class object containing the callback.
	 * @param string $callback      The callback method name.
	 * @param int    $priority      Optional. Priority. Default 10.
	 * @param int    $accepted_args Optional. Number of accepted arguments. Default 1.
	 */
	public function add_action( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Register a filter hook.
	 *
	 * @param string $hook          The name of the WordPress filter.
	 * @param object $component     Reference to the class object containing the callback.
	 * @param string $callback      The callback method name.
	 * @param int    $priority      Optional. Priority. Default 10.
	 * @param int    $accepted_args Optional. Number of accepted arguments. Default 1.
	 */
	public function add_filter( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Helper method to append hook configuration.
	 *
	 * @param array  $hooks         Registry array.
	 * @param string $hook          Hook name.
	 * @param object $component     Component object.
	 * @param string $callback      Callback method.
	 * @param int    $priority      Priority.
	 * @param int    $accepted_args Number of accepted arguments.
	 * @return array Updated hooks registry.
	 */
	private function add( array $hooks, string $hook, object $component, string $callback, int $priority, int $accepted_args ): array {
		$hooks[] = [
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		];
		return $hooks;
	}

	/**
	 * Register hooks with WordPress.
	 */
	public function run(): void {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['accepted_args'] );
		}
	}
}
