<?php
/**
 * GitHub Plugin Automatic Updater
 *
 * @package KarasuWooPannel
 * @version 1.0.4
 * @date 2026-06-23
 */

namespace WooStoreManager\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_GitHub_Updater
 */
class WSM_GitHub_Updater {

	/**
	 * Path to the main plugin file.
	 *
	 * @var string
	 */
	private string $file;

	/**
	 * Plugin basename.
	 *
	 * @var string
	 */
	private string $plugin;

	/**
	 * Plugin slug (directory name).
	 *
	 * @var string
	 */
	private string $slug;

	/**
	 * GitHub username.
	 *
	 * @var string
	 */
	private string $username;

	/**
	 * GitHub repository name.
	 *
	 * @var string
	 */
	private string $repo;

	/**
	 * WSM_GitHub_Updater constructor.
	 *
	 * @param string $file Main plugin file path.
	 */
	public function __construct( string $file ) {
		$this->file     = $file;
		$this->plugin   = plugin_basename( $file );
		$this->slug     = dirname( $this->plugin );
		$this->username = 'abaharloo4';
		$this->repo     = 'karasu-woo-pannel';
	}

	/**
	 * Register actions and filters.
	 */
	public function init(): void {
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_update' ] );
		add_filter( 'site_transient_update_plugins', [ $this, 'check_update' ] );
		add_filter( 'plugins_api', [ $this, 'plugin_popup' ], 20, 3 );
		add_filter( 'upgrader_source_selection', [ $this, 'rename_source' ], 10, 4 );
		add_action( 'delete_site_transient_update_plugins', [ $this, 'delete_transient' ] );
	}

	/**
	 * Fetch the latest release information from GitHub.
	 *
	 * @return array|null Release data array, or null on failure.
	 */
	private function get_latest_release(): ?array {
		$cache_key = 'wsm_github_release_info';
		
		// If force-check is requested in URL, bypass cache.
		if ( isset( $_GET['force-check'] ) ) {
			delete_transient( $cache_key );
			$release = false;
		} else {
			$release = get_transient( $cache_key );
		}

		if ( 'failed' === $release ) {
			return null;
		}

		if ( false === $release ) {
			$url      = sprintf( 'https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repo );
			$response = wp_remote_get(
				$url,
				[
					'headers' => [
						'User-Agent' => 'KarasuWooPannel-Updater',
					],
					'timeout' => 10,
				]
			);

			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				// Cache failure for 1 hour to prevent rapid repeated API requests on errors.
				set_transient( $cache_key, 'failed', HOUR_IN_SECONDS );
				return null;
			}

			$body    = wp_remote_retrieve_body( $response );
			$release = json_decode( $body, true );

			if ( ! is_array( $release ) ) {
				set_transient( $cache_key, 'failed', HOUR_IN_SECONDS );
				return null;
			}

			// Cache success for 12 hours.
			set_transient( $cache_key, $release, 12 * HOUR_IN_SECONDS );
		}

		return $release;
	}

	/**
	 * Hook into updates transient check and inject our update if available.
	 *
	 * @param object|mixed $transient WordPress transient object.
	 * @return object|mixed Updated transient object.
	 */
	public function check_update( $transient ) {
		if ( ! is_object( $transient ) || empty( $transient->checked ) ) {
			return $transient;
		}

		if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
			$transient->response = [];
		}

		$release = $this->get_latest_release();
		if ( empty( $release ) || empty( $release['tag_name'] ) ) {
			return $transient;
		}

		// Normalize version tags (remove leading 'v' if present).
		$remote_version = ltrim( $release['tag_name'], 'v' );
		$local_version  = WSM_VERSION;

		if ( version_compare( $remote_version, $local_version, '>' ) ) {
			$obj              = new \stdClass();
			$obj->slug        = $this->slug;
			$obj->plugin      = $this->plugin;
			$obj->new_version = $remote_version;
			$obj->url         = sprintf( 'https://github.com/%s/%s', $this->username, $this->repo );
			
			// Use zipball_url for downloading package.
			$obj->package     = $release['zipball_url'] ?? '';

			$transient->response[ $this->plugin ] = $obj;
		}

		return $transient;
	}

	/**
	 * Hook into plugins details popup and override metadata.
	 *
	 * @param object|bool $res    Default response object.
	 * @param string      $action Action performed.
	 * @param object      $args   Query arguments.
	 * @return object|bool Response object.
	 */
	public function plugin_popup( $res, string $action, $args ) {
		if ( 'plugin_information' !== $action || $this->slug !== ( $args->slug ?? '' ) ) {
			return $res;
		}

		$release = $this->get_latest_release();
		if ( empty( $release ) ) {
			return $res;
		}

		$remote_version = ltrim( $release['tag_name'], 'v' );
		$changelog      = ! empty( $release['body'] ) ? wp_kses_post( nl2br( $release['body'] ) ) : __( 'هیچ جزییاتی ارائه نشده است.', 'karasu-woo-pannel' );

		$res               = new \stdClass();
		$res->name         = 'KarasuWooPannel';
		$res->slug         = $this->slug;
		$res->version      = $remote_version;
		$res->author       = sprintf( '<a href="https://github.com/%s" target="_blank">%s</a>', $this->username, $this->username );
		$res->homepage     = sprintf( 'https://github.com/%s/%s', $this->username, $this->repo );
		$res->download_link = $release['zipball_url'] ?? '';
		$res->sections     = [
			'description' => esc_html__( 'یک پنل مدیریت فروشگاه کاملاً مستقل، راست‌چین و مدرن مبتنی بر TailwindCSS برای ووکامرس.', 'karasu-woo-pannel' ),
			'changelog'   => $changelog,
		];

		return $res;
	}

	/**
	 * Rename the source folder to the clean slug during installation selection.
	 *
	 * @param string      $source        File source location.
	 * @param string      $remote_source Remote source location.
	 * @param \WP_Upgrader $upgrader      WP_Upgrader instance.
	 * @param array|null  $hook_extra    Extra arguments.
	 * @return string New source location.
	 */
	public function rename_source( string $source, string $remote_source, $upgrader, ?array $hook_extra = null ): string {
		global $wp_filesystem;

		if ( isset( $hook_extra['plugin'] ) && $hook_extra['plugin'] === $this->plugin ) {
			$new_source = trailingslashit( $remote_source ) . $this->slug;
			if ( $source !== $new_source ) {
				if ( $wp_filesystem->move( $source, $new_source, true ) ) {
					return $new_source;
				}
			}
		}

		return $source;
	}

	/**
	 * Delete custom cache transient when WordPress clears its plugins update cache.
	 */
	public function delete_transient(): void {
		delete_transient( 'wsm_github_release_info' );
	}
}
