<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\FS;
use ATEC\TOOLS;

final class CONFIG {
	
private static $content;
private static string $regBase = '[\/_\-\.\w\d]';

public static function path()
{
	static $cached = null;
	if ($cached === null)
	{
		$dir = ABSPATH;
		while ( $dir !== '/' && $dir !== '' )
		{
			$cached = $dir . 'wp-config.php';
			if (FS::exists($cached)) return $cached;
			$dir = dirname( rtrim( $dir, '/\\' ) ) . '/';
		}
	}
	return $cached;
}

/**
 * Adjust, add, or remove a define() or error_reporting() line in wp-config.php content.
 *
 * @param string $name Define name (e.g., WP_DEBUG, WP_MEMORY_LIMIT, WP_ERROR for error_reporting)
 * @param mixed  $value Boolean true/false, or string for custom path/memory limit
 * @modifies $content directly
 */
public static function adjust($content, $name, $value): string
{
	$lines = preg_split('/\R/', $content);
	$newLines = [];
	$modified = false;

	if (is_string($value))
	{
		if (strtolower($value) === 'true') { $value = true; }
		elseif (strtolower($value) === 'false') { $value = false; }
	}

	$isErrorReporting = ($name === 'WP_ERROR');
	$defineName = $isErrorReporting ? null : $name;
	$definePattern = $isErrorReporting
	? '/^\s*error_reporting\(/i'
	: self::reg_define_expr($defineName);

	$atecLines = [];
	$insertAt = null;
	$skipNext = false;

	foreach ($lines as $i => $line)
	{
		$trimmed = trim($line);

		// Remove old-style 2-line format
		if (preg_match('/\/\*\s*Added by atec-[^*]*\*\//i', $trimmed))
		{
			if (isset($lines[$i + 1]) && preg_match($definePattern, trim($lines[$i + 1])))
			{
				$modified = true;
				$skipNext = true;
				continue;
			}
		}

		if ($skipNext) { $skipNext = false; continue; }

		// Remove exact match for current define
		if (preg_match($definePattern, $trimmed)) { $modified = true; continue; }

		// Track insertion point
		if (preg_match('/^\s*\/\*\s*Add any custom values between.*stop editing.*\*\//i', $trimmed)) 
		{ $insertAt = count($newLines) + 1; }
		elseif (
			$insertAt === null && (
				preg_match('/^\s*\/\*\s*That\'s all,\s*stop editing!/i', $trimmed) ||
				preg_match('/^\s*\/\*\*\s*Absolute path/i', $trimmed) ||
				preg_match('/^\s*if\s*\(!?\s*defined\s*\(\s*[\'"]ABSPATH[\'"]\s*\)/i', $trimmed)
			)
		) 
		{ $insertAt = count($newLines); }

		$newLines[] = $line;
	}

	$shouldInsertDefine = ($value !== false && $value !== null && $value !== '');
	if ($name === 'WP_DEBUG_DISPLAY' && $value === false) { $shouldInsertDefine = true; }	// force define( WP_DEBUG_DISPLAY, false );

	if ($shouldInsertDefine)
	{
		$defineLine = $isErrorReporting
			? 'error_reporting(E_ALL & ~E_DEPRECATED); // added by atec-Plugins'
			: "define( '" . $defineName . "', " . (is_bool($value) ? ($value ? 'true' : 'false') : "'{$value}'") . " ); // added by atec-Plugins";

		$atecLines[] = $defineLine;
	}

	if (!empty($atecLines))
	{
		sort($atecLines, SORT_STRING | SORT_FLAG_CASE);
		if ($insertAt !== null) array_splice($newLines, $insertAt, 0, $atecLines);
		else array_unshift($newLines, ...$atecLines);
		$modified = true;
	}

	$content = $modified ? implode(PHP_EOL, $newLines) : $content;
	if ($name === 'WP_DEBUG') { $content = self::adjust($content, 'WP_ERROR', $value); }
	return $content;
}

public static function reg_define_expr(string $constName): string
{
	return '/define\(\s*[\'"]' . preg_quote($constName, '/') . '[\'"]\s*,\s*[\'"]?(' . self::$regBase . '*?)[\'"]?\s*\);(?:\s*\/\/.*)?/i';
}

public static function check_define($content, $name): int
{
	if (empty($content)) return -1;

	preg_match_all(self::reg_define_expr($name), $content, $matches);
	if (empty($matches[1])) return -1;

	$value = trim(strtolower($matches[1][0]));

	// Normalize boolean-like values
	if (in_array($value, ['1', 'true'], true)) return 1;
	if (in_array($value, ['0', 'false'], true)) return 0;

	// Otherwise, just treat as "defined, truthy"
	return 1;
}

private static function parse($config)
{
	// Collapse phpdoc blocks like /**#@+ ... */ or /** ... */
	 $config = preg_replace_callback('/\/\*\*#?.*?\*\//s', function($matches) 
	{
		if (isset($matches[0])) 
		{
			$lines = explode("\n", $matches[0]);
			// If the comment block has at least two lines, process it
			if (count($lines) > 1) 
			{
				// Get the first line of the comment (ignoring any leading stars)
				$firstLine = trim(preg_replace('/^\s*\*\s?/', '', $lines[1]));
				// Return the comment with only the first line
				return "// ** $firstLine **/";
			} 
			else 
			{
				if (str_starts_with($matches[0],'/**#@-*/')) return '';
				return $matches[0];
			}
		}
		return $matches[0];
	}, $config);


	$config = preg_replace('/[\r\n]+/', "\n", $config);
	$config = trim($config);  
	return $config;
}

public static function render_prism($text, $type='php')
{
	self::reg_prism();
	echo '<div class="atec-code">';
		echo '<pre style="background: transparent; margin: 0;">';
			echo '<code class="language-', esc_attr($type), '">';
			
				// Only escape if needed (e.g., for PHP), but output raw for apacheconf
				if ($type === 'apacheconf') echo $text; 																// phpcs:ignore
				else echo htmlspecialchars($text, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');	// phpcs:ignore

			echo '</code>';
		echo '</pre>';
	echo '</div>';
}
	
public static function reg_prism()
{
	static $cached = null;
	if ($cached === null)
	{
		TOOLS::reg_style('prism',__DIR__, 'prism.min.css', '1.30.0');
		TOOLS::reg_script('prism',__DIR__, 'prism.min.js', '1.30.0');
		$cached = true;
	}
}

public static function render($littleBlock = false)
{
	self::reg_prism();
	$config = self::get();
	$wp_config_path = self::path();

	if ($littleBlock) 
	{
		TOOLS::little_block('Config file „wp_config.php“');
		echo '<p class="atec-fs-14"><strong>Path:</strong> ', esc_url($wp_config_path), '</p>';
	}

	if ($config !== '') 
	{
		$config = self::parse($config);
		echo 
		'<div class="atec-code">', 
			'<pre style="background: transparent; margin: 0;">',
				'<code class="language-php">', esc_html($config), '</code>',
			'</pre>',
		'</div>';
		TOOLS::reg_inline_style('token', '.atec-code .token.comment { color: #bbb; }');
	} 
	else 
	{
		TOOLS::msg(false, 'Can not read „wp_config.php“.');
	}
}

public static function get()
{ return FS::get(self::path(), ''); }

public static function put($content)
{
	$content = preg_replace('/[\n]{3,999}/m', "\n\n", $content);
	return FS::put(self::path(), $content); 
}

public static function backup($content, $plugin)
{
	if (empty($content)) return;
	$backup_path= str_replace('.php', '.atec-'.$plugin.'-bck.php', self::path());
	FS::put($backup_path, $content);
}

public static function init(&$una, &$status, &$memlimit, &$debug_WP_, &$custom_log, &$debug_path)
{
	$una->action_msg = '';

	$memlimit=TOOLS::clean_request('memlimit');
	
	$content 							= self::get();
	$wp_config_path				= self::path();
	$custom_log 					= false;
	$default_debug_path 		= FS::debug_path();
	$debug_path 					= $default_debug_path;

	$debug_WP_ 		= ['WP_DEBUG', 'WP_DEBUG_DISPLAY', 'WP_DEBUG_LOG'];
	$other_WP_ 			= ['SCRIPT_DEBUG', 'WP_ALLOW_REPAIR', 'SAVEQUERIES', 'WP_AUTO_UPDATE_CORE'];
	$all_WP_				= array_merge($debug_WP_, $other_WP_,['WP_MEMORY_LIMIT']);

	// Set default $status array
	$status = [];
	foreach ($all_WP_ as $wp_) { $status[$wp_] = ($wp_ === 'WP_MEMORY_LIMIT') ? '40M' : false; }

	preg_match_all('/define\(\s?[\'"]([\w]+)[\'"]\s*,\s*[\'"]?('.self::$regBase.'+)[\'"]?\s*\);\s*(\/\/.*)?/', $content, $m1);
	foreach($m1[0] as $m)
	{
		preg_match('/define\(\s?\'([\w]+)\',\s?[\']?('.self::$regBase.'+)[\']?\s?\);/', $m, $m2);
		if (isset($m2[1], $m2[2]) && in_array($m2[1], $all_WP_))
		{
			$status[$m2[1]] = (bool) (strtolower($m2[2])== 'true');
			if ($m2[1]=== 'WP_DEBUG_LOG')
			{
				if (!in_array(strtolower($m2[2]),['true', 'false'])) { $status[$m2[1]]=true; $custom_log=true; $debug_path= $m2[2]!== ''?$m2[2]:WP_DEBUG_LOG; }
			}
		}
	}

	$debug_status_before = $status['WP_DEBUG'];
	if (in_array($una->nav,['Debug', 'Memory', 'Queries', 'Repair', 'Updates', 'Script']) || $una->slug=== 'wpco') // All tabs with WP_ checkbox
	{
		if ($una->action== 'delete') { FS::unlink($debug_path); }
		else
		if (in_array($una->action, $debug_WP_) || in_array($una->action, $other_WP_) || in_array($una->action,['memlimit', 'default', 'saveLog']))
		{
			$set = TOOLS::clean_request_bool('set');
			$backup_path= str_replace('.php', '.atec-debug-bck.php', $wp_config_path);
			FS::put($backup_path, $content);
			if ($una->action== 'saveLog')
			{
				$newLog = TOOLS::clean_request('custom_log');
				$key= 'WP_DEBUG_LOG';
				if ($newLog=== '' || $newLog=== $default_debug_path)
				{
					$una->action_msg= 'Reseted to default';
					$subst= 'true';
					$debug_path= $default_debug_path;
					$custom_log=false;
				}
				else
				{
					if (!preg_match('/'.self::$regBase.'+/', $newLog) || !str_starts_with($newLog, '/'))
					{
						$una->action_msg= 'Invalid path – reseted to default';
						$subst= 'true';
						$debug_path= $default_debug_path;
						$custom_log=false;
					}
					else
					{
						$subst= $newLog;
						$debug_path= $newLog;
						$custom_log=true;
					}
				}
				$status[$key]=true;
			}
			elseif ($una->action== 'memlimit')
			{
				if ($memlimit== '') { $memlimit= '40M'; $una->action_msg= 'WP_MEMORY_LIMIT set to default value: 40M'; }
				$key= 'WP_MEMORY_LIMIT';
				$subst= $memlimit;
			}
			elseif ($una->action!= 'default')
			{
				$key= $una->action;
				$status[$una->action]= $set;
				$subst= $status[$una->action]?'true' : 'false';
			}

			if ($una->action== 'default')
			{
				foreach ($debug_WP_ as $key)
				if ($key!= 'WP_DEBUG_LOG' || !$custom_log)
				{
					$default= $key== 'WP_DEBUG_DISPLAY'?'true' : 'false';
					$subst= $default;
					$status[$key]= $value;
					$content = self::adjust($content, $key, $subst);
				}
			}
			else
			{
				$content = self::adjust($content, $key, $subst);
			}

			if ($content!== '') self::put($content);

			if (in_array($una->action,['default', 'WP_DEBUG']) && $status['WP_DEBUG']==false)
				TOOLS::reg_inline_script('wpd_hide', 'jQuery("#wp-admin-bar-atec_wpd_admin_bar").remove();');
			if (in_array($una->action,['SAVEQUERIES']) && $status['SAVEQUERIES']==false)
				TOOLS::reg_inline_script('wpd_hide_sq', 'jQuery("#wp-admin-bar-atec_wpd_admin_bar_sq").remove();');

			// Protected debug.log file by the use of a rewrite rule
			if ($debug_status_before!== $status['WP_DEBUG'])
			{
				$htaccess_path = FS::trailingslashit(WP_CONTENT_DIR).'.htaccess';
				$htaccess = FS::get($htaccess_path);
				if ($htaccess)
				{
					$reg = '/#{0,4} BEGIN ATEC-DEBUG-LOG[\n|\s|\S]*#{0,4} END ATEC-DEBUG-LOG\n{0,2}([\n|\s|\S]*)/';
					$htaccess = preg_replace($reg, "$1", $htaccess);
				}

				if ($status['WP_DEBUG'])
				{
					$install_dir = plugin_dir_path(__DIR__).'install/';
					$install_path = $install_dir.'htaccess_debug_log.txt';
					$replace = FS::get($install_path);
					if ($replace) $htaccess = $replace."\n\n".$htaccess;
				}

				if ($htaccess=== '') @FS::unlink($htaccess_path);
				else
				{
					$result = @FS::put($htaccess_path, $htaccess);
					if (!is_wp_error($result) && $status['WP_DEBUG'])
						$una->action_msg= 'The debug.log file is now protected through a rewrite rule - if mod_rewrite.c is enabled on your server';
				}
			}
		}
	}

}

}
