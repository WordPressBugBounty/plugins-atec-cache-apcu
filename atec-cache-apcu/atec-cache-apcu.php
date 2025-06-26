<?php
/**
* Plugin Name:  atec Cache APCu
* Plugin URI: https://atecplugins.com/
* Description: Super fast APCu-Object-Cache and the only APCu based page-cache plugin available.
* Version: 2.3.15
* Requires at least:4.9
* Tested up to: 6.8
* Tested up to PHP: 8.4.2
* Requires PHP: 7.4
* Requires CP: 1.7
* Premium URI: https://atecplugins.com
* Author: Chris Ahrweiler ℅ atecplugins.com
* Author URI: https://atec-systems.com/
* License: GPL2
* License URI:  https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:  atec-cache-apcu
*/
namespace ATEC;

defined('ABSPATH') || exit;
if (!defined('ATEC_LOADER')) { require __DIR__ . '/includes/ATEC/LOADER.php'; }

use ATEC\INIT;
use ATEC\WPC;
use ATEC\WPCA;

INIT::set_version('wpca', '2.3.15');

if (INIT::is_real_admin())
{
	INIT::register_activation_deactivation_hook(__FILE__, 1, 1, 'wpca');
	add_action('admin_menu', fn() => INIT::menu(__DIR__, 'wpca', 'Cache APCu'));
	
	add_action('admin_init', function ()
	{
		if (!INIT::current_user_can('admin')) return;
		
		if (INIT::is_plugins_page()) INIT::add_plugin_settings(__FILE__);
		INIT::maybe_register_settings(__DIR__, 'wpca', true, '&flushWPCA');

		if (WPCA::apcu_enabled())
		{
			$o_admin = WPCA::settings('o_cache') && WPCA::settings('o_admin');
			$p_admin = WPCA::settings('p_cache') && WPCA::settings('p_admin');

			if ($o_admin || $p_admin)
			{
				add_action('admin_bar_menu', function($wp_admin_bar) use($o_admin, $p_admin)
				{ 
					if ($o_admin) INIT::admin_bar($wp_admin_bar, 'wpca', 'flush&type=WP', 'Cache', 'oc', 'OC#Flush object cache.', true);
					if ($p_admin) INIT::admin_bar($wp_admin_bar, 'wpca', 'flush', 'Page_Cache', 'pc', 'PC#Flush page cache.', true);
				}, 999);
			}
		}
	});

	(function() {
		
		if (WPCA::apcu_enabled())
		{
			$p_cache = WPCA::settings('p_cache');
			if ($p_cache)
			{
				require __DIR__.'/includes/atec-wpca-pcache-tools.php';
			
				foreach (['after_switch_theme', 'wp_ajax_edit_theme_plugin_file', 'wp_update_nav_menu', 'wp_delete_nav_menu'] as $hook) 
					add_action($hook, function() { \ATEC_WPCA\Tools::delete_page_cache_all(); });
			
				foreach (['create_category', 'delete_category'] as $hook) 
					add_action($hook, [\ATEC_WPCA\Tools::class, 'on_category_change'], 10, 1);
					
				add_action('edited_category', [\ATEC_WPCA\Tools::class, 'on_category_edit'], 10, 2);

				foreach (['created_term', 'delete_term'] as $hook)
					add_action($hook, [\ATEC_WPCA\Tools::class, 'on_tag_change'], 10, 3);	

				add_action('edited_terms', [\ATEC_WPCA\Tools::class, 'on_tag_edit'], 10, 3);
			}
			
			if ($p_cache || WPCA::settings('o_cache'))
			{
				foreach (['activated_plugin', 'deactivated_plugin', 'upgrader_pre_install'] as $hook) 
				{ 
					add_action($hook, function() use($p_cache)
					{ 
						WPC::flush_wp_cache_options();
						if ($p_cache) \ATEC_WPCA\Tools::delete_page_cache_all();
					}); 
				}
			}
	
		}
		
	})();
	
	if (defined('ATEC_OC_ACTIVE_APCU') && defined('ATEC_OC_VERSION') && ATEC_OC_VERSION!== '2.0.4')
	{
		require(__DIR__.'/includes/atec-wpca-install-ocache.php');
		\ATEC_WPCA\Install_OCache::init(true);
	}
}
else // not is_real_admin
{
	if (INIT::is_interactive())
	{
		if (!defined('ATEC_PC_ACTIVE_APCU') && WPCA::settings('p_cache')) 
		{ 
			require(__DIR__.'/includes/atec-wpca-pcache.php');
			add_action('init', ['\ATEC_WPCA\\PCache', 'init'], -1); 
		}
	}
}

if (!INIT::is_cli() && !INIT::is_cron())
{ if (WPCA::apcu_enabled() && WPCA::settings('p_cache')) require(__DIR__.'/includes/atec-wpca-pcache-comments.php'); }

if (INIT::is_interactive())
{ if (defined('ATEC_OC_ACTIVE_APCU') && WPCA::settings('o_stats')) register_shutdown_function([WPCA::class, 'o_cache_stats']); }

?>