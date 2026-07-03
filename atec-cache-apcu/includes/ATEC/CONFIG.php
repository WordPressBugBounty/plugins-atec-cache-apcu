<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\FS;
use ATEC\INIT;
use ATEC\TOOLS;

final class CONFIG {

private static string $regBase = '[\/_\-\.\w\d]';

public static function backup($content, $plugin)
{
	if (empty($content)) return;
	$backup_path= str_replace('.php', '.atec-'.$plugin.'-bck.php', self::path());
	FS::put($backup_path, $content);
}

// Check if path is within wp-content directory
private static function valid_path($path)
{
    $path = wp_normalize_path( $path );
    $path = realpath( $path );
    return $path && strpos( $path, wp_normalize_path( WP_CONTENT_DIR ) ) === 0;
}

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

/** Expression written to wp-config.php for ATEC_WP_DEBUG_REPORTING. */
public static function debug_reporting_expr(): string
{
	return 'E_ALL & ~E_NOTICE & ~E_USER_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED';
}

/** Short token derived from AUTH_KEY — used in the obfuscated debug log filename. */
public static function secure_debug_log_token(): string
{
	$key = '';
	if (defined('AUTH_KEY') && AUTH_KEY !== '' && !str_contains(AUTH_KEY, 'put your unique phrase here'))
		$key = AUTH_KEY;
	elseif (defined('SECURE_AUTH_KEY') && SECURE_AUTH_KEY !== '')
		$key = SECURE_AUTH_KEY;
	else
		$key = function_exists('wp_salt') ? wp_salt('auth') : 'atec-debug';

	return substr(hash('sha256', $key), 0, 16);
}

/** Default debug log path: wp-content/atec-debug-{token}.log (not guessable without AUTH_KEY). */
public static function secure_debug_log_path(): string
{
	return wp_normalize_path(FS::trailingslashit(INIT::content_dir()) . 'atec-debug-' . self::secure_debug_log_token() . '.log');
}

/** Must-use plugin that applies ATEC_WP_DEBUG_REPORTING after wp_debug_mode(). */
public static function mu_reporting_version(): string
{
	return '1.2.36';
}

public static function mu_reporting_basename(): string
{
	return 'atec-debug-reporting.php';
}

public static function mu_reporting_path(): string
{
	return wp_normalize_path(WPMU_PLUGIN_DIR . '/' . self::mu_reporting_basename());
}

/** PHP source written to mu-plugins — not tied to any plugin install/ folder. */
public static function mu_reporting_content(): string
{
	$version = self::mu_reporting_version();

	return <<<PHP
<?php
/**
 * Plugin Name: atec Debug Reporting
 * Description: Applies ATEC_WP_DEBUG_REPORTING after wp_debug_mode(). Managed by atec Debug.
 * Version: {$version}
 */

defined('ABSPATH') || exit;

define('ATEC_DEBUG_REPORTING_VERSION', '{$version}');

if (!defined('WP_DEBUG') || !WP_DEBUG) {
	return;
}

\$level = defined('ATEC_WP_DEBUG_REPORTING')
	? (int) constant('ATEC_WP_DEBUG_REPORTING')
	: (E_ALL & ~E_NOTICE & ~E_USER_NOTICE & ~E_DEPRECATED & ~E_USER_DEPRECATED);

error_reporting(\$level);

PHP;
}

public static function install_mu_reporting(): bool
{
	if (!defined('WPMU_PLUGIN_DIR')) return false;

	if (defined('ATEC_DEBUG_REPORTING_VERSION')
		&& ATEC_DEBUG_REPORTING_VERSION === self::mu_reporting_version()) {
		return true;
	}

	FS::mkdir(WPMU_PLUGIN_DIR);
	return (bool) FS::put(self::mu_reporting_path(), self::mu_reporting_content());
}

public static function remove_mu_reporting(): bool
{
	return FS::unlink(self::mu_reporting_path());
}

public static function sync_mu_reporting(bool $wp_debug): void
{
	if ($wp_debug) self::install_mu_reporting();
	else self::remove_mu_reporting();
}

public static function is_secure_debug_log_path(string $path): bool
{
	return (bool) preg_match('#/atec-debug-[a-f0-9]{16}\.log$#', wp_normalize_path($path));
}

private static function migrate_debug_log_path(string $content): string
{
	if (self::check_define($content, 'WP_DEBUG') !== 1 || self::check_define($content, 'WP_DEBUG_LOG') !== 1)
		return $content;

	if (preg_match(self::reg_define_expr('WP_DEBUG_LOG'), $content, $m))
	{
		$val = wp_normalize_path(trim($m[1] ?? '', "'\""));
		if (strtolower($val) === 'true' || str_ends_with($val, '/debug.log') || preg_match('#contentatec-debug-[a-f0-9]{16}\.log$#', $val))
			return self::adjust($content, 'WP_DEBUG_LOG', self::secure_debug_log_path());
	}

	return $content;
}

/**
 * Adjust, add, or remove a define() line in wp-config.php content.
 *
 * @param string $name Define name (e.g., WP_DEBUG, WP_MEMORY_LIMIT, WP_ERROR for ATEC_WP_DEBUG_REPORTING)
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
	$defineName = $isErrorReporting ? 'ATEC_WP_DEBUG_REPORTING' : $name;
	$definePattern = $isErrorReporting
	? '/^\s*(?:error_reporting\s*\(|define\s*\(\s*[\'"]ATEC_WP_DEBUG_REPORTING[\'"]\s*,)/i'
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
			? "define( 'ATEC_WP_DEBUG_REPORTING', " . self::debug_reporting_expr() . " ); // added by atec-Plugins"
			: "define( '" . $defineName . "', " . (is_bool($value) ? ($value ? 'true' : 'false') : "'{$value}'") . " ); // added by atec-Plugins";

		$atecLines[] = $defineLine;
	}

	if (!empty($atecLines))
	{
		sort($atecLines, SORT_STRING | SORT_FLAG_CASE);
	
		if ($insertAt === null)
		{
			// 1. Look for if ( ! defined( 'ABSPATH' ) )
			foreach ($newLines as $i => $line)
			{
				if (preg_match('/if\s*\(\s*!?\s*defined\s*\(\s*[\'"]ABSPATH[\'"]\s*\)/i', $line))
				{
					$insertAt = $i;
					break;
				}
			}
			// 2. Else look for require_once wp-settings.php
			if ($insertAt === null)
			{
				foreach ($newLines as $i => $line)
				{
					if (preg_match('/require_once\s+.*wp-settings\.php/i', $line))
					{
						$insertAt = $i;
						break;
					}
				}
			}
			// 3. Else after <?php
			if ($insertAt === null)
			{
				foreach ($newLines as $i => $line)
				{
					if (preg_match('/^\s*<\?php/i', $line))
					{
						$insertAt = $i + 1;
						break;
					}
				}
			}
			// 4. Fallback: end of file
			if ($insertAt === null) $insertAt = count($newLines);
		}
	
		array_splice($newLines, $insertAt, 0, $atecLines);
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

private static function runtime_bool(string $name): bool
{
	return defined($name) && constant($name);
}

private static function sync_status_from_runtime(
	array &$status,
	array $debug_WP_,
	array $other_WP_,
	string &$debug_path,
	bool &$custom_log,
	string $secure_debug_path
): void {
	foreach ($debug_WP_ as $key)
	{
		if ($key === 'WP_DEBUG_LOG')
		{
			if (!self::runtime_bool('WP_DEBUG_LOG'))
			{
				$status['WP_DEBUG_LOG'] = false;
				$debug_path = $secure_debug_path;
				$custom_log = false;
			}
			else
			{
				$status['WP_DEBUG_LOG'] = true;
				$log = WP_DEBUG_LOG;
				if (is_string($log) && $log !== '')
				{
					$debug_path = wp_normalize_path($log);
					$custom_log = !self::is_secure_debug_log_path($debug_path);
				}
				else
				{
					$debug_path = $secure_debug_path;
					$custom_log = false;
				}
			}
		}
		else $status[$key] = self::runtime_bool($key);
	}

	foreach ($other_WP_ as $key) $status[$key] = self::runtime_bool($key);

	$status['WP_MEMORY_LIMIT'] = defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : '40M';
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
		TOOLS::little_block('Config file ‘wp_config.php’');
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
		TOOLS::msg(false, 'Can not read ‘wp_config.php’.');
	}
}

public static function get()
{ return FS::get(self::path(), ''); }

public static function put($content)
{
	$content = preg_replace('/[\n]{3,999}/m', "\n\n", $content);
	return FS::put(self::path(), $content); 
}

public static function init(&$una, &$status, &$memlimit, &$debug_WP_, &$custom_log, &$debug_path)
{
	$una->action_msg = '';

	$memlimit=TOOLS::clean_request('memlimit');
	
	$content 							= self::get();
	$wp_config_path				= self::path();

	if ($content !== '' && self::check_define($content, 'WP_DEBUG') === 1
		&& preg_match('/error_reporting\s*\([^)]*atec-Plugins/i', $content)
		&& !preg_match('/define\s*\(\s*[\'"]ATEC_WP_DEBUG_REPORTING[\'"]/i', $content))
	{
		$content = self::adjust($content, 'WP_ERROR', true);
		self::put($content);
	}

	$migrated = self::migrate_debug_log_path($content);
	if ($migrated !== $content)
	{
		$content = $migrated;
		self::put($content);
	}

	self::sync_mu_reporting(self::check_define($content, 'WP_DEBUG') === 1);

	$custom_log 					= false;
	$secure_debug_path			= self::secure_debug_log_path();
	$debug_path 					= $secure_debug_path;

	$debug_WP_ 		= ['WP_DEBUG', 'WP_DEBUG_DISPLAY', 'WP_DEBUG_LOG'];
	$other_WP_ 			= ['SCRIPT_DEBUG', 'WP_ALLOW_REPAIR', 'SAVEQUERIES', 'WP_AUTO_UPDATE_CORE'];
	$all_WP_				= array_merge($debug_WP_, $other_WP_,['WP_MEMORY_LIMIT']);

	// Set default $status array
	$status = [];
	foreach ($all_WP_ as $wp_) { $status[$wp_] = ($wp_ === 'WP_MEMORY_LIMIT') ? '40M' : false; }

	self::sync_status_from_runtime($status, $debug_WP_, $other_WP_, $debug_path, $custom_log, $secure_debug_path);

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
				if ($newLog === '' || wp_normalize_path($newLog) === $secure_debug_path)
				{
					$una->action_msg = 'Reset to secure default log path';
					$subst = $secure_debug_path;
					$debug_path = $secure_debug_path;
					$custom_log = false;
				}
				else
				{
					if (!self::valid_path($newLog))
					{
						$una->action_msg = 'Invalid path – reset to secure default';
						$subst = $secure_debug_path;
						$debug_path = $secure_debug_path;
						$custom_log = false;
					}
					else
					{
						$subst = wp_normalize_path($newLog);
						$debug_path = $subst;
						$custom_log = true;
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
				if ($una->action === 'WP_DEBUG_LOG' && $set && !$custom_log)
					$subst = $secure_debug_path;
				else
					$subst = $status[$una->action] ? 'true' : 'false';
			}

			if ($una->action== 'default')
			{
				foreach ($debug_WP_ as $key)
				if ($key!= 'WP_DEBUG_LOG' || !$custom_log)
				{
					$default= $key== 'WP_DEBUG_DISPLAY'?'true' : 'false';
					$subst= $default;
					//$status[$key]= $value;
					$status[$key] = ($subst === 'true');  // ✅ valid boolean assignment
					$content = self::adjust($content, $key, $subst);
				}
			}
			else
			{
				$content = self::adjust($content, $key, $subst);
			}

			if ($content !== '')
			{
				self::put($content);
				if ($una->action === 'default' || $una->action === 'WP_DEBUG')
					self::sync_mu_reporting((bool) $status['WP_DEBUG']);
			}

			if ($una->action === 'WP_DEBUG_LOG' && $set && !$custom_log)
				$una->action_msg = 'Log file: wp-content/atec-debug-' . self::secure_debug_log_token() . '.log (derived from AUTH_KEY)';

			if (in_array($una->action,['default', 'WP_DEBUG']) && $status['WP_DEBUG']==false)
				TOOLS::reg_inline_script('wpd_hide', 'jQuery("#wp-admin-bar-atec_wpd_admin_bar").remove();');
			if (in_array($una->action,['SAVEQUERIES']) && $status['SAVEQUERIES']==false)
				TOOLS::reg_inline_script('wpd_hide_sq', 'jQuery("#wp-admin-bar-atec_wpd_admin_bar_sq").remove();');
		}
	}

}

}
?>