<?php
if (!defined('ABSPATH')) { exit(); }

class ATEC_wpcu_dashboard { function __construct() {

atec_admin_debug('Cache APCu','wpca');

$url			= atec_get_url();
$nonce		= wp_create_nonce(atec_nonce());
$action 	= atec_clean_request('action');
$nav 		= atec_clean_request('nav');
if ($nav=='') $nav='Settings';	

echo
'<div class="atec-page">';
	atec_header(__DIR__,'wpca','Cache APCu');

	echo 
	'<div class="atec-main">';
		atec_progress();

		global $atec_wpca_apcu_enabled;
	
    	$navs=array('#gear Settings','#box Cache');
		if ($atec_wpca_apcu_enabled) 
		{
			$navs[]='#memory APCu';
			if (atec_wpca_settings('cache')) $navs=array_merge($navs,['#blog Page Cache']);
		}
		atec_nav_tab($url, $nonce, $nav, $navs);
	
		echo
		'<div class="atec-g atec-border">';
			atec_flush();
	
			if ($nav=='Info') { @require(__DIR__.'/atec-info.php'); new ATEC_info(__DIR__); }
			{
				if ($action==='flush')
				{
					$type = atec_clean_request('type');
					echo '
					<div class="notice is-dismissible">
						<p>', esc_attr__('Flushing','atec-cache-apcu'), ' ', esc_html(str_replace('_',' ',$type)),' ... ';
						atec_flush();
						$result=false;
						switch ($type) 
						{
							case 'WP_Ocache': { $result = wp_cache_flush(); break; 	}
							case 'APCu_Cache': 
							{
								$result = false;
								if (class_exists('APCUIterator')) 
								{
									$arr=['atec_WPCA_*_*'];
									if (defined('WP_APCU_KEY_SALT')) $arr[]=WP_APCU_KEY_SALT;
									foreach($arr as $a)
									{
										$apcu_it=new APCUIterator('/'.$a.'/');
										if (iterator_count($apcu_it)!==0) { foreach ($apcu_it as $entry) { apcu_delete($entry['key']); } }
										$result = true;
									}
								}
								break;
							}
						}
						if (!$result) echo '<span class="atec-green">', esc_attr__('failed','atec-cache-apcu'), '</span>.';
						echo 
						'</p>
					</div>';
					if ($result) atec_reg_inline_script('wpca_redirect','window.location.assign("'.esc_url($url).'&nav=Cache&action=flushed&type='.$type.'&_wpnonce='.$nonce.'")'); 
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
					
					global $wp_object_cache;
					$wp_enabled=is_object($wp_object_cache);
					
					if (str_contains($action,'flushed')) atec_success_msg(esc_attr__('Flushing','atec-cache-apcu').' '.esc_html(str_replace('_',' ',atec_clean_request('type'))).' '.esc_attr__('successful','atec-cache-apcu'));
					
					echo 
					'<div class="atec-g atec-g-50">
					
						<div class="atec-border-white">
							<h4>WP ', esc_attr__('Object Cache','atec-cache-apcu'), ' '; atec_enabled($wp_enabled);
								echo ($wp_enabled?' <a title="'.esc_attr__('Empty cache','atec-cache-apcu').'" class="atec-right button" id="WP_Ocache_flush" href="'.esc_url($url).'&action=flush&type=WP_Ocache&nav=Cache&_wpnonce='.esc_attr($nonce).'"><span class="'.esc_attr(atec_dash_class('trash')).'"></span><span>'.esc_attr__('Site','atec-cache-apcu').'</span></a>':''),
							'</h4><hr>';
							if ($wp_enabled) { @require(__DIR__.'/atec-WPC-info.php'); new ATEC_WPcache_info($wpc_tools); }
						echo '
						</div>
						
						<div class="atec-border-white">
							<h4>APCu Cache'; atec_enabled($atec_wpca_apcu_enabled);
							echo ($atec_wpca_apcu_enabled?'<a title="'.esc_attr__('Empty cache','atec-cache-apcu').'" class="atec-right button" id="APCu_flush" href="'.esc_url($url).'&action=flush&type=APCu_Cache&nav=Cache&_wpnonce='.esc_attr($nonce).'"><span class="'.esc_attr(atec_dash_class('trash')).'"></span><span>'.esc_attr__('Site','atec-cache-apcu').'</span></a>':''),
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

new ATEC_wpcu_dashboard();
?>