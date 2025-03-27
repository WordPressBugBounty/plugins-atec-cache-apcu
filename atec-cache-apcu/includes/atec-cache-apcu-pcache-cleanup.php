<?php
if (!defined('ABSPATH')) { exit; }

function atec_wpca_pcache_delete_comment($comment)
{
	global $atec_wpca_settings;
	$salt=$atec_wpca_settings['salt']??'';
	if (!function_exists('atec_wpca_delete_page_cache_all')) require(__DIR__.'/atec-cache-apcu-pcache-tools.php');			
	atec_wpca_delete_page($salt.'_p', $comment->comment_post_ID);
}

function atec_wpca_pcache_clean_on_comment($comment_ID)
{
	$comment = get_comment( $comment_ID );
	if ($comment && $comment->comment_approved==1) { atec_wpca_pcache_delete_comment($comment); }
}
add_action('comment_post', 'atec_wpca_pcache_clean_on_comment');

function atec_wpca_pcache_clean_on_comment_status($new_status, $old_status, $comment) 
{ if (in_array($new_status, ['trash','approved'])) atec_wpca_pcache_delete_comment($comment); }
add_action('transition_comment_status', 'atec_wpca_pcache_clean_on_comment_status', 10, 3);
?>