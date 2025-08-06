<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\TOOLS;

final class CRON {

public static function clear($name)
{ wp_clear_scheduled_hook($name); }

public static function get($name)
{ return wp_get_scheduled_event($name); }

public static function run($name)
{ do_action($name); }

public static function next($name)
{ return wp_next_scheduled($name); }

public static function next_ts($name)
{
	$next = self::next($name);
	return $next ? TOOLS::format_duration($next-time()) : false;
}

public static function set($name, $desired, $offset = 0)
{
	self::clear($name);
	wp_schedule_event(time() + $offset, $desired, $name);
	//self::error_log($name);
}

public static function set_single($name, $delay = 5, $args = [])
{
	//self::clear($name); // Optional: avoid duplicates
	wp_schedule_single_event(time() + $delay, $name, $args);
	//self::error_log($name);
}

public static function error_log($name)
{
	if (!wp_next_scheduled($name)) error_log('atec-cron: Failed to schedule ‘'.esc_attr($name).'’.');	// phpcs:ignore
}

public static function schedule($name)
{ return wp_get_schedule($name); }

}
?>