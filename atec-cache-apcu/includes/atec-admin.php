<?php
if (!defined( 'ABSPATH' )) { exit; }
define('ATEC_ADMIN_INC',true);

function atec_plugin_settings(array $links): array
{
	$atec_group_settings_arr=[
		'backup'=>'wpb','cache-apcu'=>'wpca','code'=>'wpc','deploy'=>'wpdp','meta'=>'wpm','optimize'=>'wpo',
		'page-cache'=>'wppc','poly-addon'=>'wppo','shell'=>'wpsh','web-map-service'=>'wms','smtp-mail'=>'wpsm'];
	preg_match('/plugin=atec-([\w\-]+)/', $links['deactivate']??'', $match);
	if (isset($match[1]) && isset($atec_group_settings_arr[$match[1]]))
	{
		$slug=$atec_group_settings_arr[$match[1]];
		$url = get_admin_url() . 'admin.php?page=atec_'.$slug;
		array_unshift($links, '<a href="' . $url . '"><svg width="20" height="20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M15.881 4.153l1.292 1.292c.34.34.34.896 0 1.236l-1.04 1.04c.287.537.506 1.114.645 1.722h1.348c.48 0 .874.394.874.874v1.827a.877.877 0 0 1-.874.875h-1.47a6.913 6.913 0 0 1-.763 1.672l.954.954c.34.34.34.896 0 1.236l-1.292 1.292a.877.877 0 0 1-1.236 0l-1.04-1.04a6.902 6.902 0 0 1-1.722.645v1.348c0 .48-.394.874-.874.874H8.856a.877.877 0 0 1-.874-.874v-1.47a6.916 6.916 0 0 1-1.673-.763l-.954.954a.877.877 0 0 1-1.236 0l-1.292-1.292a.877.877 0 0 1 0-1.236l1.04-1.04a6.896 6.896 0 0 1-.645-1.722H1.874A.877.877 0 0 1 1 11.683V9.856c0-.481.393-.874.874-.874h1.47a6.97 6.97 0 0 1 .763-1.673l-.954-.954a.877.877 0 0 1 0-1.236l1.292-1.292a.877.877 0 0 1 1.236 0l1.04 1.04a6.904 6.904 0 0 1 1.722-.645V2.874c0-.48.394-.874.874-.874h1.827c.481 0 .874.393.874.874v1.47a6.92 6.92 0 0 1 1.674.763l.953-.954a.877.877 0 0 1 1.236 0zm-5.88 3.255a3.592 3.592 0 1 1-.002 7.184A3.592 3.592 0 0 1 10 7.408z"/></svg></a>');
	}
	return $links;
}
?>