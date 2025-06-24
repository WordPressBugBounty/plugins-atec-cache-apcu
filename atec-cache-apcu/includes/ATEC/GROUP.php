<?php
/**
* Manages metadata about all atec Plugins as a centralized registry.
*
* This class provides static access to a predefined list of plugin metadata objects,
* including information such as slug, name, description, PRO status, WP approval, and multisite support.
*
*/
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\INIT;

final class GROUP {

// Static array to store Plugin objects (using stdClass)
public static $plugins = [];
private static $initialized = false;

// Static constructor to populate the plugins array (only if not already initialized)
private static function init() 
{
	if (self::$initialized) return;
	self::$initialized = true;

	self::$plugins = [
		self::create_plugin('wpas', 'anti-spam', 'Privacy-first spam protection', '5 extra defense layers', false, true),
		self::create_plugin('wpau', 'auth-keys', 'Randomize wp-config.php keys', 'PRO', false, true),
		self::create_plugin('wpb', 'backup', 'Fast, reliable backup & restore', 'FTP & SSH storage', true, false),
		self::create_plugin('wpbn', 'banner', 'Temporary site banner', '', false, true),
		self::create_plugin('wpbu', 'bunny', 'Light BunnyCDN integration', 'PRO', false, true),

		self::create_plugin('wpc', 'code', 'Custom PHP snippets', 'Add & manage snippets', false, true),
		self::create_plugin('wpca', 'cache-apcu', 'APCu page & object cache', 'Advanced page cache', true, true),
		self::create_plugin('wpci', 'cache-info', 'OPcache, Object-Cache, JIT info', 'PHP extension overview', true, true),
		self::create_plugin('wpcm', 'cache-memcached', 'Memcached Object Cache', 'PRO', false, true),
		self::create_plugin('wpco', 'config', 'Lightweight toolkit for WP config & cleanup', 'Performance & WooCommerce optimizations', false, true),

		self::create_plugin('wpcr', 'cache-redis', 'Redis Object Cache', 'PRO', false, true),
		self::create_plugin('wpdb', 'database', 'Optimize and clean up DB', 'Posts, comments, transients', true, true),
		self::create_plugin('wpd', 'debug', 'Debug log in admin bar', 'Queries, includes, cron jobs', true, true),
		self::create_plugin('wpdp', 'deploy', 'Install & auto-update atec plugins', '', false, true),
		self::create_plugin('wpdv', 'developer', 'Debug toolbox for developers', 'PRO', false, true),

		self::create_plugin('wpds', 'dir-scan', 'Scan folders by size and count', 'Deep folder scan', true, true),
		self::create_plugin('wpfd', 'flush-debug', 'Admin bar „debug.log” trash button', 'PRO', false, true),
		self::create_plugin('wpf', 'forms', 'Lightweight, flexible form builder with shortcode', 'PRO', false, true),
		self::create_plugin('wpdpp', 'duplicate-page-post', 'Duplicate posts/pages easily', '', false, true),
		self::create_plugin('wphi', 'hook-inspector', 'Logs hook timings for profiling', 'PRO', false, true),
		self::create_plugin('wpht', 'htaccess', 'Optimize .htaccess for speed', 'PRO', false, true),

		self::create_plugin('wplu', 'login-url', 'Custom login URL', 'Brute-force protection', false, true),
		self::create_plugin('wpll', 'limit-login', 'Limit login attempts', 'Attack stats', false, true),
		self::create_plugin('wpmtm', 'maintenance-mode', '1-click visitor lockout', '', false, true),
		self::create_plugin('wpm', 'meta', 'Custom meta tags per page', 'Auto description tags', false, true),
		self::create_plugin('wpmi', 'migrate', 'Full site migration', 'FTP Upload & Migration', false, true),

		self::create_plugin('wpocb', 'oc-benchmark', 'Object Cache Benchmark', 'PRO', false, true),
		self::create_plugin('wppp', 'page-performance', 'Measure PageScore & SpeedIndex', 'PRO', false, true),
		self::create_plugin('wppo', 'poly-addon', 'Polylang string overrides', 'PRO', false, false),
		self::create_plugin('wppr', 'profiler', 'Plugin/theme performance', 'Page timing & queries', true, true),
		self::create_plugin('wprt', 'runtime', 'Runtime stats in admin bar', 'PRO', false, true),

		self::create_plugin('wpsr', 'search-replace', 'Search & replace in DB', 'PRO', false, true),
		self::create_plugin('wpsmc', 'server-monitor', 'Site availability check', 'PRO', false, true),
		self::create_plugin('wpsm', 'smtp-mail', 'Custom SMTP for wp_mail', 'DKIM & spam test', false, true),
		self::create_plugin('wps', 'stats', 'Lightweight, GDPR-safe stats', 'World map view', true, true),
		self::create_plugin('wpsi', 'system-info', 'Full server/system info', 'PHP, OS, config files', true, true),

		self::create_plugin('wpsv', 'svg', 'Enable SVG uploads', '', false, true),
		self::create_plugin('wpta', 'temp-admin', 'Temporary admin accounts', '', false, true),
		self::create_plugin('wpur', 'user-roles', 'Manage user roles/caps', 'View and edit users', false, true),
		self::create_plugin('wpwms', 'web-map-service', 'Privacy-safe web maps', 'atecmap.com API discount', true, true),
		self::create_plugin('wpwp', 'webp', 'Auto-convert images to WebP', 'PNG, GIF, BMP support', true, true),

		self::create_plugin('wpct', 'cache-tune', 'Smart WooCommerce caching', 'Redis, Memcached, tuning', false, true),
		self::create_plugin('wpmc', 'mega-cache', 'Ultra-fast page cache', '8 storage types + Woo support', true, true),
	];
}

// Static method to get all plugins
public static function all_plugins() 
{
	self::init(); // Ensure plugins are initialized
	return self::$plugins;
}

public static function slug_by_plugin($plugin)	// required by self::slug_by_dir
{
	$plugin = str_replace('atec-', '', $plugin);
	self::init(); // Ensure plugins are initialized
	foreach (self::$plugins as $p) 
		if ($p->name === $plugin) return $p->slug;
	return null; // Return null if plugin is not found
}

public static function plugin_by_slug($slug)	// required by INIT::admin_notice
{
	self::init(); // Ensure plugins are initialized
	foreach (self::$plugins as $p) 
		if ($p->slug === $slug) return $p->name;
	return null; // Return null if plugin is not found
}

public static function slug_by_dir($dir)	// required by plugin_settings
{
	$plugin = INIT::plugin_by_dir($dir);
	return self::slug_by_plugin($plugin);
}

public static function is_plugin_approved($slug) 
{
	self::init(); // Ensure plugins are initialized
	foreach (self::$plugins as $p) 
		if ($p->slug === $slug && $p->wp) return true; // Plugin is approved
	return false; // Plugin is not approved
}

// Helper function to create a plugin using stdClass
public static function create_plugin($slug, $name, $desc, $pro, $wp, $multi) {
	$plugin = new \stdClass();
	$plugin->slug = $slug;
	$plugin->name = $name;
	$plugin->desc = $desc;
	$plugin->pro = $pro;
	$plugin->wp = $wp;
	$plugin->multi = $multi;
	return $plugin;
}

}
?>