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
	$atec_query = atec_query();
	if (str_contains($atec_query,'action=flushWPCA')) wp_cache_delete('alloptions','options');
	
	if (!function_exists('atec_opt_arr')) @require('atec-check.php');
	
	$page_slug 		= 'atec_WPCA';
    $option_group 	= $page_slug.'_settings';
    $section		= $page_slug.'_section';
	$options		= get_option($option_group,[]);

	if (str_contains($atec_query,'settings-updated=true'))
	{
		$lastOptName 			= 'atec_wpca_last_cache'; 
		$lastSettings 			= get_option($lastOptName,[]);
		$update					= false;
		$atec_wpca_pcache 	= filter_var($options['cache']??0,258);
		
		$deletePC = filter_var($options['debug']??0,258)!==filter_var($lastSettings['debug']??0,258);
		if (empty($lastSettings) || $atec_wpca_pcache!==filter_var($lastSettings['cache']??0,258))
		{ 
			if (!class_exists('ATEC_fs')) @require('atec-fs.php');
			$afs = new ATEC_fs();
			
			if ($afs->exists(WP_CONTENT_DIR.'/advanced-cache.php'))
			{
				if ($atec_wpca_pcache) { $options['cache']=0; $update=true; }
				atec_new_admin_notice('warning','Another „advanced-cache.php“ file exists. Please deactivate it first');
			}
			else
			{
				$deletePC = true;			
				$atec_wpca_adv_page_cache_filename='atec-wpca-adv-page-cache-pro.php';
				$MU_advanced_cache_path=WPMU_PLUGIN_DIR.'/@'.$atec_wpca_adv_page_cache_filename;
				if ($atec_wpca_pcache)
				{
					if ($options['salt']??''==='') { $options['salt']=hash('crc32', get_bloginfo(), FALSE); }
					if (!function_exists('atec_header')) @require(__DIR__.'/atec-tools.php');	
					if (atec_check_license())
					{
						if ($afs->mkdir(WPMU_PLUGIN_DIR)) 
							$afs->copy(plugin_dir_path(__DIR__).'install/'.$atec_wpca_adv_page_cache_filename,$MU_advanced_cache_path);
					}
				}
				else $afs->unlink($MU_advanced_cache_path);
			}
		}
		
		if ($deletePC)
		{
			if (!function_exists('atec_wpca_delete_page_cache_all')) @require(__DIR__.'/atec-cache-apcu-pcache-tools.php');
			atec_wpca_delete_page_cache_all();
		}
	
		if (empty($lastSettings) || filter_var($options['ocache']??0,258)!==filter_var($lastSettings['ocache']??0,258))
		{
			if (!function_exists('atec_wpca_set_object_cache')) @require(__DIR__.'/atec-wpca-set-object-cache.php'); 
			$result = atec_wpca_set_object_cache($options);
			if ($result==='') wp_redirect(admin_url().'admin.php?page=atec_wpca&action=flushWPCA&_wpnonce='.wp_create_nonce('atec_wpca_nonce'));
			else 
			{
				if (filter_var($options['ocache']??0,258)) { $options['ocache']=0; $update=true; }
				atec_new_admin_notice('warning',$result);
			}
		}	
		
		if ($update) update_option($option_group,$options);
		update_option($lastOptName,$options);

	}

  	register_setting($page_slug, $option_group, 'atec_wpca_sanitize_fields');
  	
	add_settings_section($section,'','',$page_slug);
	add_settings_field('ocache', __('Object Cache','atec-cache-apcu'), 'atec_checkbox', $page_slug, $section, atec_opt_arr('ocache','WPCA'));

	if (filter_var($options['ocache']??0,258)) 
	{
		add_settings_section($section.'_2','<small>'.__('Options','atec-cache-apcu').(filter_var($options['ocache']??0,258)==false).'</small>','',$page_slug);
		add_settings_field('oadmin', __('Admin bar „OC Flush“ icon','atec-cache-apcu'), 'atec_checkbox', $page_slug, $section.'_2', atec_opt_arr('oadmin','WPCA'));
	}

	$page_slug_pc = $page_slug.'_PC';
	register_setting($page_slug_pc,$option_group, 'atec_wpca_sanitize_fields');
	  
	$section_pc = $section.'_PC';
	add_settings_section($section_pc,'','',$page_slug_pc);
    add_settings_field('cache', __('Page Cache','atec-cache-apcu'), 'atec_checkbox', $page_slug_pc, $section_pc, atec_opt_arr('cache','WPCA'));
	
	if (filter_var($options['cache']??0,258)) 
	{
		add_settings_section($section_pc.'_2','<small>'.__('Options','atec-cache-apcu').'</small>','',$page_slug_pc);
		add_settings_field('admin', __('Admin bar „PC Flush“ icon','atec-cache-apcu'), 'atec_checkbox', $page_slug_pc, $section_pc.'_2', atec_opt_arr('admin','WPCA'));
		
		add_settings_field('debug', __('Show debug','atec-cache-apcu').'<br>
		<span style="font-size:80%; color:#999;">'.__('Cache indicator and browser console log','atec-cache-apcu').'.</span>', 'atec_checkbox', $page_slug_pc, $section_pc.'_2', atec_opt_arr('debug','WPCA'));
	}
	
}
add_action( 'admin_init',  'atec_wpca_settings_fields' );
?>