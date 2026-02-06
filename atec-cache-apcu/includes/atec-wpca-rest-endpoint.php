<?php
namespace ATEC_WPCA;

defined('ABSPATH') || exit;

use ATEC\INIT;

// Prevent fatal redeclare if included twice (custom loaders/opcache can do this).
if (class_exists(__NAMESPACE__ . '\\Rest', false)) {
	return;
}

/**
 * REST endpoints used by WPCA diagnostics (e.g., Workers/APCu probe).
 *
 * Route:
 *   POST /wp-json/atec-wpca/v1/worker-probe
 */
final class Rest
{
	public static function register(): void
	{
		if (!function_exists('register_rest_route')) {
			return;
		}

		register_rest_route('atec-wpca/v1', '/worker-probe', [
			'methods'  => ['GET','POST'],
			'callback' => [self::class, 'worker_probe'],
			'permission_callback' => [self::class, 'permission'],
		]);
	}

	public static function permission(\WP_REST_Request $req): bool
	{
		// Capability gate
		if (!INIT::current_user_can('admin')) {
			return false;
		}

		// Nonce gate: require X-WP-Nonce header
		$nonce = $req->get_header('x_wp_nonce');
		return $nonce && (bool) wp_verify_nonce($nonce, 'wp_rest');
	}

	public static function worker_probe(\WP_REST_Request $req): \WP_REST_Response
	{
		// Optional overlap delay (milliseconds)
		$sleep_ms = (int) ($req->get_param('sleep_ms') ?? 0);
		if ($sleep_ms > 0) {
			usleep(min($sleep_ms, 2000) * 1000); // cap at 2s
		}

		$pid = function_exists('getmypid') ? (int) getmypid() : 0;

		$apcu = function_exists('apcu_fetch') && (bool) ini_get('apc.enabled');
		$marker_pid = null;
		$shared_signal = false;

		if ($apcu) {
			$key = (defined('ATEC_OC_KEY_SALT') ? ATEC_OC_KEY_SALT : 'atec') . ':wpca:probe_marker';
			$ok = false;
			$marker = apcu_fetch($key, $ok);

			if ($ok && is_array($marker) && isset($marker['pid'])) {
				$marker_pid = (int) $marker['pid'];
				if ($marker_pid && $pid && $marker_pid !== $pid) {
					$shared_signal = true; // Different PID saw same APCu entry => shared APCu
				}
			} else {
				apcu_store($key, ['pid' => $pid, 'ts' => microtime(true)], 30);
			}
		}

		return new \WP_REST_Response([
			'pid' => $pid,
			'apcu' => $apcu,
			'marker_pid' => $marker_pid,
			'shared_apcu_signal' => $shared_signal,
			'ts' => microtime(true),
		], 200);
	}
}
