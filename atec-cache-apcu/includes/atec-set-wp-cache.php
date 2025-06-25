<?php
defined('ABSPATH') || exit;

use ATEC\FS;

final class ATEC_set_WP_Cache {

public static function init($activate, $plugin)
{
	$wp_config_path = ABSPATH . 'wp-config.php';
	$content = FS::get($wp_config_path, '');

	if (!is_string($content) || $content === '') return;
	$content = str_replace(["\r\n", "\r"], "\n", $content);

	$backup_path = str_replace('.php', ".{$plugin}-bck.php", $wp_config_path);
	FS::put($backup_path, $content);

	// Remove existing WP_CACHE define
	$content = preg_replace('/^\s*define\s*\(\s*[\'"]WP_CACHE[\'"]\s*,\s*(true|false)\s*\)\s*;\s*(\/\/.*)?$/mi', '', $content);

	if ($activate)
	{
		$lines = preg_split('/\R/', $content);
		$lines = array_map('rtrim', $lines);

		$insert_line = "define('WP_CACHE', true); // added by {$plugin}";
		$insert_at = null;

		foreach ($lines as $i => $line)
		{
			if (
				preg_match('/^\s*\/\*\s*That\'s all,\s*stop editing/i', $line) ||
				preg_match('/^\s*\/\*\*\s*Absolute path/i', $line) ||
				preg_match('/^\s*if\s*\(!?\s*defined\s*\(\s*[\'"]ABSPATH[\'"]\s*\)/i', $line)
			) 
			{
				$insert_at = $i;
				break;
			}
		}

		if ($insert_at !== null) array_splice($lines, $insert_at, 0, $insert_line);
		else array_unshift($lines, $insert_line);

		FS::put($wp_config_path, implode(PHP_EOL, $lines));
	}
	else 
	{
		// Clean up any accidental multiple blank lines
		$content = preg_replace("/\n{3,}/", "\n\n", trim($content)) . PHP_EOL;
		FS::put($wp_config_path, $content);
	}
	
	if (function_exists('opcache_invalidate')) @opcache_invalidate($wp_config_path, true);
}

}
?>
