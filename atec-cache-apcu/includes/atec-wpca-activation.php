<?php
if (!defined('ABSPATH')) { exit; }

(function() {	
	if (!function_exists('atec_header')) require(__DIR__.'/atec-tools.php');
	atec_integrity_check(__DIR__);
	
	$optName 	= 'atec_WPCA_settings';
	$options		= get_option($optName,[]);
	if ($options['salt']??''==='') $options['salt'] = hash('crc32', get_bloginfo(), FALSE);
	$options['ocache'] 	= false;
	$options['cache'] 		= false;
	update_option($optName, $options);
})();
?>