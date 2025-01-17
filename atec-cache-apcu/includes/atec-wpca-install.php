<?php
if (!defined( 'ABSPATH' )) { exit; }
if (!defined('ATEC_TOOLS_INC')) @require_once(__DIR__.'/atec-tools.php');	

add_action( 'admin_enqueue_scripts', function() 
{ 
	atec_reg_style('atec',__DIR__,'atec-style.min.css','1.0.004');

	global $atec_active_slug;
	if ($atec_active_slug!=='atec_group')
	{
		atec_reg_style('atec_check',__DIR__,'atec-check.min.css','1.0.002');
		atec_reg_script('atec_check',__DIR__,'atec-check.min.js','1.0.002');
		
		if (str_contains(atec_query(), 'nav=Cache')) atec_reg_style('atec_cache_info',__DIR__,'atec-cache-info-style.min.css','1.0.001');
	}
});

if ($atec_active_slug!=='atec_group') 
{ 
	function atec_wpca(): void { @require_once(__DIR__.'/atec-cache-apcu-dashboard.php'); }

	if (!function_exists('atec_load_pll')) { @require_once(__DIR__.'/atec-translation.php'); }
	atec_load_pll(__DIR__,'cache-apcu');		

	if (!defined('WP_APCU_KEY_SALT'))
	{
	  	global $atec_wpca_apcu_enabled;
		if (!$atec_wpca_apcu_enabled) atec_new_admin_notice('error','atec-cache-APCu: '.esc_html__('The APCu extension is not enabled but it is required for this plugin to work','atec-cache-apcu').'.');
	}
}
else
{
	if (defined('WP_APCU_KEY_SALT')) 
	{ 
		if (!defined('ATEC_APCU_OC_VERSION') || ATEC_APCU_OC_VERSION!=='1.0.8')
		{ atec_new_admin_notice('error','atec-cache-APCu: '.esc_html__('The „object-cache.php“ is outdated, please deactivate & reactivate this plugin to update the file','atec-cache-apcu').'.'); }
		
		global $atec_wpca_apcu_enabled;
		if (!$atec_wpca_apcu_enabled) 
		{ atec_new_admin_notice('error','atec-cache-APCu: '.esc_html__('APCu was disabled, but „object-cache.php“ is installed – please deactivate this plugin until APCu is re-enabled','atec-cache-apcu').'.'); }
	}
}
?>