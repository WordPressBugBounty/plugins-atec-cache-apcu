<?php
if (!defined('ABSPATH')) { exit(); }

function atec_wpca_arr_equal($arr1, $arr2) 
{
	if (!is_array($arr1) || !is_array($arr2)) return false;
	array_multisort($arr1); array_multisort($arr2); 
	return ( serialize($arr1) === serialize($arr2) ); 
}

function atec_wpca_sanitize_fields($input)
{
	$booleanArr = ['ocache','oadmin','cache','admin','debug'];
	foreach($booleanArr as $b) 
	{
		if (filter_var($input[$b]??0,258)) $input[$b]='1';
		else unset($input[$b]);
	}
	return $input;
}

function atec_wpca_settings_fields()
{ 
	if (!function_exists('atec_opt_arr')) @require('atec-check.php');
	
	$page_slug 		= 'atec_WPCA';
    $option_group 	= $page_slug.'_settings';
    $section			= $page_slug.'_section';
	$options			= get_option($option_group,[]);

	$optName = 'atec_wpca_fix_cache';
	$atec_wpca_fix_cache = filter_var(get_option($optName),258); delete_option($optName);

	$atec_wpca_ocache = filter_var($options['ocache']??0,258);
	$atec_wpca_pcache = filter_var($options['cache']??0,258);

	// ** flush the pcache if pcache settings change ** //
	if ($atec_wpca_fix_cache || str_contains(atec_query(),'settings-updated=true')) 
	{

		$lastOptName 	= 'atec_wpca_last_cache'; 
		$lastSettings 	= get_option($lastOptName);
		if ($atec_wpca_fix_cache || !atec_wpca_arr_equal($options,$lastSettings)) 
		{
			update_option($lastOptName,$options); 
			$atec_wpca_pcache = filter_var($options['cache']??0,258);
			$deletePC = filter_var($options['debug']??0,258)!==filter_var($lastSettings['debug']??0,258);
			if ($atec_wpca_fix_cache || empty($lastSettings) || $atec_wpca_pcache!==filter_var($lastSettings['cache']??0,258))
			{ 
				$deletePC = true;
				if (!class_exists('ATEC_fs')) @require('atec-fs.php');
				$afs = new ATEC_fs();
				
				$atec_wpca_adv_page_cache_filename='atec-wpca-adv-page-cache-pro.php';
				$MU_advanced_cache_path=WPMU_PLUGIN_DIR.'/@'.$atec_wpca_adv_page_cache_filename;
				if ($atec_wpca_pcache)
				{
					if ($options['salt']??''==='') { $options['salt']=hash('crc32', get_bloginfo(), FALSE); }
					if (atec_check_license())
					{
						if ($afs->mkdir(WPMU_PLUGIN_DIR)) 
							$afs->copy(plugin_dir_path(__DIR__).'install/'.$atec_wpca_adv_page_cache_filename,$MU_advanced_cache_path);
					}
				}
				else $afs->unlink($MU_advanced_cache_path);
			}
			
			if ($deletePC)
			{
				if (!function_exists('atec_wpca_delete_wp_cache')) @require(__DIR__.'/atec-cache-apcu-pcache-tools.php');
				atec_wpca_delete_page_cache_all();
			}
	
			if ($atec_wpca_fix_cache || empty($lastSettings) || filter_var($options['ocache']??0,258)!==filter_var($lastSettings['ocache']??0,258))
			{
				@require(__DIR__.'/atec-wpca-set-object-cache.php'); 
				$result = atec_wpca_set_object_cache($options);
				error_log('oc'.$result);

				if ($result!=='') 
				{
					if (!function_exists('atec_header')) @require('atec-tools.php');	
					atec_notice($notice, 'warning', $result);
					update_option( 'atec_wpca_debug', $notice, false);
				}
				else wp_redirect(admin_url().'admin.php?page=atec_wpca');
			}
			
		}
	}
	
  	register_setting($page_slug, $option_group, 'atec_wpca_sanitize_fields');
  	
	add_settings_section($section,'','',$page_slug);
	add_settings_field('ocache', __('Object Cache','atec-cache-apcu'), 'atec_checkbox', $page_slug, $section, atec_opt_arr('ocache','WPCA'));

	if ($atec_wpca_ocache)
	{
		add_settings_section($section.'_2','<small>'.__('Options','atec-cache-apcu').'</small>','',$page_slug);
		add_settings_field('oadmin', __('Admin bar „OC Flush“ icon','atec-cache-apcu'), 'atec_checkbox', $page_slug, $section.'_2', atec_opt_arr('oadmin','WPCA'));
	}

	$page_slug_pc = $page_slug.'_PC';
	register_setting($page_slug_pc,$option_group, 'atec_wpca_sanitize_fields');
	  
	$section_pc = $section.'_PC';
	add_settings_section($section_pc,'','',$page_slug_pc);
    add_settings_field('cache', __('Page Cache','atec-cache-apcu'), 'atec_checkbox', $page_slug_pc, $section_pc, atec_opt_arr('cache','WPCA'));
	
	if ($atec_wpca_pcache)
	{
		add_settings_section($section_pc.'_2','<small>'.__('Options','atec-cache-apcu').'</small>','',$page_slug_pc);
		add_settings_field('admin', __('Admin bar „PC Flush“ icon','atec-cache-apcu'), 'atec_checkbox', $page_slug_pc, $section_pc.'_2', atec_opt_arr('admin','WPCA'));
		
		add_settings_field('debug', __('Show debug','atec-cache-apcu').'<br>
		<span style="font-size:80%; color:#999;">'.__('Cache indicator and browser console log','atec-cache-apcu').'.</span>', 'atec_checkbox', $page_slug_pc, $section_pc.'_2', atec_opt_arr('debug','WPCA'));
	}
	
}
add_action( 'admin_init',  'atec_wpca_settings_fields' );
?>