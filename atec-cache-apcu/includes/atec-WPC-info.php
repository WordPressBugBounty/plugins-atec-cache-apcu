<?php
defined('ABSPATH') || exit;

use ATEC\TOOLS;
use ATEC\WPC;

final class ATEC_WPC_Info {

private static function calc_hitrate($a, $b)
{
	$total = $a+$b;
	if ($total === 0) { $hitsPerc = $missesPerc = 0; }
	else
	{
		$hitsPerc 		= $a * 100 / $total;
		$missesPerc 	= $b * 100 / $total;
	}
	return ['total'=>$total, 'hitsPerc'=>$hitsPerc, 'missesPerc'=>$missesPerc];
}

private static function test_wp_writable()
{
	$testKey= 'atec_wp_test_key';
	wp_cache_set($testKey, 'hello');
	$success=wp_cache_get($testKey)=== 'hello';
	TOOLS::badge($success, 'WP '.__('object cache', 'atec-cache-apcu').' '.__('is writeable', 'atec-cache-apcu'), 'Writing to WP '.__('object cache', 'atec-cache-apcu').' failed');
	if ($success) wp_cache_delete($testKey);
}

public static function init()
{

	global $wp_object_cache;
	if (isset($wp_object_cache->cache_hits))
	{
		$hitrate = self::calc_hitrate($wp_object_cache->cache_hits, $wp_object_cache->cache_misses);
		TOOLS::table_header([], '', 'summary');
			TOOLS::table_tr([__('Hits', 'atec-cache-apcu').':', number_format($wp_object_cache->cache_hits), TOOLS::percent_format($hitrate['hitsPerc'])]);
			TOOLS::table_tr([__('Misses', 'atec-cache-apcu').':', number_format($wp_object_cache->cache_misses), TOOLS::percent_format($hitrate['missesPerc'])]);
			if (isset($wp_object_cache->cache_sets))
			{ TOOLS::table_tr([__('Sets', 'atec-cache-apcu').':', number_format($wp_object_cache->cache_sets), '']); }
		TOOLS::table_footer();

		WPC::hitrate($hitrate['hitsPerc'], $hitrate['missesPerc']);
	}

	if (defined('LSCWP_OBJECT_CACHE') && LSCWP_OBJECT_CACHE== 'true' && (method_exists('WP_Object_Cache', 'debug')))
	{
		$debug= $wp_object_cache->debug();
		preg_match('/\[total\]\s(\d+)\s/', $debug, $m); $ls_total=(int) $m[1];
		preg_match('/\[hit\]\s(\d+)\s/', $debug, $m); $ls_hit=(int) $m[1];
		preg_match('/\[miss\]\s(\d+)\s/', $debug, $m); $ls_miss=(int) $m[1];

		$hitrate = self::calc_hitrate($ls_hit, $ls_miss);

		TOOLS::table_header([], '', 'summary');
			TOOLS::table_tr([__('Items', 'atec-cache-apcu').':', number_format($ls_total), '']);
			TOOLS::table_tr([__('Hits', 'atec-cache-apcu').':', number_format($ls_hit), TOOLS::percent_format($hitrate['hitsPerc'])]);
			TOOLS::table_tr([__('Misses', 'atec-cache-apcu').':', number_format($ls_miss), TOOLS::percent_format($hitrate['missesPerc'])]);
		TOOLS::table_footer();

		WPC::hitrate($hitrate['hitsPerc'], $hitrate['missesPerc']);

		if (defined('LSCWP_V'))
		{
			echo 
			'<p>'; 
				\ATEC\SVG::echo('litespeed'); 
				echo ' LiteSpeed ', esc_attr__('cache', 'atec-cache-apcu'), ' v.', esc_html(LSCWP_V), ' ', esc_attr__('is active', 'atec-cache-apcu'), 
			'.</p>';
		}
	}

	global $_wp_using_ext_object_cache;
	if ($_wp_using_ext_object_cache) TOOLS::msg(true, 'WP '.__('object cache', 'atec-cache-apcu').' '.__('is persistent', 'atec-cache-apcu'));

	self::test_wp_writable();

	TOOLS::help(
		'WP '.__('object cache', 'atec-cache-apcu').' '.__('explained', 'atec-cache-apcu'),
		__('The WP object cache boosts performance by storing keys that might be used by multiple scripts while handling a page request.', 'atec-cache-apcu').'<br>'.
		__('Nonetheless, this cache is solely valid for the current request, unless a persistent object cache, such as APCu, is installed', 'atec-cache-apcu'));

}

}
?>