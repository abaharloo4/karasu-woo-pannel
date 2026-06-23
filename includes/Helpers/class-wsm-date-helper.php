<?php
/**
 * Jalali (Solar Hijri) Date Helper
 *
 * @package KarasuWooPannel
 * @version 1.0.8
 * @date 2026-06-23
 */

namespace WooStoreManager\Helpers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class WSM_Date_Helper
 */
class WSM_Date_Helper {

	/**
	 * Convert Gregorian date to Jalali.
	 *
	 * @param int $gy Gregorian Year.
	 * @param int $gm Gregorian Month.
	 * @param int $gd Gregorian Day.
	 * @return array [Jalali Year, Jalali Month, Jalali Day]
	 */
	public static function gregorian_to_jalali( int $gy, int $gm, int $gd ): array {
		$g_d_m = [ 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 335 ];
		$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
		$days  = 355666 + ( 365 * $gy ) + (int) ( ( $gy2 + 3 ) / 4 ) - (int) ( ( $gy2 + 99 ) / 100 ) + (int) ( ( $gy2 + 399 ) / 400 ) + $gd + $g_d_m[ $gm - 1 ];
		$jy    = -1595 + ( 33 * (int) ( $days / 12053 ) );
		$days %= 12053;
		$jy   += 4 * (int) ( $days / 1461 );
		$days %= 1461;

		if ( $days > 365 ) {
			$jy   += (int) ( ( $days - 1 ) / 365 );
			$days  = ( $days - 1 ) % 365;
		}

		if ( $days < 186 ) {
			$jm = 1 + (int) ( $days / 31 );
			$jd = 1 + ( $days % 31 );
		} else {
			$jm = 7 + (int) ( ( $days - 186 ) / 30 );
			$jd = 1 + ( ( $days - 186 ) % 30 );
		}

		return [ $jy, $jm, $jd ];
	}

	/**
	 * Convert Jalali date to Gregorian.
	 *
	 * @param int $jy Jalali Year.
	 * @param int $jm Jalali Month.
	 * @param int $jd Jalali Day.
	 * @return array [Gregorian Year, Gregorian Month, Gregorian Day]
	 */
	public static function jalali_to_gregorian( int $jy, int $jm, int $jd ): array {
		$jy   += 1595;
		$days  = -355668 + ( 365 * $jy ) + ( (int) ( $jy / 33 ) * 8 ) + (int) ( ( ( $jy % 33 ) + 3 ) / 4 ) + $jd;

		if ( $jm < 7 ) {
			$days += ( $jm - 1 ) * 31;
		} else {
			$days += ( ( $jm - 7 ) * 30 ) + 186;
		}

		$gy    = 400 * (int) ( $days / 146097 );
		$days %= 146097;

		if ( $days > 36524 ) {
			$days--;
			$gy   += 100 * (int) ( $days / 36524 );
			$days %= 36524;
			if ( $days >= 365 ) {
				$days++;
			}
		}

		$gy   += 4 * (int) ( $days / 1461 );
		$days %= 1461;

		if ( $days > 365 ) {
			$gy   += (int) ( ( $days - 1 ) / 365 );
			$days  = ( $days - 1 ) % 365;
		}

		$gd    = $days + 1;
		$sal_a = [ 0, 31, ( ( $gy % 4 === 0 && $gy % 100 !== 0 ) || ( $gy % 400 === 0 ) ) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];

		for ( $gm = 0; $gm < 13 && $gd > $sal_a[ $gm ]; $gm++ ) {
			$gd -= $sal_a[ $gm ];
		}

		return [ $gy, $gm, $gd ];
	}

	/**
	 * Format a standard Gregorian timestamp to a Jalali date string.
	 *
	 * @param string $gregorian_date_str Format: YYYY-MM-DD HH:MM:SS
	 * @return string Jalali date string: YYYY/MM/DD HH:MM:SS
	 */
	public static function to_jalali_string( string $gregorian_date_str ): string {
		if ( empty( $gregorian_date_str ) || '0000-00-00 00:00:00' === $gregorian_date_str ) {
			return '';
		}

		$parts      = explode( ' ', $gregorian_date_str );
		$date_parts = explode( '-', $parts[0] );
		if ( 3 !== count( $date_parts ) ) {
			return '';
		}

		$gy = (int) $date_parts[0];
		$gm = (int) $date_parts[1];
		$gd = (int) $date_parts[2];

		list( $jy, $jm, $jd ) = self::gregorian_to_jalali( $gy, $gm, $gd );

		$jy = str_pad( (string) $jy, 4, '0', STR_PAD_LEFT );
		$jm = str_pad( (string) $jm, 2, '0', STR_PAD_LEFT );
		$jd = str_pad( (string) $jd, 2, '0', STR_PAD_LEFT );

		$jalali_date = "{$jy}/{$jm}/{$jd}";
		if ( isset( $parts[1] ) ) {
			$jalali_date .= ' ' . $parts[1];
		}

		return $jalali_date;
	}
}
