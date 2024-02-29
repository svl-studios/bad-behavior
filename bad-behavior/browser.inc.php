<?php
/**
 * Browser functions.
 *
 * @package             BadBehavior
 * @author              Kevin Provance/SVL Studios
 * @license             GNU General Public License, version 3
 * @copyright           2024 SVL Studios
 */

defined( 'ABSPATH' ) || exit;

/**
 * Analyze user agents claiming to be Konqueror.
 *
 * @param array $package Package.
 *
 * @return false|string
 */
function bb2_konqueror( array $package ): bool|string { // phpcs:ignore Generic.PHP.Syntax.PHPSyntax
	// CafeKelsa is a dev project at Yahoo which indexes job listings for
	// Yahoo! HotJobs. It identifies as Konqueror so we skip these checks.
	if ( stripos( $package['headers_mixed']['User-Agent'], 'YahooSeeker/CafeKelsa' ) === false || match_cidr( $package['ip'], '209.73.160.0/19' ) === false ) {
		if ( ! array_key_exists( 'Accept', $package['headers_mixed'] ) ) {
			return '17566707';
		}
	}

	return false;
}

/**
 * Analyze user agents claiming to be Lynx.
 *
 * @param array $package Package.
 *
 * @return false|string
 */
function bb2_lynx( array $package ): bool|string {
	if ( ! array_key_exists( 'Accept', $package['headers_mixed'] ) ) {
		return '17566707';
	}

	return false;
}

/**
 * Analyze user agents claiming to be Mozilla.
 *
 * @param array $package Package.
 *
 * @return false|string
 */
function bb2_mozilla( array $package ): bool|string {
	// First off, workaround for Google Desktop, until they fix it FIXME
	// Google Desktop fixed it, but apparently some old versions are
	// still out there. :(
	// Always check accept header for Mozilla user agents.
	if ( ! str_contains( $package['headers_mixed']['User-Agent'], 'Google Desktop' ) && ! str_contains( $package['headers_mixed']['User-Agent'], 'PLAYSTATION 3' ) ) {
		if ( ! array_key_exists( 'Accept', $package['headers_mixed'] ) ) {
			return '17566707';
		}
	}

	return false;
}

/**
 * Analyze user agents claiming to be MSIE.
 *
 * @param array $package Package.
 *
 * @return false|string
 */
function bb2_msie( array $package ): bool|string {
	if ( ! array_key_exists( 'Accept', $package['headers_mixed'] ) ) {
		return '17566707';
	}

	// MSIE does NOT send "Windows ME" or "Windows XP" in the user agent.
	if ( str_contains( $package['headers_mixed']['User-Agent'], 'Windows ME' ) || str_contains( $package['headers_mixed']['User-Agent'], 'Windows XP' ) || str_contains( $package['headers_mixed']['User-Agent'], 'Windows 2000' ) || str_contains( $package['headers_mixed']['User-Agent'], 'Win32' ) ) {
		return 'a1084bad';
	}

	// MSIE does NOT send Connection: TE but Akamai does
	// Bypass this test when Akamai detected
	// The latest version of IE for Windows CE also uses Connection: TE.
	if ( ! array_key_exists( 'Akamai-Origin-Hop', $package['headers_mixed'] ) && ! str_contains( $package['headers_mixed']['User-Agent'], 'IEMobile' ) && preg_match( '/\bTE\b/i', $package['headers_mixed']['Connection'] ) ) {
		return '2b90f772';
	}

	return false;
}

/**
 * Analyze user agents claiming to be Opera.
 *
 * @param array $package Package.
 *
 * @return false|string
 */
function bb2_opera( array $package ): bool|string {
	if ( ! array_key_exists( 'Accept', $package['headers_mixed'] ) ) {
		return '17566707';
	}

	return false;
}

/**
 * Analyze user agents claiming to be Safari.
 *
 * @param array $package Package.
 *
 * @return false|string
 */
function bb2_safari( array $package ): bool|string {
	if ( ! array_key_exists( 'Accept', $package['headers_mixed'] ) ) {
		return '17566707';
	}

	return false;
}
