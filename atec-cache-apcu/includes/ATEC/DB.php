<?php
namespace ATEC;
defined('ABSPATH') || exit;

final class DB
{
	private static string $basePrefix = '';

	// GENERAL
	
	public static function db_size(): array
	{
		global $wpdb;
	
		$data_size = 0;
		$index_size = 0;
		$wpdb->suppress_errors(true);
		$tablesstatus = $wpdb->get_results('SHOW TABLE STATUS');	// phpcs:ignore
		$wpdb->suppress_errors(false);
	
		if (empty($tablesstatus)) { return ['data'    => 0, 'index' => 0]; }
	
		foreach ($tablesstatus as $tablestatus)
		{
			$data_size += (int) $tablestatus->Data_length;
			$index_size += (int) $tablestatus->Index_length;
		}
	
		return [ 'data' => $data_size, 'index' => $index_size ];

		return $size;
	}
	
	public static function db_info(): array
	{
		global $wpdb;
		$unkown = '-/-';
		$wpdb->suppress_errors(true);
			$raw = $wpdb->get_var('SELECT VERSION()');														// phpcs:ignore
			$comment = $wpdb->get_var("SHOW VARIABLES LIKE 'version_comment'", 1);		// phpcs:ignore
		$wpdb->suppress_errors(false);
	
		if (!$raw) { return [ 'name' => $unkown, 'version' => $unkown, 'software' => $unkown]; }
	
		$name    = str_contains(strtolower($raw), 'mariadb') ? 'MariaDB' : 'MySQL';
		$version = str_ireplace('-MariaDB', '', $raw);
	
		return [ 'name' => $name, 'version' => $version, 'software' => $comment ?: $unkown];
	}
	
	public static function is_error($result, &$error = '')
	{
		//You can have a situation where: $result !== false and !empty($wpdb->last_error) is still true
		global $wpdb;
	
		$last_error = $wpdb->last_error;
		$is_error   = ($result === false || !empty($last_error));
	
		if ($is_error && !empty($last_error)) 
		{
			// Ensure period at end and add space if there's already content
			$last_error = rtrim($last_error, ". \t\n\r\0\x0B") . '.';
			if ($error !== '') $error .= ' ';
			$error .= $last_error;
		}
	
		return $is_error;
	}

	//ENGINE
	
	/**
	 * Returns the charset/collation string for CREATE TABLE statements, or empty if unsupported.
	 */
	public static function get_engine(): string
	{
		static $cached = null;
		if ($cached !== null) return $cached;
	
		global $wpdb;
	
		$charset = is_string($wpdb->charset) && $wpdb->charset !== '' ? $wpdb->charset : 'utf8mb4';
		$collate = is_string($wpdb->collate) && $wpdb->collate !== '' ? $wpdb->collate : 'utf8mb4_general_ci';
	
		$engine = 'ENGINE=InnoDB';
	
		if (preg_match('/^[a-z0-9_]+$/i', $charset) && preg_match('/^[a-z0-9_]+$/i', $collate)) $cached = "$engine CHARSET=$charset COLLATE=$collate";
		else $cached = $engine;
	
		return $cached;
	}

	/**
	 * Returns ROW_FORMAT=COMPRESSED if supported by creating a temp table, else empty string.
	 */
	public static function get_row_format(): string
	 {
		static $cached = null;
		if ($cached !== null) return $cached;

		global $wpdb;

		$table = $wpdb->prefix . 'atec_tmp_check_compression_' . strtolower(wp_generate_password(4, false, false));
		$success = false;

		$wpdb->suppress_errors(true);
			// phpcs:ignore
			$result = $wpdb->query("CREATE TABLE `$table` (id INT) ENGINE=InnoDB ROW_FORMAT=COMPRESSED COMMENT='ATEC TMP CHECK'");
			// phpcs:ignore
			if ($result !== false) { $success = true; $wpdb->query("DROP TABLE IF EXISTS `$table`"); }
		$wpdb->suppress_errors(false);

		$cached = $success ? ' ROW_FORMAT=COMPRESSED' : '';
		return $cached;
	}

