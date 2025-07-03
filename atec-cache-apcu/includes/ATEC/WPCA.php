<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\INIT;

final class WPCA {

public static function settings($key, $refresh=false)
{
	static $cached = null;
	if ($cached===null || $refresh) $cached = INIT::get_settings('wpca');
	if ($key==='salt')	return $cached[$key] ?? '';
	else return (bool) INIT::bool($cached[$key] ?? 0);
}

public static function apcu_enabled()
{
	static $cached = null;
	if ($cached===null) $cached = extension_loaded('apcu') && apcu_enabled() && class_exists('APCUIterator');
	return $cached;
}

public static function o_cache_stats()
{
	$key = ATEC_OC_KEY_SALT.':atec:atec_wpca_oc_stats';
	$stats = apcu_fetch($key);
		$current = wp_cache_wpc_counts();
		if (!$stats) { $stats = $current; $stats['count']=1; $stats['ts']=time(); }
		else { $stats['count']++; $stats['hits']+= $current['hits']; $stats['misses']+= $current['misses']; $stats['sets']+= $current['sets']; }
	apcu_store($key, $stats);
}

}
?>