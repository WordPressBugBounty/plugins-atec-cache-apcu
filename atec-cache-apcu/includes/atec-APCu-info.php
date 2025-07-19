<?php
defined('ABSPATH') || exit;

use ATEC\ALIAS;
use ATEC\TOOLS;
use ATEC\WPC;

final class ATEC_APCu_Info {

private static function test_apcu_writable()
{
	$testKey= 'atec_apcu_test_key';
	apcu_add($testKey, 'hello');
	$success= apcu_fetch($testKey)=== 'hello';
	TOOLS::badge($success, 'APCu '.__('is writeable', 'atec-cache-apcu'), 'Writing to cache failed');
	if ($success) apcu_delete($testKey);
}

public static function init()
{

	$apcu_cache = function_exists('apcu_cache_info') ? apcu_cache_info(true) : false;
	if ($apcu_cache)
	{
		$notNull		= 0.0000001;
		$total			= $apcu_cache['num_hits']+$apcu_cache['num_misses']+$notNull;
		$relHits			=	$apcu_cache['num_hits']*100/$total;
		$relMisses	= $apcu_cache['num_misses']*100/$total;

		if ($apcu_mem	= apcu_sma_info(true))
		{
			$mem_size 	= $apcu_mem['num_seg']*$apcu_mem['seg_size']+$notNull;
			$mem_avail	= $apcu_mem['avail_mem'];
			$mem_used 	= $mem_size-$mem_avail;
		}

		$percent = $apcu_mem?$mem_used*100/$mem_size:-1;

		TOOLS::table_header([], '', 'summary');
			ALIAS::tr([__('Version', 'atec-cache-apcu').':', phpversion('apcu'), '']);
			ALIAS::tr([__('Type', 'atec-cache-apcu').':', $apcu_cache['memory_type'], '']);
			ALIAS::tr();
			if ($percent>0)
			{
				ALIAS::tr([__('Memory', 'atec-cache-apcu').':', TOOLS::size_format($mem_size), '']);
				ALIAS::tr([__('Used', 'atec-cache-apcu').':', TOOLS::size_format($mem_used), TOOLS::percent_format($percent)]);
				ALIAS::tr([__('Items', 'atec-cache-apcu').':', number_format($apcu_cache['num_entries']), '']);
				ALIAS::tr();
				ALIAS::tr([__('Hits', 'atec-cache-apcu').':', number_format($apcu_cache['num_hits']), TOOLS::percent_format($relHits)]);
				ALIAS::tr([__('Misses', 'atec-cache-apcu').':', number_format($apcu_cache['num_misses']), TOOLS::percent_format($relMisses)]);
			}			
		TOOLS::table_footer();

		if ($percent>-1) WPC::usage($percent);
		if ($apcu_cache['mem_size']!=0) WPC::hitrate($relHits, $relMisses);

		if ($percent>90) TOOLS::msg(false, __('APCu usage is beyond 90%. Please consider increasing ‘apc.shm_size’ option', 'atec-cache-apcu'));
		elseif ($percent===-1) { TOOLS::p(__('Shared memory info is not available', 'atec-cache-apcu')); echo '<br>'; }
		elseif ($percent===0)
		{
			TOOLS::p(__('Not in use', 'atec-cache-apcu'));
			TOOLS::reg_inline_script('wpx_APCu_flush', 'jQuery("#APCu_flush").hide();', true);
		}

		self::test_apcu_writable();
	}
	else
	{
		TOOLS::msg(false, 'APCu '.__('cache data could NOT be retrieved', 'atec-cache-apcu'));
	}

}

}
?>