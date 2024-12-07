<?php
	if (!defined('ABSPATH')) { exit; }
	wp_cache_delete('atec_wpca_version');

	$arr = ['cache','clear','minify','gzip'];
	foreach ($arr as $a) { delete_option('atec_WPCA_p_'.$a.'_enabled'); }
	
	$arr = ['atec_WPCA_settings','atec_wpca_last_cache'];
	foreach ($arr as $a) { delete_option($a); }
?>