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
		self::create_plugin('wp4t', '404-tracker', 'Lightweight 404 logger for broken links', 'FREE', false, true),				// new TOOLS
		self::create_plugin('wpas', 'anti-spam', 'Privacy-first spam protection', '5 extra defense layers', false, true),			// new TOOLS
		self::create_plugin('wpau', 'auth-keys', 'Randomize wp-config.php keys', 'PRO', false, true),			// new TOOLS
		self::create_plugin('wpav', 'avatar', 'Set a custom avatar for any WordPress user', 'PRO', false, true),			// new TOOLS
		self::create_plugin('wpb', 'backup', 'Fast, reliable backup & restore', 'FTP & SSH storage', true, false),
		
		self::create_plugin('wpbn', 'banner', 'Temporary site banner', 'FREE', false, true),			// new TOOLS
		self::create_plugin('wpbs', 'bot-shield', 'Early-stage bot shield to block bad actors', 'PRO', false, true),				// new TOOLS
		self::create_plugin('wpbu', 'bunny', 'Light BunnyCDN integration', 'PRO', false, true),			// new TOOLS
		self::create_plugin('wpc', 'code', 'Custom PHP snippets', 'Add & manage snippets', false, true),
		self::create_plugin('wpca', 'cache-apcu', 'APCu page & object cache', 'Advanced page cache', true, true),

		//10
		self::create_plugin('wpci', 'cache-info', 'OPcache, Object-Cache, JIT info', 'PHP extension overview', true, true),
		self::create_plugin('wpcm', 'cache-memcached', 'Memcached Object Cache', 'PRO', false, true),	
		self::create_plugin('wpco', 'config', 'Lightweight toolkit for WP config & cleanup', 'Performance & WooCommerce optimizations', false, true),			// new TOOLS
		self::create_plugin('wpcr', 'cache-redis', 'Redis Object Cache', 'PRO', false, true),
		self::create_plugin('wpdb', 'database', 'Optimize and clean up DB', 'Posts, comments, transients', true, true),

		self::create_plugin('wpd', 'debug', 'Debug log in admin bar', 'Queries, includes, cron jobs', true, true),	
		self::create_plugin('wpdp', 'deploy', 'Install & auto-update atec plugins', 'FREE', false, true),				// new TOOLS|SKIPPED
		self::create_plugin('wpdv', 'developer', 'Debug toolbox for developers', 'PRO', false, true),				// new TOOLS
		self::create_plugin('wpds', 'dir-scan', 'Scan folders by size and count', 'FREE', true, true),				// new TOOLS|SKIPPED
		self::create_plugin('wpfd', 'flush-debug', 'Admin bar „debug.log” trash button', 'PRO', false, true),				// new TOOLS

		//20
		self::create_plugin('wpf', 'forms', 'Lightweight, flexible form builder with shortcode', 'PRO', false, true),				// new TOOLS
		self::create_plugin('wpfc', 'fake-content', 'Generates fake posts, pages, users, and images for testing', 'PRO', false, true),				// new TOOLS
		self::create_plugin('wpff', 'foxyfy', 'FoxyFy CDN integration', 'PRO', false, true),			// new TOOLS
		self::create_plugin('wpdpp', 'duplicate-page-post', 'Duplicate posts/pages easily', 'FREE', false, true),				// new TOOLS
		self::create_plugin('wphi', 'hook-inspector', 'Logs hook timings for profiling', 'PRO', false, true),			// new TOOLS

		self::create_plugin('wpht', 'htaccess', '.htaccess file editor', '', false, true),			// new TOOLS
		self::create_plugin('wplalo', 'lazy-load', 'Lightweight lazy loader for images, iframes, and videos', 'PRO', false, true),				// new TOOLS
		self::create_plugin('wplu', 'login-url', 'Custom login URL', 'Brute-force protection', false, true),				// new TOOLS
		self::create_plugin('wpll', 'limit-login', 'Limit login attempts', 'Attack stats', false, true),				// new TOOLS
		self::create_plugin('wpmcl', 'media-cleaner', 'Clean up unused media files', 'Full media scanning', false, true),				// new TOOLS

		// 30
		self::create_plugin('wpmi', 'migrate', 'Full site migration', 'FTP Upload & Migration', false, true),
		self::create_plugin('wpmin', 'minify', 'Smart minifier for CSS and JS files', 'PRO', false, true),
		self::create_plugin('wpmt', 'meta-tags', 'Custom meta tags per page', 'Auto description tags', false, true),			// new TOOLS
		self::create_plugin('wpmtm', 'maintenance-mode', '1-click visitor lockout', 'FREE', false, true),			// new TOOLS
		self::create_plugin('wpocb', 'oc-benchmark', 'Object Cache Benchmark', 'PRO', false, true),			// new TOOLS

		self::create_plugin('wppp', 'page-performance', 'Measure PageScore & SpeedIndex', 'PRO', false, true),			// new TOOLS
		self::create_plugin('wppo', 'poly-addon', 'Polylang string overrides', 'PRO', false, false),				// new TOOLS|SKIPPED
		self::create_plugin('wppr', 'profiler', 'Plugin/theme performance', 'Page timing & queries', true, true),
		self::create_plugin('wprd', 'redirect', 'Create and manage redirects with ease', 'Support for wildcard and regex-based rules', false, true),
		self::create_plugin('wprt', 'runtime', 'Runtime stats in admin bar', 'PRO', false, true),				// new TOOLS

		// 40
		self::create_plugin('wps', 'stats', 'Lightweight, GDPR-safe stats', 'World map view', true, true),
		self::create_plugin('wpsi', 'system-info', 'Full server/system info', 'PHP, OS, config files', true, true),
		self::create_plugin('wpsm', 'smtp-mail', 'Custom SMTP for wp_mail', 'DKIM & spam test', false, true),
		self::create_plugin('wpsmc', 'server-monitor', 'Site availability check', 'PRO', false, true),				// new TOOLS|SKIPPED
		self::create_plugin('wpsmx', 'sitemap', 'Generates a static sitemap.xml and serves it via PHP redirect.', 'PRO', false, true),		// new TOOLS|SKIPPED

		self::create_plugin('wpsr', 'search-replace', 'Search & replace in DB', 'PRO', false, true),				// new TOOLS|SKIPPED
		self::create_plugin('wpssl', 'ssl', 'Enforce HTTPS, fix SSL issues and detect mixed content', 'Fix mixed content', false, true),				// new TOOLS
		self::create_plugin('wpsv', 'svg', 'Enable SVG uploads', '', false, true),				// new TOOLS
		self::create_plugin('wpta', 'temp-admin', 'Temporary admin accounts', 'PRO', false, true),
		self::create_plugin('wpur', 'user-roles', 'Manage user roles/caps', 'View and edit users', false, true),

		// 50
		self::create_plugin('wpva', 'virtual-author', 'Adds a virtual author dropdown to posts', 'PRO', false, true),				// new TOOLS|SKIPPED
		self::create_plugin('wpwms', 'web-map-service', 'Privacy-safe web maps', 'atecmap.com API discount', true, true),				// new TOOLS|SKIPPED
		self::create_plugin('wpwp', 'webp', 'Auto-convert images to WebP', 'PNG, GIF, BMP support', true, true),				// new TOOLS|SKIPPED
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