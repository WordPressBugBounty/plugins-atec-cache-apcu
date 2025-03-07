<?php
/**
* Plugin Name:  atec APCu Object-Cache
* Plugin URI: https://atecplugins.com/
* Description: APCu Object Cache
* Version: 1.0.16
* Requires at least:4.9
* Tested up to: 6.7
* Tested up to PHP: 8.4.2
* Requires PHP: 7.4
* Requires CP: 1.7
* Premium URI: https://atecplugins.com
* Author: Chris Ahrweiler ℅ atecplugins.com
* Author URI: https://atec-systems.com/
* License: GPL2
* License URI:  https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:  atec-apcu-object-cache
*/

if (!defined('ABSPATH')) { exit(); }
define('ATEC_APCU_OC_VERSION','1.0.16');

function wp_cache_init() { $GLOBALS['wp_object_cache'] = WP_Object_Cache::instance(); }
function wp_cache_add($key, $data, $group = '', $expire = 0) { return WP_Object_Cache::instance()->add($key, $data, $group, (int) $expire); }
function wp_cache_add_multiple(array $data, $group = '', $expire = 0) { return WP_Object_Cache::instance()->add_multiple($data, $group, (int) $expire); }
function wp_cache_set($key, $data, $group = '', $expire = 0) { return WP_Object_Cache::instance()->set($key, $data, $group, (int) $expire); }
function wp_cache_set_multiple(array $data, $group = '', $expire = 0) { return WP_Object_Cache::instance()->set_multiple($data, $group, (int) $expire); }
function wp_cache_get($key, $group = '', $force = false, &$found = null) { return WP_Object_Cache::instance()->get($key, $group, $force, $found); }
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


// CLASS WP_Object_Cache //

class WP_Object_Cache
{
	public $cache_sets = 0;
	public $cache_hits = 0;
	public $cache_misses = 0;

	private $blog_prefix, $multisite;

	private $cache = array();
	private $np_groups = array('transient');
	private $global_groups = array();

	private static $_instance;

	/* OC specific START */

	private function __construct()
	{
		$this->multisite   = is_multisite();
		$this->blog_prefix = $this->multisite ? get_current_blog_id() . ':' : '';
		if (!defined('WP_APCU_KEY_SALT')) define('WP_APCU_KEY_SALT',hash('crc32', AUTH_KEY, FALSE));
	}

	private function build_key($key, &$group)
	{
		if (empty($group)) { $group = 'default'; }
		$prefix = ($this->multisite && !isset($this->global_groups[$group]))?$this->blog_prefix.':':'';
		return WP_APCU_KEY_SALT.':'.$prefix.$group.':'.$key;
	}

	private function exists_p($key) { return isset($this->cache[$key]) || apcu_exists($key); }

	private function delete_p($key)
	{
		if ($this->exists_p($key)) { unset($this->cache[$key]); apcu_delete($key); return true; }
		return false;
	}

	private function get_p($key, &$success = null)
	{
		$localSuccess=false;
		if (isset($this->cache[$key])) { $success = true; $localSuccess=true; $var = $this->cache[$key]; }
		else $var = apcu_fetch($key,$success);
		if ($success)
		{
			if (is_object($var)) $var = clone $var;
			if (!$localSuccess) $this->cache[$key] = $var;
			return $var;
		}
		return false;
	}

	private function set_p($key, $var, $expire = 0)
	{
		if (is_object($var)) $var = clone $var;
		$this->cache[$key] = $var;
		if (empty($var)) return false;
		return apcu_store($key, $var, max($expire, 0));
	}

	public function flush()
	{
		$this->flush_runtime();
		if (!class_exists('APCUIterator')) return false;
		$apcu_it=new APCUIterator('/'.WP_APCU_KEY_SALT.'/');
		if (iterator_count($apcu_it)!==0) { foreach ($apcu_it as $entry) apcu_delete($entry['key']); }
		return true;
	}

	public function flush_group($group)
	{
		if (!class_exists('APCUIterator')) return false;
		$prefix = ($this->multisite && !isset($this->global_groups[$group]))?$this->blog_prefix.':':'';
		$reg = WP_APCU_KEY_SALT.':'.$prefix.$group;
		if (iterator_count($apcu_it=new APCUIterator('/'.$reg.'/'))!==0)
		{ foreach ($apcu_it as $entry) { unset($this->cache[$entry['key']]); apcu_delete($entry['key']); } }
		return true;
	}
	
	public function wpc_array() { return $this->cache; }
	public function wpc_delete($key) { $exists = isset($this->cache[$key]); unset($this->cache[$key]); apcu_delete($key); return $exists; }
	public function wpc_counts() { return ['hits'=>$this->cache_hits, 'misses'=>$this->cache_misses, 'sets'=>$this->cache_sets]; }
	
	/* OC specific END */

	public static function instance()
	{
		if (self::$_instance === null) { self::$_instance = new WP_Object_Cache(); }
		return self::$_instance;
	}

	public function add($key, $var, $group = 'default', $expire = 0)
	{
		if (!(is_int($key) || (is_string($key) && trim($key)!==''))) return false;
		if (wp_suspend_cache_addition()??false) return false;
		$key = $this->build_key($key, $group);
		return isset($this->np_groups[$group]) ? $this->add_np($key, $var) : $this->add_p($key, $var, $expire);
	}

