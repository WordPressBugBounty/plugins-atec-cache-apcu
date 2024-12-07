<?php
if (!defined('ABSPATH')) { exit; }
class ATEC_wpca_activation { function __construct() {
	
if (!defined('ATEC_TOOLS_INC')) @require_once(__DIR__.'/atec-tools.php');
atec_integrity_check(__DIR__);

if (extension_loaded('apcu') && apcu_enabled())
{
	if (!function_exists('atec_load_pll')) { @require_once('atec-translation.php'); }
	atec_load_pll(__DIR__,'cache-apcu');

	$targetPath 	= WP_CONTENT_DIR.'/object-cache.php';
	
	$install 		= true; 
	$notice 		= [];

	global $wp_filesystem; 	WP_Filesystem();
	atec_mkdir_if_not_exists(WPMU_PLUGIN_DIR);

	if ($wp_filesystem->exists($targetPath)) 
	{
		$content=$wp_filesystem->get_contents($targetPath);
		if (str_contains($content,'https://github.com/l3rady/') || str_contains($content,'https://atecplugins.com/')) { $wp_filesystem->delete($targetPath); }
		else
		{
			$tmp 	= __('Another "object-cache.php" file already exists','atec-cache-apcu');
			atec_notice($notice, 'warning', $tmp);
			$install 	= false;
		}
	}
	
	if ($install)
	{
		if (!$wp_filesystem->copy(plugin_dir_path(__FILE__).'object-cache.php', $targetPath)) 
		{ 
			$tmp 	= __('Object-cache installation failed','atec-cache-apcu');
			atec_notice($notice, 'warning', $tmp);
		}
		else 
		{ 
			$tmp 	= __('Object-cache installation successful','atec-cache-apcu');
			atec_notice($notice, 'info', $tmp);
			if (function_exists('apcu_clear_cache')) { apcu_clear_cache(); }
		}
	}
	
	if (!empty($notice)) update_option( 'atec_wpca_debug', $notice, false);
	
	if ('not-exists' !== get_option('atec_WPCA_p_cache_enabled', 'not-exists' )) 
	{ 
		$args=array('cache','clear','minify','gzip');
		foreach ($args as $arg) { delete_option('atec_WPCA_p_'.$arg.'_enabled'); }
	}
	
	$optName='atec_WPCA_settings';
	$options=atec_create_options($optName,['cache','debug','clear','salt','admin'],['clear','admin']);
	$options['salt']=hash('crc32', get_bloginfo(), FALSE);
	update_option($optName, $options);
}

}} 
new ATEC_wpca_activation();
?>