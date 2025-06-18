<?php
defined('ABSPATH') || exit;

use ATEC\FS;
use ATEC\WPCA;

(function() {

	if (defined('ATEC_OC_ACTIVE_APCU'))
	{
		wp_cache_flush();
		FS::unlink(FS::trailingslashit(WP_CONTENT_DIR).'object-cache.php');
	}
	
	if (WPCA::settings('p_cache'))
	{
		require(__DIR__.'/atec-wpca-install-pcache.php');
		\ATEC_WPCA\Install_PCache::init(false);
	}

})();
?>