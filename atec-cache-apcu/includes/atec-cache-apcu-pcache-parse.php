<?php
if (!defined('ABSPATH')) { exit(); }

function atec_wpca_pcache_parse($wp_query)
{ 	 
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
			if (empty($id)) return 'CAT_EMPTY';
			$id.='|'.$wp_query->query_vars['paged'];
			$suffix='c';
		}
		elseif ($isTag)
		{
			$id=$wp_query->query_vars['tag_id']??'';
			if (empty($id)) return 'TAG_EMPTY';
			$id.='|'.$wp_query->query_vars['paged'];
			$suffix='t';
		}
		elseif ($isArchive)
		{
			$id=($wp_query->query_vars['year']??'').($wp_query->query_vars['monthnum']??'');
			if (empty($id)) return 'ARCH_EMPTY';
			$id.='|'.$wp_query->query_vars['paged'];
			$suffix='a';
		}
	}
	else
	{
		$isPP=$wp_query->is_page || $wp_query->is_single;
		if (!$isPP) return 'INVALID_TYPE';
		$id = $wp_query->post->ID;
		$hash = $wp_query->post->post_modified;
		if (empty($hash)) return 'NO_TIME';
		$suffix	= 'p';
	}
	
	$isFeed=$wp_query->is_feed;
	if ($isFeed) $suffix.='f';

	return ['suffix'=>$suffix, 'id'=>$id, 'hash'=>$hash, 'isfeed'=>$isFeed];
 }
?>