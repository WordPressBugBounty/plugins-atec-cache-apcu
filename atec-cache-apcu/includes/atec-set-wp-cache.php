<?php
defined('ABSPATH') || exit;

use ATEC\CONFIG;
use ATEC\WPC;

final class ATEC_set_WP_Cache {

public static function init($activate, $plugin)
{
	$content = CONFIG::get();
	if (empty($content)) return;

	CONFIG::backup($content, $plugin);
	$content = CONFIG::adjust($content, 'WP_CACHE', $activate);
	CONFIG::put($content);
	
	WPC::opcache_flush(CONFIG::path(), true);
}

}
?>