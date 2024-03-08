<?php
/**
 * Admin UI.
 *
 *  @package BadBehavior
 *  @author  Kevin Provance/SVL Studios
 *  @license GNU General Public License, version 3
 *  @copyright 2024 SVL Studios
 */

defined( 'ABSPATH' ) || exit;

require_once 'bad-behavior/responses.inc.php';

/**
 * Create admin pages.
 *
 * @return void
 */
function bb2_admin_pages(): void {
	if ( current_user_can( 'manage_options' ) ) {
		add_options_page( __( 'Bad Behavior' ), __( 'Bad Behavior' ), 'manage_options', 'bb2_options', 'bb2_options' );
		add_options_page( __( 'Bad Behavior Whitelist' ), __( 'Bad Behavior Whitelist' ), 'manage_options', 'bb2_whitelist', 'bb2_whitelist' );
		add_management_page( __( 'Bad Behavior Log' ), __( 'Bad Behavior Log' ), 'manage_options', 'bb2_manage', 'bb2_manage' );
	}
}

/**
 * HTTPBL Lookup.
 *
 * @param string $ip IP address.
 *
 * @return false|string
 */
function bb2_httpbl_lookup( string $ip ): bool|string { // phpcs:ignore Generic.PHP.Syntax.PHPSyntax
	session_start();

	// NB: Many of these are defunct.
	$engines = array(
		1  => 'AltaVista',
		2  => 'Teoma/Ask Crawler',
		3  => 'Baidu Spide',
		4  => 'Excite',
		5  => 'Googlebot',
		6  => 'Looksmart',
		7  => 'Lycos',
		8  => 'msnbot',
		9  => 'Yahoo! Slurp',
		10 => 'Twiceler',
		11 => 'Infoseek',
		12 => 'Minor Search Engine',
	);

	$settings   = bb2_read_settings();
	$httpbl_key = $settings['httpbl_key'];

	if ( ! $httpbl_key ) {
		return false;
	}

	$r = isset( $_SESSION['httpbl'][ $ip ] ) ? sanitize_text_field( wp_unslash( $_SESSION['httpbl'][ $ip ] ) ) : '';
	$d = '';

	if ( '' !== $r ) {  // Lookup.
		$find   = implode( '.', array_reverse( explode( '.', $ip ) ) );
		$result = gethostbynamel( "$httpbl_key.$find.dnsbl.httpbl.org." );
		if ( ! empty( $result ) ) {
			$r                         = $result[0];
			$_SESSION['httpbl'][ $ip ] = $r;
		}
	}
	if ( $r ) {   // Interpret.
		$ip = explode( '.', $r );
		if ( '127' === $ip[0] ) {
			if ( '0' === $ip[3] ) {
				if ( $engines[ $ip[2] ] ) {
					$d .= $engines[ $ip[2] ];
				} else {
					$d .= "Search engine $ip[2]<br/>\n";
				}
			}
			if ( $ip[3] & 1 ) {
				$d .= "Suspicious<br/>\n";
			}
			if ( $ip[3] & 2 ) {
				$d .= "Harvester<br/>\n";
			}
			if ( $ip[3] & 4 ) {
				$d .= "Comment Spammer<br/>\n";
			}
			if ( $ip[3] & 7 ) {
				$d .= "Threat level $ip[2]<br/>\n";
			}
			if ( $ip[3] > 0 ) {
				$d .= "Age $ip[1] days<br/>\n";
			}
		}
	}
	return $d;
}

/**
 * Add donate button.
 *
 * @param string $thispage Page URL.
 *
 * @return string
 */
