<?php
defined('ABSPATH') || exit;

use ATEC\INIT;
use ATEC\FS;

(function() {

	FS::mkdir(WPMU_PLUGIN_DIR);
	
	$settings = INIT::get_settings('wpca');
		$settings['o_cache'] = false;
		$settings['p_cache'] = false;
	INIT::update_settings('wpca', $settings);

})();
?>