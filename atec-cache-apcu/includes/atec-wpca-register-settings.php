<?php
defined('ABSPATH') || exit;

use ATEC\CHECK;
use ATEC\INIT;
use ATEC\TOOLS;
use ATEC\WPCA;

final class Settings
{
	public static function sanitize($input)
	{
		if (!is_array($input)) return [];
		$booleanArr = ['o_cache', 'o_admin', 'o_stats', 'p_cache', 'p_admin', 'p_debug'];
		foreach($booleanArr as $b) { $input[$b] = INIT::bool($input[$b]??0); }
		return $input;
	}
}

(function() {
	
	$is_updated = INIT::is_settings_updated();
	if (!$is_updated && (($_GET['action'] ?? '') === 'flushWPCA')) 
	{
		wp_cache_delete( 'alloptions', 'options' );
		WPCA::settings('',true);	// Refresh the WPCA settings cache
	}

	$page_slug = 'atec_WPCA';
	$option_group 	= $page_slug.'_settings';
	$section = $page_slug.'_section';

	if ($is_updated)	
	{
		$redirect = false;
		$update_settings = false;
		
		$settings = INIT::get_settings('wpca');
		$o_cache = INIT::bool($settings['o_cache'] ?? 0);
		if (!class_exists('ATEC_WPCA\\Install_OCache')) require(__DIR__.'/atec-wpca-install-ocache.php');
		$result = \ATEC_WPCA\Install_OCache::init($o_cache);
		if (!empty($result)) 
		{
			INIT::add_admin_notice_action(__DIR__, '', $result);
			$settings['o_cache']=0;
			$update_settings = true;
		}
		else $redirect=true;

		$p_cache = INIT::bool($settings['p_cache'] ?? 0);
		require(__DIR__.'/atec-wpca-install-pcache.php');
		$result = \ATEC_WPCA\Install_PCache::init($p_cache);
		if (!empty($result))
		{
			INIT::add_admin_notice_action(__DIR__, '', $result);
			$settings['p_cache']=0;
			$update_settings = true;
		}
		else $redirect=true;
		
		if ($update_settings) INIT::update_settings('wpca', $settings);
		if ($redirect) TOOLS::safe_redirect('wpca', 'flushWPCA');
	}

	register_setting($page_slug, $option_group, [CHECK::class, 'wpca_sanitize_fields']);
	add_settings_section($section, '', '', $page_slug);

	add_settings_field('o_cache', __('Object Cache', 'atec-cache-apcu'), [CHECK::class, 'checkbox'], $page_slug, $section, CHECK::opt_arr('o_cache', 'WPCA'));
	
	if (WPCA::settings('o_cache'))
	{
		$section.= '_OC';
		add_settings_section($section, '<small>'.__('Options', 'atec-cache-apcu').'</small>', '', $page_slug);
		add_settings_field('o_admin', __('Admin bar „OC Flush“ icon', 'atec-cache-apcu'), [CHECK::class, 'checkbox'], $page_slug, $section, CHECK::opt_arr('o_admin', 'WPCA'));
		add_settings_field('o_stats', __('Simple OC statistics', 'atec-cache-apcu'), [CHECK::class, 'checkbox'], $page_slug, $section, CHECK::opt_arr('o_stats', 'WPCA'));

	}

})();
?>