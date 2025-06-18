<?php
/**
* PC Name:  atec Cache APCu - Page Cache
* Plugin URI: https://atecplugins.com/
* Description: atec Cache APCu - Page Cache MU Plugin or Advanced Cache.
* Version: 2.0.3
* Author: Chris Ahrweiler ℅ atecplugins.com
* Author URI: https://atec-systems.com/
* OC Domain: atec-apcu-page-cache
*/

defined('ABSPATH') || exit;

(function() {
	
	if
	(
		(defined('DOING_AJAX') && DOING_AJAX) ||
		(defined('DOING_CRON') && DOING_CRON) ||
		(defined('REST_REQUEST') && REST_REQUEST) ||
		(defined('WP_CLI') && WP_CLI) ||
		defined('WP_UNINSTALL_PLUGIN') ||
		is_admin()
	) return;
	
	@require WP_CONTENT_DIR.'/plugins/atec-cache-apcu/includes/atec-wpca-pcache.php';
	\ATEC_WPCA\PCache::init();

})();
?>