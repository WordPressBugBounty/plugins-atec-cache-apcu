<?php
if (!defined('ABSPATH')) { exit(); }

function atec_wpca_set_object_cache($options)
{ 
	if (!class_exists('ATEC_fs')) @require('atec-fs.php');
	$afs = new ATEC_fs();

	$targetPath 	= WP_CONTENT_DIR.'/object-cache.php';
	$success 		= true;
	$content 		= $afs->get($targetPath);
	$isOC			= $content && str_contains($content,'atec-apcu-object-cache');

	if ($content && !$isOC) return 'Another „object-cache.php“ file exists. Please deactivate it first';
	if (filter_var($options['ocache']??0,258)) 
	{ 
		if (!$afs->copy(plugin_dir_path(__DIR__).'install/object-cache.php', $targetPath, true)) return 'Object-Cache installation failed'; 
	}
	else
	{
		if (!$afs->unlink($targetPath)) return 'Removing „object-cache.php“ failed';
		if (class_exists('APCUIterator') && defined('WP_APCU_KEY_SALT'))
		{
			add_action('shutdown', function()
			{
				$apcu_it=new APCUIterator('/'.WP_APCU_KEY_SALT.'/');
				if (iterator_count($apcu_it)!==0) { foreach ($apcu_it as $entry) { apcu_delete($entry['key']); } }
			});
		}
	}
	return '';
}
?>