function bb2_donate_button( string $thispage ): string {
	return '
		<div style="float: right; clear: right; width: 200px; border: 1px solid #e6db55; color: #333; background-color: lightYellow; padding: 0 10px">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<p>Bad Behavior is an important tool in the fight against web spam. Show your support by donating<br/>
			<select name="amount">
				<option value="2.99">$2.99 USD</option>
				<option value="4.99">$4.99 USD</option>
				<option value="9.99">$9.99 USD</option>
				<option value="19.99">$19.99 USD</option>
				<option value="">Other...</option>
			</select><br/>
			<input type="hidden" name="cmd" value="_donations">
			<input type="hidden" name="business" value="EAZGZZV7RE4QJ">
			<input type="hidden" name="lc" value="US">
			<input type="hidden" name="item_name" value="Bad Behavior ' . BB2_VERSION . ' (WordPress)">
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="no_note" value="0">
			<input type="hidden" name="cn" value="Comments about Bad Behavior">
			<input type="hidden" name="no_shipping" value="1">
			<input type="hidden" name="rm" value="1">
			<input type="hidden" name="return" value="' . $thispage . '">
			<input type="hidden" name="cancel_return" value="' . $thispage . '">
			<input type="hidden" name="currency_code" value="USD">
			<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_LG.gif:NonHosted">
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" style="border:none;" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" style="border:none;" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</p>
		</form>
	</div>';
}

/**
 * Manage settings.
 *
 * @return void
 */
