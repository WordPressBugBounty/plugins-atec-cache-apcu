<?php
/**
* OC Name:  atec Object-Cache
* Plugin URI: https://atecplugins.com/
* Description: atec Object Cache with pluggable backend (APCu, Redis, Memcached)
* Version: 2.0.3
* Author: Chris Ahrweiler ℅ atecplugins.com
* Author URI: https://atec-systems.com/
* OC Domain:  atec-object-cache
*/

declare(strict_types=1);
defined('ABSPATH') || exit;
define('ATEC_OC_VERSION', '2.0.3');

function wp_cache_init() { $GLOBALS['wp_object_cache'] = WP_Object_Cache::instance(); }
function wp_cache_add($key, $data, $group = '', $expire = 0) { return WP_Object_Cache::instance()->add($key, $data, $group, (int) $expire); }
function wp_cache_add_multiple(array $data, $group = '', $expire = 0) { return WP_Object_Cache::instance()->add_multiple($data, $group, (int) $expire); }
function wp_cache_set($key, $data, $group = '', $expire = 0) { return WP_Object_Cache::instance()->set($key, $data, $group, (int) $expire); }
function wp_cache_set_multiple(array $data, $group = '', $expire = 0) { return WP_Object_Cache::instance()->set_multiple($data, $group, (int) $expire); }
function wp_cache_get($key, $group = '', $force = false, &$found = null) { return WP_Object_Cache::instance()->get($key, $group, $force, $found); }
function wp_cache_get_pre($fullkey, &$found = null) { return WP_Object_Cache::instance()->get_pre($fullkey, $found); }
function wp_cache_get_multiple($keys, $group = '', $force = false) { return WP_Object_Cache::instance()->get_multiple($keys, $group, $force); }
function wp_cache_replace($key, $data, $group = '', $expire = 0) { return WP_Object_Cache::instance()->replace($key, $data, $group, (int) $expire); }
function wp_cache_delete($key, $group = '') { return WP_Object_Cache::instance()->delete($key, $group); }
function wp_cache_delete_multiple(array $keys, $group = '') { return WP_Object_Cache::instance()->delete_multiple($keys, $group); }
function wp_cache_incr($key, $offset = 1, $group = '') { return WP_Object_Cache::instance()->incr($key, (int) $offset, $group); }
function wp_cache_decr($key, $offset = 1, $group = '') { return WP_Object_Cache::instance()->incr($key, ((int) $offset) * -1, $group); }
function wp_cache_flush() { return WP_Object_Cache::instance()->flush(); }
function wp_cache_flush_runtime() { return WP_Object_Cache::instance()->flush_runtime(); }
function wp_cache_flush_group($group) { return WP_Object_Cache::instance()->flush_group($group); }
function wp_cache_supports($feature) { return in_array($feature, ['add_multiple', 'set_multiple', 'get_multiple', 'delete_multiple', 'flush_runtime', 'flush_group']); }
function wp_cache_add_global_groups($groups) { WP_Object_Cache::instance()->add_global_groups($groups); }
function wp_cache_get_global_groups() { return WP_Object_Cache::instance()->get_global_groups(); }
function wp_cache_add_non_persistent_groups($groups) { WP_Object_Cache::instance()->add_non_persistent_groups($groups); }
function wp_cache_get_np_groups() { return WP_Object_Cache::instance()->get_np_groups(); }
function wp_cache_switch_to_blog($blog_id) { WP_Object_Cache::instance()->switch_to_blog($blog_id); }
function wp_cache_close() { return true; }
function wp_cache_reset() { _deprecated_function(__FUNCTION__, '3.5.0', 'wp_cache_switch_to_blog()'); return false; }

function wp_cache_wpc_array() { return WP_Object_Cache::instance()->wpc_array(); }
function wp_cache_wpc_delete($key) { return WP_Object_Cache::instance()->wpc_delete($key); }
function wp_cache_wpc_counts() { return WP_Object_Cache::instance()->wpc_counts(); }


