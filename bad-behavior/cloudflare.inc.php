<?php
/**
 * Cloudflare functions.
 *
 * @package             BadBehavior
 * @author              Kevin Provance/SVL Studios
 * @license             GNU General Public License, version 3
 * @copyright           2024 SVL Studios
 */

defined( 'ABSPATH' ) || exit;

require_once BB2_CORE . '/roundtripdns.inc.php';

/**
 * Analyze requests claiming to be from CloudFlare.
 *
 * @param array $package Package.
 *
 * @return false
 */
function bb2_cloudflare( $package ) {
	// Disabled due to https://bugs.php.net/bug.php?id=53092
	// if (!bb2_roundtripdns($package['cloudflare'], "cloudflare.com")) {
	// return '70e45496';
	// }
	return false;
}
