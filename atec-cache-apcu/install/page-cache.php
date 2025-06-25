<?php
/**
* PC Name:  atec Cache APCu - Page Cache
* Plugin URI: https://atecplugins.com/
* Description: atec Cache APCu - Advanced Page Cache.
* Version: 2.0.3
* Author: Chris Ahrweiler ℅ atecplugins.com
* Author URI: https://atec-systems.com/
* OC Domain: atec-apcu-page-cache
*/

defined('ABSPATH') || exit;
define('ATEC_ADV_PC_ACTIVE_APCU', true);

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