/* OC DRIVER CLASS START */
/* OC INSERT DRIVER CLASS */
/* OC DRIVER CLASS END */

// CLASS WP_Object_Cache //

final class WP_Object_Cache
{
	private $timerStart = [];

	public $cache_hits = 0;
	public $cache_misses = 0;
	public $cache_sets = 0;

	private $blog_prefix, $multisite;

	private $cache = array();
	private $np_groups = array('atec_np' => true);	// atec_np: atec-plugins version cache
	private $global_groups = array();

	private static $_instance;

	public static function instance(): self
	{
		return self::$_instance ??= new self();
	}

	private function __construct()
	{
		$this->multisite	= is_multisite();
		$this->blog_prefix = $this->multisite ? get_current_blog_id() : '';
		$unique_key = defined('AUTH_KEY') ? AUTH_KEY : get_option('blogname');
		if (!defined('ATEC_OC_KEY_SALT')) define('ATEC_OC_KEY_SALT',hash('crc32', AUTH_KEY, FALSE));
		$this->init_driver();
	}

	/* OC DRIVER START */
/* OC INSERT DRIVER */
	/* OC DRIVER END */

	/* OC TOOLS START */

	public function wpc_array() { return $this->cache; }
	public function wpc_delete($key) { $exists = isset($this->cache[$key]); unset($this->cache[$key]); apcu_delete($key); return $exists; }
	public function wpc_counts() { return ['hits'=>$this->cache_hits, 'misses'=>$this->cache_misses, 'sets'=>$this->cache_sets]; }

	/* OC TOOLS END */

	private function build_key($key, &$group, &$fullkey)
	{
		if (!(is_int($key) || (is_string($key) && trim($key)!== ''))) { 	return false; }
		if (empty($group)) { $group = 'default'; }
		$prefix = ($this->multisite && !isset($this->global_groups[$group])) ? $this->blog_prefix . ':' : '';
		$fullkey = ATEC_OC_KEY_SALT.':'.$prefix . $group . ':' . $key;
		return true;
	}

	public function add($key, $var, $group = 'default', $expire = 0)
	{
		if (wp_suspend_cache_addition()??false) return false;
		if (!$this->build_key($key, $group, $fullKey)) return false;
		return $this->set($key, $var, $group, $expire, $fullKey, true);
	}

	public function add_multiple(array $data, $group = 'default', $expire = 0)
	{
		$values = [];
		foreach ($data as $key => $value) { $values[$key] = $this->add($key, $value, $group, $expire); }
		return $values;
	}

	public function delete($key, $group = 'default', $deprecated = false)
	{
		if (!$this->build_key($key, $group, $fullKey)) return false;
		$result = isset($this->np_groups[$group]) ? $this->delete_np($fullKey) : $this->delete_p($fullKey);
		return $result;
	}

	public function delete_multiple(array $keys, $group = 'default')
	{
		$values = [];
		foreach ($keys as $key) { $values[$key] = $this->delete($key, $group); }
		return $values;
	}

	private function delete_np($fullKey)
	{
		if (isset($this->cache[$fullKey])) { unset($this->cache[$fullKey]); return true; }
		return false;
	}

	public function get($key, $group = 'default', $force = false, &$success = null)
	{
		if (!$this->build_key($key, $group, $fullKey)) { $success = false; return false; }
		$var = isset($this->np_groups[$group]) ? $this->get_np($fullKey, $success) : $this->get_p($fullKey, $success);
		if ($success) { $this->cache_hits++; }
		else $this->cache_misses++;
		if (is_object($var)) $var = clone $var;
		return $var;
	}

	private function get_np($fullKey, &$success = null)
	{
		if (isset($this->cache[$fullKey]))
		{
			$success = true;
			return $this->cache[$fullKey];
		}
		$success = false;
		return false;
	}

	public function get_multiple($keys, $group = 'default', $force = false)
	{
		$values = [];
		foreach ($keys as $key) { $values[$key] = $this->get($key, $group, $force); }
		return $values;
	}

