<?php
if (!defined('ABSPATH')) { exit(); }

function atec_wpca_page_buffer_start(): void
{ 	 
	// @codingStandardsIgnoreStart | $_POST and $_SERVER is uncritical as it is only used for comparison */
	if (($_SERVER['REQUEST_METHOD']??'')!=='GET') { @header('X-Cache: SKIP:GET'); return; }
	// @codingStandardsIgnoreEnd

	$args = add_query_arg(null,null);
	if (str_contains($args,'/password-reset/') || str_contains($args,'/login/') || str_contains($args,'/wp-admin/')) { @header('X-Cache: SKIP/LOGIN'); return; }
	if (str_contains($args,'/?') && !(str_contains($args,'/?p=') || str_contains($args,'/?page_id='))) { @header('X-Cache: SKIP/QUERY'); return; }

	global $wp_query;
	if ($wp_query->is_404 || $wp_query->is_search || $wp_query->is_login || $wp_query->is_admin) { @header('X-Cache: SKIP:IS_'); return; }
	
	if (class_exists('WooCommerce') && (is_cart() || is_checkout() || is_account_page() || is_woocommerce())) { @header('X-Cache: SKIP:WOO'); return; }
	if (is_user_logged_in()) { @header('X-Cache: SKIP:LOGGED_IN'); return; }
	if (wp_doing_ajax()) { @header('X-Cache: SKIP:AJAX'); return; }

	if (!function_exists('atec_wpca_pcache_parse')) @require('atec-cache-apcu-pcache-parse.php');
	global $atec_wpca_pcache_params;
	$atec_wpca_pcache_params = atec_wpca_pcache_parse($wp_query);

	if (is_array($atec_wpca_pcache_params))
	{
		$suffix = $atec_wpca_pcache_params['suffix']??'';
		$id = $atec_wpca_pcache_params['id']??'';
		$hash = $atec_wpca_pcache_params['hash']??'';
		$isFeed = $atec_wpca_pcache_params['feed']??false;
	}
	else { @header('X-Cache: FAIL:'.$atec_wpca_pcache_params); return; }
	
	global $atec_wpca_settings;
	$key='atec_WPCA_'.($atec_wpca_settings['salt']??'').'_'; 
	$arr=apcu_fetch($key.$suffix.'_'.$id);
	@header('X-Cache-ID: '.$suffix.'_'.$id);
	@header('X-Cache-Enabled: true');
	if (($arr[2]??'')==='') { apcu_delete($key.$suffix.'_'.$id); apcu_delete($key.$suffix.'_h_'.$id); $arr=false; }
	if (!empty($arr))
	{	
		if ($arr[0]===$hash)
		{
			if (@ob_get_level() > 0) @ob_end_clean();
			global $atec_wpca_pcache_hit; $atec_wpca_pcache_hit=true;
		    @header('X-Cache-Type: atec APCu v'.esc_attr(wp_cache_get('atec_wpca_version')));
			@header('Content-Type: '.($isFeed?'application/rss+xml':'text/html'));		
			if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && str_contains(sanitize_text_field(wp_unslash($_SERVER['HTTP_ACCEPT_ENCODING'])), 'gzip') && $arr[1])
			{
				// @codingStandardsIgnoreStart
				$zlib='zlib.output_compression';
				if (ini_get($zlib)) ini_set($zlib,'Off');
				@header('Vary: Accept-Encoding');
				@header("Content-Encoding: gzip");
				@header('X-Cache: HIT/GZIP');
				@header('Content-Length: '.$arr[3]);
				echo $arr[2];
				// @codingStandardsIgnoreEnd
			}
			else
			{
				@header('X-Cache: HIT');
				if ($arr[1] && function_exists('gzdecode')) $arr[2] = gzdecode($arr[2]);
				// @codingStandardsIgnoreStart
				@header('Content-Length: '.strlen($arr[2]));
				echo $arr[2];
				// @codingStandardsIgnoreEnd
			}
			apcu_inc($key.$suffix.'_h_'.$id);
			die(200);
		}
	}
}

add_action('init', function() { @require('atec-cache-apcu-pcache-cb.php'); ob_start(function($buffer) { return atec_wpca_page_buffer_callback($buffer); }); }, 0);
add_action('send_headers', 'atec_wpca_page_buffer_start', 0);
?>