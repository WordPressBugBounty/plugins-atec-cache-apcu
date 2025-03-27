<?php
if (!defined('ABSPATH')) { exit; }

(function() {
	if (!class_exists('ATEC_fs')) require('atec-fs.php');
	$afs = new ATEC_fs();
	
	if (defined('WP_APCU_KEY_SALT'))
	{
		wp_cache_flush();
		$afs->unlink(trailingslashit(WP_CONTENT_DIR).'object-cache.php'); 
	}
	$MU_advanced_cache_path=WPMU_PLUGIN_DIR.'/@atec-wpca-adv-page-cache-pro.php';
	$afs->unlink($MU_advanced_cache_path); 
})();
?>