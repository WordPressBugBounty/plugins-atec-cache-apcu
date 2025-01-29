<?php
if (!defined('ABSPATH')) { exit; }
class ATEC_wpca_activation { function __construct() {
	
if (!function_exists('atec_header')) @require(__DIR__.'/atec-tools.php');
atec_integrity_check(__DIR__);

if (extension_loaded('apcu') && apcu_enabled())
{
	if (!function_exists('atec_load_pll')) @require('atec-translation.php');
	atec_load_pll(__DIR__,'cache-apcu');

	atec_mkdir_if_not_exists(WPMU_PLUGIN_DIR);

	$optName='atec_WPCA_settings';
	$options=atec_create_options($optName,['ocache','cache','debug','clear','salt'],['clear']);
	$options['salt'] = hash('crc32', get_bloginfo(), FALSE);
	$options['ocache'] = false;
	$options['cache'] = false;
	update_option($optName, $options);
}

}} 
new ATEC_wpca_activation();
?>