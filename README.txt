=== Bad Behavior ===
Tags: comment,trackback,referrer,spam,robot,antispam
Contributors: error
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=error%40ioerror%2eus&item_name=Bad%20Behavior%20%28From%20WordPress%20Page%29&no_shipping=1&cn=Comments%20about%20Bad%20Behavior&tax=0&currency_code=USD&bn=PP%2dDonationsBF&charset=UTF%2d8
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 3.0.0

Bad Behavior prevents spammers from ever delivering their junk, and in many
cases, from ever reading your site in the first place.

== Description ==

Welcome to a whole new way of keeping your blog, forum, guestbook, wiki or
content management system free of link spam. Bad Behavior is a PHP-based
solution for blocking link spam and the robots which deliver it.

Thousands of sites large and small, like SourceForge, GNOME, the U.S.
Department of Education, and many more, trust Bad Behavior to help reduce
incoming link spam and malicious activity.

Bad Behavior complements other link spam solutions by acting as a gatekeeper,
preventing spammers from ever delivering their junk, and in many cases, from
ever reading your site in the first place. This keeps your site's load down,
makes your site logs cleaner, and can help prevent denial of service
conditions caused by spammers.

Bad Behavior also transcends other link spam solutions by working in a
completely different, unique way. Instead of merely looking at the content of
potential spam, Bad Behavior analyzes the delivery method as well as the
software the spammer is using. In this way, Bad Behavior can stop spam attacks
even when nobody has ever seen the particular spam before.

Bad Behavior is designed to work alongside existing spam prevention services
to increase their effectiveness and efficiency. Whenever possible, you should
run it in combination with a more traditional spam prevention service.

Bad Behavior works on, or can be adapted to, virtually any PHP-based Web
software package. Bad Behavior is available natively for WordPress, MediaWiki,
Drupal, ExpressionEngine, and LifeType, and people have successfully made it
work with Movable Type, phpBB, and many other packages.

Installing and configuring Bad Behavior on most platforms is simple and takes
only a few minutes. In most cases, no configuration at all is needed. Simply
turn it on and stop worrying about spam!

The core of Bad Behavior is free software released under the GNU Lesser General
Public License, version 3, or at your option, any later version.

== Installation ==

*Warning*: If you are upgrading from a 2.0.x release of Bad Behavior, it is
recommended that you delete the old version from your system before
installing the 2.2.x release, or obsolete files may be left lying around.

*Warning*: If you are upgrading from a 1.x.x version of Bad Behavior,
you must remove it from your system entirely, and delete all of its
database tables, before installing Bad Behavior 2.2.x or 2.0.x. If you are
upgrading from version 2.0.18 or prior, you must delete all of its files
before upgrading, but do not need to delete the database tables.

Bad Behavior has been designed to install on each host software in the
manner most appropriate to each platform. It's usually sufficient to
follow the generic instructions for installing any plugin or extension
for your host software.

On MediaWiki, it is necessary to add a second line to LocalSettings.php
when installing the extension. Your LocalSettings.php should include
the following:

