=== atec Cache APCu ===
Contributors: DocJoJo
Tags: apcu, object cache, page cache, performance, persistent cache
Requires CP: 1.7
Tested up to: 6.8
Requires at least:4.9
Requires PHP: 7.4
Tested up to PHP: 8.4.5
Stable tag: 2.3.14
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Super fast APCu-based Object Cache and the only APCu-powered Page Cache plugin for WordPress.

== Description ==

<code>atec Cache APCu</code> provides a drop-in object-cache and optional page cache, built entirely on APCu.

It replaces WordPress’s core cache with a fast, persistent alternative — offering major performance gains, especially on single-server setups.

APCu is faster than Redis and Memcached in low-latency scenarios. This plugin uses advanced logic that improves object cache efficiency by up to 16.67%.

The optional page cache is the only APCu-powered full-page cache for WordPress, and includes cache exclusion rules, cache flushing and profiler/debugging options.

=== Specifications ===

* Size: only 160 KB
* CPU footprint (idle): <5 ms.
* Includes object cache drop-in and optional full page cache  
* Profiler and debug options for diagnostics

== Requirements ==

* APCu extension enabled  

== Third-Party Services ==

= Integrity check =

Once, when activating the plugin, an integrity check is requested from our server – if you give your permission.
Source: https://atecplugins.com/
Privacy policy: https://atecplugins.com/privacy-policy/

== Installation ==

1. Upload the plugin to <code>/wp-content/plugins/</code> or install via the WP admin panel.  
2. Activate the plugin from the Plugins menu.
3. Select "atec Cache APCu" link in admin menu bar.
4. Enable Object-Cache and Page-Cache in the settings panel.

== Frequently Asked Questions ==

– What is Object caching?
Object caching involves storing variables and database queries thereby speeding up PHP execution times. This reduces the load on your server, and delivers content to your visitors faster.

– What is Page caching?
Page caching refers to caching the content of a whole page on the server-side. Later when the same page is requested again, its content will be served from the cache instead of regenerating it from scratch.

- Does the object-cache also work in WP-CLI?
If you use WP-CLI to run .php scripts, please set "apc.enable_cli=1" in your php.ini – otherwise object-cache will not work.

- Is the page cache multisite compatibel?
This plugin isn’t optimized for multisite environments. Since multisite setups require incorporating a blog ID — a feature not supported by this plugin — we recommend using our "mega-cache" plugin instead.

- Is the page cache multi-language compatibel?
This depends on the translation plugin being used. When translations are performed on the fly, the page or post ID remains unchanged, preventing "atec-cache-apcu" from detecting any differences. Please use our "mega-cache" plugin instead.

= Does this work with WooCommerce? =
Partly. Cart, checkout, and account pages are automatically excluded from the page cache.

= Will this work on shared hosting? =
Ye – if APCu is available. It is ideal for VPS or dedicated servers.

= Can I use Redis or Memcached with this? =
No. This plugin is APCu-only and does not require other memory caches.

= What makes this faster than other solutions? =
It uses pure APCu without network latency, with optimized logic for set/get/flush and auto-purging support.

== „PRO“ Features ==

- AOC Mode (Advanced Object Cache)
Advanced Object Cache Mode – takes full advantage of APCu’s in-memory array support, eliminating unnecessary serialize()/unserialize() cycles and boosting PHP performance on every request.

- APC Mode (Advanced Page Cache)
Advanced Page Cache is a „PRO“-level optimization that activates earlier than regular page cache — before most WordPress logic even runs.

== Screenshots ==

1. Settings
2. Cache Info
3. Server Info
4. Persistent Object Cache Groups
5. Page Cache Overview
6. Cache comparison (APCu, Redis, Memcached)

== Changelog ==

= 2.3.14 [2025.06.26] =
* Removed safe_unserialize and improved serializer handling

= 2.3.13 [2025.06.25] =
* Cleanup old MU ADV CACHE

= 2.3.12 [2025.06.25] =
* Fixed delete PC item

= 2.3.11 [2025.06.25] =
* Cleanup PC for v2.1

= 2.3.10 [2025.06.25] =
* Flushing opcache on install

= 2.3.9 [2025.06.25] =
* New PC install script and wp-config

= 2.3.8 [2025.06.24] =
* Fixed PC Flush and old OC / PC compatibility

= 2.3.7 [2025.06.24] =
* Framework change | License check improved

= 2.3.5 [2025.06.19] =
* Fixed LOADER for windows

= 2.1.97 [2025.04.30] =
* AWF now fully namespaced
* Minor fixes and profiler tweak

= 2.1.95 [2025.04.23] =
* NAMESPACE implemented
* Object cache logger upgrade

= 2.1.94 [2025.04.23] =
* before profiler_debug removed

= 2.1.93 [2025.04.06] =
* Framework change

= 2.1.92 [2025.04.05] =
* alloptions/cron fix

= 2.1.91 [2025.04.03] =
* New FS

= 2.1.89 [2025.03.28] =
* New OC enabling

= 2.1.88 [2025.03.28] =
* Always save settings

= 2.1.86 [2025.03.16] =
* New style.css and check.css

