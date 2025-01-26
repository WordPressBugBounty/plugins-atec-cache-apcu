<?php
if (!defined( 'ABSPATH' )) { exit; }
define('ATEC_WPCA_CACHE_TOOLS',true);

function atec_wpca_delete_wp_cache(): void 
{
	// error_log('atec Cache APCu – Flush WP OC');
	// wp_cache_delete('alloptions','options'); wp_cache_get('notoptions','options'); 
	// delete_option -> 	wp_protect_special_option( $option );
	// Will die if $option is in protected list. Protected options are ‘alloptions’ and ‘notoptions’ options.
	wp_cache_delete('active_plugins','options');
	if (atec_wpca_settings('cache')) atec_wpca_delete_page_cache_all();
}

function atec_wpca_delete_page($suffix, $id): void 
{ apcu_delete('atec_WPCA_'.$suffix.'_'.$id); apcu_delete('atec_WPCA_'.$suffix.'_h_'.$id); }

function atec_wpca_delete_page_cache($plugin='',$reg='[f|p|c|t|a]+'): void
{
	if (!class_exists('APCUIterator')) return;
	global $atec_wpca_settings;
	if (!empty($apcu_it=new APCUIterator('/atec_WPCA_/'))) 
	{ 
		$salt=$atec_wpca_settings['salt']??'';
		$reg_apcu = '/atec_WPCA_'.$salt.'_('.($reg).')_([\d|\|]+)/';
		foreach ($apcu_it as $entry) 
		{							
			preg_match($reg_apcu, $entry['key'], $match);
			if (isset($match[2])) atec_wpca_delete_page($salt.'_'.$match[1],$match[2]); 
		}
		update_option( 'atec_wpca_debug', ['type'=>'info', 'message'=>'PCache '.__('cleared','atec-cache-apcu').'.'], false);
	}
}

function atec_wpca_delete_page_cache_all(): void
{
	//error_log('atec Cache APCu – Flush PC');
	if (!class_exists('APCUIterator')) return;
	global $atec_wpca_settings;
	$salt=$atec_wpca_settings['salt']??'';
	if (!empty($apcu_it=new APCUIterator('/atec_WPCA_'.$salt.'_/'))) 
	{ 
		foreach ($apcu_it as $entry) apcu_delete($entry['key']);
		update_option( 'atec_wpca_debug', ['type'=>'info', 'message'=>'PCache '.__('cleared','atec-cache-apcu').'.'], false);
	}
}
?>