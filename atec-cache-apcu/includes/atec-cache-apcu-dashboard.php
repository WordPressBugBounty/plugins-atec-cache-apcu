<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_wpcu_results { function __construct() {

atec_admin_debug('Cache APCu','wpca');

$url			= atec_get_url();
$nonce		= wp_create_nonce(atec_nonce());
$action 	= atec_clean_request('action');
$nav 		= atec_clean_request('nav');
if ($nav=='') $nav='Settings';	

echo '
<div class="atec-page">';
	atec_header(__DIR__,'wpca','Cache APCu');

	echo '
	<div class="atec-main">';
		atec_progress();

		global $wp_object_cache, $atec_wpca_apcu_enabled;
		$atec_wpca_pcache = atec_wpca_settings('cache');
	
    	$navs=array('#gear Settings','#box Cache');       
		if ($atec_wpca_apcu_enabled) 
		{
			$navs[]='#memory APCu';
			if ($atec_wpca_pcache) $navs=array_merge($navs,['#blog Page Cache']);
		}
		atec_nav_tab($url, $nonce, $nav, $navs, 999, false);
	
		echo '
		<div class="atec-g atec-border">';
			atec_flush();
	
			if ($nav=='Info') { @require(__DIR__.'/atec-info.php'); new ATEC_info(__DIR__); }
			{
				
				if (in_array($action,['WP_Ocache','APCu_Cache']))
				{
					echo '
					<div class="notice is-dismissible">
						<p>', esc_attr__('Flushing','atec-cache-apcu'), ' ', esc_html($action),' ... ';
						atec_flush();
						$result=false;
						switch ($action) 
						{
							case 'WP_Ocache': 
							{
								if ($_wp_using_ext_object_cache = wp_using_ext_object_cache()) wp_using_ext_object_cache(false);
								$result = wp_cache_flush(); wp_cache_init();
								if ($_wp_using_ext_object_cache) wp_using_ext_object_cache(true);
								break;
							}
							case 'APCu_Cache': if (function_exists('apcu_clear_cache')) $result=apcu_clear_cache(); break;
						}
						echo '<span class="atec-', $result?'green':'red', '">', ($result?esc_attr__('successful','atec-cache-apcu'):esc_attr__('failed','atec-cache-apcu')), '</span>';
						echo 
						'.</p>
					</div>';
				}
				
				if (!class_exists('ATEC_wpc_tools')) @require('atec-wpc-tools.php');
				$wpc_tools=new ATEC_wpc_tools();
	
				if ($nav=='Settings') { @require(__DIR__.'/atec-cache-apcu-settings.php'); new ATEC_wpcu_settings($url,$nonce); }
				elseif ($nav=='APCu') { @require(__DIR__.'/atec-cache-apcu-groups.php'); new ATEC_apcu_groups($url, $nonce, $action, 'atec_WPCA', $wpc_tools); }
				elseif ($nav=='Page_Cache') { @require(__DIR__.'/atec-cache-apcu-pcache-stats.php'); new ATEC_wpcu_pcache($url, $nonce, $action); }
				elseif ($nav=='Cache')
				{
			
					$arr=array('Zlib'=>ini_get('zlib.output_compression')?'#yes-alt':'#dismiss');
					atec_little_block_with_info('WP & APCu '.__('Object Cache','atec-cache-apcu'), $arr);
					$atec_wpca_key='atec_wpca_key';
			
					$wp_enabled=is_object($wp_object_cache);
					
					echo '<div class="atec-g atec-g-50">
					
							<div class="atec-border-white">
								<h4>WP ', esc_attr__('Object Cache','atec-cache-apcu'), ' '; atec_enabled($wp_enabled);
									echo ($wp_enabled?' <a title="'.esc_attr__('Empty cache','atec-cache-apcu').'" class="atec-right button" id="WP_Ocache_flush" href="'.esc_url($url).'&flush=WP_Ocache&nav=Cache&_wpnonce='.esc_attr($nonce).'"><span class="'.esc_attr(atec_dash_class('trash')).'"></span><span>'.esc_attr__('Site','atec-cache-apcu').'</span></a>':''),
								'</h4><hr>';
								if ($wp_enabled) { @require(__DIR__.'/atec-WPC-info.php'); new ATEC_WPcache_info($wpc_tools); }
							echo '
							</div>
							
							<div class="atec-border-white">
								<h4>APCu Cache'; atec_enabled($atec_wpca_apcu_enabled);
								echo ($atec_wpca_apcu_enabled?'<a title="'.esc_attr__('Empty cache','atec-cache-apcu').'" class="atec-right button" id="APCu_flush" href="'.esc_url($url).'&flush=APCu_Cache&nav=Cache&_wpnonce='.esc_attr($nonce).'"><span class="'.esc_attr(atec_dash_class('trash')).'"></span><span>'.esc_attr__('All','atec-cache-apcu').'</span></a>':''),
								'</h4><hr>';
								if ($atec_wpca_apcu_enabled) { @require(__DIR__.'/atec-APCu-info.php'); new ATEC_APCu_info($wpc_tools); }
								else 
								{
									atec_p('APCu '.esc_attr__('extension is NOT installed/enabled','atec-cache-apcu'));
									echo '<div class="atec-mt-5">'; @require(__DIR__.'/atec-APCu-help.php'); echo '</div>';
								}
							echo '
							</div>
						</div>';
				}
			}
	
		echo 
		'</div>
	</div>
</div>';

@require('atec-footer.php');

}}

new ATEC_wpcu_results();
?>