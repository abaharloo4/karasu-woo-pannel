<?php
/**
 * GitHub Plugin Automatic Updater
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
			$request_args = [
				'headers'   => [
					'User-Agent' => 'KarasuWooPannel-Updater',
				],
				'timeout'   => 15,
				'sslverify' => false,
			];

			$url      = sprintf( 'https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repo );
			$response = wp_remote_get( $url, $request_args );

			$api_success = false;

			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$body    = wp_remote_retrieve_body( $response );
				$release = json_decode( $body, true );
				if ( is_array( $release ) ) {
					$api_success = true;
				}
			}

			// Fallback to releases.atom feed if API rate-limited or failed
			if ( ! $api_success ) {
				$feed_url = sprintf( 'https://github.com/%s/%s/releases.atom', $this->username, $this->repo );
				$feed_res = wp_remote_get( $feed_url, $request_args );

				if ( ! is_wp_error( $feed_res ) && 200 === wp_remote_retrieve_response_code( $feed_res ) ) {
					$xml_content = wp_remote_retrieve_body( $feed_res );
					if ( preg_match( '/<entry>.*?<title>(.*?)<\/title>/s', $xml_content, $matches ) ) {
						$tag_name = trim( $matches[1] );
						$release = [
							'tag_name' => $tag_name,
							'body'     => 'به‌روزرسانی جدید نسخه ' . $tag_name,
						];
						$api_success = true;
					}
				}
			}

			if ( ! $api_success ) {
				// Cache failure for 30 minutes to prevent rapid repeated API requests on errors.
				set_transient( $cache_key, 'failed', 30 * MINUTE_IN_SECONDS );
				return null;
			}

			// Always use direct GitHub archive ZIP URL (no API auth required, no rate limit).
			$tag = $release['tag_name'];
			$release['zip_url'] = sprintf(
				'https://github.com/%s/%s/archive/refs/tags/%s.zip',
				$this->username,
				$this->repo,
				$tag
			);

			// Cache success for 6 hours.
			set_transient( $cache_key, $release, 6 * HOUR_IN_SECONDS );
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
		if ( ! is_object( $transient ) ) {
			return $transient;
		}

		if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
			$transient->response = [];
		}
		if ( ! isset( $transient->no_update ) || ! is_array( $transient->no_update ) ) {
			$transient->no_update = [];
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
			
			// Use direct GitHub archive ZIP URL (no API auth, no rate limit).
			$obj->package     = $release['zip_url'] ?? '';
			$obj->icons       = [];
			$obj->banners     = [];
			$obj->tested      = '';
			$obj->requires_php = '8.0';

			$transient->response[ $this->plugin ] = $obj;

			// Remove from no_update to ensure WordPress shows the update notice.
			unset( $transient->no_update[ $this->plugin ] );
		} else {
			// Plugin is up to date — register in no_update and remove from response.
			$obj              = new \stdClass();
			$obj->slug        = $this->slug;
			$obj->plugin      = $this->plugin;
			$obj->new_version = $local_version;
			$obj->url         = sprintf( 'https://github.com/%s/%s', $this->username, $this->repo );
			$obj->package     = '';

			$transient->no_update[ $this->plugin ] = $obj;
			unset( $transient->response[ $this->plugin ] );
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
		$res->download_link = $release['zip_url'] ?? '';
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
