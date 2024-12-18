<?php
if (!defined( 'ABSPATH' )) { exit; }
if (!class_exists('ATEC_wpc_tools')) @require_once('atec-wpc-tools.php');

class ATEC_wpcu_settings { function __construct() {

$wpc_tools=new ATEC_wpc_tools();
require_once('atec-check.php');

global $atec_wpca_apcu_enabled;
$optName='atec_WPCA_settings';
$options=get_option($optName,[]);
$atec_wpca_pcache = $options['cache']??null==true;

$advanced=defined('WP_APCU_MU_PAGE_CACHE');
$arr=array('Advanced page cache'=>$advanced?'#yes-alt':'#dismiss');
atec_little_block_with_info('APCu - '.__('Settings','atec-cache-apcu'), $arr, $advanced?'atec-green':'atec-red');

echo '	
<div class="atec-g atec-g-50">
	<div>
    	<div class="atec-border-white">
    		<h4>APCu '.esc_attr__('Object Cache','atec-cache-apcu').' '; $wpc_tools->enabled($atec_wpca_apcu_enabled); echo '</h4><hr>';
			if ($atec_wpca_apcu_enabled)
			{    
				$apcu_cache=apcu_cache_info(true);
				if ($apcu_cache)
				{
					$total	= (int) $apcu_cache['num_entries']??0;
					$size	= (int) $apcu_cache['mem_size']??0;
					echo '
					<p style="padding: 4px;" class="atec-box-white">', 
					esc_attr__('Current size is','atec-cache-apcu'), ' <strong>', esc_attr(size_format($size)), '</strong> (', 
					esc_attr(number_format($total)), ' ', $total>1?esc_attr__('items','atec-cache-apcu'):esc_attr__('item','atec-cache-apcu'), 	').
					</p><br>';
				}
				else { $wpc_tools->error('',__('No object cache data available','atec-cache-apcu')); }

				if (defined('WP_APCU_KEY_SALT'))
				{
					atec_success_msg(__('You now have a persistent WP object cache','atec-cache-apcu'));
					$wpc_tools->p(__('This is the main feature of the plugin and will speed up your site','atec-cache-apcu'));
				}
				else atec_error_msg('APCu is enabled, but the persistent object cache is not installed.<br>'.__('Please deactivate/reactivate this plugin to install the object-cache.php script','atec-cache-apcu'));	
			}
			else { $wpc_tools->error('APCu',__('extension is NOT installed/enabled','atec-cache-apcu')); }

		echo '
		</div>
		<div class="atec-border-white">
		<h4>'.esc_attr__('APCu Page Cache','atec-cache-apcu').' '; $wpc_tools->enabled($atec_wpca_pcache); echo '</h4><hr>';
							
		if ($atec_wpca_apcu_enabled && class_exists('APCUIterator'))
		{    
			if (!empty($apcu_it=new APCUIterator('/atec_WPCA_p_/')))
			{
				$c=0; $size=0;
				foreach ($apcu_it as $entry) 
				{ if (!str_contains($entry['key'],'_h')) { $c++; $size+=$entry['mem_size']; } }
				echo '<p style="padding: 4px;" class="atec-box-white">', 
				esc_attr__('Current size is','atec-cache-apcu'), ' <strong>', esc_attr(size_format($size)),
				'</strong> (', esc_attr(number_format($c)), ' ', $c>1?esc_attr__('items','atec-cache-apcu'):esc_attr__('item','atec-cache-apcu'), 	').</p>';
			}
			else { $wpc_tools->error('',__('No page cache data available','atec-cache-apcu')); }
			echo '<p>'.esc_attr__('The page cache is an additional feature of this plugin','atec-cache-apcu').'.<br>'.esc_attr__('It will give your page an additonal boost, by delivering pages from APCu cache','atec-cache-apcu').'.</p>';
		}
		else { $wpc_tools->error('',__('APCu not enabled – Page cache disabled','atec-cache-apcu').'.'); }
		echo '<p>', esc_attr__('The page cache saves pages, posts and categories – no product/shop pages (WooCommerce).','atec-cache-apcu'), '</p>';
		if (defined('LITESPEED_ALLOWED') && LITESPEED_ALLOWED) 
		{ 
			atec_badge('',__('LiteSpeed-server and -cache plugin detected','atec-cache-apcu'),false); 
			atec_badge(__('Please do not use LiteSpeed page-cache together with APCu page-cache – choose either one','atec-cache-apcu'),'',true); 
		}
		echo '
		</div>
	</div>';

	echo '
	<div>';				
		if (!$atec_wpca_pcache) { atec_reg_inline_style('apcu_settings_form', '.form-table, form H2 { display:none; } .form-table:nth-of-type(1), form H2:nth-of-type(1) { display:table; }'); }
		if ($atec_wpca_apcu_enabled)
		{
			echo '
			<div id="atec_WPCA_settings" class="atec-border-white">
				<form method="post" action="options.php">
					<input type="hidden" name="atec_WPCA_settings[salt]" value="', esc_attr($options['salt']??''), '">';
					$slug = 'atec_WPCA';
				  	settings_fields($slug);
				  	do_settings_sections($slug);
					echo '<div style="margin-top: -10px;">';
						$licenseOk=atec_pro_feature(' - this will enable the<br>advanced page cache and give your site an extra boost',true);
					echo '</div>';
				  	submit_button(__('Save','atec-cache-apcu'));
				echo '
				</form>
				<p class="atec-red">', esc_attr__('Do not use multiple page cache plugins simultaneously.','atec-cache-apcu'), '</p>';
				atec_help('show_debug',__('„Show debug“','atec-cache-apcu'));
				echo '
				<div id="show_debug_help" class="atec-help atec-dn">',
				esc_attr__('The „Show debug“ feature is for temporary use. It will show a small green circle in the upper left corner, when the page is served from cache. In addition you will find further details in your browser console. Please flush the page cache, once you are done with testing.','atec-cache-apcu');
				echo '
				</div>
			</div>';
		}
	echo '
	</div>
</div>';
}}

new ATEC_wpcu_settings();
?>