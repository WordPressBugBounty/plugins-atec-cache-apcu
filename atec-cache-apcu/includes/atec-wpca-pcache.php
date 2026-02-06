<?php
namespace ATEC_WPCA;
defined('ABSPATH') || exit;

use ATEC\WPCA;

final class PCache {
	
private static $params, $pcache_hit;

public static function init()
{
	define('ATEC_PC_ACTIVE_APCU', true);

	// ---- ultra-cheap preflight (avoid ob_start + hooks when pointless) ----

	$method = $_SERVER['REQUEST_METHOD'] ?? '';	 // phpcs:ignore
	if ($method !== 'GET' && $method !== 'HEAD') { @header('X-Cache: SKIP/METHOD'); return; }

	foreach ($_COOKIE as $k => $v) { // phpcs:ignore
		if (stripos($k, 'wordpress_logged_in_') === 0) return;
	}

	// Proceed only when preflight passes
	self::$pcache_hit = false;
	add_action('send_headers', [__CLASS__, 'headers']);
	ob_start([__CLASS__, 'callback']);
}

public static function headers()
{
	if (defined('DONOTCACHEPAGE') && DONOTCACHEPAGE) { @header('X-Cache: SKIP:DONOTCACHEPAGE'); return; }
	if (is_404()) { @header('X-Cache: SKIP/404'); return; }
	if (is_user_logged_in()) { @header('X-Cache: SKIP:LOGGED_IN'); return; }

	$uri = $_SERVER['REQUEST_URI'] ?? ''; // phpcs:ignore
	if (str_contains($uri, '/password-reset/') || str_contains($uri, '/login/') || str_contains($uri, '/wp-admin/')) { @header('X-Cache: SKIP/LOGIN'); return; }

	if (str_contains($uri, '?'))
	{
		// extract query part
		$parts = explode('?', $uri, 2);
		$query = $parts[1];
	
		// allow only these keys
		$allowed = array('p', 'page_id');
		parse_str($query, $q);
	
		foreach ($q as $k => $v)
		{
			if (!in_array($k, $allowed, true)) { @header('X-Cache: SKIP/QUERY'); return; }
		}
	}

	global $wp_query;
	if ($wp_query->is_404 || $wp_query->is_search || $wp_query->is_login || $wp_query->is_admin) { @header('X-Cache: SKIP:IS_'); return; }

	$isWooActive = class_exists('WooCommerce');
	if ($isWooActive)
	{
		if (is_cart() || is_checkout() || is_account_page() || is_woocommerce()) { @header('X-Cache: SKIP:WOO'); return; }
		if (!empty($_COOKIE))
		{
			foreach ($_COOKIE as $ck => $cv)
			{
				if (strpos($ck, 'wp_woocommerce_session_') === 0) { @header('X-Cache: SKIP:WOO'); return; }
			}
		}
	}

	self::$params = self::parse();
	
	if (is_array(self::$params))
	{
		$suffix	= self::$params['suffix']??'';
		$id		= self::$params['id']??'';
		$hash	= self::$params['hash']??'';
		$isFeed	= self::$params['feed'] ?? false;
	}
	else { @header('X-Cache: FAIL:'.self::$params); return; }
	
	$key= 'atec_WPCA_'.WPCA::settings('salt').'_';
	$arr = apcu_fetch($key.$suffix.'_'.$id);
	
	@header('X-Cache-ID: '.$suffix.'_'.$id);
	@header('X-Cache-Enabled: true');
	@header('Vary: Accept-Encoding');

	if (($arr[2]??'')=== '') 
	{ 
		apcu_delete($key.$suffix.'_'.$id); 
		$arr = false; 
	}
	if (!empty($arr))
	{
		if ($arr[0] === $hash)
		{
			if (ob_get_level() > 0) ob_end_clean();
			self::$pcache_hit = true;
			@header('Content-Type: '.($isFeed?'application/rss+xml' : 'text/html'));
			@header('Cache-Control: public, no-transform');
			@header('X-Cache-Type: atec APCu v'.esc_attr(wp_cache_get('atec_wpca_version', 'atec_np')));
			if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && str_contains(sanitize_text_field(wp_unslash($_SERVER['HTTP_ACCEPT_ENCODING'])), 'gzip') && $arr[1])
			{
				$zlib = 'zlib.output_compression';
				$is_zlib_enabled = filter_var(ini_get($zlib), FILTER_VALIDATE_BOOLEAN);
				if ($is_zlib_enabled) ini_set($zlib, 'Off');	// phpcs:ignore
				@header("Content-Encoding: gzip");
				@header('X-Cache: HIT/GZIP');
				echo $arr[2];		// phpcs:ignore
			}
			else
			{
				@header('X-Cache: HIT');
				if ($arr[1] && function_exists('gzdecode')) $arr[2] = gzdecode($arr[2]);
				echo $arr[2];		// phpcs:ignore
			}
			exit;
		}
	}		
}

