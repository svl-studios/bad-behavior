<?php
/**
 * MovableType functions.
 *
 * @package             BadBehavior
 * @author              Kevin Provance/SVL Studios
 * @license             GNU General Public License, version 3
 * @copyright           2024 SVL Studios
 */

defined( 'ABSPATH' ) || exit;

/**
 * MovableType.
 *
 * @param array $package Package.
 *
 * @return false|string
 */
function bb2_movabletype( array $package ): bool|string {
	// Is it a trackback?
	if ( strcasecmp( $package['request_method'], 'POST' ) ) {
		if ( strcmp( $package['headers_mixed']['Range'], 'bytes=0-99999' ) ) {
			return '7d12528e';
		}
	}
	return false;
}
