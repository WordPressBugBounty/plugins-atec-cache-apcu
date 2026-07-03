<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\TOOLS;

final class CRON {

public static function clear($name)
{ wp_clear_scheduled_hook($name); }

public static function get($name)
{ return wp_get_scheduled_event($name); }

public static function run($name, $args = null)
{
	$event = wp_get_scheduled_event($name, $args ?? []);
	if (!$event) return false;

	$args = $event->args ?? [];

	// Future event — queue an immediate single run. Already due — leave the existing entry.
	if ($event->timestamp > time())
	{
		$ok = wp_schedule_single_event(time(), $name, $args);
		if (!$ok && wp_next_scheduled($name, $args)) $ok = true;
	}
	else $ok = true;

	if ($ok && function_exists('spawn_cron')) spawn_cron();

	return $ok;
}

public static function next($name)
{ return wp_next_scheduled($name); }

public static function next_ts($name)
{
	$next = self::next($name);
	if (!$next) return false;
	$diff = (int) ($next - time());
	if ($diff <= 0) return 'due';
	return TOOLS::format_duration($diff);
}

public static function set($name, $desired, $offset = 0)
{
	self::clear($name);
	wp_schedule_event(time() + $offset, $desired, $name);
}

public static function set_single($name, $delay = 5, $args = [])
{
	wp_schedule_single_event(time() + $delay, $name, $args);
}

public static function error_log($name)
{
	if (!wp_next_scheduled($name)) error_log('atec-cron: Failed to schedule ‘'.esc_attr($name).'’.');	// phpcs:ignore
}

public static function schedule($name)
{ return wp_get_schedule($name); }

}
?>