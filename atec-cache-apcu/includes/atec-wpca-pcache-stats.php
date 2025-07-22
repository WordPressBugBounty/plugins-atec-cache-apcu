<?php
defined('ABSPATH') || exit;

use ATEC\ALIAS;
use ATEC\INIT;
use ATEC\TOOLS;
use ATEC\WPC;
use ATEC\WPCA;

return function($una) 
{
	$salt= WPCA::settings('salt');
	$is_zlib_enabled = INIT::bool(ini_get('zlib.output_compression'));
	$arr = array('Zlib'=>$is_zlib_enabled, 'PC 🧂'=>$salt);
	TOOLS::little_block_multi($una, __('Cached pages and posts', 'atec-cache-apcu'), [], '', $arr);

	switch ($una->action)
	{
		case 'flush':
			WPC::flushing_start('PC');
			if (!class_exists('ATEC_WPCA\\Tools')) require(__DIR__.'/atec-wpca-pcache-tools.php');
			\ATEC_WPCA\Tools::delete_page_cache_all();
			WPC::flushing_end(true);
			break;
			
		case 'delete':
			if ($una->id !== '')
			{
				$ex=explode('_', $una->id);
				if (isset($ex[1])) \ATEC_WPCA\Tools::delete_page($salt, $ex[0], $ex[1]);
			}
			break;
	}

	$apcu_it = new APCUIterator();
	if (!empty($apcu_it))
	{
		TOOLS::table_header(
			[
			'ID',
			__('Type', 'atec-cache-apcu'),
			__('Key', 'atec-cache-apcu'),
			'<span title="'.__('Page', 'atec-cache-apcu').'" class="'.TOOLS::dash_class('admin-page').'"></span>',
			'<span title="'.__('RSS', 'atec-cache-apcu').'" class="'.TOOLS::dash_class('rss').'"></span>',
			__('Hits', 'atec-cache-apcu'),
			__('Size', 'atec-cache-apcu'),
			__('Title', 'atec-cache-apcu'),
			__('Link', 'atec-cache-apcu'),
			''
			]);
			
			$c=0; $size=0;
			$home_url = INIT::home_url();
			$reg=preg_replace('/\//', '\/',preg_replace('/https?:\/\//', '',$home_url));
			$reg_apcu = '/atec_WPCA_'.$salt.'_([fpcta]+)_([\d|]+)/';
			$site_url= INIT::site_url();
			foreach ($apcu_it as $entry)
			{

				preg_match($reg_apcu, $entry['key'], $match);
				if (isset($match[2]))
				{
					$c++;
					$size 			+= $entry['mem_size'];
					$isCat			= str_contains($match[1], 'c');
					$isTag			= str_contains($match[1], 't');
					$isArchive		= str_contains($match[1], 'a');
					$isFeed			= str_contains($match[1], 'f');
					
					if ($isCat || $isTag || $isArchive) 
					{ 
						$ex = explode('|', $match[2]); 
						$id = (int) $ex[0]; 
						$page = isset($ex[1]) ? (int) $ex[1] : 0;  // Default to page 0 if not paginated
					}
					else { $id = (int) $match[2]; $page=0; }
					
					$type		= $isCat?'category':($isTag?'tag':($isArchive?'archive':get_post_type($id)));
					$title			= $id===0?'Homepage':($isCat?get_cat_name($id):($isTag?get_tag($id)->name:($isArchive?substr($id,0,4).'/'.substr($id,4,2):get_the_title($id))));
					$link			= $id===0?$home_url.'/':($isCat?get_category_link($id):($isTag?get_tag_link($id):($isArchive?$site_url.'/'.substr($id,0,4).'/'.str_pad(substr($id,4,2),2, '0',STR_PAD_LEFT):get_permalink($id))));
					if ($isFeed) $link.= 'feed/';
					if ($page!=0) { $link=((str_contains($link, '?cat=') || str_contains($link, '?tag='))?$link.'&paged= ':rtrim($link, '/').'/page/').$page; }
					
					$short_url 	= preg_replace('/(^https?:\/\/)'.$reg.'/', '', $link);
					echo
					'<tr>';
						ALIAS::td($id);
						ALIAS::td(ucfirst($type));
						ALIAS::td($match[1].'_'.$match[2]);
						ALIAS::td($isCat ? $page : '');
						ALIAS::td(($isFeed ? ' <span class="'.esc_attr(TOOLS::dash_class('yes')).'"></span>' : ''));
						ALIAS::td($entry['num_hits']);
						ALIAS::td(size_format($entry['mem_size']), 'atec-nowrap');
						ALIAS::td($title);
						echo
						'<td><a href="', esc_url($link), '" target="_blank">', esc_url($short_url), '</a></td>';
						TOOLS::dash_button_td($una, 'delete', 'Page_Cache', 'trash', true, $match[1].'_'.$match[2]);
					echo '
					</tr>';
				}
			}
			if ($c>0) ALIAS::tr(['2@', number_format($c), '3@', TOOLS::size_format($size), '3@'], 'td', 'bold');
			else ALIAS::tr(['99@-/-']);
		
		TOOLS::table_footer();
		
		if ($c>0) TOOLS::button($una, 'flush&type=PCache', 'Page_Cache', '#trash '.esc_attr__('Empty page cache', 'atec-cache-apcu'));
	}
	else { TOOLS::msg(false, __('No page cache data available', 'atec-cache-apcu')); }

}
?>