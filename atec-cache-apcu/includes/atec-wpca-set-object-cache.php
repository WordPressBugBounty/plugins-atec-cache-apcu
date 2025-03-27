<?php
if (!defined('ABSPATH')) { exit; }

function atec_wpca_set_object_cache($option)
{ 
	if (!class_exists('ATEC_fs')) require('atec-fs.php');
	$afs = new ATEC_fs();

	$targetPath 	= trailingslashit(WP_CONTENT_DIR).'object-cache.php';
	$content 		= $afs->get($targetPath);

	if ($content && !str_contains($content,'atec-apcu-object-cache')) return 'Another „object-cache.php“ file exists. Please deactivate it first';

	if (filter_var($option??0,258)) { if (!$afs->copy(plugin_dir_path(__DIR__).'install/object-cache.php', $targetPath, true)) return 'Object-Cache installation failed'; }
	else
	{ 
		wp_cache_flush(); 
		if (!$afs->unlink($targetPath)) return 'Removing „object-cache.php“ failed';
	}
	return '';
}
?>