= 2.1.85 [2025.03.14] =
* wp_redirect(admin_url()

= 2.1.84 [2025.03.07] =
* Removed redirect

= 2.1.83 [2025.03.07] =
* OC 1.0.21

= 2.1.82 [2025.03.07] =
* define(\'ATEC_APCU_OC_VERSION\',\'1.0.16\');

= 2.1.81 [2025.03.07] =
* Fixed object caching (unserialize)

= 2.1.80 [2025.03.07] =
* Fixed post_type

= 2.1.79 [2025.03.04] =
* Framework changes

= 2.1.78 [2025.03.03] =
* New OC install routine

= 2.1.76 [2025.02.28] =
* atec_wpca_oc_stats

= 2.1.75 [2025.02.26] =
* add_action(\'init\', function() { require(\'atec-cache-apcu-pcache-cb.php\'); 

= 2.1.74 [2025.02.24] =
* Fixed callback

= 2.1.73 [2025.02.23] =
* alloptions unserialize

= 2.1.72 [2025.02.23] =
* Flush Cache Indicator

= 2.1.71 [2025.02.22] =
* Flush alloptions

= 2.1.70 [2025.02.22] =
* wp_cache flush

= 2.1.69 [2025.02.20] =
* Remove OCache cleanup

= 2.1.68 [2025.02.15] =
* (function() {

= 2.1.67 [2025.02.14] =
* $wp_query

= 2.1.66 [2025.02.14] =
* Fixed $atec_wpca_pcache_params

= 2.1.65 [2025.02.14] =
* Fixed the fix routine

= 2.1.64 [2025.02.14] =
* Improved cache check

= 2.1.63 [2025.02.13] =
* Base on send_headers

= 2.1.62 [2025.02.13] =
* Minor fix

= 2.1.61 [2025.02.10] =
* New atec-fs filesystem

= 2.1.60 [2025.02.07] =
* OC fix pre_update_option

= 2.1.59 [2025.02.05] =
* Settings fixed

= 2.1.58 [2025.02.05] =
* New flushing

= 2.1.57 [2025.02.05] =
* Verify cache settings

= 2.1.56 [2025.02.04] =
* atec_warning_msg

= 2.1.55 [2025.02.03] =
* Spanish translation

= 2.1.53 [2025.02.03] =
* includes/atec-cache-apcu-pcache-tools.php

= 2.1.52 [2025.02.03] =
* Updated atec-check.js

= 2.1.51 [2025.02.02] =
* Russian translation updated

= 2.1.50 [2025.02.02] =
* French translation by Stephane

= 2.1.47 [2025.02.02] =
* Framework changes (atec-check)

= 2.1.46 [2025.02.02] =
* Added settings sanitizing

= 2.1.44 [2025.01.29] =
* Fixed wp_cache_flush

= 2.1.43 [2025.01.29] =
* define(\'ATEC_TOOLS_INC\',true); // just for backwards compatibility

= 2.1.42 [2025.01.27] =
* Flush Icon in admin bar for OC & PC

= 2.1.41 [2025.01.26] =
* Fixed require path

= 2.1.40 [2025.01.26] =
* switched require_once -> require

= 2.1.39 [2025.01.26] =
* atec-check issue?

= 2.1.38 [2025.01.26] =
* ATEC_WPcache_info

= 2.1.37 [2025.01.22] =
* wp_cache flush

= 2.1.36 [2025.01.21] =
* Sort group list 

= 2.1.35 [2025.01.18] =
* Optimized APCu Info

= 2.1.34 [2025.01.17] =
* Check button replaced

= 2.1.32 [2025.01.17] =
* new atec-check

= 2.1.31 [2025.01.17] =
* Fixed invalid header

= 2.1.30 [2025.01.16] =
* Translation update

= 2.1.29 [2025.01.16] =
* New object cache activation

= 2.1.28 [2025.01.16] =
* SVN cleanup

= 2.1.27 [2025.01.16] =
* German translation

= 2.1.26 [2025.01.10] =
* 	wp_cache_delete(\'active_plugins\',\'options\');

= 2.1.25 [2025.01.06] =
* Removed server-info and memory-info

= 2.1.24 [2025.01.06] =
* Optimized install routine

= 2.1.23 [2025.01.05] =
* Fixed APcu Groups

= 2.1.22 [2024.12.30] =
* Fixed ATEC_OC_KEY_SALT

= 2.1.21 [2024.12.27] =
* Advanced page cache

= 2.1.20 [2024.12.24] =
* Fixed style sheet

= 2.1.19 [2024.12.21] =
* Clean up

= 2.1.17 [2024.12.21] =
* New styles, cleaned up .svg

= 2.1.16 [2024.12.17] =
* On plugin change: wp_cache_delete(\'plugins\',\'plugins\');

= 2.1.15 [2024.12.12] =
* Toogle admin bar – improved

= 2.1.14 [2024.12.10] =
* atec_wpca_delete_wp_cache

= 2.1.13 [2024.12.09] =
* if (!class_exists(\'APCUIterator\')) ...

= 2.1.12 [2024.12.07] =
* Optional flush button

= 2.1.11 [2024.12.07] =
* Toogle admin bar display

= 2.1.10 [2024.11.27] =
* Improved plugin activation routine

= 2.1.9 [2024.11.27] =
* Cleanup routine moved up one level; Defined ATEC_admin_bar_memory

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

= 1.6.3 [2024.06.10] =
* no more submenu

= 1.6, 1.6.1 [2024.06.08] =
* bug fix

= 1.5.8, 1.5.9 [2024.06.07] =
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
* PCache fix & show debug
* Cache product pages

= 1.4.8 [2024.05.18] =
* x-cache-enabled

= 1.4.6, 1.4.7 [2024.05.17] =
* new install routine, bug fix

= 1.4.3, 1.4.4, 1.4.5 [2024.05.14] =
* new atec-wp-plugin-framework
* new object_cache.php, Version: 1.2

= 1.4.0 [2024.04.29] =
* register_activation_hook

= 1.3.5 [2024.04.14] =
* server info

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
