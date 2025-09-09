<?php
defined('ABSPATH') || exit;

use ATEC\FS;
use ATEC\INIT;
use ATEC\WPCA;

(function() {

	if (defined('ATEC_OC_ACTIVE_APCU'))
	{
		wp_cache_flush();
		FS::unlink(FS::trailingslashit(INIT::content_dir()).'object-cache.php');
	}
	
	if (WPCA::apcu_enabled() && WPCA::settings('p_cache'))
	{
		require(__DIR__.'/atec-wpca-install-pcache.php');
		\ATEC_WPCA\Install_PCache::init(false);
	}

})();
?>