	// IS_TABLE

	public static function table_exists(string $table, string $wildcard = ''): bool
	{
		global $wpdb;
	
		$pattern = esc_sql($wpdb->esc_like($table) . $wildcard);
	
		$wpdb->suppress_errors(true);
		$result = $wpdb->get_var("SHOW TABLES LIKE '{$pattern}'");	// phpcs:ignore
		$wpdb->suppress_errors(false);
	
		if ($wildcard !== '') {
			return !empty($result); // wildcard match
		}
	
		return strtolower((string) $result) === strtolower($table);
	}

	public static function column_exists(string $table, string $column): bool
	{
		global $wpdb;
	
		// Validate table and column names to avoid injection
		if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
			return false;
		}
	
		$wpdb->suppress_errors(true);
		$escaped_table  = esc_sql($table);
		$escaped_column = esc_sql($wpdb->esc_like($column));
	
		$result = $wpdb->get_var("SHOW COLUMNS FROM `$escaped_table` LIKE '{$escaped_column}'");	// phpcs:ignore
		$wpdb->suppress_errors(false);
	
		return strtolower($result) === strtolower($column);
	}
	
	public static function primary_key(string $table): ?string
	{
		global $wpdb;
		$wpdb->suppress_errors(true);
			$table_safe = esc_sql($table);
			$prim = $wpdb->get_results("SHOW COLUMNS FROM `$table_safe` WHERE `Key` = 'PRI'");	// phpcs:ignore
		$wpdb->suppress_errors(false);
	
		return isset($prim[0]->Field) ? $prim[0]->Field : null;
	}

	// TABLE

	public static function drop_table(string $table): bool
	{
		global $wpdb;
	
		$safe_table = esc_sql($table);
		$wpdb->suppress_errors(true);
			$result = $wpdb->query("DROP TABLE IF EXISTS `$safe_table`");		// phpcs:ignore
		$wpdb->suppress_errors(false);
	
		return $result !== false;
	}

	public static function truncate_table(string $table): bool
	{
		global $wpdb;
	
		$safe_table = esc_sql($table);
		$wpdb->suppress_errors(true);
			$result = $wpdb->query("TRUNCATE TABLE `$safe_table`");	// phpcs:ignore
		$wpdb->suppress_errors(false);
	
		return $result !== false;
	}

	public static function create_table(string $table, string $sql, bool $compress=false): bool
	{
		// Enable compress = true only if:
		// The table contains LONGTEXT, BLOB, or similar large fields
		// It's expected to hold tens of thousands of rows
		global $wpdb;
	
		$safe_table = esc_sql($table);
		$engine = self::get_engine();
		if ($compress) $engine .= self::get_row_format();
		$query  = "CREATE TABLE `$safe_table` ($sql) $engine";
		$wpdb->suppress_errors(true);
			$result = $wpdb->query($query);	// phpcs:ignore
		$wpdb->suppress_errors(false);
	
		return $result !== false;
	}
	
	public static function insert(string $table, array $data): bool
	{
		global $wpdb;
	
		if (empty($table) || empty($data)) return false;
	
		$format = [];
		foreach ($data as $value)
		{
			if (is_int($value)) $format[] = '%d';
			elseif (is_float($value)) $format[] = '%f';
			else $format[] = '%s';
		}
	
		return (bool) $wpdb->insert($table, $data, $format);
	}

	private static array $customTables = ['wpmc' => 'mega_cache'];

	public static function esc_table(string $slug): string
	{
		return esc_sql(self::table($slug));
	}
	
	public static function prefix(): string
	{
		if (self::$basePrefix === '')
		{
			global $wpdb;
			self::$basePrefix = $wpdb->base_prefix;
		}
		return self::$basePrefix;
	}

	public static function table(string $slug): string
	{
		$name = self::$customTables[$slug] ?? 'atec_' . $slug;
		return self::prefix() . $name;
	}
	
	public static function all_tables(): array
	{
		global $wpdb;
		return $wpdb->get_results('SHOW TABLES');		// phpcs:ignore
	}

}
?>