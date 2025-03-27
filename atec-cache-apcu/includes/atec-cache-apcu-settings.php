<?php
if (!defined('ABSPATH')) { exit; }

class ATEC_wpcu_settings { 
	
function __construct($url,$nonce,$action) {
	
$optName='atec_WPCA_settings';
$options=get_option($optName,[]);

$arr = [];
if (defined('ATEC_APCU_OC_VERSION')) $arr['OC']=ATEC_APCU_OC_VERSION;
if (defined('WP_APCU_KEY_SALT')) $arr['OC salt']=WP_APCU_KEY_SALT;
$arr['PC salt']=$options['salt']??'-/-';
$is_zlib_enabled = filter_var(ini_get('zlib.output_compression'), FILTER_VALIDATE_BOOLEAN);
$arr['Zlib']=$is_zlib_enabled?'#yes-alt':'#dismiss';

atec_little_block_with_info('APCu - '.__('Settings','atec-cache-apcu'), $arr);

$atec_wpca_ocache = filter_var($options['ocache']??0,258);
$atec_wpca_pcache = filter_var($options['cache']??0,258);
$atec_wpca_advanced = defined('WP_APCU_MU_PAGE_CACHE');

$error = '';
if ($atec_wpca_ocache!==defined('WP_APCU_KEY_SALT')) { $error = 'KEY_SALT is '.(defined('WP_APCU_KEY_SALT')?'defined':'not defined'); }
elseif (!$atec_wpca_pcache && $atec_wpca_advanced) $error = 'MU_PAGE_CACHE is defined';

if ($error!=='') { atec_error_msg('The cache settings are inconsistent ('.$error.'). Please save again to auto-fix it'); }

echo '	
<div class="atec-g atec-g-50">

	<div>
    	<div class="atec-border-white">
    		<h4>APCu ', esc_attr__('Object Cache','atec-cache-apcu'), ' '; atec_enabled($atec_wpca_ocache); echo '</h4>';

			global $atec_wpca_apcu_enabled;
			if ($atec_wpca_apcu_enabled)
			{    
				$str = esc_attr__('Object Cache','atec-cache-apcu');
				atec_badge($str.' '.esc_attr__('is active','atec-cache-apcu'),$str.' '.esc_attr__('is inactive','atec-cache-apcu'),defined('WP_APCU_KEY_SALT'));
				echo 
				'<hr class="atec-mb-10">
				<form class="atec-form atec-mt-10" method="post" action="options.php">
					<input type="hidden" name="atec_WPCA_settings[salt]" value="', esc_attr($options['salt']??''), '">
					<div class="atec-form">'; 
						$slug = 'atec_WPCA'; settings_fields($slug); do_settings_sections($slug); submit_button(__('Save','atec-cache-apcu')); 
					echo 
					'</div>';
			}
			else atec_error_msg('APCu '.__('extension is NOT installed/enabled','atec-cache-apcu'));

			echo 
			'<br><hr>';

			$licenseOk = atec_pro_feature(' - '.__('this will enable the advanced','atec-cache-apcu').' '.__('object cache','atec-cache-apcu'),true);
			if (!$licenseOk) echo '<br class="atec-mb-20">';

			atec_info('ocache_debug',__('Object Cache','atec-cache-apcu'));
			echo '
			<div id="ocache_debug_help" class="atec-help atec-dn atec-mt-10">', esc_attr__('The object cache is the main feature of the plugin and will speed up your site','atec-cache-apcu'); echo '.</div>';
			
		echo
		'</div>
	</div>

	<div>		
		<div id="atec_WPCA_settings" class="atec-border-white">
			<h4>', esc_attr__('APCu Page Cache','atec-cache-apcu'), ' '; atec_enabled($atec_wpca_pcache); echo '</h4>';
			
			if ($atec_wpca_apcu_enabled)
			{
					if ($atec_wpca_advanced) atec_success_msg(__('The advanced page cache is installed','atec-cache-apcu'));
					else 
					{
						$str = esc_attr__('Page Cache','atec-cache-apcu');
						atec_badge($str.' '.esc_attr__('is active','atec-cache-apcu'),$str.' '.esc_attr__('is inactive','atec-cache-apcu'),$atec_wpca_pcache);
					}
				
					echo 
					'<hr class="atec-mb-10">
					<div class="atec-form">';
						settings_fields($slug.'_PC'); 
						do_settings_sections($slug.'_PC'); 
						submit_button(__('Save','atec-cache-apcu')); 
					echo 
					'</div>
				</form>';
			}
			else atec_error_msg('APCu '.__('extension is NOT installed/enabled','atec-cache-apcu'));
			
			echo 
			'<br><hr>';

			$licenseOk = atec_pro_feature(' - '.__('this will enable the advanced','atec-cache-apcu').' '.__('page cache','atec-cache-apcu'),true);
			if (!$licenseOk) echo '<br class="atec-mb-20">';
			
			if ($atec_wpca_pcache)
			{
				atec_help('show_debug',__('„Show debug“','atec-cache-apcu'));
				echo '
				<div id="show_debug_help" class="atec-help atec-dn">', esc_attr__('The „Show debug“ feature is for temporary use. It will show a small green circle in the upper left corner, when the page is served from cache. In addition you will find further details in your browser console. Please flush the page cache, once you are done with testing','atec-cache-apcu'), '.</div>';
			}
			
			atec_help('multi_pc',__('Multiple PC plugins','atec-cache-apcu'));
			echo '
			<div id="multi_pc_help" class="atec-help atec-dn atec-mt-10 atec-orange">', esc_attr__('Do not use multiple page cache plugins simultaneously','atec-cache-apcu'), '.</div>';

			atec_info('pcache_debug',__('Page Cache','atec-cache-apcu'));
			echo '
			<div id="pcache_debug_help" class="atec-help atec-dn atec-mt-10">', 
				esc_attr__('The page cache is an additional feature of this plugin','atec-cache-apcu'), '. ', 
				esc_attr__('It will give your page an additonal boost, by delivering pages from APCu cache','atec-cache-apcu'), '. ', 
				esc_attr__('The page cache saves pages, posts and categories – no product/shop pages (WooCommerce)','atec-cache-apcu'),
			'.</div>';
			
			if (is_multisite()) atec_warning_msg(__('The page cache is not designed to support multisites','atec-cache-apcu').'.<br>'.__('Please try the „Mega-Cache“-Plugin for multisites','atec-cache-apcu'),true);
			
			if (defined('LITESPEED_ALLOWED') && LITESPEED_ALLOWED) 
				atec_warning_msg(__('Please do not use LiteSpeed page-cache together with APCu page-cache – choose either one','atec-cache-apcu'),true);
			
	echo '
		</div>
	</div>
</div>';

atec_reg_inline_style('wpca_settings', '.atec-form { min-height: 260px; }');
}}

?>