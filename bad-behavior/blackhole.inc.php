<?php
/**
 * Blackhole functions.
 *
 * @package             BadBehavior
 * @author              Kevin Provance/SVL Studios
 * @license             GNU General Public License, version 3
 * @copyright           2024 SVL Studios
 */

defined( 'ABSPATH' ) || exit;

/**
 * Compare on HTTP:BL.
 *
 * @param array $settings Settings.
 * @param array $package  Package.
 *
 * @return false|int|string
 */
function bb2_httpbl( array $settings, array $package ): bool|int|string { // phpcs:ignore Generic.PHP.Syntax.PHPSyntax
	// Can't use IPv6 addresses yet.
	if ( is_ipv6( $package['ip'] ) ) {
		return false;
	}

	if ( ! $settings['httpbl_key'] ) {
		return false;
	}

	// Workaround for "MySQL server has gone away".
	bb2_db_query( 'SET @@session.wait_timeout = 90' );

	$find   = implode( '.', array_reverse( explode( '.', $package['ip'] ) ) );
	$result = gethostbynamel( $settings['httpbl_key'] . ".$find.dnsbl.httpbl.org." );
	if ( ! empty( $result ) ) {
		$ip = explode( '.', $result[0] );
		if ( '127' === $ip[0] && ( $ip[3] & 7 ) && $ip[2] >= $settings['httpbl_threat'] && $ip[1] <= $settings['httpbl_maxage'] ) {
			return '2b021b1f';
		}
		// Check if search engine.
		if ( '0' === $ip[3] ) {
			return 1;
		}
	}
	return false;
}
