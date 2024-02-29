<?php
/**
 * Core functions.
 *
 * @package             BadBehavior
 * @author              Kevin Provance/SVL Studios
 * @license             GNU General Public License, version 3
 * @copyright           2024 SVL Studios
 */

use JetBrains\PhpStorm\NoReturn;

defined( 'ABSPATH' ) || exit;

/**
 *
 */
const BB2_VERSION = '2.2.24';

// Bad Behavior entry point is bb2_start()
// If you're reading this, you are probably lost.
// Go read the bad-behavior-generic.php file.

/**
 *
 */
const BB2_CORE = __DIR__;

require_once BB2_CORE . '/functions.inc.php';

/**
 * Kill 'em all!
 *
 * @param array       $settings     Settings.
 * @param array       $package      Package.
 * @param string      $key          Key.
 * @param bool|string $previous_key Previous key.
 */
#[NoReturn] function bb2_banned( array $settings, array $package, string $key, bool|string $previous_key = false ): void {
	// Some spambots hit too hard. Slow them down a bit.
	sleep( 2 );

	require_once BB2_CORE . '/banned.inc.php';
	bb2_display_denial( $package, $key, $previous_key );
	bb2_log_denial( $settings, $package, $key );

	if ( is_callable( 'bb2_banned_callback' ) ) {
		bb2_banned_callback( $settings, $package, $key );
	}

	// Penalize the spammers some more.
	bb2_housekeeping( $settings );

	die();
}

/**
 * Approved.
 *
 * @param array $settings Settings.
 * @param array $package  Package.
 *
 * @return void
 */
function bb2_approved( array $settings, array $package ): void {
	// Dirk wanted this.
	if ( is_callable( 'bb2_approved_callback' ) ) {
		bb2_approved_callback( $package );
	}

	// Decide what to log on approved requests.
	if ( ( $settings['verbose'] && $settings['logging'] ) || empty( $package['user_agent'] ) ) {
		bb2_db_query( bb2_insert( $settings, $package, '00000000' ) );
	}
}

/**
 * If this is reverse-proxied or load balanced, obtain the actual client IP.
 *
 * @param array $settings      Settings.
 * @param array $headers_mixed Headers.
 *
 * @return false|mixed|string
 */
function bb2_reverse_proxy( array $settings, array $headers_mixed ): mixed {
	// Detect if option is on when it should be off.
	$header = uc_all( $settings['reverse_proxy_header'] );
	if ( ! array_key_exists( $header, $headers_mixed ) ) {
		return false;
	}

	$addrs = array_reverse( preg_split( '/[\s,]+/', $headers_mixed[ $header ] ) );

	// Skip our known reverse proxies and private addresses.
	if ( ! empty( $settings['reverse_proxy_addresses'] ) ) {
		foreach ( $addrs as $addr ) {
			if ( ! match_cidr( $addr, $settings['reverse_proxy_addresses'] ) && ! is_rfc1918( $addr ) ) {
				return $addr;
			}
		}
	} else {
		foreach ( $addrs as $addr ) {
			if ( ! is_rfc1918( $addr ) ) {
				return $addr;
			}
		}
	}

	// If we got here, someone is playing a trick on us.
	return false;
}

/**
 * Let God sort 'em out!
 *
 * @param array $settings Settings.
 *
 * @return bool|int|string
 */
