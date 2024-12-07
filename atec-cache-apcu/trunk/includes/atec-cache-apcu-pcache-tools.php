<?php
if (!defined( 'ABSPATH' )) { exit; }

function atec_wpca_delete_page($suffix, $id): void 
{ apcu_delete('atec_WPCA_'.$suffix.'_'.$id); apcu_delete('atec_WPCA_'.$suffix.'_h_'.$id); }

function atec_wpca_delete_page_cache($plugin='',$reg='[f|p|c|t|a]+'): void
{
	global $atec_wpca_settings;
	$apcu_it=new APCUIterator('/atec_WPCA_/');	
	if (!empty($apcu_it)) 
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
	global $atec_wpca_settings;
	$salt=$atec_wpca_settings['salt']??'';
	$apcu_it=new APCUIterator('/atec_WPCA_'.$salt.'_/');	
	if (!empty($apcu_it)) 
	{ 
		foreach ($apcu_it as $entry) apcu_delete($entry['key']);
		update_option( 'atec_wpca_debug', ['type'=>'info', 'message'=>'PCache '.__('cleared','atec-cache-apcu').'.'], false);
	}
}

function atec_wpca_delete_category_cache(): void { atec_wpca_delete_page_cache('','[cf|c]+'); }
function atec_wpca_delete_tag_cache($term_id, $tt_id, $taxo): void { if ($taxo==='post_tag') atec_wpca_delete_page_cache('','[tf|f]+'); }
?>