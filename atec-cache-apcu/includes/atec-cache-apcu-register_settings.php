<?php
if (!defined( 'ABSPATH' )) { exit; }

function atec_wpca_arr_equal($arr1, $arr2) 
{
	if (!is_array($arr1) || !is_array($arr2)) return false;
	array_multisort($arr1); array_multisort($arr2); return ( serialize($arr1) === serialize($arr2) ); 
}

function atec_wpca_settings_fields()
{ 
	$page_slug 		= 'atec_WPCA';
    $option_group 	= $page_slug.'_settings';
    $section			= $page_slug.'_section';
	$options			= get_option($option_group,[]);

	if (!defined('ATEC_CHECK_INC')) @require('atec-check.php');

	// ** flush the pcache if pcache settings change ** //
	if (str_contains(atec_query(),'settings-updated=true')) 
	{
		$optName = 'atec_wpca_last_cache'; $lastSettings=get_option($optName,[]); update_option($optName,$options,false);

		$atec_wpca_pcache 	= $options['cache']??false==true;
		global $wp_filesystem; WP_Filesystem();

		if ($atec_wpca_pcache!==($lastSettings['cache']??false) || ($options['debug']??false)!==($lastSettings['debug']??false))
		{ 
			if (!defined('ATEC_WPCA_CACHE_TOOLS')) @require(__DIR__.'/includes/atec-cache-apcu-pcache-tools.php');			
			atec_wpca_delete_page_cache_all(); 
		}

		$atec_wpca_adv_page_cache_filename='atec-wpca-adv-page-cache-pro.php';
		$MU_advanced_cache_path=WPMU_PLUGIN_DIR.'/@'.$atec_wpca_adv_page_cache_filename;
	
		if ($atec_wpca_pcache)
		{
			if ($options['salt']??''==='') { $options['salt']=hash('crc32', get_bloginfo(), FALSE); update_option($option_group,$options); }
			if (atec_check_license())
			{
				atec_mkdir_if_not_exists(WPMU_PLUGIN_DIR);
				@$wp_filesystem->copy(plugin_dir_path(__DIR__).'install/'.$atec_wpca_adv_page_cache_filename,$MU_advanced_cache_path);
			}
		}
		else @$wp_filesystem->delete($MU_advanced_cache_path);
		
		wp_redirect(admin_url().'admin.php?page=atec_wpca&nav=Settings&_wpnonce='.wp_create_nonce('atec_wpca_nonce')); 
	}
	
  	register_setting($page_slug,$option_group);
	
  	add_settings_section($section,'','',$page_slug);
	
  	add_settings_field('cache', __('Page Cache','atec-cache-apcu'), 'atec_checkbox', $page_slug, $section, atec_opt_arr('cache','WPCA'));
	  
	$section.='_options';
	add_settings_section($section,__('Page Cache','atec-cache-apcu').' '.__('Options','atec-cache-apcu'),'',$page_slug);

  	add_settings_field('debug', __('Show debug','atec-cache-apcu').'<br>
  	<span style="font-size:80%; color:#999;">'.__('Cache indicator and browser console log','atec-cache-apcu').'.</span>', 'atec_checkbox', $page_slug, $section, atec_opt_arr('debug','WPCA'));
	
	add_settings_field('admin', __('Show „Flush“ icon in the admin bar','atec-cache-apcu'), 'atec_checkbox', $page_slug, $section, atec_opt_arr('admin','WPCA'));
}
add_action( 'admin_init',  'atec_wpca_settings_fields' );
?>