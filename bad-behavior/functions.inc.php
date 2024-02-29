<?php

/**
 * Functions.
 *
 * @package             BadBehavior
 * @author              Kevin Provance/SVL Studios
 * @license             GNU General Public License, version 3
 * @copyright           2024 SVL Studios
 */

defined( 'ABSPATH' ) || exit;

/**
 * Quick and dirty check for an IPv6 address.
 *
 * @param string $address IP Address.
 *
 * @return bool
 */
function is_ipv6( string $address ): bool {
	return (bool) strpos( $address, ':' );
}

/**
 * Convert a string to mixed-case on word boundaries.
 *
 * @param string $str String to UC.
 *
 * @return string
 */
function uc_all( string $str ): string {
	$temp = preg_split( '/(\W)/', str_replace( '_', '-', $str ), - 1, PREG_SPLIT_DELIM_CAPTURE );
	foreach ( $temp as $key => $word ) {
		$temp[ $key ] = ucfirst( strtolower( $word ) );
	}

	return join( '', $temp );
}

/**
 * Determine if an IP address resides in a CIDR netblock or netblocks.
 *
 * @param string       $addr Address.
 * @param array|string $cidr CIDR.
 *
 * @return bool
 */
function match_cidr( string $addr, array|string $cidr ): bool { // phpcs:ignore Generic.PHP.Syntax.PHPSyntax
	$output = false;

	if ( is_array( $cidr ) ) {
		foreach ( $cidr as $cidrlet ) {
			if ( match_cidr( $addr, $cidrlet ) ) {
				$output = true;
				break;
			}
		}
	} else {
		list( $ip, $mask ) = explode( '/', $cidr );

		if ( ! $mask ) {
			$mask = 32;
		}
		$mask   = pow( 2, 32 ) - pow( 2, ( 32 - $mask ) );
		$output = ( ( ip2long( $addr ) & $mask ) === ( ip2long( $ip ) & $mask ) );
	}

	return $output;
}

/**
 * Determine if RFC 1918 reserves an IP address.
 *
 * @param string $addr Address.
 *
 * @return bool
 */
function is_rfc1918( string $addr ): bool {
	return match_cidr( $addr, array( '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16' ) );
}

/**
 * Obtain all the HTTP headers.
 * NB: on PHP-CGI we have to fake it out a bit, since we can't get the REAL
 * headers. Run PHP as Apache 2.0 module if possible for best results.
 *
 * @return array|false|string
 */
function bb2_load_headers(): bool|array|string {
	if ( ! is_callable( 'getallheaders' ) ) {
		$headers = array();
		foreach ( $_SERVER as $h => $v ) {
			if ( preg_match( '/HTTP_(.+)/', $h, $hp ) ) {
				$headers[ str_replace( '_', '-', uc_all( $hp[1] ) ) ] = $v;
			}
		}
	} else {
		$headers = getallheaders();
	}

	return $headers;
}