	public function set($key, $var, $group = 'default', $expire = 0, $fullKey=null, $add=null)
	{
		if (!is_null($fullKey)) 
		{
			$exists = $this->exists($fullKey);
			if ($add) { if ($exists) return; } // Add
			elseif (!$exists) return; // Replace
		}
		elseif (!$this->build_key($key, $group, $fullKey)) return false;
	
		// Skip persistent cache if group is marked non-persistent
		$result = $this->set_np($fullKey, $var);	// Set local cache before changes to $var
		if (isset($this->np_groups[$group])) { return $result; }
		
		if ($group=== 'options')
		{
			if ($key === 'alloptions')	// Sanitize & optimize "options:alloptions"
			{
				unset($var['cron']); 	// Unset highly volatile keys
			}
			elseif ($key=== 'cron')
			{
				return $result;
			}
		}
	
		return $this->set_p($fullKey, $var, $expire); 
	}

	public function set_multiple(array $data, $group = '', $expire = 0)
	{
		$values = [];
		foreach ($data as $key => $var) { $values[$key] = $this->set($key, $var, $group, $expire); }
		return $values;
	}

	private function set_np($fullKey, $var)
	{
		$this->cache_sets++;
		if (is_object($var)) { $var = clone $var; }
		$this->cache[$fullKey] = $var;
		return true;
	}

	public function incr($key, $offset = 1, $group = 'default')
	{
		if (!$this->build_key($key, $group, $fullKey)) return false;
		if (!$this->exists($fullKey)) return false;
		if (isset($this->np_groups[$group]))
		{
			$value = $this->incr_clean($this->cache[$fullKey], $offset);
		} 
		else
		{
			$value = $this->incr_clean($this->get_p($fullKey), $offset);
			$this->set_p($fullKey, $value);
		}
		$this->cache[$fullKey] = $value;
		return $value;
	}

	private function incr_clean($value, $offset)
	{
		if (!is_numeric($value)) $value = 0;
		return max($value+$offset,0);
	}

	// wp_cache_replace is not used in any WP core files
	public function replace($key, $var, $group = 'default', $expire = 0)	
	{
		if (!$this->build_key($key, $group, $fullKey)) return false;
		return $this->set($key, $var, $group, $expire, $fullKey, false);
	}

	public function add_global_groups($groups) { foreach ((array)$groups as $group) { $this->global_groups[$group] = true; } }
	public function get_global_groups() { return $this->global_groups; }

	public function add_non_persistent_groups($groups) { foreach ((array)$groups as $group) { $this->np_groups[$group] = true; } }
	public function get_np_groups() { return $this->np_groups; }

	public function flush_runtime()
	{
		$this->cache = [];
		$this->cache_hits = $this->cache_misses = $this->cache_sets = 0;
		return true;
	}

	public function switch_to_blog($blog_id) { $this->blog_prefix = $this->multisite ? $blog_id . ' : ' : ''; }

	public function stats()
	{
		echo '
		<p>
		<strong>Cache Hits:</strong> ', esc_attr($this->cache_hits), '<br />
		<strong>Cache Misses:</strong>', esc_attr($this->cache_misses), '<br />
		<strong>Cache Sets:</strong>', esc_attr($this->cache_sets), '<br />
		</p>';
	}

}

// Automatically clean up stale WordPress update lock on shutdown
register_shutdown_function(function ()
{

	if (defined('DOING_AJAX') || defined('DOING_CRON') || defined('WP_UNINSTALL_PLUGIN') || defined('WP_CLI') || (defined('REST_REQUEST') && REST_REQUEST) || !is_admin()) return;

	// Get the update lock timestamp set by WordPress core
	// If the lock is numeric and has expired, it’s safe to clear
	$lock = get_option('core_updater.lock');
	if (is_numeric($lock) && time() > (int)$lock)
	{
		delete_option('core_updater.lock');
		unlink(ABSPATH . '.maintenance');	// phpcs:ignore
	
		if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) 
			error_log('[AOC AutoHeal] Stale core_updater.lock + .maintenance file cleared');	// phpcs:ignore
	}
});
?>