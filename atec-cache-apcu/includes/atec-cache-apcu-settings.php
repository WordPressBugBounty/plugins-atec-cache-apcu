<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_wpcu_settings { 
	
function __construct($url,$nonce) {

global $atec_wpca_apcu_enabled;
$optName='atec_WPCA_settings';
$options=get_option($optName,[]);
$atec_wpca_pcache = $options['cache']??false==true;
$atec_wpca_ocache = $options['ocache']??false==true;
$atec_wpca_advanced = defined('WP_APCU_MU_PAGE_CACHE');

$update = false;
if (!$atec_wpca_pcache && $atec_wpca_advanced) { $options['cache']=true; $atec_wpca_pcache=true; $update=true; }
if (!$atec_wpca_ocache && defined('WP_APCU_KEY_SALT')) { $options['ocache']=true; $atec_wpca_ocache=true; $update=true; }

if (atec_clean_request('action')==='ocache') { $options['ocache'] = atec_clean_request('check_ocache')=='1'; $atec_wpca_ocache=$options['ocache']; $update=true; }

if ($update)
{
	wp_cache_delete('alloptions','options'); update_option($optName,$options);
	@require_once('atec-wpca-set-object-cache.php'); $installedMsg = atec_wpca_set_object_cache($options);
}		
else $installedMsg = '';

$arr = array('PC salt'=>$options['salt']??'');
if (defined('WP_APCU_KEY_SALT')) $arr['APCU salt (*)']=WP_APCU_KEY_SALT;

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
				<form method="post" action="'.esc_url($url).'&action=ocache&nav=Settings&_wpnonce='.esc_attr($nonce).'">
					<table class="form-table" role="presentation"><tbody><tr><th scope="row">', esc_attr__('Object Cache','atec-cache-apcu'), '</th><td>
						<div class="atec-ckbx">
							<label class="switch" for="check_ocache">
								<input id="check_ocache" name="check_ocache" type="checkbox" value="1" onclick="atec_check_validate(\'ocache\');"', checked($atec_wpca_ocache,true,true), '>
								<div class="slider round"></div>
							</label>
						</div>
					</td></tr></tbody></table>
					<p style="margin-top: -5px;"><input class="button button-primary"  type="submit" value="', esc_attr__('Save','atec-cache-apcu'), '"><br class="atec-clear"></p>
				</form>';
				
				if ($installedMsg!=='') atec_error_msg($installedMsg);
			}
			else atec_error_msg('APCu '.__('extension is NOT installed/enabled','atec-cache-apcu'));

		echo '
		</div>
		<div class="atec-border-white">
			<div class="atec-box-white atec-pt-0">';
				atec_p(__('The object cache is the main feature of the plugin and will speed up your site','atec-cache-apcu'));
			echo
			'</div>
		</div>
	</div>

	<div>		
		<div id="atec_WPCA_settings" class="atec-border-white">';
		
			echo '<h4>', esc_attr__('APCu Page Cache','atec-cache-apcu'), ' '; atec_enabled($atec_wpca_pcache); echo '</h4>';
			
			if (!$atec_wpca_pcache) { atec_reg_inline_style('apcu_settings_form', '.form-table:nth-of-type(2), form H2 { display:none; }'); }
			if ($atec_wpca_apcu_enabled)
			{
				if ($atec_wpca_advanced) atec_success_msg('The advanced page cache is installed');
				else 
				{
					$str = esc_attr__('Page Cache','atec-cache-apcu');
					atec_badge($str.' '.esc_attr__('is active','atec-cache-apcu'),$str.' '.esc_attr__('is inactive','atec-cache-apcu'),$atec_wpca_pcache);
				}
				
				echo '
					<hr class="atec-mb-10">
					<form class="atec-mt-10" method="post" action="options.php">
						<input type="hidden" name="atec_WPCA_settings[salt]" value="', esc_attr($options['salt']??''), '">
						<input type="hidden" name="atec_WPCA_settings[ocache]" value="', esc_attr($options['ocache']??''), '">';
						$slug = 'atec_WPCA';
						settings_fields($slug);
						do_settings_sections($slug);
						echo '<div style="margin-top: -10px;">'; 
						$licenseOk=atec_pro_feature(' - '.__('this will enable the advanced','atec-cache-apcu').'<br>'.__('page cache and can give your site an extra ~20% speed boost','atec-cache-apcu'),true); 
						echo '</div>';
						submit_button(__('Save','atec-cache-apcu'));
					echo '
					</form>';

			}
			else atec_error_msg('APCu '.__('extension is NOT installed/enabled','atec-cache-apcu'));
			
			echo '
			</div>
			
			<div class="atec-border-white">';

				atec_help('show_debug',__('„Show debug“','atec-cache-apcu'));
				echo '
				<div id="show_debug_help" class="atec-help atec-dn">', esc_attr__('The „Show debug“ feature is for temporary use. It will show a small green circle in the upper left corner, when the page is served from cache. In addition you will find further details in your browser console. Please flush the page cache, once you are done with testing','atec-cache-apcu').'.';
				echo '
				</div><br class="atec-clear"><br>';

				atec_warning_msg(esc_attr__('Do not use multiple page cache plugins simultaneously','atec-cache-apcu'),false);
				if (is_multisite()) atec_error_msg(__('The page cache is not designed to support multisites','atec-cache-apcu').'.<br>'.__('Please try the „Mega-Cache“-Plugin for multisites','atec-cache-apcu'));
			
				if (defined('LITESPEED_ALLOWED') && LITESPEED_ALLOWED) 
				{ 
					atec_info(__('LiteSpeed-server and -cache plugin detected','atec-cache-apcu'),false);
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