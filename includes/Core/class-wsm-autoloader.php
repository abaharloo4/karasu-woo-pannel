<?php
/**
 * Custom Autoloader for KarasuWooPannel
 *
 * @package KarasuWooPannel
 * @version 1.0.10
 * @date 2026-06-23
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WSM_Autoloader' ) ) {
	/**
	 * Class WSM_Autoloader
	 */
	class WSM_Autoloader {

		/**
		 * Register autoloader.
		 */
		public static function register(): void {
			spl_autoload_register( [ self::class, 'autoload' ] );
		}

		/**
		 * Autoload class files.
		 *
		 * @param string $class Class name.
		 */
		public static function autoload( string $class ): void {
			$prefix = 'WooStoreManager\\';
			$len    = strlen( $prefix );

			if ( 0 !== strncmp( $prefix, $class, $len ) ) {
				return;
			}

			$relative_class = substr( $class, $len );
			$parts          = explode( '\\', $relative_class );

			$file      = array_pop( $parts );
			$file_name = 'class-' . strtolower( str_replace( '_', '-', $file ) ) . '.php';

			$dir_path = '';
			if ( ! empty( $parts ) ) {
				$dir_path = implode( DIRECTORY_SEPARATOR, $parts ) . DIRECTORY_SEPARATOR;
			}

			// WSM_PLUGIN_DIR constant must be defined in the main plugin file.
			$full_path = WSM_PLUGIN_DIR . 'includes' . DIRECTORY_SEPARATOR . $dir_path . $file_name;

			if ( file_exists( $full_path ) ) {
				require_once $full_path;
			}
		}
	}
}
// Register immediately on include.
WSM_Autoloader::register();
