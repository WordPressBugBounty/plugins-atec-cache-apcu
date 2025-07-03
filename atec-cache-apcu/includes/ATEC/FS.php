<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\INIT;

final class FS {

	// @codingStandardsIgnoreStart
	
	public static function debug_path(): string
	{
		static $cached = null;
		if ($cached === null) 
		{
			$cached = self::trailingslashit(
				defined(WP_DEBUG_LOG) && !is_bool(WP_DEBUG_LOG)
				? WP_DEBUG_LOG
				: WP_CONTENT_DIR
				).'debug.log';
		}
		return $cached;
	}
	
	public static function htaccess_path(): string
	{
		return self::home_path() . '.htaccess';
	}
	
	public static function home_path(): string
	{
		static $cached = null;
		if ($cached === null) $cached = get_home_path();
		return $cached;
	}
	
	public static function flock_put($path, $content): bool
	{
		@unlink($path);
	
		$fp = @fopen($path, 'wb');
			if (!$fp) return false;
			if (!flock($fp, LOCK_EX)) { fclose($fp); return false; }
				$written = fwrite($fp, $content);
				fflush($fp);
			flock($fp, LOCK_UN);
		fclose($fp);
		return $written !== false;
	}

	public static function trailingslashit(string $path): string { return rtrim($path, '/\\') . DIRECTORY_SEPARATOR; }

	public static function trailingslashit_ftp(string $path): string { return rtrim($path, "/\\") . '/'; }

	public static function fix_separator($str): string { return (DIRECTORY_SEPARATOR === '/') ? $str : str_replace('/', DIRECTORY_SEPARATOR, $str); 	}

	public static function install_default_files($dir, $slug, &$s) : array
	{
		$notice = [];
		$upload_dir = self::install_files($dir, '', [], $s);
		if (!$s)
		{
			INIT::build_notice($notice, '', 'Failed to create „uploads“ folder and files');
			INIT::set_admin_debug($slug, $notice);
		}
		return ['notice' => $notice, 'upload_dir' => $upload_dir];
	}
	
	public static function install_files($dir, $sub_dir, $arr, &$s) : string
	{
		$plugin = INIT::plugin_by_dir($dir);
		$plugin_dir = WP_PLUGIN_DIR .'/'. $plugin;
		$sub_dir = $sub_dir === '' ? '' : '/'.$sub_dir;
		$upload_dir = self::upload_dir(str_replace('atec-', '', $plugin).$sub_dir);	// Base directory is uploads/plugin unless sub_dir is provided
		self::mkdir($upload_dir);	// Ensure directory exists
		$s = $s && (self::put($upload_dir.'/index.php', '<?php exit(403); ?>')!==false);	// Protect the directory
		$s = $s && (self::put($upload_dir.'/.htaccess', 'Require local')!==false);			// Protect the directory
		foreach($arr as $key=>$value)
		{ $s = $s && self::copy($plugin_dir.'/install/'.$key, $upload_dir.'/'.$value, true); }
		return $upload_dir;
	}
	
	public static function wp_upload_dir(): array
	{
		static $cached = null;
		if ($cached === null) $cached = wp_get_upload_dir();
		return $cached;
	}

	public static function upload_basedir(): string
	{ return self::wp_upload_dir()['basedir']; }
		
	public static function upload_baseurl(): string
	{ return self::wp_upload_dir()['baseurl']; }
	
	public static function upload_dir($p= ''): string
	{
		$path = 
			$p !== '' 
			? self::trailingslashit(self::upload_basedir()) . INIT::plugin_prefix($p) . $p
			: self::upload_basedir();
		return self::fix_separator($path);
	}

	public static function download_and_unzip(string $url,string $destDir,int $minSize = 1,bool $clean = true,bool $subfolder = false): string
	{
		$error = '';
		$tmp = tempnam(sys_get_temp_dir(), 'atec_dl_');
		if (!$tmp) return 'Could not create temporary file.';

		$zip_path = $tmp . '.zip';
		rename($tmp, $zip_path);

		// Download
		$ch = curl_init($url);
		$fp = fopen($zip_path, 'w');
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_exec($ch);
			$curlError = curl_error($ch);
			curl_close($ch);
		fclose($fp);

		// Validate
		if (!empty($curlError)) { $error = 'Download error: ' . $curlError; }
		elseif (!file_exists($zip_path) || filesize($zip_path) < $minSize) { $error = 'Downloaded file is corrupt or too small.'; }
		else
		{
			$zip = new \ZipArchive;
			if ($zip->open($zip_path) === true)
			{
				// Optional: get real target dir if expecting a plugin ZIP with a subfolder
				$targetDir = $destDir;
				if ($subfolder)
				{
					$firstEntry = $zip->getNameIndex(0);
					if ($firstEntry)
					{
						$subDir = explode('/', $firstEntry)[0];
						$targetDir = self::trailingslashit($destDir) . $subDir;
					}
				}

				if ($clean && is_dir($targetDir)) { self::rmdir($targetDir, true); }
				if (!$zip->extractTo($destDir)) { $error = 'Extraction failed.'; }

				$zip->close();
			}
			else { $error = 'Unzip failed.'; }
		}

		if (file_exists($zip_path)) @unlink($zip_path);

		return $error;
	}

