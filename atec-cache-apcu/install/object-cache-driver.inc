	<?php
	private function init_driver()
	{
		define('ATEC_OC_ACTIVE_APCU', true);
		if (!defined('APC_ITER_KEY')) define('APC_ITER_KEY', 1);
	}

	private function exists($fullKey) { return isset($this->cache[$fullKey]) || apcu_exists($fullKey); }

	private function delete_p($fullKey)
	{
		if ($this->exists($fullKey)) { unset($this->cache[$fullKey]); apcu_delete($fullKey); return true; }
		return false;
	}

	private function get_p($fullKey, &$success = null)
	{
		$localSuccess=false;
		
		if (isset($this->cache[$fullKey]))
		{
			$success = true;
			$localSuccess=true;
			$var = $this->cache[$fullKey];
		}
		else 
		{
			$var = apcu_fetch($fullKey, $success);
		}
		
		if ($success)
		{
			if (!$localSuccess) $this->cache[$fullKey] = $var;	// Add persistent $key to local cache
			return $var;
		}
		
		$success = false;
		return false;
	}

	private function set_p($fullKey, $var, $expire = 0)
	{
		$this->cache_sets++;
		$result = apcu_store($fullKey, $var, max($expire, 0));
		if (!$result) 
		{
			error_log("⚠️ APCu failed to store: $fullKey (" . gettype($var) . "), size: " . strlen(serialize($var)) . " bytes");
			// Prevent stale data from being served
			apcu_delete($fullKey);
		}
		return $result;
	}

	public function flush()
	{
		$this->flush_runtime();
		$pattern = '/^' . preg_quote(ATEC_OC_KEY_SALT, '/') . ':/';
		$it = new APCUIterator($pattern, APC_ITER_KEY, 200);
	
		$n = 0;
		foreach ($it as $entry) {
			apcu_delete($entry['key']);
			//if (++$n >= 50000) break; // optional hard cap safety (prevents pathological cases)
		}
	
		return true;
	}

	public function flush_group($group)
	{
		$prefix = ($this->multisite && !isset($this->global_groups[$group])) ? $this->blog_prefix . ':' : '';
		$needle = ATEC_OC_KEY_SALT . ':' . $prefix . $group;

		// Remove matching local cache entries
		foreach ($this->cache as $fullkey => $_)
		{
			//if (str_starts_with($fullkey, $needle)) unset($this->cache[$fullkey]);
			if (strncmp($fullkey, $needle, strlen($needle)) === 0) unset($this->cache[$fullkey]);
		}

		$pattern = '/^' . preg_quote($needle, '/') . ':/';
		$apcu_it = new APCUIterator($pattern, APC_ITER_KEY, 200);

		foreach ($apcu_it as $entry)
		{
			unset($this->cache[$entry['key']]);
			apcu_delete($entry['key']);
		}

		return true;
	}
