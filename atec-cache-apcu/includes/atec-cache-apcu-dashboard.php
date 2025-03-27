<?php
if (!defined('ABSPATH')) { exit; }

class ATEC_wpcu_dashboard { function __construct() {

atec_admin_debug('Cache APCu','wpca');

$url			= atec_get_url();
$nonce		= wp_create_nonce(atec_nonce());
$action 	= atec_clean_request('action');
$nav 		= atec_clean_request('nav');
if ($nav==='') $nav='Settings';	

echo
'<div class="atec-page">';
	$licenseOk = atec_header(__DIR__,'wpca','Cache APCu');

	echo 
	'<div class="atec-main">';
		atec_progress();

		global $atec_wpca_apcu_enabled;
	
    	$navs=array('#admin-generic Settings','#archive Cache');
		if ($atec_wpca_apcu_enabled) 
		{
			$navs[]='#memory APCu';
			if (atec_wpca_settings('cache')) $navs=array_merge($navs,['#blog Page Cache']);
		}
		atec_nav_tab($url, $nonce, $nav, $navs, 999, $licenseOk, '', false, false, defined('WP_APCU_KEY_SALT') && function_exists('wp_cache_wpc_array') && $licenseOk);
	
		echo
		'<div class="atec-g atec-border">';
			atec_flush();
	
