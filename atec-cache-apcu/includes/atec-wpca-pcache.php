<?php
namespace ATEC_WPCA;
defined('ABSPATH') || exit;

use ATEC\WPCA;

final class PCache {
	
private static $params, $pcache_hit;

public static function init()
{
	define('ATEC_PC_ACTIVE_APCU', true);
	self::$pcache_hit = false;
	add_action('send_headers', [__CLASS__, 'headers']);
	ob_start([__CLASS__, 'callback']);
}

public static function headers()
{
	if (($_SERVER['REQUEST_METHOD']??'')!== 'GET') { @header('X-Cache: SKIP:GET'); return; }	// phpcs:ignore
	
	$args = add_query_arg(null, null);
	if (str_contains($args, '/password-reset/') || str_contains($args, '/login/') || str_contains($args, '/wp-admin/')) { @header('X-Cache: SKIP/LOGIN'); return; }
	
	if (str_contains($args, '/?') && !(str_contains($args, '/?p=') || str_contains($args, '/?page_id='))) { @header('X-Cache: SKIP/QUERY'); return; }
	
	global $wp_query;
	if ($wp_query->is_404 || $wp_query->is_search || $wp_query->is_login || $wp_query->is_admin) { @header('X-Cache: SKIP:IS_'); return; }
	
	if (class_exists('WooCommerce') && (is_cart() || is_checkout() || is_account_page() || is_woocommerce())) { @header('X-Cache: SKIP:WOO'); return; }
	if (is_user_logged_in()) { @header('X-Cache: SKIP:LOGGED_IN'); return; }
	
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
			@header('X-Cache-Type: atec APCu v'.esc_attr(wp_cache_get('atec_wpca_version')));
			if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && str_contains(sanitize_text_field(wp_unslash($_SERVER['HTTP_ACCEPT_ENCODING'])), 'gzip') && $arr[1])
			{
				$zlib = 'zlib.output_compression';
				$is_zlib_enabled = filter_var(ini_get($zlib), FILTER_VALIDATE_BOOLEAN);
				if ($is_zlib_enabled) ini_set($zlib, 'Off');	// phpcs:ignore
				@header('Vary: Accept-Encoding');
				@header("Content-Encoding: gzip");
				@header('X-Cache: HIT/GZIP');
				//@header('Content-Length: '.$arr[3]);
				echo $arr[2];									// phpcs:ignore
			}
			else
			{
				@header('X-Cache: HIT');
				if ($arr[1] && function_exists('gzdecode')) $arr[2] = gzdecode($arr[2]);
				//@header('Content-Length: '.strlen($arr[2]));
				echo $arr[2];									// phpcs:ignore
			}
			exit;
		}
	}		
}

public static function callback($buffer)
{
	//(($bufferLen = 
	if (strlen($buffer)<1024) return $buffer;
	if (defined('DONOTCACHEPAGE') && DONOTCACHEPAGE) return $buffer; // Skip cache output

	if (self::$pcache_hit) return $buffer;
	
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
	$debug				= ''; 
	//$debug_len		= 0;
	$key					= 'atec_WPCA_'.WPCA::settings('salt').'_';
	
	if (WPCA::settings('p_debug') && !str_contains($suffix, 'f'))
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
		//$debug_len= strlen($debug);
	}
	
	$powered = '<a href="https://atecplugins.com/" style="position:absolute; top:-9999px; left:-9999px; width:1px; height:1px; overflow:hidden; text-indent:-9999px;">Powered by atecplugins.com</a>';
	//$powered_len = strlen($powered);

	if (function_exists('gzencode')) { $compressed = gzencode($buffer.$debug.$powered); $gzip=true; }
	apcu_store($key.$suffix.'_'.$id,array($hash, $gzip, $gzip?$compressed:$buffer.$debug.$powered));	//$gzip?strlen($compressed):$bufferLen+$debug_len+$powered_len
	unset($compressed); 
	unset($content);
	//if (!empty($_COOKIE)) unset($_COOKIE);
	return $buffer;
}

public static function parse()
{
	global $wp_query;

	$hash	= '';
	$suffix	= '';
	
	$isArchive= $wp_query->is_archive;
	if ($isArchive)
	{
		$isCat	= $wp_query->is_category;
		$isTag	= $wp_query->is_tag;
		$posts	= $wp_query->posts;
	
		foreach ($posts as $value) $hash.= $value->ID.' ';
	
		$hash = rtrim($hash);
	
		if ($isCat)
		{
			$id = $wp_query->query_vars['cat']??'';
			if (empty($id)) return 'CAT_EMPTY';
			$id.= '|'.$wp_query->query_vars['paged'];
			$suffix = 'c';
		}
		elseif ($isTag)
		{
			$id = $wp_query->query_vars['tag_id']??'';
			if (empty($id)) return 'TAG_EMPTY';
			$id.= '|'.$wp_query->query_vars['paged'];
			$suffix = 't';
		}
		elseif ($isArchive)
		{
			$id = ($wp_query->query_vars['year']??'').($wp_query->query_vars['monthnum']??'');
			if (empty($id)) return 'ARCH_EMPTY';
			$id.= '|'.$wp_query->query_vars['paged'];
			$suffix = 'a';
		}
	}
	else
	{
		if (is_home()) { $id = 0; $suffix= 'a'; }
		else
		{
			$isPP = in_array(($wp_query->post->post_type ?? ''),['page', 'post']);
			if (!$isPP) return 'INVALID_TYPE';
			$id = $wp_query->post->ID;
			$suffix = 'p';
		}
		$hash = $wp_query->post->post_modified ?? '';
		if (empty($hash)) return 'NO_TIME';
	}
	
	$isFeed= $wp_query->is_feed;
	if ($isFeed) $suffix.= 'f';
	
	return ['suffix'=>$suffix, 'id'=>$id, 'hash'=>$hash, 'isfeed'=>$isFeed];
}

}
?>