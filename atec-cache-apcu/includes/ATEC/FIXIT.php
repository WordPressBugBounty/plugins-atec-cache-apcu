<?php
namespace ATEC;
defined('ABSPATH') || exit;

final class FIXIT
{
	private static function version_compare($a, $b) { return explode(".", $a) <=> explode(".", $b); }

	public static function init($dir, $slug, $option = null)
	{
		$option_key = 'atec_fix_it';
		if (!$option) $option = get_option($option_key, []);
		$ver = wp_cache_get('atec_' . $slug . '_version', 'atec_np');
	
		if ($ver && (!isset($option[$slug]) || self::version_compare($option[$slug], $ver) === -1))
		{
			$include = $dir . '/fixit.php';
			if (file_exists($include)) require($include);	// phpcs:ignore
			if (defined('WP_DEBUG') && WP_DEBUG) 
			{
				$plugin = \ATEC\INIT::plugin_fixed_name(\ATEC\INIT::plugin_by_dir($dir));
				error_log('atec-fixit: Auto repair of plugin „' . $plugin . '“.');	// phpcs:ignore
			}
			$option[$slug] = $ver;
			update_option($option_key, $option);
		}
	}


}
?>