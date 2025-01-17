<?php
if (!defined('ABSPATH')) { exit; }
class ATEC_wpca_activation { function __construct() {
	
if (!defined('ATEC_TOOLS_INC')) @require_once(__DIR__.'/atec-tools.php');
atec_integrity_check(__DIR__);

if (extension_loaded('apcu') && apcu_enabled())
{
	if (!function_exists('atec_load_pll')) { @require_once('atec-translation.php'); }
	atec_load_pll(__DIR__,'cache-apcu');

	atec_mkdir_if_not_exists(WPMU_PLUGIN_DIR);

	$optName='atec_WPCA_settings';
	$options=atec_create_options($optName,['ocache','cache','debug','clear','salt'],['clear']);
	$options['salt']=hash('crc32', get_bloginfo(), FALSE);
	update_option($optName, $options);
}

}} 
new ATEC_wpca_activation();
?>