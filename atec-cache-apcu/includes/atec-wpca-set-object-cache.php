<?php
if (!defined('ABSPATH')) { exit(); }

function atec_wpca_set_object_cache($options)
{ 
	global $wp_filesystem; WP_Filesystem();
	$targetPath 	= WP_CONTENT_DIR.'/object-cache.php';
	$success 		= true;
	$content 		= $wp_filesystem->exists($targetPath)?$wp_filesystem->get_contents($targetPath):'';
	$isOC			= str_contains($content,'atec-apcu-object-cache');

	if ($content!=='' && !$isOC) return 'Another „object-cache.php“ file exists. Please deactivate it first';
	if (filter_var($options['ocache']??0,258)) 
	{ 
		if (!@$wp_filesystem->copy(plugin_dir_path(__DIR__).'install/object-cache.php', $targetPath, true)) return 'Object-Cache installation failed';
	}
	else
	{
		if (!@$wp_filesystem->delete($targetPath)) return 'Removing „object-cache.php“ failed';
		if (class_exists('APCUIterator') && defined('WP_APCU_KEY_SALT'))
		{
			add_action('shutdown', function()
			{
				$apcu_it=new APCUIterator('/'.WP_APCU_KEY_SALT.'/');
				if (iterator_count($apcu_it)!==0) { foreach ($apcu_it as $entry) { error_log($entry['key']); apcu_delete($entry['key']); } }
			});
		}
	}
	return '';
}
?>