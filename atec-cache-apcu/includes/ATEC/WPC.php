<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\INIT;
use ATEC\TOOLS;

class WPC
{

public static function fix_name($type)
{
	switch ($type)
	{
		case 'WP':
			$tmp = 'WP '.__('Object Cache', 'atec-cache-apcu');
			break;

		case 'OP':
			$tmp = 'OPcache';
			break;
			
		case 'PC':
			$tmp = __('Page Cache', 'atec-cache-apcu');
			break;
					
		default:
			$tmp = $type;
			break;
	}
	return $tmp;
}

public static function dash_trash() : string
{ return '<span class="'.TOOLS::dash_class('trash').'"></span>'; }

public static function hitrate($hits, $misses): void
{
	$id1 = uniqid();
	$id2 = uniqid();
	echo
	'<div class="atec-db atec-border atec-percent-block">
		<div class="atec-dilb atec-fs-12">', esc_html__('Hitrate', 'atec-cache-apcu'), '</div>
		<div class="atec-dilb atec-float-right atec-fs-12">', esc_attr(round($hits, 1)), '%</div>
		<br>
		<div class="atec-percent-div">
			<span id="atec_hitrate_', esc_attr($id1), '" style="background-color:green;"></span>
			<span id="atec_hitrate_', esc_attr($id2), '" style="background-color:red;"></span>
		</div>
	</div>';
	TOOLS::reg_inline_script('wpx_anim_hitrate_'.$id1,
		'jQuery("#atec_hitrate_'.esc_attr($id1).'").animate({ width: "'.$hits.'%" }, 1000);
		jQuery("#atec_hitrate_'.esc_attr($id2).'").animate({ width: "'.$misses.'%" }, 1000);'
	);
}

public static function usage($percent): void
{
	$id1 = uniqid();
	echo
	'<div class="atec-db atec-border atec-percent-block">
		<div class="atec-dilb atec-fs-12">', esc_html__('Usage', 'atec-cache-apcu'), '</div>
		<div class="atec-dilb atec-float-right atec-fs-12">', esc_attr(round($percent, 1)), '%</div><br>
		<div class="atec-percent-div"><span id="atec_usage_', esc_attr($id1), '" style="background-color:orange;"></span></div>
	</div>';
	TOOLS::reg_inline_script('wpx_anim_usage_'.$id1, 'jQuery("#atec_usage_'.esc_attr($id1).'").animate({ width: "'.$percent. '%" }, 1000);');
}

public static function flushing_start($type): void
{
	echo
	'<div id="atec_wpx_flushing" class="atec-badge atec-mb-10 atec-bg-w6">
		<span id="atec_wpc_dash" class="dashicon-spin ', esc_attr(TOOLS::dash_class('hourglass')), '"></span> ',
		esc_html__('Flushing', 'atec-cache-apcu'), ' ', esc_attr(self::fix_name($type));
		TOOLS::loader_dots();
	echo
	'</div>';
	TOOLS::flush();
}

public static function flushing_end($result): void
{
	TOOLS::reg_inline_script('wpx_remove','jQuery("#atec_wpx_flushing").remove();');
}

public static function cache_block($dir, $una, $settings, $type, $enabled)
{
	$type_lower = strtolower($type);
	echo
	'<div class="atec-border-white">
	
		<h4>'; 
			TOOLS::enabled($enabled[$type_lower]);
			echo ' ', esc_html(self::fix_name($type));	// phpcs:ignore
			if ($enabled[$type_lower])
			{
				$href = INIT::build_url($una, 'flush', 'Cache', ['type' => $type]);
				echo 
				'<a title="', esc_attr__('Empty cache', 'atec-cache-apcu'), '" ',
					'class=" atec-float-right button" style="margin-top: -5px;" id="', esc_attr($type),'_flush" ',
					'href="', esc_url($href), '">', wp_kses_post(self::dash_trash()), '<span>';
					echo $type === 'WP' ?  esc_attr__('Site', 'atec-cache-apcu') : esc_attr__('All', 'atec-cache-apcu'); 
					echo
					'</span>',
				'</a>';
			}
		echo 
		'</h4><hr>';
	
		if ($enabled[$type_lower]) 
		{
			$tmp = in_array($type, ['OP', 'WP']) ? $type.'C' : $type;
			TOOLS::lazy_require_class($dir, 'atec-'.$tmp.'-info.php', $tmp.'_Info', $una, $settings[$type_lower] ?? []);
		}
		else
		{
			TOOLS::p(
				$type.' '.
				(in_array($type, ['OP', 'WP', 'JIT', 'SQLite'])
				? __('is NOT enabled', 'atec-cache-apcu') 
				: __('extension is NOT installed/enabled', 'atec-cache-apcu')
				));
			if (in_array($type, ['APCu', 'JIT'])) require $dir.'/atec-'.$type.'-help.php';
		}
	
	echo
	'</div>';
}

public static function flush_cache($una, $settings, $type = null) 
{

	if ($type===null) $type = TOOLS::clean_request('type');

	self::flushing_start($type);
		$result=false;
		
		switch ($type)
		{
			case 'JIT': 
			case 'OP': 
				$result=opcache_reset(); 
				break;
				
			case 'WP':
				if ($_wp_using_ext_object_cache = wp_using_ext_object_cache()) wp_using_ext_object_cache(false);
				$result = wp_cache_flush(); 
				wp_cache_init();
				if ($_wp_using_ext_object_cache) wp_using_ext_object_cache(true);
				break;

			case 'APCu': 
				if (function_exists('apcu_clear_cache')) $result= apcu_clear_cache(); 
				break;
			
			case 'Memcached':
				$result = self::memcached_connect($settings['memcached'] ?? []);
				$m = $result['m'];
				$result= $m ? $m->flush() : false;
				break;

			case 'Redis':
				$result = self::redis_connect($settings['redis'] ?? []);
				$redis = $result['redis'];
				$result= $redis ? $redis->flushAll() : false;
				break;

			case 'SQLite': 
				$result = wp_cache_flush(); 
				wp_cache_init();
				break;

		}
	self::flushing_end($result);

	TOOLS::badge($result, __('Flushing', 'atec-cache-apcu').' '.self::fix_name($type).'#'.__('succeeded', 'atec-cache-apcu'), __('failed', 'atec-cache-apcu'));

	return $result;
}

public static function flush_wp_cache_options(): void
{
	wp_cache_delete('alloptions', 'options');
	wp_cache_delete('notoptions', 'options');
	wp_cache_delete('active_plugins', 'options');
}

public static function redis_connect($settings)
{
	$redis = new \Redis();
	$redSuccess = true;
	$redConn = $settings['conn']??'TCP/IP';
	$redHost = $settings['host']??'localhost';
	$redPort = (int) ($settings['port']??6379);
	$redPwd = $settings['pwd']??'';
	try
	{
		if ($redHost!== '' && ($redPort!== '' || $redConn=== 'SOCKET'))
		{
			if ($redConn=== 'SOCKET') $redis->pconnect($settings['host']);
			else $redis->pconnect($redHost, $redPort);
			if ($redPwd!== '') $redSuccess = $redis->auth($redPwd);
			if ($redSuccess) $redSuccess = $redSuccess && $redis->ping();

		}
		else throw new \RedisException('Connection parameter missing');
	}
	catch (\RedisException $e) { return array('redis'=>null, 'error'=>rtrim($e->getMessage(), '.')); }
	return array('redis'=>$redSuccess?$redis:null, 'error'=>$redSuccess?'' : 'Connection failed', 	'host'=>$redHost, 'port'=>$redPort, 'conn'=>$redConn);
}


public static function memcached_connect($settings)
{
	$m = new \Memcached();

	$memSuccess = true;
	$memConn = $settings['conn']??'TCP/IP';
	$memHost = $settings['host']??'localhost';
	$memPort = (int) ($settings['port']??11211);
	if ($memConn=== 'SOCKET') $memPort=0;
	$m->addServer($memHost, $memPort);
	if (!$m->getVersion()) $m = false;

	return array('m'=>$m, 'host'=>$memHost, 'port'=>$memPort, 'conn'=>$memConn);
}

}
?>