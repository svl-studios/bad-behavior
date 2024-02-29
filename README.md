Just as a reminder, if you use CloudFlare, Incapsula, Amazon Elastic Load Balancer, Azure Load Balancer, Google Cloud Load Balancing, or similar services on your site, you may need to enable the Reverse Proxy option in Bad Behavior’s settings, or many of your visitors and search engines will be blocked.
<h1>Configuration</h1>
<p>Bad Behavior has several options which apply to all ports.</p>
<p>For some ports, these options are set within the <code>settings.ini</code> file. A sample file is included with Bad Behavior; to use it, copy <code>settings-sample.ini</code> to <code>settings.ini</code> and upload it to the same folder which contained <code>settings-sample.ini</code>.</p>
<p>Other ports, such as WordPress, ignore <code>settings.ini</code> and have a built-in administrative page where you can change these settings.</p>
<p>Note that Bad Behavior&#8217;s default options are fine for most users. Also note that some ports provide additional options specific to that host platform; such options are also documented in the instructions for that platform.</p>
<p><strong>Display Statistics</strong> (default off): On some platforms, enabling this option will add a blurb to your web site footer advertising Bad Behavior&#8217;s presence and the number of recently blocked requests. Sites receiving more than 1,000 visitors per day should leave this option disabled as it is database intensive. This option is not available or has no effect when logging is not in use.</p>
<p><strong>Logging</strong> (default on): You can disable logging entirely, but this is not recommended since it may cause additional spam to get through. Logging is only available on platforms with a connected database.</p>
<p><strong>Verbose Logging</strong> (default off): Turning on verbose mode causes all HTTP requests to be logged. When verbose mode is off, only blocked requests and a few suspicious (but permitted) requests are logged. Verbose mode is off by default. Using verbose mode is not recommended as it can significantly slow down your site; it exists to capture data from live spammers which are not being blocked.</p>
<p><strong>Strict Mode</strong> (default off): Bad Behavior operates in two blocking modes: normal and strict. When strict mode is enabled, some additional checks for buggy software which have been spam sources are enabled, but occasional legitimate users using the same software (usually corporate or government users using very old software) may be blocked as well. It is up to you whether you want to have the government reading your blog, or keep away more spammers.</p>
<p><strong>Allow Offsite Forms</strong> (default false): Bad Behavior normally prevents your site from receiving data posted from forms on other web sites. This prevents spammers from, e.g., using a Google cached version of your web site to send you spam. However, some web applications such as OpenID require that your site be able to receive form data in this way. If you are running OpenID, enable this option.</p>
<p><strong>http:BL API Key</strong> (no default): Bad Behavior is capable of using data from the <a href="https://web.archive.org/web/20210416161446/https://www.projecthoneypot.org/faq.php#g">http:BL</a> service provided by <a href="https://web.archive.org/web/20210416161446/https://www.projecthoneypot.org/">Project Honey Pot</a> to screen requests. This is purely optional; however if you wish to use it, you must <a href="https://web.archive.org/web/20210416161446/https://www.projecthoneypot.org/account_login.php">sign up for the service</a> and obtain an API key. To disable http:BL use, remove the API key from your settings.</p>
<p><strong>http:BL Threat Level</strong> (default 25): This number provides a measure of how suspicious an IP address is, based on activity observed at Project Honey Pot. Bad Behavior will block requests with a threat level equal or higher to this setting. Project Honey Pot has <a href="https://web.archive.org/web/20210416161446/https://www.projecthoneypot.org/threat_info.php">more information on this parameter</a>.</p>
<p><strong>http:BL Maximum Age</strong> (default 30): This is the number of days since suspicious activity was last observed from an IP address by Project Honey Pot. Bad Behavior will block requests with a maximum age equal to or less than this setting. Project Honey Pot has <a href="https://web.archive.org/web/20210416161446/https://www.projecthoneypot.org/threat_info.php">more information on this parameter</a>.</p>
<p><strong>Reverse Proxy</strong> (default off): If your web server is behind a reverse proxy, load balancer or content distribution network, you may need to enable this option in order for Bad Behavior to screen requests properly. This option does not apply to most users and should be left off unless you are absolutely certain that you need it.</p>
<p>If you use the CloudFlare service, you should enable this option.</p>
<p><strong>Reverse Proxy Header</strong> (default &#8220;X-Forwarded-For&#8221;): When a reverse proxy is in use, Bad Behavior looks at this HTTP header to determine the actual source IP address for each web request. Your reverse proxy or load balancer must add an HTTP header containing the remote IP address where the connection originated. Most do this by default; check the configuration for your reverse proxy or load balancer to ensure that this header is sent.</p>
<p>If you use the CloudFlare service, you should change this option to &#8220;CF-Connecting-IP&#8221;.</p>
<p><strong>Reverse Proxy Addresses</strong> (no default): In some server farm configurations, Bad Behavior may be unable to determine whether a remote request originated from your reverse proxy/load balancer or arrived directly. In this case, you should add all of the internal IP addresses for your reverse proxy/load balancer servers, as seen from the origin server. These can usually be omitted; however if you have a configuration where some requests can bypass the reverse proxy/load balancer and connect to the origin server directly, then you should use this option. You should also use this option when incoming requests pass through two or more reverse proxies before reaching the origin server.</p>
