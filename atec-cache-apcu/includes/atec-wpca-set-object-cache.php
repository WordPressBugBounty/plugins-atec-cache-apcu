<?php
if (!defined('ABSPATH')) { exit(); }

function atec_wpca_set_object_cache($options)
{ 
	global $wp_filesystem; WP_Filesystem();
	$targetPath 	= WP_CONTENT_DIR.'/object-cache.php';
	
	$success 	= true;
	$content 	= $wp_filesystem->exists($targetPath)?$wp_filesystem->get_contents($targetPath):'';
	$isOC		= $content && str_contains($content,'atec-apcu-object-cache');
	wp_cache_flush();
	if (!filter_var($options['ocache']??0,258)) 
	{
		if ($content)
		{
			if ($isOC) $success = $wp_filesystem->delete($targetPath);
			else $success = false;
			if (!$success) return 'Removing „object-cache.php“ failed';
		}
	}
	else
	{
		if ($content && !$isOC) atec_notice($notice, 'warning', 'Another „object-cache.php“ file already exists. Please deactivate it first');
		else $success = $success && $wp_filesystem->copy(plugin_dir_path(__DIR__).'install/object-cache.php', $targetPath, true);
		$content = $wp_filesystem->get_contents($targetPath);
		$success = $success && str_contains($content,'atec-apcu-object-cache');		
		if (!$success) return 'Object-Cache installation failed';
	}
	return '';
}
?>