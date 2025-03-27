<?php
if (!defined('ABSPATH')) { exit; }

function atec_wpca_page_buffer_callback($buffer)
{
	if (($bufferLen = strlen($buffer))<1024) return $buffer;

	global $atec_wpca_pcache_hit; 
	if ($atec_wpca_pcache_hit??false) return $buffer;
	
	global $wp_query, $atec_wpca_pcache_params;
	if (is_null($atec_wpca_pcache_params))
	{
		require('atec-cache-apcu-pcache-parse.php');
		$atec_wpca_pcache_params = atec_wpca_pcache_parse($wp_query);
	}
	
	if (is_array($atec_wpca_pcache_params))
	{
		$suffix = $atec_wpca_pcache_params['suffix']??'';
		$id = $atec_wpca_pcache_params['id']??'';
		$hash = $atec_wpca_pcache_params['hash']??'';
	}
	else return $buffer;
	
    $gzip=false; $compressed=''; $debug=''; $debugLen=0;
	global $atec_wpca_settings;
	$key='atec_WPCA_'.($atec_wpca_settings['salt']??'').'_';
	if (($atec_wpca_settings['debug']??null)==true && !str_contains($suffix,'f'))
	{
		$debug='	
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
		$debugLen=strlen($debug);
	}
	$powered='<a style="font-size:0; margin:0; color:transparent;" href="https://atecplugins.com/">Powered by atecplugins.com</a>';
	if (function_exists('gzencode')) { $compressed = gzencode($buffer.$debug.$powered); $gzip=true; }
	apcu_store($key.$suffix.'_'.$id,array($hash,$gzip,$gzip?$compressed:$buffer.$debug.$powered,$gzip?strlen($compressed):$bufferLen+$debugLen+115));
	apcu_store($key.$suffix.'_h_'.$id,0);
	unset($compressed); unset($content);
	if (!empty($_COOKIE)) unset($_COOKIE);
	return $buffer;
}
?>