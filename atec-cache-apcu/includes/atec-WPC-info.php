<?php
if (!defined('ABSPATH')) { exit; }

class ATEC_WPcache_info {
	
private static function atec_calc_hitrate($a,$b)
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

private static function atec_test_wp_writable()
{
	$testKey='atec_wp_test_key';
	wp_cache_set($testKey,'hello');
	$success=wp_cache_get($testKey)==='hello';
	atec_badge('WP '.__('object cache','atec-cache-apcu').' '.__('is writeable','atec-cache-apcu'),'Writing to WP '.__('object cache','atec-cache-apcu').' failed',$success);
	if ($success) wp_cache_delete($testKey);
}

function __construct() {	

global $wp_object_cache;
if (isset($wp_object_cache->cache_hits))
{
	$hitrate = self::atec_calc_hitrate($wp_object_cache->cache_hits,$wp_object_cache->cache_misses);	

	echo'
	<table class="atec-table atec-table-tiny atec-table-td-first">
		<tbody>
			<tr><td>', esc_attr__('Hits','atec-cache-apcu'), ':</td>
				<td>', esc_html(number_format($wp_object_cache->cache_hits)), '</td><td><small>', esc_attr(sprintf("%.1f%%",$hitrate['hitsPerc'])), '</small></td></tr>
			<tr><td>', esc_attr__('Misses','atec-cache-apcu'), ':</td>
				<td>', esc_html(number_format($wp_object_cache->cache_misses)), '</td><td><small>', esc_attr(sprintf("%.1f%%",$hitrate['missesPerc'])), '</small></td></tr>
		</tbody>
	</table>';
		
	ATEC_wpc_tools::hitrate($hitrate['hitsPerc'],$hitrate['missesPerc']);
}

if (defined('LSCWP_OBJECT_CACHE') && LSCWP_OBJECT_CACHE=='true' && (method_exists('WP_Object_Cache', 'debug'))) 
{
	$debug=$wp_object_cache->debug();
	preg_match('/\[total\]\s(\d+)\s/', $debug, $m); $ls_total=(int) $m[1];
	preg_match('/\[hit\]\s(\d+)\s/', $debug, $m); $ls_hit=(int) $m[1];
	preg_match('/\[miss\]\s(\d+)\s/', $debug, $m); $ls_miss=(int) $m[1];
	
	$hitrate = self::atec_calc_hitrate($ls_hit,$ls_miss);	

	echo ' 
	<table class="atec-table atec-table-tiny atec-table-td-first">
	<tbody>
		<tr><td>', esc_attr__('Items','atec-cache-apcu'), ':</td><td>', esc_html(number_format($ls_total)), '</td><td></td></tr>
		<tr><td>', esc_attr__('Hits','atec-cache-apcu'), ':</td>
			<td>', esc_html(number_format($ls_hit)), '</td><td><small>', esc_attr(sprintf(" (%.1f%%)",$hitrate['hitsPerc'])), '</small></td></tr>
		<tr><td>', esc_attr__('Misses','atec-cache-apcu'), ':</td>
			<td>', esc_html(number_format($ls_miss)), '</td><td><small>', esc_attr(sprintf(" (%.1f%%)",$hitrate['missesPerc'])), '</small></td></tr>
	</tbody>
	</table>';

	ATEC_wpc_tools::hitrate($hitrate['hitsPerc'],$hitrate['missesPerc']);

	if (defined('LSCWP_V')) 
	{
		echo '<p>'; atec_server_sys_icon(__DIR__,'litespeed'); echo ' LiteSpeed ', esc_attr__('cache','atec-cache-apcu'), ' v.', esc_html(LSCWP_V), ' ', esc_attr__('is active','atec-cache-apcu'), '.</p>';
	}
}

global $_wp_using_ext_object_cache;
if ($_wp_using_ext_object_cache) atec_success_msg('WP '.__('object cache','atec-cache-apcu').' '.__('is persistent','atec-cache-apcu'),false,true);

self::atec_test_wp_writable();

echo '<br>';
atec_help('WPcache','WP '.__('object cache','atec-cache-apcu').' '.__('explained','atec-cache-apcu'));
echo '<div id="WPcache_help" class="atec-help atec-dn">', esc_html__('The WP object cache boosts performance by storing keys that might be used by multiple scripts while handling a page request.','atec-cache-apcu').' '.esc_html__('Nonetheless, this cache is solely valid for the current request, unless a persistent object cache, such as APCu, is installed','atec-cache-apcu'), '.</div>';

}}
?>