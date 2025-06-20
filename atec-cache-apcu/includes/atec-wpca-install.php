<?php
defined('ABSPATH') || exit;

use ATEC\INIT;
use ATEC\WPCA;

add_action('plugins_loaded', function() { \ATEC\TRANSLATE::load_pll(__DIR__, 'cache-apcu'); });

if (!WPCA::apcu_enabled())
{
	INIT::add_admin_notice_action(__DIR__, '', __('The APCu extension is not enabled but it is required for this plugin to work', 'atec-cache-apcu').'.');
	
	if (defined('ATEC_OC_KEY_SALT'))
	{ INIT::add_admin_notice_action(__DIR__, '', __('APCu was disabled, but „object-cache.php“ is installed – please deactivate this plugin until APCu is re-enabled', 'atec-cache-apcu').'.'); }
}
?>