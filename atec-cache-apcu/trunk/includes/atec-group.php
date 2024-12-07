<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_group { 

private function atec_clean_request_license($t): string { return atec_clean_request($t,'atec_license_nonce'); } 

function __construct() {
	
if (!defined('ATEC_TOOLS_INC')) @require_once(__DIR__.'/atec-tools.php');	

$url				= atec_get_url();
$nonce 		= wp_create_nonce(atec_nonce());
$action 		= atec_clean_request('action');

$atec_slug_arr = ['wpb','wpca','wpci','wpc','wpdb',		'wpd','wpdp','wpds','wpht','wpm',	'wpo','wppp','wppo','wppr','wpsh',	'wpsm','wps','wpsi','wpsv','wpta',	'wpur','wms','wpwp',	'wpmc'];

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

echo '
<div class="atec-page">';

	$mega = $plugin==='mega-cache';
	if ($license!=='true')	atec_header(__DIR__ ,'','atec','Plugins');
	else
	{
		$opensslEnabled = extension_loaded('openssl');
		if ($opensslEnabled) atec_admin_notice('warning','The openSSL extension is required for license handling.',true);

		$supportLink	= $mega?'https://wpmegacache.com/contact/':'https://atecplugins.com/contact/';
		$imgSrc = plugins_url('/assets/img/atec-group/'.($mega?'atec_wpmc_icon.svg':'atec_logo_blue.png'), __DIR__ );
		echo '
		<div class="atec-header">
			<h3 class="atec-mb-0 atec-center" style="line-height: 0.85em;">';
			// @codingStandardsIgnoreStart
			// Image is not an attachement
			echo '<sub><img alt="Plugin icon" src="', esc_url($imgSrc), '" style="height:22px;"></sub> ', esc_html($mega?'Mega-Cache':'atec-Plugins'), 
			'</h3>';
			// @codingStandardsIgnoreEnd		
			atec_progress_div();
			echo '
			<div class="atec-center">	
				<a style="position:relative;" class="atec-fs-12 atec-nodeco atec-btn-small" href="', esc_url($supportLink), '" target="_blank">
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
			if ($mega)
			{
				echo '
				<div class="atec-g atec-border atec-center" style="padding: 20px 10px;">
					<h3 class="atec-mt-0">';
					// @codingStandardsIgnoreStart
					// Image is not an attachement
					echo '<sub><img alt="Plugin icon" src="', esc_url( plugins_url( '/assets/img/atec-group/atec_wpmc_icon.svg', __DIR__ )), '" style="height:22px;"></sub>&nbsp;';
					// @codingStandardsIgnoreEnd
					echo 'Mega-Cache „PRO“ package', 
					'</h3>
					<div id="atec_group_pro_package">
					
						<div id="pro_package_welcome" class="atec-fit" style="margin: 0 auto;">
							<div class="atec-mt-5 atec-border-white atec-bg-w atec-fit" style="font-size: 16px !important; padding: 10px;">
								<ul class="atec-m-0">
								<li>⭐ 7 additional storage options.</li>					
								<li>⭐ ', esc_attr__('Access to all the „PRO“ features','atec-cache-apcu'), '.</li>
								<li>⭐ ', esc_attr__('„Lifetime-site-License“ for your site (domain)','atec-cache-apcu'), '.</li>
								</ul>
							</div>
						</div>	
						<br>
					
					<div>';
				
					echo '
					<center>';
					$c=0;
					$pattern = '/atec-[\w\-]+/';
					$imgSrc = plugins_url( '/assets/img/logos/', __DIR__ );
					$imgSrc = preg_replace($pattern, 'mega-cache', $imgSrc);
					foreach (['apcu','redis','memcached','sqlite','mongodb','mariadb','mysql'] as $a)
					{
						$c++;
						if ($c % 12===0) echo '<br>';
						// @codingStandardsIgnoreStart
						// Image is not an attachement
						echo '<img src="', esc_url($imgSrc.$a.'.svg'), '" style="height:22px; margin: 0 5px 10px 5px;">';
						// @codingStandardsIgnoreEnd
					}
					echo '
					</center>
				</div>';
			}
			else
			{
				echo '
				<div class="atec-g atec-border atec-center" style="padding: 20px 10px;">
					<h3 class="atec-mt-0">';
					// @codingStandardsIgnoreStart
					// Image is not an attachement
					echo '<sub><img alt="Plugin icon" src="', esc_url( plugins_url( '/assets/img/atec-group/atec_logo_blue.png', __DIR__ )), '" style="height:22px;"></sub>&nbsp;';
					// @codingStandardsIgnoreEnd
					echo esc_attr__('atec-Plugins „PRO“ package','atec-cache-apcu'), 
					'</h3>
					<div id="atec_group_pro_package">
					
						<div id="pro_package_welcome" class="atec-fit" style="margin: 0 auto;">
							<div class="atec-mt-5 atec-border-white atec-bg-w atec-fit" style="font-size: 16px !important; padding: 10px;">
								<ul class="atec-m-0">
								<li>⭐ ', esc_attr__('23 valuable plugins','atec-cache-apcu'), '.</li>					
								<li>⭐ ', esc_attr__('Access to all the „PRO“ features','atec-cache-apcu'), '.</li>
								<li>⭐ ', esc_attr__('„Lifetime-site-License“ for your site (domain)','atec-cache-apcu'), '.</li>
								</ul>
							</div>
						</div>	
						<br>
					
					<div>';
	
					echo '
					<center>';
					$c=0;
					$imgSrc = plugins_url( '/assets/img/atec-group/atec_', __DIR__ );
					foreach ($atec_slug_arr as $a)
					{
						$c++;
						if ($c % 13===0) echo '<br>';
						// @codingStandardsIgnoreStart
						// Image is not an attachement
						echo '<img src="', esc_url($imgSrc.$a.'_icon.svg'), '" style="height:22px; margin: 0 5px 10px 5px;">';
						// @codingStandardsIgnoreEnd
					}
					echo '
					</center>
				</div>';				
			}
			
			$licenseUrl = $mega?'https://wpmegacache.com/license/':'https://atecplugins.com/license';
			echo '<a class="atec-nodeco" style="width: fit-content !important; margin: 10px auto;" href="', esc_textarea($licenseUrl), '" target="_blank">
			<button class="button button-primary">', esc_attr__('GET YOUR „PRO“ PACKAGE NOW','atec-cache-apcu'), '</button></a>
			<div class="atec-small">Links to ', esc_textarea($licenseUrl), '</div>';

			echo '<p styl="font-size: 18px !important;">',
				esc_attr__('Buy the „PRO“ package through one time payment','atec-cache-apcu'), '.<br>',
				esc_attr__('The license is valid for the lifetime of your site (domain)','atec-cache-apcu'), '.<br><b>',
				esc_attr__('No subscription. No registration required.','atec-cache-apcu'), '</b>
			</p>
			</div>';

			$include=__DIR__.'/atec-pro.php';
			if (!class_exists('ATEC_pro') && file_exists($include)) @include_once($include);
			if (class_exists('ATEC_pro')) { (new ATEC_pro)->atec_pro_form($url, $nonce, atec_clean_request('licenseCode'), $plugin); }
		}
		else
		{

		echo '
		<div class="atec-g">
			<table style="width: auto; margin:0 auto;" class="atec-table atec-table-med">
			<thead>
				<tr>
				<th></th>
				<th>Name (Link)</th>
				<th>', esc_attr__('Size','atec-cache-apcu'), '</th>
				<th>Status</th>
				<th>', esc_attr__('Preview','atec-cache-apcu'), '</th>
				<th>', esc_attr__('Installed','atec-cache-apcu'), '</th>
				<th>', esc_attr__('Description','atec-cache-apcu'), '</th>
				<th>', '<span class="', esc_attr(atec_dash_class('awards')), '" style="margin-right: 4px;"></span>', esc_attr__('PRO features','atec-cache-apcu'), '</th>			
				</tr>
			</thead>
			<tbody>';

		$atec_group_arr = [
			'backup','cache-apcu','cache-info','code','database',
			'debug','deploy','dir-scan','htaccess','meta',
			'optimize',	'page-performance','poly-addon','profiler','shell',
			'smtp-mail','stats','system-info','SVG','temp-admin',
			'user-roles','web-map-service','webp',
			'mega-cache'];
			
		$atec_pro_arr = [
		'FTP storage','Advanced page cache','PHP extensions','PHP-snippets','Cleanup comments, posts, revisions, transients and options',
		'Show queries, includes and wp-config.php; manage cron jobs','./.','Deep scan for folder sizes','./.','Automatically add description tag per page',
		'Enable performance and WooCommerce tweaks','./.','./.','Monitor page performance and queries','./.',
		'DKIM support and test; SPAM filter',	'Statistics on a world map','Show the php.ini file, PHP-extensions and system variables; wp-config.php and .htaccess content','./.','./.',
		'List and manage users','Discount on atecmap.com API key','PNG, GIF and BMP support',
		'Multiple storage options: APCu, Redis, Memcached, SQLite, MongoDB, MariaDB, MySQL'];

		$atec_group_arr_size 	= [83,112,99,68,85,		82,70,111,85,65,	115,371,79,85,76,	100,	822,115,68,72,		72,440,78,	250];
		$atec_active			= ['cache-apcu','cache-info','database',	'debug','dir-scan','stats',	'system-info','web-map-service','webp', 	'mega-cache'];
		$atec_review			= ['backup'];
		$atec_desc_arr		= [
							__('All-in-one Backup and restore solution – fast & reliable','atec-cache-apcu'),
							__('APCu object and page cache','atec-cache-apcu'),
							__('atec Cache Info & Statistics (OPcache, WP-object-cache, JIT, APCu, Memcached, Redis, SQLite-object-cache)','atec-cache-apcu'),
							__('Custom code snippets for WP','atec-cache-apcu'),	
							__('Optimize WP database tables','atec-cache-apcu'),

							__('Show debug log in admin bar','atec-cache-apcu'),																	
							__('Install and auto update `atec´ plugins','atec-cache-apcu'),
							__('Dir Scan & Statistics (Number of files and size per directory)','atec-cache-apcu'),
							__('Optimize the webserver /.htaccess file to increase the performance of your site','atec-cache-apcu'),
							__('Add custom meta tags to the head section','atec-cache-apcu'),

							__('Lightweight performance tuning plugin','atec-cache-apcu'),
							__('Measure the PageScore and SpeedIndex of your WordPress site','atec-cache-apcu'),
							__('Custom translation strings for polylang plugin','atec-cache-apcu'),
							__('Measure plugins & theme plus pages execution time','atec-cache-apcu'),
							__('Connect to a remote server via SSH','atec-cache-apcu'),

							__('Add custom SMTP mail settings to WP_Mail','atec-cache-apcu'),	
							__('Lightweight and GDPR compliant WP statistics','atec-cache-apcu'),				
							__('System Information (OS, server, memory, PHP info and more)','atec-cache-apcu'),
							__('Adds SVG support for media uploads.','atec-cache-apcu'),
							__('Create temporary admin accounts for maintenance purposes','atec-cache-apcu'),

							__('Manage WordPress User Roles and Capabilities','atec-cache-apcu'),
							__('Web map, conform with privacy regulations','atec-cache-apcu'),						
							__('Auto convert all images to WebP format','atec-cache-apcu'),

							__('Ultra fast page cache to improve site speed. Reduces CPU time, memory usage and database queries','atec-cache-apcu')
						];
					
		$c=0;
		global $wp_filesystem;
		WP_Filesystem();

		function fixName($p) { return ucwords(str_replace(['-','apcu','webp'],[' ','APCu','WebP'],$p)); }

		foreach ($atec_group_arr as $a)
		{
			$prefix = $a==='mega-cache'?'':'atec-';
			if ($prefix==='') atec_empty_tr();
			$installed = $wp_filesystem->exists(WP_PLUGIN_DIR.'/'.$prefix.esc_attr($a));
			$active = $installed && is_plugin_active($prefix.esc_attr($a).'/'.$prefix.esc_attr($a).'.php');
			echo '<tr>';
				// @codingStandardsIgnoreStart
				// Image is not an attachement
				echo '
				<td><img alt="Plugin icon" src="',esc_url( plugins_url( '/assets/img/atec-group/atec_'.esc_attr($atec_slug_arr[$c]).'_icon.svg', __DIR__ ) ) ,'" style="height:22px;"></td>';
				// @codingStandardsIgnoreEnd
				$isWP=in_array($atec_group_arr[$c], $atec_active);
				$atecplugins='https://atecplugins.com/';
				$link=$isWP?'https://wordpress.org/plugins/'.$prefix.esc_attr($a).'/':$atecplugins;
				echo '
				<td class="atec-nowrap"><a class="atec-nodeco" href="', esc_url($link) ,'" target="_blank">', esc_attr(fixName($atec_group_arr[$c])), '</a></td>
				<td class="atec-table-right">', esc_attr(size_format($atec_group_arr_size[$c]*1024,$atec_group_arr_size[$c]>1024?1:0)), '</td>';
				if ($isWP) echo '
					<td><span title="', esc_attr__('Published','atec-cache-apcu'), '" class="',esc_attr(atec_dash_class('wordpress')), '"></span></td>
					<td><a class="atec-nodeco" title="WordPress Playground" href="https://playground.wordpress.net/?plugin=', esc_attr($prefix.$atec_group_arr[$c]), '&blueprint-url=https://wordpress.org/plugins/wp-json/plugins/v1/plugin/', esc_attr($prefix.$atec_group_arr[$c]), '/blueprint.json" target="_blank"><span class="',esc_attr(atec_dash_class('welcome-view-site')), '"></span></a></td>';
				else 
				{
					$inReview=in_array($atec_group_arr[$c], $atec_review);
					echo '
					<td colspan="2">
						<span title="', $inReview?esc_attr__('In review','atec-cache-apcu'):esc_attr__('In progress','atec-cache-apcu'), '"><span class="',esc_attr(atec_dash_class($inReview?'visibility':'')) ,'"></span>
					</td>';
				}
				if ($installed) echo '<td title="Installed', ($active?' and active':''), '"><span class="',esc_attr(atec_dash_class(($active?'plugins-checked':'admin-plugins'), 'atec-'.($active?'green':''))), '"></span></td>';
				else echo '
				<td>
					<a title="Download from atecplugins.com" class="atec-nodeco atec-vam button button-secondary" style="padding: 0px 4px;" target="_blank" href="', esc_url($atecplugins), 'WP-Plugins/atec-', esc_attr($a), '.zip" download><span style="padding-top: 4px;" class="', esc_attr(atec_dash_class('download','')), '"></span></a></td>';
				echo '
				<td>',esc_attr($atec_desc_arr[$c]),'</td>
				<td><small>',esc_attr($atec_pro_arr[$c]),'</small></td>
				</tr>';
			$c++;
		} 
		echo '</tbody></table>
		</div>
		<center>
			<p class="atec-fs-12" style="max-width:80%;">',
				esc_attr__('All our plugins are optimized for speed, size and CPU footprint with an average of only 1 ms CPU time (frontend & backend)','atec-cache-apcu'), '.<br>',
				esc_attr__('Also, they share the same `atec-WP-plugin´ framework – so that shared code will only load once, even with multiple plugins enabled','atec-cache-apcu'), '.	<br>',
				esc_attr__('Tested with','atec-cache-apcu'), ': Linux (CloudLinux, Debian, Ubuntu), Windows & Mac-OS, Apache, NGINX & LiteSpeed.
			</p>
			<a class="atec-nodeco" class="atec-center" href="https://de.wordpress.org/plugins/search/atec/" target="_blank"><button class="button">', esc_attr__('All atec-plugins in the WordPress directory','atec-cache-apcu'), '.</button></a>
		</center>';
	}
	
	echo '
	</div>
</div>';
	
	if ($license) @require_once('atec-footer.php');
	atec_reg_inline_script('group','
	jQuery(".atec-page").css("gridTemplateRows","45px 1fr");
	jQuery("#atec_loading").css("opacity",0);', true);
	
}}

new ATEC_group();
?>