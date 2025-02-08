<?php
if (!defined('ABSPATH')) { exit(); }
define('ATEC_ADMIN_INC',true); // just for backwards compatibility

function atec_plugin_settings(array $links, $plugin): array
{
	$atec_group_settings_arr=[
		'backup'=>'wpb',			'cache-apcu'=>'wpca',		'cache-memcached'=>'wpcm',		'cache-redis'=>'wpcr',	
		'optimize'=>'wpo',		'smtp-mail'=>'wpsm',		'web-map-service'=>'wms',			'mega-cache'=>'wpmc'];
		
	preg_match('/([\w\-]+)\.php/', $plugin, $match);
	if (isset($match[1]))
	{
		$match = str_replace('atec-','',$match[1]);
		if (isset($atec_group_settings_arr[$match]))
		{
			$slug=$atec_group_settings_arr[$match];
			$url = get_admin_url() . 'admin.php?page=atec_'.$slug;
			array_unshift($links, '<a href="' . $url . '" style="vertical-align:sub"><svg width="16" height="16" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M13.228 1.914l1.148 1.148a.779.779 0 0 1 0 1.099l-.924.924c.255.478.45.99.573 1.531h1.198a.78.78 0 0 1 .777.777v1.624a.78.78 0 0 1-.777.778h-1.307a6.145 6.145 0 0 1-.678 1.486l.848.848a.779.779 0 0 1 0 1.099l-1.148 1.148a.78.78 0 0 1-1.099 0l-.924-.924a6.135 6.135 0 0 1-1.531.573v1.198a.78.78 0 0 1-.777.777H6.983a.78.78 0 0 1-.777-.777v-1.307a6.148 6.148 0 0 1-1.487-.678l-.848.848a.78.78 0 0 1-1.099 0l-1.148-1.148a.78.78 0 0 1 0-1.099l.924-.924a6.13 6.13 0 0 1-.573-1.531H.777A.78.78 0 0 1 0 8.607V6.983c0-.427.35-.777.777-.777h1.307a6.196 6.196 0 0 1 .678-1.487l-.848-.848a.78.78 0 0 1 0-1.099l1.148-1.148a.78.78 0 0 1 1.099 0l.924.924a6.137 6.137 0 0 1 1.531-.573V.777A.78.78 0 0 1 7.393 0h1.624c.427 0 .777.35.777.777v1.307a6.151 6.151 0 0 1 1.488.678l.847-.848a.78.78 0 0 1 1.099 0zM8 4.807a3.193 3.193 0 1 1-.002 6.386A3.193 3.193 0 0 1 8 4.807z"/></svg></a>');
		}
	}
	return $links;
}
?>