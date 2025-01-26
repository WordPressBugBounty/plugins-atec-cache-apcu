<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_group { 

private function atec_clean_request_license($t): string { return atec_clean_request($t,'atec_license_nonce'); } 

private function atec_group_star_list($mega)
{
	echo 
	'<div id="pro_package">
		<div class="atec-border-white atec-bg-w atec-fit" style="font-size: 16px !important; padding: 0 20px; text-align: left; margin:0 auto;">
			<ul class="atec-p-0">
				<li>🎁 <strong>', $mega?'Seven additional storage options':esc_attr__('Including 32 valuable plugins','atec-cache-apcu'), '.</strong></li>
				<li style="line-height:5px;"><br></li>
				<li>⭐ ', esc_attr__('Priority support','atec-cache-apcu'), '.</li>
				<li>⭐ ', esc_attr__('Upgrades & updates','atec-cache-apcu'), '.</li>';
				
				if ($mega) 
				echo '
				<li>⭐ Custom post types.</li>
				<li>⭐ WooCommerce product caching.</li>';
				else
				echo '
				<li>⭐ ', esc_attr__('„Lifetime-site-License“','atec-cache-apcu'), '.</li>
				<li>⭐ ', esc_attr__('Access to all the „PRO“ features','atec-cache-apcu'), '.</li>';		
			echo 
			'</ul>
		</div>';
}

function __construct() {
	
if (!defined('ATEC_TOOLS_INC')) @require_once(__DIR__.'/atec-tools.php');	
if (!function_exists('atec_fix_name')) 
{ function atec_fix_name($p) { return ucwords(str_replace(['-','apcu','webp','svg','htaccess'],[' ','APCu','WebP','SVG','HTaccess'],$p)); } }

$url				= atec_get_url();
$nonce 		= wp_create_nonce(atec_nonce());
$action 		= atec_clean_request('action');

$atec_group_arr=[];
require_once(__DIR__.'/atec-group-array.php');

$license 			= $this->atec_clean_request_license('license');
if ($license==='') $license = atec_clean_request('license');

$plugin = $this->atec_clean_request_license('plugin');
if ($plugin==='') $plugin = atec_clean_request('plugin');

$integrity			= $this->atec_clean_request_license('integrity');
$integrityString 	= '';
if ($integrity!=='')
{
	$integrityString='Thank you. Connection to atecplugins.com is '.($integrity=='true'?'enabled':'disabled');
	if ($integrity=='true') atec_integrity_check(__DIR__,$plugin);
	update_option('atec_allow_integrity_check',$integrity);
}

$goupAssetPath = plugins_url('/assets/img/atec-group/',__DIR__);
echo '
<div class="atec-page">';

	$mega = $plugin==='mega-cache';
	if ($license!=='true')	atec_header(__DIR__ ,'','atec Plugins','');
	else
	{
		if (!extension_loaded('openssl')) atec_admin_notice('warning','The openSSL extension is required for license handling.',true);

		echo '
		<div class="atec-header">
			<h3 class="atec-mb-0 atec-center" style="line-height: 0.85em;">';
			// @codingStandardsIgnoreStart
			// Image is not an attachement
				echo '<sub><img class="atec-plugin-icon" alt="Plugin icon" src="', esc_url($goupAssetPath.'atec_'.($mega?'wpmc':'wpa').'_icon.svg'), '" style="height: 22px;"></sub> ', 
				esc_html($mega?'Mega-Cache':'atec-Plugins'), 
			'</h3>';
			// @codingStandardsIgnoreEnd		
			atec_progress_div();
			echo '
			<div class="atec-center">	
				<a class="atec-fs-12 atec-nodeco atec-btn-small" style="position:relative;" href="', esc_url('https://'.($mega?'wpmegacache':'atecplugins').'.com/contact/'), '" target="_blank">
				<span class="', esc_attr(atec_dash_class('sos')), '"></span> Plugin contact</a>
			</div>
		</div>';
	}
	
	echo '
	<div class="atec-main" style="padding-top: 30px;">';
		atec_progress();
		
		if ($integrityString!=='') { echo '<br><center>'; atec_success_msg($integrityString); echo '</center>'; }
		if ($license=='true')
		{
			echo 
			'<div class="atec-g atec-border atec-center" style="padding: 20px 10px;">
				<h3 class="atec-mt-0">';
				// @codingStandardsIgnoreStart
				// Image is not an attachement
				echo '<sub><img class="atec-plugin-icon" alt="Plugin icon" src="', esc_url($goupAssetPath.'atec_'.($mega?'wpmc':'wpa').'_icon.svg'), '" style="height: 22px;"></sub>&nbsp;';
				// @codingStandardsIgnoreEnd
				echo $mega?'Mega-Cache „PRO“ package':esc_attr__('atec-Plugins „PRO“ package','atec-cache-apcu'), 
				'</h3>';
				$this->atec_group_star_list($mega);
				echo 
				'<div class="atec-db atec-fit atec-box-white" style="margin: 25px auto; padding-bottom:0;">';
					if ($mega)
					{
						$pattern = '/atec-[\w\-]+/';
						$imgSrc = preg_replace($pattern, 'mega-cache', plugins_url( '/assets/img/logos/', __DIR__ ));
						foreach (['apcu','redis','memcached','sqlite','mongodb','mariadb','mysql'] as $a)
						{
							// @codingStandardsIgnoreStart
							// Image is not an attachement
							echo '<img class="atec-plugin-icon" src="', esc_url($imgSrc.$a.'.svg'), '" style="height: 22px; margin: 0 5px 10px 5px;">';
							// @codingStandardsIgnoreEnd
						}
					}
					else
					{
						$c=0;
						foreach ($atec_group_arr as $a)
						{
							$c++;
							if ($a['slug']==='wpmc') continue;
							if ($c % 17===0) echo '<br>';
							// @codingStandardsIgnoreStart
							// Image is not an attachement
							echo '<img class="atec-plugin-icon" src="', esc_url($goupAssetPath.'atec_'.$a['slug'].'_icon.svg'), '" style="height: 22px; margin: 0 5px 10px 5px;">';
							// @codingStandardsIgnoreEnd
						}	
					}
					echo 
				'</div>';
				
				$licenseUrl = $mega?'https://wpmegacache.com/license/':'https://atecplugins.com/license';
				echo 
				'<a class="atec-nodeco" style="width: fit-content !important; margin: 10px auto;" href="', esc_textarea($licenseUrl), '" target="_blank">
					<button class="button button-primary">', esc_attr__('GET YOUR „PRO“ PACKAGE NOW','atec-cache-apcu'), '</button>
				</a>
				<div class="atec-small">Links to ', esc_textarea($licenseUrl), '</div>';
	
				echo 
				'<p styl="font-size: 18px !important;">',
					esc_attr__('Buy the „PRO“ package through one time payment','atec-cache-apcu'), '.<br>',
					esc_attr__('The license is valid for the lifetime of your site (domain)','atec-cache-apcu'), '.<br><b>',
					esc_attr__('No subscription. No registration required.','atec-cache-apcu'), '</b>
				</p>';
				
			echo 
			'</div>'; // pro_package DIV
						
			$include=__DIR__.'/atec-pro.php';
			if (!class_exists('ATEC_pro') && file_exists($include)) @include_once($include);
			if (class_exists('ATEC_pro')) { (new ATEC_pro)->atec_pro_form($url, $nonce, atec_clean_request('licenseCode'), $plugin); }

			echo 
			'</div>'; // atec-g DIV
		}
		else
		{
			echo '
			<div class="atec-g atec-fit" style="margin:0 auto;">';
				atec_table_header_tiny(['','Name (Link)','#wordpress','#admin-multisite',esc_attr__('Status','atec-cache-apcu'),esc_attr__('Description','atec-cache-apcu'),'#awards '.esc_attr__('PRO features','atec-cache-apcu')],'','atec-table-med');
		
				$atec_active			= ['cache-apcu','cache-info','database','debug','dir-scan',		'stats','system-info','web-map-service','webp','mega-cache'];
				$atec_review			= ['backup'];
							
				$c=0;
				global $wp_filesystem;
				WP_Filesystem();
		
				foreach ($atec_group_arr as $a)
				{
					$prefix = $a['name']==='mega-cache'?'':'atec-';
					if ($prefix==='') atec_empty_tr();
					$installed = $wp_filesystem->exists(WP_PLUGIN_DIR.'/'.esc_attr($prefix.$a['name']));
					$active = $installed && is_plugin_active(esc_attr($prefix.$a['name']).'/'.esc_attr($prefix.$a['name']).'.php');
					echo '<tr>';
						// @codingStandardsIgnoreStart
						// Image is not an attachement
						echo '
						<td><img class="atec-plugin-icon" alt="Plugin icon" src="',esc_url($goupAssetPath.'atec_'.esc_attr($a['slug']).'_icon.svg'), '" style="height: 22px;"></td>';
						// @codingStandardsIgnoreEnd
						$atecplugins='https://atecplugins.com/';
						$link=$a['wp']?'https://wordpress.org/plugins/'.$prefix.esc_attr($a['name']).'/':$atecplugins;
						echo '
						<td class="atec-nowrap"><a class="atec-nodeco" href="', esc_url($link) ,'" target="_blank">', esc_attr(atec_fix_name($a['name'])), '</a></td>';
						if ($a['wp']) echo '
							<td><a class="atec-nodeco" title="WordPress Playground" href="https://playground.wordpress.net/?plugin=', esc_attr($prefix.$a['name']), '&blueprint-url=https://wordpress.org/plugins/wp-json/plugins/v1/plugin/', esc_attr($prefix.$a['name']), '/blueprint.json" target="_blank"><span class="',esc_attr(atec_dash_class('welcome-view-site')), '"></span></a></td>';
						else 
						{
							$inReview=in_array($a['name'], $atec_review);
							echo 
							'<td>
								<span title="', $inReview?esc_attr__('In review','atec-cache-apcu'):esc_attr__('In progress','atec-cache-apcu'), '"><span class="',esc_attr(atec_dash_class($inReview?'visibility':'')) ,'"></span>
							</td>';
						}
						echo '<td>', $a['multi']?'<span class="'.esc_attr(atec_dash_class('yes')).'"></span>':'', '</td>';
						if ($installed) echo '<td title="Installed', ($active?' and active':''), '"><span class="',esc_attr(atec_dash_class(($active?'plugins-checked':'admin-plugins'), 'atec-'.($active?'green':'grey'))), '"></span></td>';
						else echo '
						<td>
							<a title="Download from atecplugins.com" class="atec-nodeco atec-vam button button-secondary" style="padding: 0px 4px;" target="_blank" href="', esc_url($atecplugins), 'WP-Plugins/atec-', esc_attr($a['name']), '.zip" download><span style="padding-top: 4px;" class="', esc_attr(atec_dash_class('download','')), '"></span></a></td>';
						echo '
						<td>',esc_attr($a['desc']),'</td>
						<td><small>',esc_attr($a['pro']),'</small></td>
						</tr>';
					$c++;
				} 
				atec_table_footer();
			echo 
			'</div>
			
			<center>
				<p class="atec-fs-12" style="max-width:80%;">',
					esc_attr__('All our plugins are optimized for speed, size and CPU footprint with an average of only 1 ms CPU time','atec-cache-apcu'), '.<br>',
					esc_attr__('Also, they share the same „atec-WP-plugin“ framework. Shared code will only load once across multiple plugins','atec-cache-apcu'), '.	<br>',
					esc_attr__('Tested with','atec-cache-apcu'), ': Linux (CloudLinux, Debian, Ubuntu), Windows & Mac-OS, Apache, NGINX & LiteSpeed.
				</p>
				<a class="atec-nodeco" class="atec-center" href="https://de.wordpress.org/plugins/search/atec/" target="_blank"><button class="button">', esc_attr__('Visit atec-plugins in the WordPress directory','atec-cache-apcu'), '.</button></a>
			</center>';
		}
	
	echo '
	</div>
</div>';
	
	if ($license) @require_once('atec-footer.php');
	atec_reg_inline_script('group','jQuery(".atec-page").css("gridTemplateRows","45px 1fr"); jQuery("#atec_loading").css("opacity",0);', true);
	
}}

new ATEC_group();
?>