	// = = = = = WP_Filesystem replacement = = = = = //

	/**
	* Changes the permissions of a file or directory.
	*
	* Accepts both octal integers (e.g. 0755) and string representations (e.g. "0755").
	* Automatically converts string inputs to octal using `octdec()`.
	*
	* @param string     $path The file or directory path.
	* @param int|string $mode The permission mode (e.g. 0755 or "0755").
	* @return bool True on success, false on failure.
	*/
	public static function chmod($path, $mode): bool
	{
		$mode = is_string($mode) ? octdec($mode) : $mode;
		return @chmod($path, $mode);
	}

	public static function copy($source, $target, $overwrite = true, $mode = false): bool
	{
		if (!self::exists($source)) return false;
		if (!$overwrite && self::exists($target)) return false;
		$result = copy($source, $target);
		if ($result && $mode !== false) { self::chmod($target, $mode); }
		return $result;
	}

	public static function delete($path, $recursive = false): bool
	{
		if (!file_exists($path)) return true;
		if (is_dir($path)) return self::rmdir($path, $recursive);
		return unlink($path);
	}

	public static function dirlist($path, $include_hidden = true, $recursive = false) : array
	{
		if (!self::is_dir($path)) return [];
		$result = [];
		$items = scandir($path);
		foreach ($items as $item)
		{
			if ($item === '.' || $item === '..') continue;
			if (!$include_hidden && $item[0] === '.') continue;
			$full = $path . DIRECTORY_SEPARATOR . $item;
			if (is_link($full)) continue;
			$result[$item] = [
				'name' => $item,
				'type' => is_dir($full) ? 'folder' : 'file',
				'size' => is_file($full) ? filesize($full) : 0,
				'lastmodunix' => filemtime($full),
			];
			if ($recursive && is_dir($full) && !is_link($full)) { $result[$item]['files'] = self::dirlist($full, $include_hidden, true); }
		}
		return $result;
	}

	public static function exists($path): bool { return file_exists($path); }
	public static function get($path, $default = false) { return self::exists($path) ? file_get_contents($path) : $default; }
	public static function get_array($path, $default = false) { return self::exists($path) ? @file($path) : $default; }
	public static function getchmod($path): string { return self::exists($path) ? substr(sprintf('%o', fileperms($path)), -4) : '0000'; }
	public static function is_dir($dir): bool { return self::exists($path) && is_dir($dir); }

	public static function mkdir($dir, $chmod = 0755): bool
	{
		if (self::exists($dir)) return true;
		return mkdir($dir, $chmod, true);
	}

	public static function move($source, $target, $overwrite = true): bool
	{
		if (!self::exists($source)) return false;
		if (!$overwrite && self::exists($target)) return false;
		return rename($source, $target);
	}

	public static function mtime($path) { return self::exists($path) ? @filemtime($path) : false; }
	public static function put($path, $content, $flags = 0) { return file_put_contents($path, $content, $flags); }

	public static function rmdir($dir, $recursive = false): bool
	{
		if (!is_dir($dir)) return true;
		if (!$recursive) return rmdir($dir);
		foreach (scandir($dir) as $item)
		{
			if ($item === '.' || $item === '..') continue;
			$path = $dir . DIRECTORY_SEPARATOR . $item;
			if (is_link($path)) { unlink($path); }
			elseif (is_dir($path)) { self::rmdir($path, true); }
			else { unlink($path); }
		}
		return rmdir($dir);
	}

	public static function size($path) { return self::exists($path) ? filesize($path) : false; }

	public static function touch($path, $time = null, $atime = null): bool
	{
		$time = $time ?? time();
		$atime = $atime ?? $time;
		return @touch($path, $time, $atime);
	}

	public static function unlink($path): bool { return self::exists($path) ? unlink($path) : true; }

	// @codingStandardsIgnoreEnd

}
?>