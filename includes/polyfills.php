<?php
/**
 * Some polyfills
 *
 * @package     FestingerVault
 * @since       4.0.1.h6
 * @author      Festinger Vault (refactored by Hans Schuijff)
 * @link        https://festingervault.com
 * @license     GPLv2 or later
 */

 if ( ! function_exists( __NAMESPACE__ . '\str_contains' ) ) {
	/**
	 * Polyfill for `str_contains()` function added in PHP 8.0.
	 *
	 * Performs a case-sensitive check indicating if needle is
	 * contained in haystack.
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle   The substring to search for in the haystack.
	 * @return bool True if `$needle` is in `$haystack`, otherwise false.
	 */
	function str_contains( string $haystack, string $needle ): bool {
		return ( '' === $needle || false !== strpos( $haystack, $needle ) );
	}
}

if ( ! function_exists( __NAMESPACE__ . '\str_starts_with' ) ) {
	/**
	 * Polyfill for `str_starts_with()` function added in PHP 8.0.
	 *
	 * Performs a case-sensitive check indicating if
	 * the haystack begins with needle.
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle   The substring to search for in the `$haystack`.
	 * @return bool True if `$haystack` starts with `$needle`, otherwise false.
	 */
	function str_starts_with( string $haystack, string $needle ): bool {
		return ( '' === $needle || 0 === strpos( $haystack, $needle ) );
	}
}

if ( ! function_exists( __NAMESPACE__ . '\str_ends_with' ) ) {
	/**
	 * Polyfill for `str_ends_with()` function added in PHP 8.0.
	 *
	 * Performs a case-sensitive check indicating if
	 * the haystack ends with needle.
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle   The substring to search for in the `$haystack`.
	 * @return bool True if `$haystack` ends with `$needle`, otherwise false.
	 */
	function str_ends_with( $haystack, $needle ) {
		$len = strlen( $needle );
		if ( strlen( $haystack ) < $len ) {
			return false;
		}
		return 0 === substr_compare( haystack: $haystack, needle: $needle, offset: -$len, length: $len );
	}
}

if ( ! function_exists( 'str_remove_suffix' ) ) {
	function str_remove_suffix( $str, $suffix ) {
		if ( ! str_ends_with( haystack: $str, needle: $suffix ) ) {
			return $str;
		}
		return substr( string: $str, offset: 0, length: -strlen( $suffix ) );
	}
}

