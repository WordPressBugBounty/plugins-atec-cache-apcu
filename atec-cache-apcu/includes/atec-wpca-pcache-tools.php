<?php
namespace ATEC_WPCA;
defined('ABSPATH') || exit;
use ATEC\WPCA;

final class Tools {
	
public static function on_category_change($term_id): void
{
	self::delete_page_cache('c|cf');

	$posts = get_posts(['fields' => 'ids', 'post_status' => 'publish', 'numberposts' => -1, 'category' => $term_id,]);
	foreach ($posts as $post_id) self::delete_page('', 'p', $post_id);
}

public static function on_category_edit($term_id, $tt_id): void
{
	self::on_category_change($term_id);
}

public static function on_tag_change($term_id, $tt_id, $taxonomy): void
{
	if ($taxonomy !== 'post_tag') return;
	self::delete_page_cache('t|tf');
	$posts = get_posts([
		'fields' => 'ids', 'post_status' => 'publish', 'numberposts' => -1, 
		// phpcs:ignore
		'tax_query' => [[ 'taxonomy' => 'post_tag',	'field' => 'term_id', 'terms' => [$term_id], ]],
		]);
	foreach ($posts as $post_id) self::delete_page('', 'p', $post_id);
}

public static function on_tag_edit($term_id, $taxonomy, $args): void
{
	if ($taxonomy !== 'post_tag') return;
	self::on_tag_change($term_id, 0, $taxonomy);
}

public static function delete_page($salt='', $suffix='', $id=0): void
{
	if ($salt==='') $salt = WPCA::settings('salt');
	apcu_delete('atec_WPCA_'.$salt.'_'.$suffix.'_'.$id);
}

public static function delete_page_cache($reg=''): void
{
	if ($reg==='') return;

	$salt			= WPCA::settings('salt');
	$pattern	= '/^atec_WPCA_' . $salt . '_(' . $reg . ')_(\d+(?:\|\d+)?)$/';
	$apcu_it	= new \APCUIterator('/^atec_WPCA_' . $salt . '_/');

	foreach ($apcu_it as $entry) 
	{
		if (preg_match($pattern, $entry['key'], $match)) self::delete_page($salt, $match[1], $match[2]);
	}
}

public static function delete_page_cache_all(): void
{
	$salt			= WPCA::settings('salt');
	$pattern	= '/^atec_WPCA_'.$salt.'_/';
	$apcu_it	= new \APCUIterator($pattern);
	foreach ($apcu_it as $entry) apcu_delete($entry['key']);
}

}
?>