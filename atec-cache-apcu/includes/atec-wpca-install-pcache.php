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
	$update = false;

	$lastOptName = 'atec_WPCA_settings_last';
	$last_settings = get_option($lastOptName,[]);
	
	$delete_pc = WPCA::settings('p_debug') !== INIT::bool($last_settings['p_debug'] ?? 0);
	$install_path = dirname(__DIR__).'/install/page-cache.php';
	
	$error_str = '„advanced-cache.php“';
	$target_path = WP_CONTENT_DIR.'/advanced-cache.php';
	$content = FS::get($target_path);

	if ($content && (!str_contains($content, 'atec-apcu-page-cache')))
	{ return 'Another '.$error_str.' already exists - please deactivate it first'; }
		
	if ($p_cache) 
	{
		// Advanced cache is a PRO feature. Things must be handled here in case of PRO status being changed.
		if (TOOLS::pro_license())
		{
			if (!FS::copy($install_path, $target_path)) return 'Installing '.$error_str.' failed.';
		}
	}
	else 
	{ 
		if (!FS::unlink($target_path)) return 'Removing '.$error_str.' failed';
		else $delete_pc = true;
	}

	if ($delete_pc)
	{
		if (!class_exists('ATEC_WPCA\\Tools')) require(__DIR__.'/atec-wpca-pcache-tools.php');
		\ATEC_WPCA\Tools::delete_page_cache_all();
	}
	
	return '';
}

}
?>