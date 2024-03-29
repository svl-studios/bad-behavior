<?php
/**
 * Database functions.
 *
 * @package             BadBehavior
 * @author              Kevin Provance/SVL Studios
 * @license             GNU General Public License, version 3
 * @copyright           2024 SVL Studios
 */

defined( 'ABSPATH' ) || exit;

/**
 * Our log table structure.
 *
 * @param string $name DB name.
 *
 * @return string
 */
function bb2_table_structure( string $name ): string {
	// It's not paranoia if they really are out to get you.
	$name_escaped = bb2_db_escape( $name );
	return "CREATE TABLE IF NOT EXISTS `$name_escaped` (
		`id` INT(11) NOT NULL auto_increment,
		`ip` TEXT NOT NULL,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`request_method` TEXT NOT NULL,
		`request_uri` TEXT NOT NULL,
		`server_protocol` TEXT NOT NULL,
		`http_headers` TEXT NOT NULL,
		`user_agent` TEXT NOT NULL,
		`request_entity` TEXT NOT NULL,
		`key` TEXT NOT NULL,
		INDEX (`ip`(15)),
		INDEX (`user_agent`(10)),
		PRIMARY KEY (`id`) );"; // TODO: INDEX might need tuning.
}

/**
 * Insert a new record.
 *
 * @param array  $settings Settings.
 * @param array  $package  Package.
 * @param string $key      Key.
 *
 * @return string
 */
function bb2_insert( array $settings, array $package, string $key ): string {
	if ( ! $settings['logging'] ) {
		return '';
	}

	$ip              = bb2_db_escape( $package['ip'] );
	$date            = bb2_db_date();
	$request_method  = bb2_db_escape( $package['request_method'] );
	$request_uri     = bb2_db_escape( $package['request_uri'] );
	$server_protocol = bb2_db_escape( $package['server_protocol'] );
	$user_agent      = bb2_db_escape( $package['user_agent'] );
	$headers         = "$request_method $request_uri $server_protocol\n";

	foreach ( $package['headers'] as $h => $v ) {
		$headers .= bb2_db_escape( "$h: $v\n" );
	}

	$request_entity = '';

	if ( ! strcasecmp( $request_method, 'POST' ) ) {
		foreach ( $package['request_entity'] as $h => $v ) {
			$request_entity .= bb2_db_escape( "$h: $v\n" );
		}
	}

	return 'INSERT INTO `' . bb2_db_escape( $settings['log_table'] ) . "`
		(`ip`, `date`, `request_method`, `request_uri`, `server_protocol`, `http_headers`, `user_agent`, `request_entity`, `key`) VALUES
		('$ip', '$date', '$request_method', '$request_uri', '$server_protocol', '$headers', '$user_agent', '$request_entity', '$key')";
}