`	include_once( 'includes/DatabaseFunctions.php' );
	include( './extensions/Bad-Behavior/bad-behavior-mediawiki.php' );

For complete documentation and installation instructions, please visit
https://bad-behavior.ioerror.us/

== Screenshots ==

1. Most of the time, only spammers see this. In the rare event a human
winds up here, a way out is provided. This may involve removing malicious
software from the user's computer, changing firewall settings or other simple
fixes which will immediately grant access again.

2. Bad Behavior's built in log viewer (WordPress) shows why requests were
blocked and allows you to click on any IP address, user-agent string or
block reason to filter results.

== Release Notes ==

= Bad Behavior 2.2 Known Issues =

* Bad Behavior 2.3 requires MySQL 5.0 or later and PHP 8.0 or later.

* CloudFlare users must enable the Reverse Proxy option in Bad Behavior's
settings. See the documentation for further details.

* Bad Behavior is unable to protect internally cached pages on MediaWiki.
Only form submissions will be protected.

* When upgrading from version 2.0.19 or prior on MediaWiki and WordPress,
you must remove the old version of Bad Behavior from your system manually
before manually installing the new version. Other platforms are not
affected by this issue.

* Bad Behavior on WordPress requires version 3.1 or later. Users of older
versions should upgrade WordPress prior to installing Bad Behavior.

* On WordPress when using WP-Super Cache, Bad Behavior must be enabled in
WP-Super Cache's configuration in order to protect PHP Cached or Legacy
Cached pages. Bad Behavior cannot protect mod_rewrite cached (Super Cached)
pages.

* When using Bad Behavior in conjunction with Spam Karma 2, you may see PHP
warnings when Spam Karma 2 displays its internally generated CAPTCHA. This
is a design problem in Spam Karma 2. Contact the author of Spam Karma 2 for
a fix.

== Changelog ==
= 3.0.0 =
* Added: PHP 8.x support
* Added: PHPDocs
* Added: WordPress 6.x support
* Modified: Added WPCS security fixes.

= 2.2.14 =
* Some deprecated code has been rewritten. Thanks to Doug Joseph for reporting the issue.

= 2.2.23 =
* All live links to web sites have been converted to HTTPS links, where available.

= 2.2.22 =
* A leftover bit of the screening code which was removed in Bad Behavior 2.2.21 caused a spurious PHP notice. This bit has also been removed.

= 2.2.21 =
* Screening code which used cookies and JavaScript, and had poor performance, has been removed. Because Bad Behavior no longer uses cookies, EU-specific cookie handling code is no longer necessary and has been removed.
* Resolved an incompatibility with the Health Check plugin

= 2.2.20 =
* A spurious PHP notice was removed.
* The current use of cookies to screen requests is being deprecated. Any cookies will be removed from users’ browsers, if they exist.

= 2.2.19 =
* In certain circumstances, a cross-site scripting attack was possible via the Bad Behavior Whitelist options page. This issue has been fixed.
* Protection from cross-site request forgery (WordPress nonces) has been added to the Bad Behavior Whitelist and Bad Behavior Options pages. This covers cases where Bad Behavior’s built-in CSRF protection is disabled or ineffective.

= 2.2.18 =
* A new IP address range is in use by the Bing search engine; this range has been added to Bad Behavior.

= 2.2.17 =
* Bad Behavior is now compatible with PHP 7.
* Bad Behavior is now compatible with WordPress 4.4.
* Bad Behavior can now be used on sites which run on a non-standard HTTP port (the standard ports are 80 for HTTP and 443 for HTTPS).

= 2.2.16 =
* The via HTTP header, when present in all lowercase letters, violates a convention that headers should be in mixed case, and the lowercase-only header is commonly seen from malicious proxy servers. However, the actual HTTP specifications do not disallow it, and a check for this lowercase header does block some legitimate traffic. Therefore this version of Bad Behavior has been changed to check for lowercase via only in strict mode. This resolves an issue where web users at certain large companies are blocked; sites expecting these visitors should not enable strict mode.

= 2.2.15 =
* An additional exploit scanner has been identified and blocked.
* A deprecated function has been removed and replaced.
* Recent versions of the Google Chrome and Firefox browsers do not actually delete session cookies by default when closing the browser. This resulted in a rare case where old session cookies, served when the site’s EU Cookie setting was off, would be returned if the EU Cookie setting was later enabled. When this happened, the visitor would be blocked. This issue is now resolved.

= 2.2.14 =
* An additional exploit scanner has been identified and blocked.
* A small change has been made to accommodate a change made by Firefox to its User-Agent format, to ensure that Firefox 25 (which doesn’t yet exist) is not improperly blocked.

= 2.2.13 =
* Requests from the Baidu search engine now go through screening similar to Google and other major search engines. This will help to prevent illegitimate access from clients which falsely claim to be the Baidu search engine. A logic error which prevented these checks from ever running has been fixed.

= 2.2.12 =
* Search engine screening by IP address is now more lenient; a failure to match a known IP address range no longer blocks the bot outright. This change is in response to a major search engine which is adding large numbers of IP address ranges faster than they can be tracked and added to Bad Behavior. Requests which don’t match a known IP address range still go through normal screening, while requests which match will be passed immediately.
* Search engine IP address screening is bypassed when the request originates from an IPv6 address, pending the addition of IPv6 subnet matching code.
* Requests from the Baidu search engine now go through screening similar to Google and other major search engines. This will help to prevent illegitimate access from clients which falsely claim to be the Baidu search engine.
* Some URL blacklist strings have been removed due to the possibility of their matching legitimate user input (e.g. in a site search phrase).

= 2.2.11 =
* Google AdSense has changed their crawler’s User-Agent string to a string that matches a user agent blacklist entry. This would prevent the delivery of targeted ads to a page, and result in generic ads being displayed. The blacklist entry was temporarily removed pending communication with Google.
* A PHP warning would be generated if any whitelist had blank lines in it. Blank lines are now stripped out of whitelist entries.

= 2.2.10 =
* Code added in the previous release to support detection of malicious attacks contained an unfortunate typo causing PHP warnings to appear. This has been fixed.

= 2.2.9 =
* Several patterns associated with malicious activity such as SQL injection and vulnerability scanning have been identified and blocked.
* A code change regarding display of the whitelist in the administrative page was reverted due to unforeseen issues.

= 2.2.8 =
* Several robots associated with spam and malicious activity have been identified and blocked.
* A minor bug causing the whitelist to not appear properly in the administrative page in some circumstances has been fixed.

= 2.2.7 =
* A site scraper and a spambot have been identified and blocked.
* The Bad Behavior timer code, which adds an HTML comment to rendered pages, has been fixed. It should be safe to enable this option (to do so, set $wgBadBehaviorTimer = true; in LocalSettings.php). Please report back if you find a skin with which this functionality still fails.
* The code which adds Bad Behavior statistics to the blog footer is now disabled by default for new installations. This change was made long ago but somehow got reverted. To change this setting, visit Settings » Bad Behavior.
