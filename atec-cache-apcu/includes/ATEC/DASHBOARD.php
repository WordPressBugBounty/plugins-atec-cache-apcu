<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\INIT;
use ATEC\FS;
use ATEC\GROUP;
use ATEC\TOOLS;

final class DASHBOARD {
	
private static function render_on_off_button($fill, $isOn = true)
{
	echo '
	<div class="atec-badge-icon">
		<svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
			<circle cx="32" cy="32" r="32" fill="', esc_attr($fill), '" />';
	
	if ($isOn) {
		echo '
			<path d="M32 8v24" stroke="#fff" stroke-width="6" stroke-linecap="round"/>
			<path d="M20 20a16 16 0 1 0 24 0" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round"/>';
	} 
	else {
		// Dismissed-style X
		echo '
			<line x1="20" y1="20" x2="44" y2="44" stroke="#fff" stroke-width="6" stroke-linecap="round"/>
			<line x1="44" y1="20" x2="20" y2="44" stroke="#fff" stroke-width="6" stroke-linecap="round"/>';
	}

	echo '
		</svg>
	</div>';
}


private static function group_badge($str='', $ok=true, $slug = '', $nav= '', $warn=false, $status=[]): void
{
	if ($str==='' && in_array($slug, ['wpfd','wprt']))
	{
		echo '<span style="color:#aaa;">·/·</span>';
		return;
	}

	$url = INIT::build_url($slug, '', $nav);

	$bg		= $str==='' ? '#fafafa' : ($ok ? '#e9f9ec' : ( $warn ? '#fffafa' : '#fbe9e9' ));			// soft background
	$border	= $str==='' ? '#ddd' : ($ok ? '#b4e2c1' : ( $warn ? '#edd' : '#e2b4b4' ));		// gentle border
	$color	 = $str==='' ? '#bbb' : ($ok ? '#2ebb71' : ( $warn ? '#a99' : '#bb2e2e' ));			// readable text
	$btnColor  = $str==='' ? '#bbb' : ($ok ? '#00cc00' : ( $warn ? '#bbb' : '#cc0000' ));		// bold icon
	
	$on_demand = $str==='';

	echo '
	<div class="atec-dilb atec-vat">
		<a href="' . esc_url($url) . '" class="atec-nodeco">
			<div class="atec-badge" style="background:', esc_attr($bg), '; border:1px solid ', esc_attr($border), '; border-radius:4px; padding: 1px 4px 1px 4px;">';
				echo '<div class="atec-badge-icon"', $on_demand ? ' style="margin-left: -5px; width: 0;"' : '', '>'; 
				if ($on_demand) echo '';
				else self::render_on_off_button($btnColor, $ok, $warn);
				echo '</div>';

				if ($on_demand) $str = 'On demand';
				echo '<div style="color:', esc_attr($color), '">', esc_html($str), '</div>';
				
				if ($warn)
				{
					echo '<div class="atec-dilb" style="background:#', $ok ? 'f9c9cc' : 'c9f9cc', '; border-top-right-radius:2px; border-bottom-right-radius:2px; height:100%; margin-right: -3px; padding: 0 2px 0 3px;">';
					if ($ok) 	echo ' <span title="Turn off for production!">‼️</span>';
					else echo ' <span title="Leave off for production!">❇️</span>';
					echo '</div>';
				}
				
			echo
			'</div>',
		'</a>',
	'</div>';
	if (!empty($status)) self::status_line($status);
}

private static function status_line($arr, $br=false): void
{
	if ($br) echo '<br class="atec-break">';
	echo
	'<div class="atec-status-line">',
		'<div class="atec-dilb atec-status-label">', esc_html($arr['label']), ':</div>',
		'<div class="atec-dilb atec-status-value">', esc_html($arr['text']), '</div>',
	'</div>';
}

private static function plugin_div($p)
{
	echo
	'<div class="atec-badge-row">';
	
	switch ($p->name)
	{
		case 'anti-spam':
			$result = self::wpas_count_spam();
			self::group_badge('Anti SPAM', true, $p->slug , 'Settings', false, $result);
			break;

		case 'backup':
			$settings = INIT::get_settings('wpb');
			$active = INIT::bool($settings['automatic'] ?? 0);
			$wpb_dir = $settings['path']??'';
			$latest = $wpb_dir!=='' ? self::wpb_latest_backup($wpb_dir) : [];
			self::group_badge('Automatic', $active, $p->slug, 'Settings', false, $latest);
			break;

		case 'banner':
			self::group_badge('Banner', INIT::get_settings('wpbn', 'enabled'), $p->slug);
			break;

		case 'bunny':
			self::group_badge('CDN Zone', INIT::get_settings('wpbu', 'zone') !== '', $p->slug);
			break;

		case 'cache-apcu':
			$settings = INIT::get_settings('wpca');
			$o_cache = INIT::bool($settings['o_cache'] ?? 0);
			$p_cache = INIT::bool($settings['p_cache'] ?? 0);		
			self::group_badge('Object Cache', $o_cache, $p->slug);
			self::group_badge('Page Cache', $p_cache, $p->slug);
			if ($p_cache) self::wpca_count_pages();
			break;

		case 'cache-memcached':
			self::group_badge('Object Cache', INIT::get_settings('wpcm', 'cache'), $p->slug);
			break;

		case 'cache-redis':
			self::group_badge('Object Cache', INIT::get_settings('wpcr', 'cache'), $p->slug);
			break;

		case 'config':
			$settings = INIT::get_settings('wpco');
			$active = !empty($settings);
			self::group_badge('Config', $active, $p->slug);
			break;

		case 'database':
			self::group_badge('Transitions', INIT::get_settings('wpdb','auto_timedout'), $p->slug);
			break;

		case 'debug':
			self::group_badge('DEBUG', defined('WP_DEBUG') && WP_DEBUG, $p->slug, 'Debug', true);
			self::group_badge('D_DISPLAY', WP_DEBUG && (defined('WP_DEBUG_DISPLAY') ? WP_DEBUG_DISPLAY : true), $p->slug, 'Debug', true);
			self::group_badge('D_LOG',defined('WP_DEBUG_LOG') && WP_DEBUG_LOG, $p->slug,'Debug',true);
			self::group_badge('SAVEQUERIES',defined('SAVEQUERIES') && SAVEQUERIES, $p->slug,'Queries',true);
			break;

		case 'deploy':
			self::group_badge('Auto deploy', INIT::get_settings('wpdp', 'auto'), $p->slug);
			break;

		case 'developer':
			self::group_badge('Console', INIT::get_settings('wpdv', 'console'), $p->slug, '', true);
			break;

		case 'svg':
		case 'duplicate-page-post':
			self::group_badge('Active', true, $p->slug);
			break;

		case 'hook-inspector':
			self::group_badge('Logging', INIT::get_settings('wphi', 'enabled'), $p->slug,'',true);
			break;

		case 'limit-login':
			$result = self::wpll_count_blocked();
			self::group_badge('Protected', true, $p->slug, '', false, $result);
			break;

		case 'login-url':
			self::group_badge('Custom URL', INIT::get_settings('wplu', 'url') !== '', $p->slug,'Settings');
			break;

		case 'maintenance-mode':
			self::group_badge('Maintenance', INIT::get_settings('wpmtm', 'enabled'), $p->slug);
			break;

		case 'mega-cache':
			self::group_badge('Page Cache', INIT::get_settings('wpmc', 'cache'), $p->slug,'Settings');
			break;

		case 'profiler':
			$settings = INIT::get_settings('wppr');
			self::group_badge('Profiler',INIT::bool($settings['processes'] ?? 0), $p->slug,'Processes',true);
			self::group_badge('PP Profiler',INIT::bool($settings['pages'] ?? 0), $p->slug,'Pages',true);
			break;

		case 'stats':
			$result = ['label' => 'Visitors lately', 'text' => (int) INIT::get_settings('wps','today')];
			self::group_badge('Active', true, $p->slug, '', false, $result);
			break;

		case 'smtp-mail':
			self::group_badge('Settings tested', INIT::get_settings('wpsm', 'mail_tested'), $p->slug, 'Settings');
			break;

		case 'temp-admin':
			$result = self::wpta_count_users();
			self::group_badge('', true, $p->slug);
			break;

		case 'webp':
			self::group_badge('Conversion', INIT::get_settings('wpwp','enabled'), $p->slug);
			break;

		default:
			self::group_badge('', true, $p->slug);
			break;
	}

	echo
	'</div>';
}

private static function wpas_count_spam()
{
	$stats = 
	get_option('atec_WPAS_stats', []);
	$thisMonth  = gmdate('Y-m');
	$thisCount  = $stats[$thisMonth] ?? 0;

	return ['label'=>'Blocked '.gmdate('y/m'), 'text'=>number_format($thisCount)];
}

private static function wpb_latest_backup($dir)
{
	$files = glob(rtrim($dir, '/') . '/*.zip');
	if (!$files) $files = [];
	else usort($files, function($a, $b) { return filemtime($b) - filemtime($a); 	});

	if (isset($files[0]))
	{
		$date = gmdate('m-d', filemtime($files[0]));
		preg_match('/^atec_backup_([A-Z]+)_/', basename($files[0]), $match);
		$type = $match[1] ?? '';
		$result =  $type. " {$date}";
	}
	else $result = 'No files found';
	return ['label' => 'Latest', 'text' => $result];
}

private static function wpca_count_pages()
{
	if (!defined('ATEC_OC_KEY_SALT')) return;
	if (class_exists('APCUIterator'))
	{
		$pattern = '/^atec_WPCA_[^ ]+_h_[0-9]+$/';
		$count = iterator_count(new \APCUIterator($pattern));
		return ['label' => 'Pages cached', 'text'=>$count];
	}
}

private static function wpll_count_blocked()
{
	$option_key		= 'wpll_stats';
	$settings 		= INIT::get_settings($option_key,[]);
	$stats 	 		= $settings['stats']??[];
	$count			= $stats['blocked'] ?? 0;
	return ['label' => 'Blocked', 'text'=>$count];
}

private static function wpta_count_users()
{
	$users = get_users();
	$tempUsers = 0;
	foreach($users as $u) { if (str_starts_with($u->user_login,'wpta_')) { $tempUsers++; } }
	return ['label' => 'Temp users', 'text'=>$tempUsers];
}

private static function load_style()
{
	TOOLS::reg_inline_style('dashboard',
		'.atec-badge { display: inline-flex; align-items: center; 	white-space: nowrap; padding: 2px 4px; margin: 0; border-radius: 4px; }
		.atec-badge-row { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; order:0; }
		.atec-badge-icon { display: inline-flex; align-items: center; justify-content: center; width: 16px; height: 16px; }
		.atec-badge > div { display: inline; }
	
		.atec-status-line { display: inline-flex; color: #444; font-size: 12px; align-self: middle; }
		.atec-status-label { font-weight: 600; margin-right: 4px; color: #222; }
		.atec-status-value { color: #555; }
		.dashicons { width:20px; height:20px; }
		.atec-page A.button { border-color: #e0e0e0 !important; background: #f6f6f6; padding: 0 !important; }
		.atec-page .button { min-width: 24px !important; min-height: 24px !important; }
		.atec-pro, .atec-free { align-self: start; margin-left: -5px; font-size: 10px; }
		');
}

private static function block_start($min_height=20, $min_width=255)
{ echo '<div class="atec-dilb atec-box-white atec-vat" style="min-width: ', esc_attr($min_width), 'px; min-height: ', esc_attr($min_height), 'px; margin: 0 5px 5px 5px; padding: 5px 5px 0 5px;">'; }

private static function block_end()
{ echo '</div>'; }

private static function border_block_start()
{ echo '<div class="atec-db atec-border-white atec-mb-10">'; }

private static function row_start($padding='0 5px')
{ echo '<div class="atec-row atec-mb-5" style="padding: ', esc_attr($padding), '; align-items: center;">'; }

private static function row_end()
{ echo '</div>'; }

private static function pro_or_free($a)
{
	if ($a->pro === 'PRO') echo '<div class="atec-pro">PRO</div>';
	else if ($a->pro === 'FREE') echo '<div class="atec-free">FREE</div>';
}

public static function init($plugin)
{
	self::load_style();
	TOOLS::flush();

	$una = TOOLS::una(__DIR__);
	if (in_array($una->action, ['play', 'pause']))
	{
		$plugin = TOOLS::clean_request('plugin', 'atec_group_nonce');
		$plugin_file = $plugin . '/' . $plugin . '.php';
		switch ($una->action)
		{
			case 'play':
				activate_plugins($plugin_file);
				break;
				
			case 'pause':
				deactivate_plugins($plugin_file);
				break;
		}			
	}

	static $active_plugins = null;
	if ($active_plugins===null) $active_plugins = get_option('active_plugins', []);

	$atec_group_arr = GROUP::all_plugins();
	$una = TOOLS::una(__DIR__);

	$integrity = TOOLS::clean_request('integrity', 'atec_group_nonce');
	if ($integrity!== '')
	{
		$integrity = INIT::bool($integrity);
		$integrityString= 'Thank you. Connection to atecplugins.com is '.($integrity== 'true'?'enabled':'disabled');
		if ($integrity) INIT::integrity_check($plugin);
		update_option('atec_allow_integrity_check', $integrity);
	}
	else $integrityString = '';

	$installed = [];
	$not_installed = [];
	$active = [];
	
	$show_active = false;
	$show_passive = false;
	
	foreach ($atec_group_arr as $a)
	{
		$prefix = INIT::plugin_prefix($a->name);
		$plugin = $prefix.$a->name;
	
		$is_installed = FS::exists(WP_PLUGIN_DIR.'/'.esc_attr($plugin));
		
		if ($is_installed) 
		{
			$installed[$a->name] = $plugin;
			$plugin_file = $plugin . '/' . $plugin . '.php';
			$is_active = in_array($plugin_file, $active_plugins, true);
			$active[$a->name] = $is_active;
			$show_active = $show_active || $is_active;
			$show_passive = $show_passive || !$is_active;
		}
		else $not_installed[$a->name] = $plugin;
	}

	echo
	'<div class="atec-page">',
		'<div class="atec-main">',
			'<div class="atec-g">';

				echo
				'<div class="atec-dilb atec-head atec-m-auto atec-mb-20">',
					'<h3 class="atec-mt-0 atec-mb-5">';
						\ATEC\SVG::echo('wpa');
						echo
						' atec Plugins · Dashboard',
					'</h3>',
				'</div>';

				if ($integrityString!== '') { echo '<div class="atec-db atec-center atec-mb-10">'; TOOLS::msg($integrity,$integrityString); echo '</div>'; }

				if ($show_active)
				{
					self::border_block_start();
	
						foreach ($atec_group_arr as $a)
						{
							if (!isset($installed[$a->name])) continue;
							if (!$active[$a->name]) continue;
	
							$plugin = $installed[$a->name];
	
							$fixed_name = INIT::plugin_fixed_name($a->name);
							self::block_start(75, $a->name === 'debug' ? 520 : 255);
								
								self::row_start();
									
									\ATEC\SVG::echo($a->slug);
									if ($active)
									{
										$href = INIT::build_url($a->slug);
										echo '<a class="atec-nodeco" href="', esc_url($href) ,'">', esc_attr($fixed_name), '</a>';
										self::pro_or_free($a);
										$href = INIT::build_url('group','pause', '', ['plugin'=>$installed[$a->name]]);
										echo
										'<div class="atec-row-right">',
											'<a class="atec-nodeco button button-secondary atec-btn-small" title="Deactivate" ',
												'href="', esc_url($href), '">',
												'<span class="', esc_attr(TOOLS::dash_class('controls-pause')), '"></span>',
											'</a>',
										'</div>';
									}
	
								self::row_end();
								
								echo
								'<hr>',
								'<div style="padding: 0 5px;">';
									self::plugin_div($a);
								echo
								'</div>';
								
							self::block_end();
						}
						
					self::block_end();
				}
	
				if ($show_passive)
				{
					self::border_block_start();
					
						foreach ($atec_group_arr as $a)
						{
							if (!isset($installed[$a->name])) continue;
							if ($active[$a->name]) continue;
							
							$fixed_name = INIT::plugin_fixed_name($a->name);
							self::block_start();
								
								self::row_start(0);
									\ATEC\SVG::echo($a->slug);
									$href = INIT::build_url('group','play', '', ['plugin'=>$installed[$a->name]]);
									echo 
									esc_attr($fixed_name);
									self::pro_or_free($a);
									echo
									'<div class="atec-row-right">',
										'<a class="atec-nodeco button button-secondary" title="Activate" ',
											'href="', esc_url($href), '">',
											'<span class="', esc_attr(TOOLS::dash_class('controls-play')), '"></span>',
										'</a>',
									'</div>';
								self::row_end();
								
							self::block_end();
						}
						
					self::block_end();
				}

				if (!empty($not_installed))
				{
					self::border_block_start();
					
						$atecplugins = 'https://atecplugins.com/';
						$megacache = 'https://wpmegacache.com/';
						
						foreach ($atec_group_arr as $a)
						{
							if (!isset($not_installed[$a->name])) continue;
	
							$href = 
								$a->wp
								? 'https://wordpress.org/plugins/'.$not_installed[$a->name].'/'
								:( $a->name ==='mega-cache' ? $megacache : $atecplugins);
					
							self::block_start();

								self::row_start();
									\ATEC\SVG::echo($a->slug);
									echo
									'<a class="atec-nodeco" href="', esc_url($href) ,'" target="_blank">', esc_attr(INIT::plugin_fixed_name($a->name)), '</a>';
									self::pro_or_free($a);
									echo
									'<div class="atec-row-right">
										<a title="Download from atecplugins.com" class="atec-nodeco button button-secondary atec-btn-small" ',
											'href="', esc_url($atecplugins), 'WP-Plugins/', esc_attr($plugin), '.zip" download>',
											'<span class="', esc_attr(TOOLS::dash_class('download')), '"></span>',
										'</a>',
									'</div>';
								self::row_end();
								
								echo
								'<hr>',
								'<div style="padding: 0 5px 10px 5px;">', 
									esc_html($a->desc), '.',
								'</div>';
	
							self::block_end();
						}
						
					self::block_end();
				}

				echo
				'<center>
					<p class="atec-fs-12" style="max-width:80%; line-height: 1.4em;">
						Optimized for speed, size, and minimal CPU footprint.<br>
						Adds under <strong>0.2 <small>ms</small></strong> per plugin — native PHP, zero bloat.<br>
						Fully tested on Linux, Windows, macOS — Apache, NGINX, LiteSpeed.
					</p>
					<a class="atec-nodeco" class="atec-center button" href="https://de.wordpress.org/plugins/search/atec/" target="_blank">Visit all brand plugins in the WordPress directory.</a>
				</center>';

				echo '
			</div>
		</div>
	</div>';

}

}
?>