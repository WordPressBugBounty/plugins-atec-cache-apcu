<?php
namespace ATEC_WPCA;
defined('ABSPATH') || exit;

use ATEC\FS;

final class Install_OCache {
	
public static function init($option): string
{
	$target_path = WP_CONTENT_DIR.'/object-cache.php';
	$content = FS::get($target_path);


	if ($content && !(str_contains($content, 'ATEC_OC_ACTIVE_APCU') || str_contains($content, 'atec-apcu-object-cache')))
	{ return 'Another „object-cache.php“ file exists. Please deactivate it first'; }

	if ($option)
	{
		$success = true;
		$install_dir = plugin_dir_path(__DIR__).'install';
		if (
			($objectCache = FS::get($install_dir.'/object-cache.php')) && 
			($objectCacheDriver = FS::get($install_dir.'/object-cache-driver.inc'))
		)
		{
			$objectCacheDriver =  str_replace(["<?php\n",">?"], '', $objectCacheDriver);
			$objectCache = str_replace('/* OC INSERT DRIVER */', $objectCacheDriver, $objectCache);
			if (FS::put($target_path, $objectCache))
			{
				$size = FS::size($target_path);
				$success = $success && is_numeric($size) && $size>0;
				if (!$success) FS::unlink($target_path);
			}
			else $success = false;
		}
		else $success = false;
		if (!$success) return 'Object-Cache installation failed';
	}
	else
	{
		wp_cache_flush();
		if (!FS::unlink($target_path)) return 'Removing „object-cache.php“ failed';
	}
	
	return '';
}

}
?>