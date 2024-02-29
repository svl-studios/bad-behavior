<?php
/**
 * Whitelist functions.
 *
 * @package             BadBehavior
 * @author              Kevin Provance/SVL Studios
 * @license             GNU General Public License, version 3
 * @copyright           2024 SVL Studios
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whitelist.
 *
 * @param array $package Package.
 *
 * @return bool
 */
function bb2_run_whitelist( array $package ): bool {
	// FIXME: Transitional, until port maintainters implement bb2_read_whitelist.
	if ( function_exists( 'bb2_read_whitelist' ) ) {
		$whitelists = bb2_read_whitelist();
	} else {
		$whitelists = parse_ini_file( dirname( BB2_CORE ) . '/whitelist.ini' );
	}

	if ( ! empty( $whitelists['ip'] ) ) {
		foreach ( array_filter( $whitelists['ip'] ) as $range ) {
			if ( match_cidr( $package['ip'], $range ) ) {
				return true;
			}
		}
	}
	if ( ! empty( $whitelists['useragent'] ) ) {
		foreach ( array_filter( $whitelists['useragent'] ) as $user_agent ) {
			if ( ! strcmp( $package['headers_mixed']['User-Agent'], $user_agent ) ) {
				return true;
			}
		}
	}
	if ( ! empty( $whitelists['url'] ) ) {
		if ( ! str_contains( $package['request_uri'], '?' ) ) {
			$request_uri = $package['request_uri'];
		} else {
			$request_uri = substr( $package['request_uri'], 0, strpos( $package['request_uri'], '?' ) );
		}
		foreach ( array_filter( $whitelists['url'] ) as $url ) {
			$pos = strpos( $request_uri, $url );
			if ( false !== $pos && 0 === $pos ) {
				return true;
			}
		}
	}

	return false;
}
