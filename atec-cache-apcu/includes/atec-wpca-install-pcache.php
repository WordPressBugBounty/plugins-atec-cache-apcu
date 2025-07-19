<?php
namespace ATEC_WPCA;
defined('ABSPATH') || exit;

use ATEC\FS;
use ATEC\INIT;
use ATEC\TOOLS;
use ATEC\WPCA;

final class Install_PCache {
	
public static function init($p_cache)
{
	
	// Cleanup old version 2.1
	FS::unlink(WPMU_PLUGIN_DIR.'/@atec-wpca-adv-page-cache-pro.php');

	$update = false;
	$lastOptName = 'atec_WPCA_settings_last';
	$last_settings = get_option($lastOptName,[]);
	
	$delete_pc = WPCA::settings('p_debug') !== INIT::bool($last_settings['p_debug'] ?? 0);
	$install_path = dirname(__DIR__).'/install/page-cache.php';
	
	$error_str = '‘advanced-cache.php’';
	$target_path = WP_CONTENT_DIR.'/advanced-cache.php';
	$content = FS::get($target_path);

	if ($content && (!(str_contains($content, 'atec-apcu-page-cache') || str_contains($content, 'atec-cache-apcu-adv-page-cache'))))
	{ return 'Another '.$error_str.' already exists - please deactivate it first'; }
		
	if ($p_cache) 
	{
		// Advanced cache is a PRO feature. Things must be handled here in case of PRO status being changed.
		if (TOOLS::pro_license('wpca'))
		{
			if (!FS::copy($install_path, $target_path)) return 'Installing '.$error_str.' failed.';
		}
	}
	else 
	{ 
		if (!FS::unlink($target_path)) return 'Removing '.$error_str.' failed';
		else $delete_pc = true;
	}
	
	TOOLS::lazy_require_class(__DIR__, 'atec-set-wp-cache.php', 'set_WP_Cache', $p_cache, 'atec-cache-apcu');

	if ($delete_pc)
	{
		if (!class_exists('ATEC_WPCA\\Tools')) require(__DIR__.'/atec-wpca-pcache-tools.php');
		\ATEC_WPCA\Tools::delete_page_cache_all();
	}
	
	return '';
}

}
?>