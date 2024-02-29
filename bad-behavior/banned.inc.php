<?php
/**
 * Banned functions.
 *
 * @package             BadBehavior
 * @author              Kevin Provance/SVL Studios
 * @license             GNU General Public License, version 3
 * @copyright           2024 SVL Studios
 */

defined( 'ABSPATH' ) || exit;

/**
 * Functions called when a request has been denied
 * This part can be gawd-awful slow, doesn't matter :)
 */

require_once BB2_CORE . '/responses.inc.php';

/**
 * Housekeeping.
 *
 * @param array $settings Settings.
 *
 * @return void
 */
function bb2_housekeeping( array $settings ): void {
	if ( ! $settings['logging'] ) {
		return;
	}

	// FIXME Yes, the interval's hard coded (again) for now.
	$query = 'DELETE FROM `' . $settings['log_table'] . "` WHERE `date` < DATE_SUB('" . bb2_db_date() . "', INTERVAL 7 DAY)";
	bb2_db_query( $query );

	// Waste a bunch more of the spammer's time, sometimes.
	if ( wp_rand( 1, 1000 ) === 1 ) {
		$query = 'OPTIMIZE TABLE `' . $settings['log_table'] . '`';
		bb2_db_query( $query );
	}
}

/**
 * Display denial.
 *
 * @param array       $package      Package.
 * @param string      $key          Key.
 * @param bool|string $previous_key Previous key.
 *
 * @return void
 */
function bb2_display_denial( array $package, string $key, bool|string $previous_key = false ): void { // phpcs:ignore Generic.PHP.Syntax.PHPSyntax
	define( 'DONOTCACHEPAGE', true ); // WP Super Cache.

	if ( ! $previous_key ) {
		$previous_key = $key;
	}

	if ( 'e87553e1' === $key ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
		// FIXME: lookup the real key.
	}

	// Create a support key.
	$ip     = explode( '.', $package['ip'] );
	$ip_hex = '';

	foreach ( $ip as $octet ) {
		$ip_hex .= str_pad( dechex( $octet ), 2, 0, STR_PAD_LEFT );
	}

	$support_key = implode( '-', str_split( "$ip_hex$key", 4 ) );

	// Get response data.
	$response = bb2_get_response( $previous_key );

	header( 'HTTP/1.1 ' . $response['response'] . ' Bad Behavior' );
	header( 'Status: ' . $response['response'] . ' Bad Behavior' );

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

	if ( ! $request_uri ) {
		$request_uri = isset( $_SERVER['SCRIPT_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) : '';
	}    // IIS.

	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<!--< html xmlns="http://www.w3.org/1999/xhtml">-->
	<head>
		<title>HTTP Error <?php echo esc_html( $response['response'] ); ?></title>
	</head>
	<body>
		<h1>Error <?php echo esc_html( $response['response'] ); ?></h1>
		<p>We're sorry: we could not fulfill your request for
		<?php echo esc_html( htmlspecialchars( $request_uri ) ); ?> on this server.</p>
		<p><?php echo esc_html( $response['explanation'] ); ?></p>
		<p>Your technical support key is: <strong><?php echo esc_html( $support_key ); ?></strong></p>
		<p>You can use this key to <a href="https://www.ioerror.us/bb2-support-key?key=<?php echo esc_html( $support_key ); ?>">fix this problem yourself</a>.</p>
		<p>If you are unable to fix the problem yourself, please contact <a
		href="mailto:<?php echo esc_attr( htmlspecialchars( str_replace( '@', '+nospam@nospam.', bb2_email() ) ) ); ?>"><?php echo esc_html( htmlspecialchars( str_replace( '@', ' at ', bb2_email() ) ) ); ?></a>
		and be sure to provide the technical support key shown above.</p>
	<?php
}

/**
 * Log denial.
 *
 * @param array  $settings     Settings.
 * @param array  $package      Package.
 * @param string $key          Key.
 *
 * @return void
 */
function bb2_log_denial( array $settings, array $package, string $key ): void {
	if ( ! $settings['logging'] ) {
		return;
	}

	bb2_db_query( bb2_insert( $settings, $package, $key ) );
}
