<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_wpcu_settings { 
	
function __construct($url,$nonce) {

global $atec_wpca_apcu_enabled;
$optName='atec_WPCA_settings';
$options=get_option($optName,[]);
$atec_wpca_ocache = filter_var($options['ocache']??0,258);
$atec_wpca_pcache = filter_var($options['cache']??0,258);
$atec_wpca_advanced = defined('WP_APCU_MU_PAGE_CACHE');

$update = false;
if (!$atec_wpca_ocache) { if (defined('WP_APCU_KEY_SALT')) { $options['ocache']=1; $atec_wpca_ocache=1; $update=true; } }
else { if (!defined('WP_APCU_KEY_SALT')) { $options['ocache']=0; $atec_wpca_ocache=0; $update=true; } }
if (!$atec_wpca_pcache && $atec_wpca_advanced) { $options['cache']=1; $atec_wpca_pcache=1; $update=true; }
if ($update) update_option($optName,$options); 

$arr = [];
if (defined('WP_APCU_KEY_SALT')) $arr['APCu salt']=WP_APCU_KEY_SALT;
$arr['PC salt']=$options['salt']??'';

atec_little_block_with_info('APCu - '.__('Settings','atec-cache-apcu'), $arr);

echo '	
<div class="atec-g atec-g-50">
	<div>
    	<div class="atec-border-white">
    		<h4>APCu ', esc_attr__('Object Cache','atec-cache-apcu'), ' '; atec_enabled($atec_wpca_ocache); echo '</h4>';

			if ($atec_wpca_apcu_enabled)
			{    
				$str = esc_attr__('Object Cache','atec-cache-apcu');
				atec_badge($str.' '.esc_attr__('is active','atec-cache-apcu'),$str.' '.esc_attr__('is inactive','atec-cache-apcu'),$atec_wpca_ocache);
				echo 
				'<hr class="atec-mb-10">
				<form class="atec-mt-10" method="post" action="options.php">
					<input type="hidden" name="atec_WPCA_settings[salt]" value="', esc_attr($options['salt']??''), '">';
					$slug = 'atec_WPCA';
					settings_fields($slug);
					do_settings_sections($slug);
					submit_button(__('Save','atec-cache-apcu'));		
			}
			else atec_error_msg('APCu '.__('extension is NOT installed/enabled','atec-cache-apcu'));

			echo 
			'<br><hr><br>
			<div class="atec-box-white atec-mt-10">', esc_attr__('The object cache is the main feature of the plugin and will speed up your site','atec-cache-apcu'); echo '</div>
		</div>
	</div>

	<div>		
		<div id="atec_WPCA_settings" class="atec-border-white">
			<h4>', esc_attr__('APCu Page Cache','atec-cache-apcu'), ' '; atec_enabled($atec_wpca_pcache); echo '</h4>';
			
			if ($atec_wpca_apcu_enabled)
			{
				if ($atec_wpca_advanced) atec_success_msg('The advanced page cache is installed');
				else 
				{
					$str = esc_attr__('Page Cache','atec-cache-apcu');
					atec_badge($str.' '.esc_attr__('is active','atec-cache-apcu'),$str.' '.esc_attr__('is inactive','atec-cache-apcu'),$atec_wpca_pcache);
				}
				
				echo 
				'<hr class="atec-mb-10">';
				settings_fields($slug.'_PC');
				do_settings_sections($slug.'_PC');
				echo '<div style="margin-top: -10px;">'; 
				$licenseOk=atec_pro_feature(' - '.__('this will enable the advanced','atec-cache-apcu').'<br>'.__('page cache and can give your site an extra ~20% speed boost','atec-cache-apcu'),true);
				echo '</div>';
				submit_button(__('Save','atec-cache-apcu'));
				echo '
				</form>';
			}
			else atec_error_msg('APCu '.__('extension is NOT installed/enabled','atec-cache-apcu'));
			
			echo '<br><hr><br>';
			atec_help('show_debug',__('„Show debug“','atec-cache-apcu'));
			echo '
			<div id="show_debug_help" class="atec-help atec-dn">', esc_attr__('The „Show debug“ feature is for temporary use. It will show a small green circle in the upper left corner, when the page is served from cache. In addition you will find further details in your browser console. Please flush the page cache, once you are done with testing','atec-cache-apcu').'.';
			echo '
			</div><br class="atec-clear">';

			atec_warning_msg(esc_attr__('Do not use multiple page cache plugins simultaneously','atec-cache-apcu'),true);
			if (is_multisite()) atec_error_msg(__('The page cache is not designed to support multisites','atec-cache-apcu').'.<br>'.__('Please try the „Mega-Cache“-Plugin for multisites','atec-cache-apcu'),true);
		
			if (defined('LITESPEED_ALLOWED') && LITESPEED_ALLOWED) 
			{ 
				atec_info(__('LiteSpeed-server and -cache plugin detected','atec-cache-apcu'),true);
				atec_warning(__('Please do not use LiteSpeed page-cache together with APCu page-cache – choose either one','atec-cache-apcu'),true);
			}

			echo 
			'<br>
			<div class="atec-box-white atec-mt-10">',
				esc_attr__('The page cache is an additional feature of this plugin','atec-cache-apcu'), '. ', 
				esc_attr__('It will give your page an additonal boost, by delivering pages from APCu cache','atec-cache-apcu'), '. ', 
				esc_attr__('The page cache saves pages, posts and categories – no product/shop pages (WooCommerce)','atec-cache-apcu'),
			'</div>';
			
	echo '
		</div>
	</div>
</div>';
}}

?>