	public function add_multiple(array $data, $group = 'default', $expire = 0)
	{
		$values = [];
		foreach ($data as $key => $value) { $values[$key] = $this->add($key, $value, $group, $expire); }
		return $values;
	}

	private function add_p($key, $var, $expire)
	{
		if ($this->exists_p($key)) return false;
		return $this->set_p($key, $var, $expire);
	}

	private function add_np($key, $var)
	{
		if (isset($this->cache[$key])) return false;
		return $this->set_np($key, $var);
	}

	public function delete($key, $group = 'default', $deprecated = false)
	{
		if (!(is_int($key) || (is_string($key) && trim($key)!==''))) return false;
		$key = $this->build_key($key, $group);
		return isset($this->np_groups[$group]) ? $this->delete_np($key) : $this->delete_p($key);
	}

	public function delete_multiple(array $keys, $group = 'default')
	{
		$values = [];
		foreach ($keys as $key) { $values[$key] = $this->delete($key, $group); }
		return $values;
	}

	private function delete_np($key)
	{
		if (isset($this->cache[$key])) { unset($this->cache[$key]); return true; }
		return false;
	}

	public function get($key, $group = 'default', $force = false, &$success = null)
	{
		if (!(is_int($key) || (is_string($key) && trim($key)!==''))) return false;
		$key = $this->build_key($key, $group);
		$var = isset($this->np_groups[$group]) ? $this->get_np($key, $success) : $this->get_p($key, $success);
		if ($success) $this->cache_hits++;
		else $this->cache_misses++;
		return $var;
	}

	private function get_np($key, &$success = null)
	{
		if (isset($this->cache[$key])) { $success = true; return $this->cache[$key]; }
		$success = false; 
		return false;
	}

	public function get_multiple($keys, $group = 'default', $force = false)
	{
		$values = [];
		foreach ($keys as $key) { $values[$key] = $this->get($key, $group, $force); }
		return $values;
	}
	
	private 	function can_unserialize($v) 	{ return is_string($v) && preg_match('/^a:\d+:\{.*\}$/s', $v) && !preg_match('/[;}O]:\d+:"[^"]+"/s', $v); }

	public function set($key, $var, $group = 'default', $expire = 0)
	{   
		if (!(is_int($key) || (is_string($key) && trim($key)!==''))) return false;
		if ($key==='alloptions') { foreach($var as $k=>$v) { if ($this->can_unserialize($v)) $var[$k]=unserialize($v); } }
		elseif ($this->can_unserialize($var)) $var=unserialize($var);
		$key = $this->build_key($key, $group);
		$this->cache_sets++;
		return isset($this->np_groups[$group]) ? $this->set_np($key, $var) : $this->set_p($key, $var, $expire);
	}

	public function set_multiple(array $data, $group = '', $expire = 0)
	{
		$values = [];
		foreach ($data as $key => $var) { $values[$key] = $this->set($key, $var, $group, $expire); }
		return $values;
	}

	private function set_np($key, $var)
	{
		if (is_object($var)) $var = clone $var;
		$this->cache[$key] = $var;
		return true;
	}

	public function incr($key, $offset = 1, $group = 'default')
	{
		if (!(is_int($key) || (is_string($key) && trim($key)!==''))) return false;
		$key = $this->build_key($key, $group);
		return isset($this->np_groups[$group]) ? $this->incr_np($key, $offset) : $this->incr_p($key, $offset);
	}

	private function incr_clean($value, $offset)
	{
		if (!is_numeric($value)) $value = 0;
		return max($value+$offset,0);
	}

	private function incr_p($key, $offset)
	{
		if (!$this->exists_p($key)) return false;
		$value = $this->incr_clean($this->get_p($key), $offset);
		$this->set_p($key,$value);
		return $value;
	}

	private function incr_np($key, $offset)
	{
		if (!isset($this->cache[$key])) return false;
		$value = $this->incr_clean($this->cache[$key], $offset);
		$this->cache[$key] = $value;
		return $value;
	}

	public function replace($key, $var, $group = 'default', $expire = 0)
	{
		if (!(is_int($key) || (is_string($key) && trim($key)!==''))) return false;
		$key = $this->build_key($key, $group);
		return isset($this->np_groups[$group]) ? $this->replace_np($key, $var) : $this->replace_p($key, $var, $expire);
	}

	private function replace_p($key, $var, $expire)
	{
		if (!$this->exists_p($key)) return false;
		return $this->set_p($key, $var, $expire);
	}

	private function replace_np($key, $var)
	{
		if (!isset($this->cache[$key])) return false;
		return $this->set_np($key, $var);
	}

	public function add_global_groups($groups) { foreach ((array)$groups as $group) { $this->global_groups[$group] = true; } }
	public function get_global_groups() { return $this->global_groups; }
	
	public function add_non_persistent_groups($groups) { foreach ((array)$groups as $group) { $this->np_groups[$group] = true; } }
	public function get_np_groups() { return $this->np_groups; }

	public function flush_runtime()
	{
		$this->cache = [];
		return true;
	}

	public function switch_to_blog($blog_id) { $this->blog_prefix = $this->multisite ? $blog_id . ':' : ''; }
		
	public function stats() 
	{
		echo '
		<p>
		<strong>Cache Hits:</strong> ', esc_attr($this->cache_hits), '<br />
		<strong>Cache Misses:</strong>', esc_attr($this->cache_misses), '<br />
		</p>';
	}

}