function bb2_manage(): void {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

	if ( '' !== $request_uri ) {
		$request_uri = isset( $_SERVER['SCRIPT_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_NAME'] ) ) : ''; // IIS.
	}

	$settings      = bb2_read_settings();
	$rows_per_page = 100;
	$where         = '';

	// Get query variables desired by the user with input validation.
	$paged      = 0 + isset( $_GET['paged'] ) ? sanitize_text_field( wp_unslash( $_GET['paged'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$key        = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$blocked    = isset( $_GET['blocked'] ) ? sanitize_text_field( wp_unslash( $_GET['blocked'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$permitted  = isset( $_GET['permitted'] ) ? sanitize_text_field( wp_unslash( $_GET['permitted'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$ip         = isset( $_GET['ip'] ) ? sanitize_text_field( wp_unslash( $_GET['ip'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$user_agent = isset( $_GET['user_agent'] ) ? sanitize_text_field( wp_unslash( $_GET['user_agent'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$request    = isset( $_GET['request_method'] ) ? sanitize_text_field( wp_unslash( $_GET['request_method'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( ! $paged ) {
		$paged = 1;
	}

	if ( '' !== $key ) {
		$where .= "AND `key` = '" . esc_sql( $key ) . "' ";
	}

	if ( '' !== $blocked ) {
		$where .= "AND `key` != '00000000' ";
	} elseif ( '' !== $permitted ) {
		$where .= "AND `key` = '00000000' ";
	}

	if ( '' !== $ip ) {
		$where .= "AND `ip` = '" . esc_sql( $ip ) . "' ";
	}

	if ( '' !== $user_agent ) {
		$where .= "AND `user_agent` = '" . esc_sql( $user_agent ) . "' ";
	}

	if ( '' !== $request ) {
		$where .= "AND `request_method` = '" . esc_sql( $request ) . "' ";
	}

	// Query the DB based on variables selected.
	$r          = bb2_db_query( 'SELECT COUNT(id) FROM `' . $settings['log_table'] );
	$results    = bb2_db_rows( $r );
	$totalcount = $results[0]['COUNT(id)'];
	$r          = bb2_db_query( 'SELECT COUNT(id) FROM `' . $settings['log_table'] . '` WHERE 1=1 ' . $where );
	$results    = bb2_db_rows( $r );
	$count      = $results[0]['COUNT(id)'];
	$pages      = ceil( $count / 100 );
	$r          = bb2_db_query( 'SELECT * FROM `' . $settings['log_table'] . '` WHERE 1=1 ' . $where . 'ORDER BY `date` DESC LIMIT ' . ( $paged - 1 ) * $rows_per_page . ',' . $rows_per_page );
	$results    = bb2_db_rows( $r );

	// Display rows to the user.
	?>
<div class="wrap">
	<?php echo bb2_donate_button( admin_url( 'tools.php?page=bb2_manage' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<h2><?php esc_html_e( 'Bad Behavior Log', 'bad-behavior' ); ?></h2>
	<form method="post" action="<?php echo esc_url( admin_url( 'tools.php?page=bb2_manage' ) ); ?>">
		<p>For more information please visit the <a href="https://github.com/svl-studios/bad-behavior">Bad Behavior</a> homepage.</p>
		<p>See also: <a href="<?php echo esc_url( admin_url( 'options-general.php?page=bb2_options' ) ); ?>">Settings</a> | <a href="<?php echo esc_url( admin_url( 'options-general.php?page=bb2_whitelist' ) ); ?>">Whitelist</a></p>
		<div class="tablenav">
	<?php

	$page_links = paginate_links(
		array(
			'base'    => add_query_arg( 'paged', '%#%' ),
			'format'  => '',
			'total'   => $pages,
			'current' => $paged,
		)
	);

	if ( $page_links ) {
		echo "<div class=\"tablenav-pages\">$page_links</div>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	?>
			<div class="alignleft">
	<?php if ( $count < $totalcount ) { ?>
				Displaying <strong><?php echo esc_html( $count ); ?></strong> of <strong><?php echo esc_html( $totalcount ); ?></strong> records filtered by:<br/>
		<?php
		if ( $key ) {
			echo 'Status [<a href="' . esc_url( remove_query_arg( array( 'paged', 'key' ), $request_uri ) ) . '">X</a>] ';}
		?>
		<?php
		if ( $blocked ) {
			echo 'Blocked [<a href="' . esc_url( remove_query_arg( array( 'paged', 'blocked', 'permitted' ), $request_uri ) ) . '">X</a>] ';}
		?>
		<?php
		if ( $permitted ) {
			echo 'Permitted [<a href="' . esc_url( remove_query_arg( array( 'paged', 'blocked', 'permitted' ), $request_uri ) ) . '">X</a>] ';}
		?>
		<?php
		if ( $ip ) {
			echo 'IP [<a href="' . esc_url( remove_query_arg( array( 'paged', 'ip' ), $request_uri ) ) . '">X</a>] ';}
		?>
		<?php
		if ( $user_agent ) {
			echo 'User Agent [<a href="' . esc_url( remove_query_arg( array( 'paged', 'user_agent' ), $request_uri ) ) . '">X</a>] ';}
		?>
		<?php
		if ( $request ) {
			echo 'GET/POST [<a href="' . esc_url( remove_query_arg( array( 'paged', 'request_method' ), $request_uri ) ) . '">X</a>] ';}
		?>
<?php } else { ?>
			Displaying all <strong><?php echo esc_html( $totalcount ); ?></strong> records<br/>
<?php } ?>
	<?php
	if ( '' !== $key && '' !== $blocked ) {
		?>
		<a href="
		<?php
		echo esc_url(
			add_query_arg(
				array(
					'blocked'   => '1',
					'permitted' => '0',
					'paged'     => false,
				),
				$request_uri
			)
		);
		?>
		">Show Blocked</a>
		<?php
	}
	?>
	<?php
	if ( '' !== $key && '' !== $permitted ) {
		?>
		<a href="
		<?php
		echo esc_url(
			add_query_arg(
				array(
					'permitted' => '1',
					'blocked'   => '0',
					'paged'     => false,
				),
				$request_uri
			)
		);
		?>
		">Show Permitted</a>
		<?php
	}
	?>
			</div>
		</div>

		<table class="widefat">
			<thead>
				<tr>
				<th scope="col" class="check-column">
					<label>
						<input type="checkbox" onclick="checkAll(document.getElementById('request-filter'));" />
					</label>
				</th>
				<th scope="col"><?php esc_html_e( 'IP/Date/Status', 'bad-behavior' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Headers', 'bad-behavior' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Entity', 'bad-behavior' ); ?></th>
				</tr>
			</thead>
			<tbody>
	<?php
	$alternate = 0;
	if ( $results ) {
		foreach ( $results as $result ) {
			$key = bb2_get_response( $result['key'] );
			++$alternate;

			if ( $alternate % 2 ) {
				echo '<tr id="request-' . esc_attr( $result['id'] ) . "\" valign=\"top\">\n";
			} else {
				echo '<tr id="request-' . esc_attr( $result['id'] ) . "\" class=\"alternate\" valign=\"top\">\n";
			}

			echo '<th scope="row" class="check-column"><input type="checkbox" name="submit[]" value="' . esc_attr( $result['id'] ) . "\" /></th>\n";

			$httpbl = bb2_httpbl_lookup( $result['ip'] );
			$host   = gethostbyaddr( $result['ip'] );

			if ( ! strcmp( $host, $result['ip'] ) ) {
				$host = '';
			} else {
				$host .= "<br/>\n";
			}

			echo '<td><a href="' . esc_url( add_query_arg( 'ip', $result['ip'], remove_query_arg( 'paged', $request_uri ) ) ) . '">' . esc_html( $result['ip'] ) . '</a><br/>' . esc_html( $host ) . "<br/>\n" . esc_html( $result['date'] ) . '<br/><br/><a href="' . esc_url( add_query_arg( 'key', $result['key'], remove_query_arg( array( 'paged', 'blocked', 'permitted' ), $request_uri ) ) ) . '">' . esc_html( $key['log'] ) . "</a>\n";

			if ( $httpbl ) {
				echo '<br/><br/><a href="https://www.projecthoneypot.org/ip_' . esc_html( $result['ip'] ) . '">http:BL</a>:<br/>' . $httpbl . '\n'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			echo "</td>\n";

			$headers = str_replace( "\n", "<br/>\n", htmlspecialchars( $result['http_headers'] ) );

			if ( str_contains( $headers, $result['user_agent'] ) ) {
				$headers = substr_replace( $headers, '<a href="' . esc_url( add_query_arg( 'user_agent', rawurlencode( $result['user_agent'] ), remove_query_arg( 'paged', $request_uri ) ) ) . '">' . $result['user_agent'] . '</a>', strpos( $headers, $result['user_agent'] ), strlen( $result['user_agent'] ) );
			}

			if ( str_contains( $headers, $result['request_method'] ) ) {
				$headers = substr_replace( $headers, '<a href="' . esc_url( add_query_arg( 'request_method', rawurlencode( $result['request_method'] ), remove_query_arg( 'paged', $request_uri ) ) ) . '">' . $result['request_method'] . '</a>', strpos( $headers, $result['request_method'] ), strlen( $result['request_method'] ) );
			}

			echo "<td>$headers</td>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<td>' . str_replace( "\n", "<br/>\n", htmlspecialchars( $result['request_entity'] ) ) . "</td>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "</tr>\n";
		}
	}
	?>
			</tbody>
		</table>
		<div class="tablenav">
	<?php
	$page_links = paginate_links(
		array(
			'base'    => add_query_arg( 'paged', '%#%' ),
			'format'  => '',
			'total'   => $pages,
			'current' => $paged,
		)
	);

	if ( $page_links ) {
		echo "<div class=\"tablenav-pages\">$page_links</div>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	?>
			<div class="alignleft">
		</div>
		</div>
		<?php wp_nonce_field( 'bad-behavior-log' ); ?>
	</form>
</div>
	<?php
}


/**
 * Whitelist.
 *
 * @return void
 */
function bb2_whitelist(): void {
	$whitelists = bb2_read_whitelist();

	if ( empty( $whitelists ) ) {
		$whitelists              = array();
		$whitelists['ip']        = array();
		$whitelists['url']       = array();
		$whitelists['useragent'] = array();
	}

	if ( ! empty( $_POST ) && check_admin_referer( 'bad-behavior-whitelist' ) ) {
		$ip         = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
		$url        = isset( $_POST['url'] ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : '';
		$user_agent = isset( $_POST['useragent'] ) ? sanitize_text_field( wp_unslash( $_POST['useragent'] ) ) : '';

		if ( $ip ) {
			$whitelists['ip'] = array_filter( preg_split( '/\s+/m', $ip ) );
		} else {
			$whitelists['ip'] = array();
		}
		if ( $url ) {
			$whitelists['url'] = array_filter( preg_split( '/\s+/m', $url ) );
		} else {
			$whitelists['url'] = array();
		}
		if ( $user_agent ) {
			$whitelists['useragent'] = array_filter( preg_split( "/[\r\n]+/m", $user_agent ) );
		} else {
			$whitelists['useragent'] = array();
		}
		update_option( 'bad_behavior_whitelist', $whitelists );

		?>
	<div id="message" class="updated fade"><p><strong><?php esc_html_e( 'Options saved.', 'bad-behavior' ); ?></strong></p></div>
		<?php

	}

	?>
	<div class="wrap">
	<?php echo bb2_donate_button( admin_url( 'options-general.php?page=bb2_whitelist' ) ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<h2><?php esc_html_e( 'Bad Behavior Whitelist', 'bad-behavior' ); ?></h2>
	<form method="post" action="<?php echo esc_url( admin_url( 'options-general.php?page=bb2_whitelist' ) ); ?>">
	<p>Inappropriate whitelisting WILL expose you to spam, or cause Bad Behavior to stop functioning entirely! DO NOT WHITELIST unless you are 100% CERTAIN that you should.</p>
	<p>For more information please visit the <a href="https://bad-behavior.ioerror.us/">Bad Behavior</a> homepage.</p>
	<p>See also: <a href="<?php echo esc_url( admin_url( 'options-general.php?page=bb2_options' ) ); ?>">Settings</a> | <a href="<?php echo esc_url( admin_url( 'tools.php?page=bb2_manage' ) ); ?>">Log</a></p>

	<h3><?php esc_html_e( 'IP Address', 'bad-behavior' ); ?></h3>
	<table class="form-table">
	<tr><td><label>IP address or CIDR format address ranges to be whitelisted (one per line)<br/><textarea cols="24" rows="6" name="ip"><?php echo esc_textarea( implode( "\n", $whitelists['ip'] ) ); ?></textarea></td></tr>
	</table>

	<h3><?php esc_html_e( 'URL', 'bad-behavior' ); ?></h3>
	<table class="form-table">
	<tr><td><label>URL fragments beginning with the / after your web site hostname (one per line)<br/><textarea cols="48" rows="6" name="url"><?php echo esc_textarea( implode( "\n", $whitelists['url'] ) ); ?></textarea></td></tr>
	</table>

	<h3><?php esc_html_e( 'User Agent', 'bad-behavior' ); ?></h3>
	<table class="form-table">
	<tr><td><label>User agent strings to be whitelisted (one per line)<br/><textarea cols="48" rows="6" name="useragent"><?php echo esc_textarea( implode( "\n", $whitelists['useragent'] ) ); ?></textarea></td></tr>
	</table>

	<?php wp_nonce_field( 'bad-behavior-whitelist' ); ?>

	<p class="submit"><input class="button" type="submit" name="submit" value="<?php esc_html_e( 'Update &raquo;', 'bad-behavior' ); ?>" /></p>
	</form>
	<?php
}


/**
 * Options screen.
 *
 * @return void
 */
function bb2_options(): void {
	$settings = bb2_read_settings();

	if ( ! empty( $_POST ) && check_admin_referer( 'bad-behavior-options' ) ) {
		$stats         = isset( $_POST['display_stats'] ) ? sanitize_text_field( wp_unslash( $_POST['display_stats'] ) ) : '';
		$strict        = isset( $_POST['strict'] ) ? sanitize_text_field( wp_unslash( $_POST['strict'] ) ) : '';
		$verbose       = isset( $_POST['verbose'] ) ? sanitize_text_field( wp_unslash( $_POST['verbose'] ) ) : '';
		$logging       = isset( $_POST['logging'] ) ? sanitize_text_field( wp_unslash( $_POST['logging'] ) ) : '';
		$httpbl_key    = isset( $_POST['httpbl_key'] ) ? sanitize_text_field( wp_unslash( $_POST['httpbl_key'] ) ) : '';
		$httpbl_threat = isset( $_POST['httpbl_threat'] ) ? sanitize_text_field( wp_unslash( $_POST['httpbl_threat'] ) ) : '';
		$httpbl_max    = isset( $_POST['httpbl_maxage'] ) ? sanitize_text_field( wp_unslash( $_POST['httpbl_maxage'] ) ) : '';
		$offsite       = isset( $_POST['offsite_forms'] ) ? sanitize_text_field( wp_unslash( $_POST['offsite_forms'] ) ) : '';
		$reverse       = isset( $_POST['reverse_proxy'] ) ? sanitize_text_field( wp_unslash( $_POST['reverse_proxy'] ) ) : '';
		$rp_header     = isset( $_POST['reverse_proxy_header'] ) ? sanitize_text_field( wp_unslash( $_POST['reverse_proxy_header'] ) ) : '';
		$rp_address    = isset( $_POST['reverse_proxy_addresses'] ) ? sanitize_text_field( wp_unslash( $_POST['reverse_proxy_addresses'] ) ) : '';

		if ( '' !== $stats ) {
			$settings['display_stats'] = true;
		} else {
			$settings['display_stats'] = false;
		}

		if ( '' !== $strict ) {
			$settings['strict'] = true;
		} else {
			$settings['strict'] = false;
		}

		if ( '' !== $verbose ) {
			$settings['verbose'] = true;
		} else {
			$settings['verbose'] = false;
		}

		if ( '' !== $logging ) {
			if ( 'verbose' === $logging ) {
				$settings['verbose'] = true;
				$settings['logging'] = true;
			} elseif ( 'normal' === $logging ) {
				$settings['verbose'] = false;
				$settings['logging'] = true;
			} else {
				$settings['verbose'] = false;
				$settings['logging'] = false;
			}
		} else {
			$settings['verbose'] = false;
			$settings['logging'] = false;
		}

		if ( '' !== $httpbl_key ) {
			if ( preg_match( '/^[a-z]{12}$/', $httpbl_key ) ) {
				$settings['httpbl_key'] = $httpbl_key;
			} else {
				$settings['httpbl_key'] = '';
			}
		} else {
			$settings['httpbl_key'] = '';
		}

		if ( '' !== $httpbl_threat ) {
			$settings['httpbl_threat'] = intval( $httpbl_threat );
		} else {
			$settings['httpbl_threat'] = '25';
		}

		if ( '' !== $httpbl_max ) {
			$settings['httpbl_maxage'] = intval( $httpbl_max );
		} else {
			$settings['httpbl_maxage'] = '30';
		}

		if ( '' !== $offsite ) {
			$settings['offsite_forms'] = true;
		} else {
			$settings['offsite_forms'] = false;
		}

		unset( $settings['eu_cookie'] );

		if ( '' !== $reverse ) {
			$settings['reverse_proxy'] = true;
		} else {
			$settings['reverse_proxy'] = false;
		}

		if ( '' !== $rp_header ) {
			$settings['reverse_proxy_header'] = uc_all( $rp_header );
		} else {
			$settings['reverse_proxy_header'] = 'X-Forwarded-For';
		}

		if ( '' !== $rp_address ) {
			$settings['reverse_proxy_addresses'] = preg_split( '/[\s,]+/m', $rp_address );
			$settings['reverse_proxy_addresses'] = array_map( 'sanitize_text_field', $settings['reverse_proxy_addresses'] );
		} else {
			$settings['reverse_proxy_addresses'] = array();
		}

		bb2_write_settings( $settings );

		?>
	<div id="message" class="updated fade"><p><strong><?php esc_html_e( 'Options saved.', 'bad-behavior' ); ?></strong></p></div>
		<?php
	}
	?>
	<div class="wrap">
	<?php

	echo bb2_donate_button( admin_url( 'options-general.php?page=bb2_options' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	?>
	<h2><?php esc_html_e( 'Bad Behavior', 'bad-behavior' ); ?></h2>
	<form method="post" action="<?php echo esc_html( admin_url( 'options-general.php?page=bb2_options' ) ); ?>">
		<p>For more information please visit the <a href="https://github.com/svl-studios/bad-behavior">Bad Behavior</a> homepage.</p>
		<p>See also: <a href="<?php echo esc_html( admin_url( 'tools.php?page=bb2_manage' ) ); ?>">Log</a> | <a href="<?php echo esc_html( admin_url( 'options-general.php?page=bb2_whitelist' ) ); ?>">Whitelist</a></p>

		<h3><?php esc_html_e( 'Statistics', 'bad-behavior' ); ?></h3>
	<?php bb2_insert_stats( true ); ?>
		<table class="form-table">
			<tr><td><label><input type="checkbox" name="display_stats" value="true"
	<?php
	if ( $settings['display_stats'] ) {
		?>
		checked="checked" <?php } ?>/> <?php esc_html_e( 'Display statistics in blog footer', 'bad-behavior' ); ?></label></td></tr>
		</table>

		<h3><?php esc_html_e( 'Logging', 'bad-behavior' ); ?></h3>
		<table class="form-table">
			<tr><td><label><input type="radio" name="logging" value="verbose"
	<?php
	if ( $settings['verbose'] && $settings['logging'] ) {
		?>
		checked="checked" <?php } ?>/> <?php esc_html_e( 'Verbose HTTP request logging', 'bad-behavior' ); ?></label></td></tr>
			<tr><td><label><input type="radio" name="logging" value="normal"
	<?php
	if ( $settings['logging'] && ! $settings['verbose'] ) {
		?>
		checked="checked" <?php } ?>/> <?php esc_html_e( 'Normal HTTP request logging (recommended)', 'bad-behavior' ); ?></label></td></tr>
			<tr><td><label><input type="radio" name="logging" value="false"
	<?php
	if ( ! $settings['logging'] ) {
		?>
		checked="checked" <?php } ?>/> <?php esc_html_e( 'Do not log HTTP requests (not recommended)', 'bad-behavior' ); ?></label></td></tr>
		</table>

		<h3><?php esc_html_e( 'Security', 'bad-behavior' ); ?></h3>
		<table class="form-table">
			<tr><td><label><input type="checkbox" name="strict" value="true"
	<?php
	if ( $settings['strict'] ) {
		?>
		checked="checked" <?php } ?>/> <?php esc_html_e( 'Strict checking (blocks more spam but may block some people)', 'bad-behavior' ); ?></label></td></tr>
			<tr><td><label><input type="checkbox" name="offsite_forms" value="true"
	<?php
	if ( $settings['offsite_forms'] ) {
		?>
		checked="checked" <?php } ?>/> <?php esc_html_e( 'Allow form postings from other web sites (required for OpenID; increases spam received)', 'bad-behavior' ); ?></label></td></tr>
		</table>

		<h3><?php esc_html_e( 'http:BL', 'bad-behavior' ); ?></h3>
		<p>To use Bad Behavior's http:BL features you must have an <a href="https://www.projecthoneypot.org/httpbl_configure.php?rf=24694">http:BL Access Key</a>.</p>
		<table class="form-table">
			<tr><td><label><input type="text" size="12" maxlength="12" name="httpbl_key" value="<?php echo sanitize_key( $settings['httpbl_key'] ); ?>" /> http:BL Access Key</label></td></tr>
			<tr><td><label><input type="text" size="3" maxlength="3" name="httpbl_threat" value="<?php echo intval( $settings['httpbl_threat'] ); ?>" /> Minimum Threat Level (25 is recommended)</label></td></tr>
			<tr><td><label><input type="text" size="3" maxlength="3" name="httpbl_maxage" value="<?php echo intval( $settings['httpbl_maxage'] ); ?>" /> Maximum Age of Data (30 is recommended)</label></td></tr>
		</table>

		<h3><?php esc_html_e( 'Reverse Proxy/Load Balancer', 'bad-behavior' ); ?></h3>
		<p>If you are using Bad Behavior behind a reverse proxy, load balancer, HTTP accelerator, content cache or similar technology, enable the Reverse Proxy option.</p>
		<p>If you have a chain of two or more reverse proxies between your server and the public Internet, you must specify <em>all</em> of the IP address ranges (in CIDR format) of all of your proxy servers, load balancers, etc. Otherwise, Bad Behavior may be unable to determine the client's true IP address.</p>
		<p>In addition, your reverse proxy servers must set the IP address of the Internet client from which they received the request in an HTTP header. If you don't specify a header, <a href="https://en.wikipedia.org/wiki/X-Forwarded-For">X-Forwarded-For</a> will be used. Most proxy servers already support X-Forwarded-For and you would then only need to ensure that it is enabled on your proxy servers. Some other header names in common use include <u>X-Real-Ip</u> (nginx) and <u>Cf-Connecting-Ip</u> (CloudFlare).</p>
		<p>Note: This option is not required if reverse proxy IP address handing is configured in your web server, e.g. with Apache mod_remoteip or Nginx realip, but it is safe to enable it anyway if you are not sure about the web server configuration.</p>
		<table class="form-table">
			<tr><td><label><input type="checkbox" name="reverse_proxy" value="true"
	<?php
	if ( $settings['reverse_proxy'] ) {
		?>
		checked="checked" <?php } ?>/> <?php esc_html_e( 'Enable Reverse Proxy', 'bad-behavior' ); ?></label></td></tr>
			<tr><td><label><input type="text" size="32" name="reverse_proxy_header" value="<?php echo esc_attr( sanitize_text_field( $settings['reverse_proxy_header'] ) ); ?>" /> Header containing Internet clients' IP address</label></td></tr>
			<tr><td><label>IP address or CIDR format address ranges for your proxy servers (one per line)<br/><textarea cols="24" rows="6" name="reverse_proxy_addresses"><?php echo esc_textarea( implode( "\n", $settings['reverse_proxy_addresses'] ) ); ?></textarea></td></tr>
		</table>

	<?php wp_nonce_field( 'bad-behavior-options' ); ?>

		<p class="submit"><input class="button" type="submit" name="submit" value="<?php esc_html_e( 'Update &raquo;', 'bad-behavior' ); ?>" /></p>
	</form>
</div>
	<?php
}

add_action( 'admin_menu', 'bb2_admin_pages' );

/**
 * Add links to plugin entry.
 *
 * @param array  $links Plugin screen links.
 * @param string $file File.
 *
 * @return array
 */
function bb2_plugin_action_links( array $links, string $file ): array {
	if ( 'bad-behavior/bad-behavior-wordpress.php' === $file ) {
		$log_link       = '<a href="' . admin_url( 'tools.php?page=bb2_manage' ) . '">Log</a>';
		$settings_link  = '<a href="' . admin_url( 'options-general.php?page=bb2_options' ) . '">Settings</a>';
		$whitelist_link = '<a href="' . admin_url( 'options-general.php?page=bb2_whitelist' ) . '">Whitelist</a>';

		array_unshift( $links, $settings_link, $log_link, $whitelist_link );
	}

	return $links;
}

add_filter( 'plugin_action_links', 'bb2_plugin_action_links', 10, 2 );