function bb2_start( array $settings ): bool|int|string {
	// Gather all the information we need, first of all.
	$headers = bb2_load_headers();

	// Postprocess the headers to mixed-case
	// TODO: get the world to stop using PHP as CGI.
	$headers_mixed = array();
	foreach ( $headers as $h => $v ) {
		$headers_mixed[ uc_all( $h ) ] = $v;
	}

	// IPv6 - IPv4 compatibility mode hack.
	$_SERVER['REMOTE_ADDR'] = preg_replace( '/^::ffff:/', '', isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );

	// Reconstruct the HTTP entity, if present.
	$request_entity = array();
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && ( ! strcasecmp( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ), 'POST' ) || ! strcasecmp( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ), 'PUT' ) ) ) {
		foreach ( $_POST as $h => $v ) {
			if ( is_array( $v ) ) {
				// Workaround, see Bug #12.
				$v = 'Array';
			}
			$request_entity[ $h ] = $v;
		}
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	if ( ! $request_uri ) {
		$request_uri = isset( $_SERVER['SCRIPT_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) : '';
	}    // IIS.

	if ( $settings['reverse_proxy'] && $ip = bb2_reverse_proxy( $settings, $headers_mixed ) ) {
		$headers['X-Bad-Behavior-Remote-Address']       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$headers_mixed['X-Bad-Behavior-Remote-Address'] = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	} else {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	}

	$package = array(
		'ip'              => $ip,
		'headers'         => $headers,
		'headers_mixed'   => $headers_mixed,
		'request_method'  => isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '',
		'request_uri'     => $request_uri,
		'server_protocol' => isset( $_SERVER['SERVER_PROTOCOL'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ) ) : '',
		'request_entity'  => $request_entity,
		'user_agent'      => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		'is_browser'      => false,
	);

	$result = bb2_screen( $settings, $package );
	if ( $result && ! defined( 'BB2_TEST' ) ) {
		bb2_banned( $settings, $package, $result );
	}

	return $result;
}

/**
 * Screen.
 *
 * @param array $settings Settings.
 * @param array $package  Package.
 *
 * @return bool|int|string
 */
function bb2_screen( array $settings, array $package ): bool|int|string {
	// Please proceed to the security checkpoint, have your identification
	// and boarding pass ready, and prepare to be nakedized or fondled.

	// CloudFlare-specific checks not handled by reverse proxy code
	// Thanks to butchs at Simple Machines.
	if ( array_key_exists( 'Cf-Connecting-Ip', $package['headers_mixed'] ) ) {
		require_once BB2_CORE . '/cloudflare.inc.php';
		$r = bb2_cloudflare( $package );
		if ( false !== $r && $package['ip'] !== $r ) {
			return $r;
		}
	}

	// First check the whitelist.
	require_once BB2_CORE . '/whitelist.inc.php';
	if ( ! bb2_run_whitelist( $package ) ) {

		// Now check the blacklist.
		require_once BB2_CORE . '/blacklist.inc.php';

		if ( $r = bb2_blacklist( $package ) ) {
			return $r;
		}

		// Check the http:BL.
		require_once BB2_CORE . '/blackhole.inc.php';
		if ( $r = bb2_httpbl( $settings, $package ) ) {
			if ( 1 === $r ) {
				return false;
			}    // whitelisted

			return $r;
		}

		// Check for common stuff.
		require_once BB2_CORE . '/common_tests.inc.php';
		if ( $r = bb2_protocol( $settings, $package ) ) {
			return $r;
		}
		if ( $r = bb2_cookies( $package ) ) {
			return $r;
		}
		if ( $r = bb2_misc_headers( $settings, $package ) ) {
			return $r;
		}

		// Specific checks.
		$ua = $package['user_agent'];

		// Search engine checks come first.
		if ( stripos( $ua, 'bingbot' ) !== false || stripos( $ua, 'msnbot' ) !== false || stripos( $ua, 'MS Search' ) !== false ) {
			require_once BB2_CORE . '/searchengine.inc.php';
			if ( $r = bb2_msnbot( $package ) ) {
				if ( 1 === $r ) {
					return false;
				}    // whitelisted

				return $r;
			}

			return false;
		} elseif ( stripos( $ua, 'Googlebot' ) !== false || stripos( $ua, 'Mediapartners-Google' ) !== false || stripos( $ua, 'Google Web Preview' ) !== false ) {
			require_once BB2_CORE . '/searchengine.inc.php';
			if ( $r = bb2_google( $package ) ) {
				if ( 1 === $r ) {
					return false;
				}    // whitelisted

				return $r;
			}

			return false;
		} elseif ( stripos( $ua, 'Yahoo! Slurp' ) !== false || stripos( $ua, 'Yahoo! SearchMonkey' ) !== false ) {
			require_once BB2_CORE . '/searchengine.inc.php';
			if ( $r = bb2_yahoo( $package ) ) {
				if ( 1 === $r ) {
					return false;
				}    // whitelisted

				return $r;
			}

			return false;
		} elseif ( stripos( $ua, 'Baidu' ) !== false ) {
			require_once BB2_CORE . '/searchengine.inc.php';
			if ( $r = bb2_baidu( $package ) ) {
				if ( 1 === $r ) {
					return false;
				}    // whitelisted

				return $r;
			}

			return false;
		}

		// MSIE checks.
		if ( stripos( $ua, '; MSIE' ) !== false ) {
			$package['is_browser'] = true;
			require_once BB2_CORE . '/browser.inc.php';
			if ( stripos( $ua, 'Opera' ) !== false ) {
				if ( $r = bb2_opera( $package ) ) {
					return $r;
				}
			} elseif ( $r = bb2_msie( $package ) ) {
					return $r;
			}
		} elseif ( stripos( $ua, 'Konqueror' ) !== false ) {
			$package['is_browser'] = true;
			require_once BB2_CORE . '/browser.inc.php';
			if ( $r = bb2_konqueror( $package ) ) {
				return $r;
			}
		} elseif ( stripos( $ua, 'Opera' ) !== false ) {
			$package['is_browser'] = true;
			require_once BB2_CORE . '/browser.inc.php';
			if ( $r = bb2_opera( $package ) ) {
				return $r;
			}
		} elseif ( stripos( $ua, 'Safari' ) !== false ) {
			$package['is_browser'] = true;
			require_once BB2_CORE . '/browser.inc.php';
			if ( $r = bb2_safari( $package ) ) {
				return $r;
			}
		} elseif ( stripos( $ua, 'Lynx' ) !== false ) {
			$package['is_browser'] = true;
			require_once BB2_CORE . '/browser.inc.php';
			if ( $r = bb2_lynx( $package ) ) {
				return $r;
			}
		} elseif ( stripos( $ua, 'MovableType' ) !== false ) {
			require_once BB2_CORE . '/movabletype.inc.php';
			if ( $r = bb2_movabletype( $package ) ) {
				return $r;
			}
		} elseif ( stripos( $ua, 'Mozilla' ) !== false && stripos( $ua, 'Mozilla' ) == 0 ) {
			$package['is_browser'] = true;
			require_once BB2_CORE . '/browser.inc.php';
			if ( $r = bb2_mozilla( $package ) ) {
				return $r;
			}
		}

		// More intensive screening applies to POST requests.
		if ( ! strcasecmp( 'POST', $package['request_method'] ) ) {
			require_once BB2_CORE . '/post.inc.php';
			if ( $r = bb2_post( $settings, $package ) ) {
				return $r;
			}
		}
	}

	// And that's about it.
	bb2_approved( $settings, $package );

	return false;
}
