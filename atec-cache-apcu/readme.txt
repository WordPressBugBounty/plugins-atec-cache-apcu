=== atec Cache APCu ===
Contributors: DocJoJo
Tags: APCu object-cache and the only APCu based page-cache plugin available.
Requires at least: 5.2
Tested up to: 6.7.1
Requires PHP: 7.4
Tested up to PHP: 8.4.1
Stable tag: 2.1.11
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

APCu object-cache and the only APCu based page-cache plugin available.

== Description ==

This plugin provides a super fast APCu object-cache and the only APCu based page-cache plugin available.
Using an object-cache will speed up your site – APCu is the fastest cache, compared against the two other memory-based cache options Redis and Memcached.

– Object caching involves storing variables and database queries thereby speeding up PHP execution times. This reduces the load on your server, and delivers content to your visitors faster.
- Page caching refers to caching the content of a whole page on the server-side. Later when the same page is requested again, its content will be served from the cache instead of regenerating it from scratch.

Requires PHP APCu extension.
Lightweight (70KB) and resource-efficient.
Backend CPU footprint: 6 ms.
Frontend CPU footprint: <1 ms.

== 3rd party as a service ==

Once, when activating the plugin, an integrity check is requested from our server (https://atecplugins.com/) – if you give your permission.
Privacy policy: https://atecplugins.com/privacy-policy/

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory or through the `Plugins` menu.
2. Activate the plugin through the `Plugins` menu in WordPress.
3. Click "atec Cache APCu" link in admin menu bar.
4. Enable Page-Cache in the `Settings` tab.

== Frequently Asked Questions ==

- Use WP-CLI
If you use WP-CLI to run .php scripts, please set "apc.enable_cli=1" in your php.ini – otherwise object-cache will not work.

== Screenshots ==

1. Settings
2. Cache Info
3. Server Info
4. Persistent Object Cache Groups
5. Page Cache Overview
6. Cache comparison (APCu, Redis, Memcached)

== Changelog ==

= 2.1.11 [2024.12.07] =
* Toogle admin bar display

= 2.1.10 [2024.11.27] =
* Improved plugin activation routine

= 2.1.9 [2024.11.27] =
* Cleanup routine moved up one level; Defined ATEC_WP_MEMORY_ADMIN_BAR

= 2.1.8 [2024.11.23] =
* Fixed admin Flush button

= 2.1.7 [2024.11.22] =
* Optimized atec-*-install.php routine

= 2.1.6 [2024.11.21] =
* JIT issue fixed

= 2.1.5 [2024.11.21] =
* Improved OPC stats

= 2.1.3, 2.1.4 [2024.11.18] =
* if (file_exists($include)) @include_once($include);
* ob_flush() issue

= 2.1.1, 2.1.2 [2024.11.17] =
* APCu help und persisten OC test

= 2.1 [2024.11.13] =
* advanced cache, fixed atec_wpca_delete_page_cache_all()

= 2.0.12 [2024.10.24] =
* disabled_functions

= 2.0.11 [2024.10.10] =
* $_POST

= 2.0.10 [2024.10.09] =
* new translation

= 2.0.5, 2.0.6, 2.0.7, 2.0.8, 2.0.9 [2024.10.03] =
* new object-cache

= 2.0.3, 2.0.4 [2024.10.01] =
* atec_wpca fix
* inc/dec fix

= 2.0, 2.0.1, 2.0.2 [2024.09.29] =
* new object-cache
* fixed page_id=0
* OC update notice

= 1.9.7 [2024.09.23] =
* skip Woo pages

= 1.9.6 [2024.09.17] =
* flush "plugins" cache

= 1.9.5 [2024.09.05] =
* Removed plugin install feature

= 1.9.4 [2024.08.26] =
* OPC info

= 1.9.2, 1.9.3 [2024.08.21] =
* framework changes

= 1.8.9, 1.9.1 [2024.08.13] =
* new pcache (gzip) and zlib error protection

= 1.8.9, 1.9.0 [2024.08.08] =
* license code, cache fix

= 1.8.8 [2024.07.29] =
* inline_style

= 1.8.7 [2024.07.23] =
* pcache_delete_all

= 1.8.3, 1.8.4 [2024.07.23] =
* x-cache, tags

= 1.8.2 [2024.07.20] =
* bug fix

= 1.7.6, 1.8.1 [2024.07.18] =
* feeds, auto salt, bug fix

= 1.7.5 [2024.07.16] =
* create/delete category

= 1.7.4 [2024.07.05] =
* salt

= 1.6.9, 1.7, 1.7.2 [2024.07.02] =
* wp_cache_set

= 1.6.7 [2024.06.26] =
* deploy

= 1.6.5, 1.6.6 [2024.06.20] =
* update

= 1.6.4 [2024.06.16] =
* update

= 1.6.3 [2024.06.10] =
* no more submenu

= 1.6, 1.6.1 [2024.06.08] =
* bug fix

= 1.5.8, 1.5.9 [2024.06.07] =
* atec-check

= 1.5.7 [2024.06.06] =
* atec-check

= 1.5.6 [2024.06.05] =
* WP 6.5.4 approved

= 1.5.5 [2024.06.01] =
* max_accelerated_files, interned_strings_buffer, revalidate_freq

= 1.5.4 [2024.05.30] =
* del PCcache

= 1.5.3 [2024.05.27] =
* push update

= 1.5.2 [2024.05.23] =
* new PCache key handling
* translation

= 1.5.1 [2024.05.23] =
* PCache show debug
* Cache product pages

= 1.5 [2024.05.22] =
* PCache fix

= 1.4.9 [2024.05.21] =
* false|string

= 1.4.8 [2024.05.18] =
* x-cache-enabled

= 1.4.6, 1.4.7 [2024.05.17] =
* new install routine, bug fix

= 1.4.3, 1.4.4, 1.4.5 [2024.05.14] =
* new atec-wp-plugin-framework
* new object_cache.php, Version: 1.2

= 1.4.1 [2024.05.03] =
* optimized

= 1.4.0 [2024.04.29] =
* register_activation_hook

= 1.3.5 [2024.04.14] =
* server info

= 1.3.4 [2024.04.02] =
* bug fix

= 1.3.3 [2024.04.01] =
* requestUrl | port

= 1.3.1, 1.3.2 [2024.03.29] =
* OPcache bug fix

= 1.3.0 [2024.03.28] =
* tabs

= 1.2.9 [2024.03.27] =
* new grid

= 1.2.8 [2024.03.24] =
* admin menu atec group

= 1.2.7 [2024.03.23] =
* PCache bug fix, PCache always gzip

= 1.2.6 [2024.03.21] =
* check boxes

= 1.2.5 [2024.03.19] =
* APCu flush improved

= 1.2.4 [2024.03.15] =
* new atec-style

= 1.2.3 [2024.03.13] =
* changes according to plugin check

= 1.2, 1.2.2 [2024.02.23] =
* new options

= 1.2, 1.2.1 [2024.02.22] =
* fixed install

= 1.1.6 [2024.02.22] =
* fixed settings

= 1.1.4, 1.1.5 [2024.02.21] =
* fixed minify, page cache

= 1.1.2, 1.1.3 [2024.02.20] =
* fixed URL bug

= 1.1.1 [2023.09.14] =
* woocommerce Styles

= 1.1 [2023.07.21] =
* Tested with WP 6.3

= 1.1 [2023.05.07] =
* Changes requested by WordPress.org review team

= 1.0 [2023.04.07] =
* Initial Release
