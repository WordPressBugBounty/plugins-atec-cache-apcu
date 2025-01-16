<?php
if (!defined( 'ABSPATH' )) { exit; }

function atec_wpca_settings_fields()
{ 
	$page_slug 		= 'atec_WPCA';
    $option_group 	= $page_slug.'_settings';
    $section			= $page_slug.'_section';
	$options			= get_option($option_group,[]);

	if (!defined('ATEC_CHECK_INC')) @require_once('atec-check.php');

	// ** flush the pcache if pcache settings change ** //
	if (str_contains(atec_query(),'settings-updated=true')) 
	{
		if (get_option('atec_wpca_last_cache')!==($options['cache']??false)) 
		{ @require_once(__DIR__.'/atec-cache-apcu-pcache-tools.php'); atec_wpca_delete_page_cache_all(); };
		if (($options['salt']??'')==='') 	{ $options['salt']=hash('crc32', get_bloginfo(), FALSE); update_option($option_group,$options); }
				
		if (atec_check_license())
		{
			global $wp_filesystem; 	WP_Filesystem();
			atec_mkdir_if_not_exists(WPMU_PLUGIN_DIR);
			$atec_wpca_adv_page_cache_filename='atec-wpca-adv-page-cache-pro.php';
			$MU_advanced_cache_path=WPMU_PLUGIN_DIR.'/@'.$atec_wpca_adv_page_cache_filename;

			if ($options['cache']??false) @$wp_filesystem->copy(plugin_dir_path(__DIR__).'includes/'.$atec_wpca_adv_page_cache_filename,$MU_advanced_cache_path);
			else @$wp_filesystem->delete($MU_advanced_cache_path);
		}
	}
	update_option('atec_wpca_last_cache', $options['cache']??false, false);
	
  	register_setting($page_slug,$option_group);
	
  	add_settings_section($section,__('APCu Page Cache','atec-cache-apcu'),'',$page_slug);
	
  	$middot='&middot;&middot;&middot;&#187;&#187; ';
  	add_settings_field('cache', __('Enable page cache','atec-cache-apcu'), 'atec_checkbox', $page_slug, $section, atec_opt_arr('cache','WPCA'));
	  
	$section.='_options';
	add_settings_section($section,__('Page Cache Options','atec-cache-apcu'),'',$page_slug);

  	add_settings_field('debug', $middot.__('Show debug','atec-cache-apcu').'<br>
  	<span style="font-size:80%; color:#999;">'.__('Cache indicator and browser console log','atec-cache-apcu').'.</span>', 'atec_checkbox', $page_slug, $section, atec_opt_arr('debug','WPCA'));
	
  	add_settings_field('clear', $middot.__('Auto clear','atec-cache-apcu').'<br>
  	<span style="font-size:80%; color:#999;">'.__('Clear cache, after plugin & theme changes','atec-cache-apcu').'.</span>', 'atec_checkbox', $page_slug, $section, atec_opt_arr('clear','WPCA'));

	add_settings_field('admin', $middot.__('Show "Flush" icon in admin bar','atec-cache-apcu'), 'atec_checkbox', $page_slug, $section, atec_opt_arr('admin','WPCA'));
}
add_action( 'admin_init',  'atec_wpca_settings_fields' );
?>