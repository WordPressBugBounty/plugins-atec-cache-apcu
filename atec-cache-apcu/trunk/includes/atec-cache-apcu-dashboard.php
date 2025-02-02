<?php
if (!defined( 'ABSPATH' )) { exit; }
if (!class_exists('ATEC_wpc_tools')) @require_once('atec-wpc-tools.php');

class ATEC_wpcu_results { function __construct() {

atec_admin_debug('Cache APCu','wpca');

$wpc_tools=new ATEC_wpc_tools();

echo '
<div class="atec-page">';
	atec_header(__DIR__,'wpca','Cache APCu');

	echo '
	<div class="atec-main">';
		atec_progress();

		global $wp_object_cache, $atec_wpca_apcu_enabled;
		
		$flush=atec_clean_request('flush');
		if ($flush!='')
		{
			echo '
			<div class="notice is-dismissible">
			<p>', esc_attr__('Flushing','atec-cache-apcu'), ' ', esc_html($flush),' ... ';
			$result=false;
			switch ($flush) 
			{
				case 'OPcache': $result=opcache_reset(); break;
	    		case 'WP_Ocache': $result=$wp_object_cache->flush(); break;
				case 'APCu_Cache': if (function_exists('apcu_clear_cache')) $result=apcu_clear_cache(); break;
				case 'APCu_PCache': @require_once('atec-cache-apcu-pcache-tools.php'); atec_wpca_delete_page_cache_all(); $result=true; break; }
			echo '<span class="atec-', $result?'green':'red', '">', esc_attr__($result?'successful':'failed','atec-cache-apcu'), '</span>';
			echo '.</p>
			</div>';
		}
	
		$url		= atec_get_url();
		$nonce		= wp_create_nonce(atec_nonce());
		$nav 		= atec_clean_request('nav');
		$action 	= atec_clean_request('action');
	
		if ($nav=='') $nav='Settings';	
		$atec_wpca_pcache 	= atec_wpca_settings('cache');
	
    	$navs=array('#gear Settings','#box Cache');       
		if ($atec_wpca_apcu_enabled) 
		{
			$navs[]='#memory APCu';
			if ($atec_wpca_pcache) $navs=array_merge($navs,['#blog Page cache']);
		}
		atec_nav_tab($url, $nonce, $nav, $navs, 999, false);
	
		echo '
		<div class="atec-g atec-border">';
		atec_flush();
	
		if ($nav=='Info') { @require_once(__DIR__.'/atec-info.php'); new ATEC_info(__DIR__); }
		elseif ($nav=='Settings') { @require_once(__DIR__.'/atec-cache-apcu-settings.php'); }
		elseif ($nav=='APCu') { @require_once(__DIR__.'/atec-cache-apcu-groups.php'); new ATEC_apcu_groups($url, $nonce, 'atec_WPCA'); }
		elseif ($nav=='Page_cache') { @require_once(__DIR__.'/atec-cache-apcu-pcache-stats.php'); new ATEC_wpcu_pcache($url, $nonce, $action); }
		elseif ($nav=='Cache')
		{
	
			$arr=array('Zlib'=>ini_get('zlib.output_compression')?'#yes-alt':'#dismiss');
			atec_little_block_with_info('APCu & WP '.__('Object Cache','atec-cache-apcu'), $arr);
			$atec_wpca_key='atec_wpca_key';
	
			$wp_enabled=is_object($wp_object_cache);
			
			echo '<div class="atec-g atec-g-50">
			
					<div class="atec-border-white">
    	    			<h4>WP ', esc_attr__('Object Cache','atec-cache-apcu'), ' '; atec_enabled($wp_enabled);
		        			echo ($wp_enabled?' <a title="'.esc_attr__('Empty cache','atec-cache-apcu').'" class="atec-right button" id="WP_Ocache_flush" href="'.esc_url($url).'&flush=WP_Ocache&nav=Cache&_wpnonce='.esc_attr($nonce).'"><span class="'.esc_attr(atec_dash_class('trash')).'"></span> '.esc_attr__('Flush SITE','atec-cache-apcu').'</a>':''),
						'</h4><hr>';
						if ($wp_enabled) { @require_once(__DIR__.'/atec-WPC-info.php'); new ATEC_WPcache_info($op_conf,$op_status,$opcache_file_only,$wpc_tools); }
    				echo '
					</div>
					
					<div class="atec-border-white">
						<h4>APCu Cache'; atec_enabled($atec_wpca_apcu_enabled);
						echo ($atec_wpca_apcu_enabled?'<a title="'.esc_attr__('Empty cache','atec-cache-apcu').'" class="atec-right button" id="APCu_flush" href="'.esc_url($url).'&flush=APCu_Cache&nav=Cache&_wpnonce='.esc_attr($nonce).'"><span class="'.esc_attr(atec_dash_class('trash')).'"></span> '.esc_attr__('Flush ALL','atec-cache-apcu').'</a>':''),
						'</h4><hr>';
						if ($atec_wpca_apcu_enabled) { @require_once(__DIR__.'/atec-APCu-info.php'); new ATEC_APCu_info($wpc_tools); }
						else 
						{
							atec_p('APCu '.esc_attr__('extension is NOT installed/enabled','atec-cache-apcu'));
							echo '<div class="atec-mt-5">'; @require_once(__DIR__.'/atec-APCu-help.php'); echo '</div>';
						}
					echo '
					</div>
				</div>';
	    }
	
	echo '
		</div>
	</div>
</div>';

if (!class_exists('ATEC_footer')) @require_once('atec-footer.php');

}}

new ATEC_wpcu_results();
?>