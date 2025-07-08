<?php
namespace ATEC;
defined('ABSPATH') || exit;

use ATEC\INIT;
use ATEC\TOOLS;

final class AJAX
{
	
public static function generic_inline(string $slug, bool $remove = true): void
{
	$handle		= self::script_id($slug);					// 'atec-wpfd-ajax-script'
	$var				= self::var_name($slug);				// 'atec_wpfd_ajax'
	$func			= "{$var}_cb";								// 'atec_wpfd_ajax_cb'
	$action			= "atec_{$slug}_ajax";
	$nonce_key	= self::nonce_key($slug);				// 'atec_wpfd_ajax_nonce'

	wp_register_script($handle, false, ['jquery'], null, true);

	wp_localize_script($handle, $var, [
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce'   => wp_create_nonce($nonce_key),
	]);

	wp_enqueue_script($handle);

	$inline_js = 'function ' . $func . '(cmd){'
		. 'jQuery.post(' . $var . '.ajaxurl,{'
		. 'action:"' . $action . '",'
		. 'nonce:' . $var . '.nonce,'
		. 'cmd:cmd'
		. '});';

	// Dim the button color onclick
	if ($remove)
	{
		$inline_js .= 'var el=jQuery(event.currentTarget);'
		. 'el.css({opacity:0.75,pointerEvents:"none",filter:"grayscale(1)"});'
		. 'el.html(el.html() + " ✅");';
	}
	
	$inline_js .= '}';

	wp_add_inline_script($handle, $inline_js);
}

public static function script_id(string $slug): string
{ return 'atec-' . $slug . '-ajax-script'; }

public static function var_name(string $slug): string
{ return 'atec_' . $slug . '_ajax'; }

public static function nonce_key(string $slug): string
{ return 'atec_' . $slug . '_ajax_nonce'; }

public static function nonce_check(string $slug): void
{
	$key = self::nonce_key($slug);
	$val = INIT::POST('nonce');

	if (! wp_verify_nonce($val, $key)) {
		wp_send_json_error(['error' => 'Nonce check failed', 'key' => $key], 403);
	}
}

/**
* Load and localize a plugin-specific AJAX script.
* Registers script "atec-{$slug}-ajax-script" and injects nonce + ajaxurl as "atec_{$slug}_ajax".
*/
public static function load_script(string $slug, string $dir, string $ver = '1.0.1', array $deps = [], bool $lazy = false, array $extraData = []): void
{
	$filename = 'atec-' . $slug . '-ajax.js';
	if (INIT::is_atec_dev_mode()) $filename = str_replace('.js', '.min.js', $filename);

	$id				= self::script_id($slug);
	$var_name	= self::var_name($slug);
	$nonce_key	= self::nonce_key($slug);

	$data = array_merge([
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce'   => wp_create_nonce($nonce_key)
	], $extraData);

	TOOLS::load_script_localized($id, $dir, $filename, $ver, $var_name, $data, $deps, $lazy);
}

}
?>