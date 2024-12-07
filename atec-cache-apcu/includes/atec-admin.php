<?php
if (!defined( 'ABSPATH' )) { exit; }
define('ATEC_ADMIN_INC',true);

function atec_plugin_settings(array $links): array
{
	if (!function_exists('atec_load_pll')) { @require_once('atec-translation.php'); }
	atec_load_pll(__DIR__,'admin','admin');

	$atec_group_settings_arr=[
		'backup'=>'wpb','cache-apcu'=>'wpca','code'=>'wpc','deploy'=>'wpdp','meta'=>'wpm','optimize'=>'wpo',
		'page-cache'=>'wppc','poly-addon'=>'wppo','shell'=>'wpsh','web-map-service'=>'wms','smtp-mail'=>'wpsm'];
	preg_match('/plugin=atec-([\w\-]+)/', $links['deactivate'], $match);
	if (isset($match[1]) && isset($atec_group_settings_arr[$match[1]]))
	{
		$slug=$atec_group_settings_arr[$match[1]];
		$url = get_admin_url() . 'admin.php?page=atec_'.$slug;
		array_unshift($links, '<a href="' . $url . '">' . __('Settings', 'atec-cache-apcu') . '</a>');
	}
	return $links;
}
?>