public static function callback($buffer)
{
	if (strlen($buffer)<1024) return $buffer;
	if (defined('DONOTCACHEPAGE') && DONOTCACHEPAGE) return $buffer;
		
	if (self::$pcache_hit) return $buffer;
		
	$headers = headers_list();
	if (is_array($headers))
	{
		foreach ($headers as $h)
		{
			if (stripos($h, 'Set-Cookie: wp_woocommerce_session_') === 0
			 || stripos($h, 'Set-Cookie: woocommerce_items_in_cart') === 0
			 || stripos($h, 'Set-Cookie: woocommerce_cart_hash') === 0)
			{
				// WP/Woo is creating/updating Woo state on THIS response.
				// Do NOT store this HTML in APCu, because we can't replay the cookie.
				return $buffer;
			}
		}
	}
	
	if (is_null(self::$params)) self::$params = self::parse();
	if (is_array(self::$params))
	{
		$suffix	= self::$params['suffix']??'';
		$id		= self::$params['id']??'';
		$hash	= self::$params['hash']??'';
	}
	else return $buffer;
	
	$gzip				= false; 
	$compressed	= ''; 
	$key					= 'atec_WPCA_'.WPCA::settings('salt').'_';
	
	$p_debug = WPCA::settings('p_debug');
	$pos = strripos($buffer, '</body');
	if ($pos !== false)
	{
		if ($p_debug && !str_contains($suffix, 'f'))
		{
			$debug= '
				<script id="atec_wpca_debug_script">
				console.log(\'APCu Cache: HIT '.get_locale().' | '.strtoupper($suffix).' | '.$id.'\');
				var elemDiv = document.createElement("div");
				elemDiv.innerHTML="🟢";
				elemDiv.id="atec_wpca_debug";
				elemDiv.style.cssText = "position:absolute;top:3px;width:8px;height:8px;font-size:8px;left:3px;z-index:99999;";
				document.body.appendChild(elemDiv);
				setTimeout(()=>{ const elem=document.getElementById("atec_wpca_debug"); if (elem) elem.remove(); }, 3000);
				const elem=document.getElementById("atec_wpca_debug_script"); if (elem) elem.remove();
			</script>';
		}
		else 	$debug	 = ''; 

		$powered = '<a href="https://atecplugins.com/" style="position:absolute; top:-9999px; left:-9999px; width:1px; height:1px; overflow:hidden; text-indent:-9999px;">Powered by atecplugins.com</a>';
	
		$buffer = substr($buffer, 0, $pos) . $debug.$powered . substr($buffer, $pos);
	}

	if (function_exists('gzencode')) { $compressed = gzencode($buffer); $gzip=true; }
	apcu_store($key.$suffix.'_'.$id,array($hash, $gzip, $gzip?$compressed:$buffer));
	unset($compressed);
	if ($p_debug) 
	{
		$hide = '<style id="atec-wpca-hide-debug">#atec_wpca_debug{display:none!important}</style>';
		$pos = strripos($buffer, '</body>');
		if ($pos !== false) $buffer = substr($buffer, 0, $pos) . $hide . substr($buffer, $pos);
	}
	return $buffer;
}

public static function parse()
{
	global $wp_query;
	if (empty($wp_query)) return 'NO_QUERY';

	$hash = '';
	$suffix = '';
	$id = '';

	$isArchive = !empty($wp_query->is_archive);
	$isFeed = !empty($wp_query->is_feed);

	if ($isArchive)
	{
		// Cheap archive hash: concat post IDs from main query
		if (!empty($wp_query->posts))
		{
			foreach ($wp_query->posts as $p)
			{
				if (isset($p->ID)) { $hash .= $p->ID . ' '; }
			}
			$hash = rtrim($hash);
		}

		if (!empty($wp_query->is_category))
		{
			$id = (string)($wp_query->query_vars['cat'] ?? '');
			if ($id === '') return 'CAT_EMPTY';
			$id .= '|' . (int)($wp_query->query_vars['paged'] ?? 0);
			$suffix = 'c';
		}
		elseif (!empty($wp_query->is_tag))
		{
			$id = (string)($wp_query->query_vars['tag_id'] ?? '');
			if ($id === '') return 'TAG_EMPTY';
			$id .= '|' . (int)($wp_query->query_vars['paged'] ?? 0);
			$suffix = 't';
		}
		else
		{
			// Date/generic archive (still cheap: just query_vars)
			$year  = (string)($wp_query->query_vars['year'] ?? '');
			$month = (string)($wp_query->query_vars['monthnum'] ?? '');
			if ($year === '' && $month === '') return 'ARCH_EMPTY';
			$id = $year . $month . '|' . (int)($wp_query->query_vars['paged'] ?? 0);
			$suffix = 'a';
		}
	}
	else
	{
		if (!empty($wp_query->is_home))
		{
			// Blog index like archive
			$id = '0';
			$suffix = 'a';

			if (!empty($wp_query->posts))
			{
				foreach ($wp_query->posts as $p)
				{
					if (isset($p->ID)) { $hash .= $p->ID . ' '; }
				}
				$hash = rtrim($hash);
			}
		}
		else
		{
			// Singular post/page — use only wp_query props, no helpers
			$post = $wp_query->post ?? ($wp_query->queried_object ?? null);

			// If still missing, try cheap IDs from query_vars (no DB call)
			if (!$post && !empty($wp_query->query_vars))
			{
				$maybe_id = (int)($wp_query->query_vars['p'] ?? $wp_query->query_vars['page_id'] ?? 0);
				if ($maybe_id > 0)
				{
					// Create a tiny stub so we don't call WP functions
					$post = (object)[
						'ID' => $maybe_id,
						'post_type' => ($wp_query->query_vars['post_type'] ?? 'post'),
						'post_modified_gmt' => '',
						'post_modified' => '',
					];
				}
			}

			if (!$post || empty($post->post_type) || !in_array($post->post_type, ['post','page'], true))
			{
				return 'INVALID_TYPE';
			}

			$id = (string)$post->ID;
			$suffix = 'p';

			// Prefer GMT for consistency; stay cheap (no extra lookups)
			$hash = $post->post_modified_gmt ?? '';
			if ($hash === '') $hash = $post->post_modified ?? '';
			if ($hash === '') return 'NO_TIME';
		}
	}

	if ($isFeed) $suffix .= 'f';

	// Note: headers() expects 'feed' key, not 'isfeed'
	return ['suffix' => $suffix, 'id' => $id, 'hash' => $hash, 'feed' => $isFeed];
}

}
?>