			if ($nav==='Info') { require(__DIR__.'/atec-info.php'); new ATEC_info(__DIR__); }
			{
				if (!class_exists('ATEC_wpc_tools')) require('atec-wpc-tools.php');
	
				if ($nav==='Settings') { require(__DIR__.'/atec-cache-apcu-settings.php'); new ATEC_wpcu_settings($url,$nonce,$action); }
				elseif ($nav==='APCu') { require(__DIR__.'/atec-cache-apcu-groups.php'); new ATEC_apcu_groups($url, $nonce, $action, 'atec_WPCA'); }
				elseif ($nav==='Debug') { require(__DIR__.'/atec-cache-apcu-wpc-groups.php'); new ATEC_apcu_wpc_groups($url, $nonce, $action, 'atec_WPCA'); }
				elseif ($nav==='Page_Cache') { require(__DIR__.'/atec-cache-apcu-pcache-stats.php'); new ATEC_wpcu_pcache($url, $nonce, $action); }
				elseif ($nav==='Cache')
				{
			
					if ($action==='flush')
					{
						$type = atec_clean_request('type');
						ATEC_wpc_tools::flushing_start($type);
							$result=false;
							switch ($type) 
							{
								case 'OC_Stats': { apcu_delete(WP_APCU_KEY_SALT.':atec_wpca_oc_stats'); $result = true; break; }
								case 'WP_Ocache': { $result = wp_cache_flush(); break; }
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
						ATEC_wpc_tools::flushing_end($result);

						if ($result) atec_reg_inline_script('wpca_redirect','window.location.assign("'.esc_url($url).'&nav=Cache&action=flushed&type='.$type.'&_wpnonce='.$nonce.'")'); 
					}	

					atec_little_block('WP & APCu '.__('Object Cache','atec-cache-apcu'));
					$atec_wpca_key='atec_wpca_key';
					
					global $wp_object_cache;
					$wp_enabled=is_object($wp_object_cache);
					
					if (str_contains($action,'flushed')) atec_success_msg(esc_attr__('Flushing','atec-cache-apcu').' '.esc_html(str_replace('_',' ',atec_clean_request('type'))).' '.esc_attr__('successful','atec-cache-apcu'));
					
					echo 
					'<div class="atec-g atec-g-50">
					
						<div>

							<div class="atec-border-white atec-dilb atec-fit atec-vat atec-mr-10">
								<h4>WP ', esc_attr__('Object Cache','atec-cache-apcu'), ' '; atec_enabled($wp_enabled);
									echo ($wp_enabled?' <a title="'.esc_attr__('Empty cache','atec-cache-apcu').'" class=" atec-float-right atec-ml-20 button" style="margin-top: -5px;" id="WP_Ocache_flush" href="'.esc_url($url).'&action=flush&type=WP_Ocache&nav=Cache&_wpnonce='.esc_attr($nonce).'"><span class="'.esc_attr(atec_dash_class('trash')).'"></span><span>'.esc_attr__('Site','atec-cache-apcu').'</span></a>':''),
								'</h4><hr>';
								if ($wp_enabled) { require(__DIR__.'/atec-WPC-info.php'); new ATEC_WPcache_info(); }
							echo '
							</div>
							
							<div class="atec-border-white atec-dilb atec-fit atec-vat">
								<h4>APCu Cache'; atec_enabled($atec_wpca_apcu_enabled);
								echo ($atec_wpca_apcu_enabled?'<a title="'.esc_attr__('Empty cache','atec-cache-apcu').'" class=" atec-float-right atec-ml-20 button" style="margin-top: -5px;" id="APCu_flush" href="'.esc_url($url).'&action=flush&type=APCu_Cache&nav=Cache&_wpnonce='.esc_attr($nonce).'"><span class="'.esc_attr(atec_dash_class('trash')).'"></span><span>'.esc_attr__('Site','atec-cache-apcu').'</span></a>':''),
								'</h4><hr>';
								if ($atec_wpca_apcu_enabled) { require(__DIR__.'/atec-APCu-info.php'); new ATEC_APCu_info(); }
								else 
								{
									atec_p('APCu '.esc_attr__('extension is NOT installed/enabled','atec-cache-apcu'));
									echo '<div class="atec-mt-5">'; require(__DIR__.'/atec-APCu-help.php'); echo '</div>';
								}
							echo '
							</div>
							
						</div>';
						
						if (defined('WP_APCU_KEY_SALT') && !empty($stats = apcu_fetch(WP_APCU_KEY_SALT.':atec_wpca_oc_stats')))
						{
							echo
							'<div>
							
								<div class="atec-border-white atec-dilb atec-fit atec-vat">
								<h4>WP OC Overall Statistics<a title="'.esc_attr__('Reset statistics','atec-cache-apcu').'" class=" atec-float-right atec-ml-20 button" style="margin-top: -5px;" href="'.esc_url($url).'&action=flush&type=OC_Stats&nav=Cache&_wpnonce='.esc_attr($nonce).'"><span class="'.esc_attr(atec_dash_class('trash')).'"></span></a></h4><hr>';
								
								$diff 		= time()-($ts = $stats['ts']??0);
								$dayFrac	= $diff/86400;
								echo'
								<table class="atec-table atec-table-tiny atec-table-td-first">
								<tbody>
									<tr><td>Started:</td><td>', esc_attr(gmdate('m/d H:i',$ts)), '</td></tr>
									<tr><td>Requests:</td><td>', esc_attr($stats['count']??0), '</td></tr>';
									atec_empty_TR();
									$hits = $stats['hits']??0; $misses = $stats['misses']??0; $total = $hits+$misses;
									$sets = $stats['sets']??0;
									echo
									'<tr><td>Set:</td><td>', esc_html(number_format($sets)), '</td></tr>
									<tr><td>Get:</td><td>', esc_html(number_format($hits+$misses)), '</td></tr>';
									if ($dayFrac>1)
									{
										atec_empty_TR();
										echo
										'<tr><td>Set/d:</td><td>', esc_html(number_format($sets/$dayFrac)), '</td></tr>
										<tr><td>Get/d:</td><td>', esc_html(number_format(($total)/$dayFrac)), '</td></tr>';
									}
								echo
								'</tbody>
								</table>';	
								
								ATEC_wpc_tools::hitrate($hits*100/$total,$misses*100/$total);
							
								echo
								'</div>
	
							</div>';
						}
						
					echo
					'</div>';
				}
			}
	
		echo 
		'</div>
	</div>
</div>';

require('atec-footer.php');

}}

new ATEC_wpcu_dashboard();
?>