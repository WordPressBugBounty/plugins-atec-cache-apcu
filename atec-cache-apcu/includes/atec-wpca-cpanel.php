<?php
defined('ABSPATH') || exit;

use ATEC\TOOLS;
use ATEC\WPC;
use ATEC\WPCA;

return function($una) 
{

	TOOLS::little_block('WP & APCu '.__('Object Cache', 'atec-cache-apcu'));

	switch ($una->action)
	{
		case 'flush':
			$type = TOOLS::clean_request('type');
			if ($type==='OC_Stats') apcu_delete(ATEC_OC_KEY_SALT.':atec:atec_wpca_oc_stats');
			else WPC::flush_cache($una, [], $type);
			break;
			
		case 'flushed':
			$tmp = __('Flushing', 'atec-cache-apcu').' '.WPC::fix_name(TOOLS::clean_request('type'), 'atec-cache-apcu').' '.__('succeeded', 'atec-cache-apcu');
			TOOLS::msg(true, $tmp);
			break;
	}

	global $wp_object_cache;

	$enabled = [];
	$enabled['wp'] = is_object($wp_object_cache);
	$enabled['apcu'] = WPCA::apcu_enabled();
	
	$cache_settings = [];

	echo
	'<div class="atec-g atec-g-25">';

		foreach(['WP', 'APCu'] as $type)
		{ WPC::cache_block(__DIR__, $una, $cache_settings, $type, $enabled); }

		if (defined('ATEC_OC_KEY_SALT'))
		{
			$stats = apcu_fetch(ATEC_OC_KEY_SALT.':atec:atec_wpca_oc_stats');
			if (!empty($stats)) TOOLS::lazy_require(__DIR__, 'atec-wpca-ocache-stats.php', $una, $stats);
		}

	echo
	'</div>';
}
?>