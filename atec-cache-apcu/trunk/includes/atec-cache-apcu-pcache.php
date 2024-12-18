<?php
if (!defined( 'ABSPATH' )) { exit; }

function atec_wpca_page_buffer_start(): void
{ 	 
	// @codingStandardsIgnoreStart
	/* $_POST and $_SERVER is uncritical as it is only used for comparison */
	if (($_SERVER['REQUEST_METHOD']??'')!=='GET') { @header('X-Cache: SKIP:GET'); return; }
	// @codingStandardsIgnoreEnd

	$args = add_query_arg(null,null);
	if (str_contains($args,'/password-reset/') || str_contains($args,'/login/') || str_contains($args,'/wp-admin/')) { @header('X-Cache: SKIP/LOGIN'); return	; }
	
	global $wp_query;
	if ($wp_query->is_404 || $wp_query->is_search || $wp_query->is_login || $wp_query->is_admin) { @header('X-Cache: SKIP:IS_'); return; }
	
	if (class_exists('woocommerce' ) && (is_cart() || is_checkout() || is_account_page() || is_woocommerce())) { @header('X-Cache: SKIP:WOO'); return; }
	if (is_user_logged_in()) { @header('X-Cache: SKIP:LOGGED_IN'); return; }
	if (wp_doing_ajax()) { @header('X-Cache: SKIP:AJAX'); return; }

	$isCat=$wp_query->is_category;
	$isTag=$wp_query->is_tag;
	$isArchive=$wp_query->is_archive;

	$hash = '';
	$suffix = '';
	if ($isCat || $isTag || $isArchive) 
	{
		$posts=$wp_query->posts;
		foreach ($posts as $value) $hash.=$value->ID.' ';
		$hash=rtrim($hash);
		
		if ($isCat)
		{
			$id=$wp_query->query_vars['cat']??'';
			if (empty($id)) { @header('X-Cache: FAIL:CAT_EMPTY'); return; }
			$id.='|'.$wp_query->query_vars['paged'];
			$suffix='c';
		}
		elseif ($isTag)
		{
			$id=$wp_query->query_vars['tag_id']??'';
			if (empty($id)) { @header('X-Cache: FAIL:TAG_EMPTY'); return; }
			$id.='|'.$wp_query->query_vars['paged'];
			$suffix='t';
		}
		elseif ($isArchive)
		{
			$id=($wp_query->query_vars['year']??'').($wp_query->query_vars['monthnum']??'');
			if (empty($id)) { @header('X-Cache: FAIL:ARCH_EMPTY'); return; }
			$id.='|'.$wp_query->query_vars['paged'];
			$suffix='a';
		}
	}
	else
	{
		$isPP=$wp_query->is_page || $wp_query->is_single;
		if (!$isPP) { @header('X-Cache: FAIL:INVALID_TYPE'); return; }
		$id = $wp_query->post->ID;
		$hash = $wp_query->post->post_modified;
		if (empty($hash)) { @header('X-Cache: FAIL:NO_TIME'); return; }
		$suffix	= 'p';
	}
	
	$isFeed=$wp_query->is_feed;
	if ($isFeed) $suffix.='f';
	global $atec_wpca_settings;
	$key='atec_WPCA_'.($atec_wpca_settings['salt']??'').'_'; 
	$arr=apcu_fetch($key.$suffix.'_'.$id);
	@header('X-Cache-ID: '.$suffix.'_'.$id);
	@header('X-Cache-Enabled: true');
	if (($arr[2]??'')==='') { apcu_delete($key.$suffix.'_'.$id); apcu_delete($key.$suffix.'_h_'.$id); $arr=false; }
	if ($arr!==false)
	{	
		if ($arr[0]===$hash)
		 {
		    @header('X-Cache-Type: atec APCu v'.esc_attr(wp_cache_get('atec_wpca_version')));
			@header('Content-Type: '.($isFeed?'application/rss+xml':'text/html'));
			if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && str_contains(sanitize_text_field(wp_unslash($_SERVER['HTTP_ACCEPT_ENCODING'])), 'gzip') && $arr[1])
			{
				// @codingStandardsIgnoreStart
				$zlib='zlib.output_compression';
				if (ini_get($zlib)) ini_set($zlib,'Off');
				// @codingStandardsIgnoreEnd
				header('Vary: Accept-Encoding');
				header("Content-Encoding: gzip");
				@header('X-Cache: HIT/GZIP');
				// @codingStandardsIgnoreStart
				echo $arr[2];
				// @codingStandardsIgnoreEnd
			}
			else
			{
				@header('X-Cache: HIT');
				if ($arr[1] && function_exists('gzdecode')) $arr[2] = gzdecode($arr[2]);
				// @codingStandardsIgnoreStart
				echo $arr[2];
				// @codingStandardsIgnoreEnd
			}
			apcu_inc($key.$suffix.'_h_'.$id);
			exit(200);
		}
	}
	else 
	{
		@require_once(WP_CONTENT_DIR.'/plugins/atec-cache-apcu/includes/atec-cache-apcu-pcache-cb.php');
		ob_start(function($buffer) use ($id, $hash, $suffix) { return atec_wpca_page_buffer_callback($buffer, $suffix, $id, $hash); });
	}
 }

add_action('wp', 'atec_wpca_page_buffer_start',-100);
?>