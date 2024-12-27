<?php
if (!defined('ABSPATH')) { exit; }

global $wp_filesystem; WP_Filesystem();
if (defined('WP_APCU_KEY_SALT'))
{
	if (function_exists('apcu_clear_cache')) { apcu_clear_cache(); }
    $wp_filesystem->delete(WP_CONTENT_DIR.'/object-cache.php'); 
}
$MU_advanced_cache_path=WPMU_PLUGIN_DIR.'/@atec-wpca-adv-page-cache-pro.php';
$wp_filesystem->delete($MU_advanced_cache_path); 
?>