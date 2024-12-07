<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_wpcu_pcache { function __construct($url, $nonce, $action,$wpc_tools) {

global $atec_wpca_apcu_enabled;	
global $atec_wpca_settings;
$salt=$atec_wpca_settings['salt']??'';

$arr=array('Zlib'=>(@ini_get('zlib.output_compression')??'Off'?'On':'Off'), 'PCache salt'=>$salt);
atec_little_block_with_info(__('Cached pages and posts','atec-cache-apcu'),$arr);

echo '
<div class="atec-g">
<div>';

if ($atec_wpca_apcu_enabled)
{    
	if ($action==='delete')
	{
		$id=atec_clean_request('id');
		if ($id!=='') 
		{
			$ex=explode('_',$id);
			if (isset($ex[2])) atec_wpca_delete_page($ex[0].'_'.$ex[1], $ex[2]);
		}
	}
	
	$apcu_cache=apcu_cache_info(true);
	if ($apcu_cache)
	{
    	$apcu_it=new APCUIterator();
    	if (!empty($apcu_it))
    	{
	    	echo '
	    	<table class="atec-table atec-table-tiny atec-table-td-first">
		    	<thead>
		    	<tr>
					<th>'.esc_attr__('Type','atec-cache-apcu').'</th>
					<th>Key</th>
					<th>ID</th>
					<th><span title="'.esc_attr__('Page Nr.','atec-cache-apcu').'" class="'.esc_attr(atec_dash_class('admin-page')).'"></span></th>
					<th><span title="'.esc_attr__('RSS feed','atec-cache-apcu').'" class="'.esc_attr(atec_dash_class('rss')).'"></span></th>
					<th>'.esc_attr__('Hits','atec-cache-apcu').'</th>
					<th>'.esc_attr__('Size','atec-cache-apcu').'</th>
					<th>'.esc_attr__('Title','atec-cache-apcu').'</th>
					<th>Link</th>
					<th></th>
				</tr>
				</thead>
				<tbody>';					    
		    		$c=0; $size=0;
		    		$reg=preg_replace('/\//','\/',preg_replace('/https?:\/\//','',get_home_url()));
					$reg_apcu = '/atec_WPCA_'.$salt.'_([f|p|c|t|a]+)_([\d|\|]+)/';
					$siteUlr=get_site_url();
		    		foreach ($apcu_it as $entry) 
		    		{							
						preg_match($reg_apcu, $entry['key'], $match);
			    		if (isset($match[2]))
			    		{
							$c++; 
							$size 			+= $entry['mem_size']; 
							$isCat			= str_contains($match[1],'c');
							$isTag			= str_contains($match[1],'t');
							$isArchive		= str_contains($match[1],'a');
							$isFeed			= str_contains($match[1],'f');						
							if ($isCat || $isTag || $isArchive) { $ex = explode('|',$match[2]); $id = (int) $ex[0]; $page = $ex[1]; }
							else { $id = (int) $match[2]; $page=0; }
							$type		= $isCat?'category':($isTag?'tag':($isArchive?'archive':get_post_type($id)));
							$title			= $isCat?get_cat_name($id):($isTag?get_tag($id)->name:($isArchive?substr($id,0,4).'/'.substr($id,4,2):get_the_title($id)));
							$link			= $isCat?get_category_link($id):($isTag?get_tag_link($id):($isArchive?$siteUlr.'/'.substr($id,0,4).'/'.str_pad(substr($id,4,2),2,'0',STR_PAD_LEFT):get_permalink($id)));
							if ($isFeed) $link.='feed/';
							if ($page!=0) { $link=((str_contains($link, '?cat=') || str_contains($link, '?tag='))?$link.'&paged=':rtrim($link,'/').'/page/').$page; }
							$short_url 	= preg_replace('/(^https?:\/\/)'.$reg.'/', '', $link);
							echo '
							<tr>
								<td>', esc_attr(ucfirst($type)), '</td>
								<td>', esc_attr($match[1].'_'.$match[2]), '</td>						
								<td>', esc_attr($id), '</td>
								<td>', esc_attr($isCat?$page:''), '</td>
								<td>', ($isFeed?' <span class="'.esc_attr(atec_dash_class('yes')).'"></span>':''), '</td>
								<td>', esc_attr(apcu_fetch('atec_WPCA_'.$salt.'_'.$match[1].'_h_'.$match[2])), '</td>
								<td class="atec-nowrap">', esc_attr(size_format($entry['mem_size'])), '</td>
								<td>', esc_html($title), '</td>
								<td><a href="', esc_url($link), '" target="_blank">', esc_url($short_url), '</a></td>';
								atec_create_button('delete&nav=Page_cache','trash',true,$url,$salt.'_'.$match[1].'_'.$match[2],$nonce);
							echo '
							</tr>';						    
			    		}
		    		}
            	if ($c>0) echo '<tr class="atec-table-tr-bold"><td colspan="2"></td><td>',esc_attr(number_format($c)),'</td><td></td><td></td><td></td><td class="atec-nowrap">',esc_attr(size_format($size)),'</td><td colspan="3"></td></tr>';
				else echo '<tr><td colspan="10">./.</td></tr>';
				echo '
	    		</tbody>
	    	</table>';
	    	if ($c>0)
	    	{
			$link=$url.'&flush=APCu_PCache&nav=Cache&_wpnonce='.esc_attr(wp_create_nonce(atec_nonce()));
			echo '<a class="atec-clear button" href="', esc_url($link), '" title="', esc_attr__('Empty PCache','atec-cache-apcu'), '"><span style="margin-top: 2px;" class="', esc_attr(atec_dash_class('trash')), '"></span> ', esc_attr__('Empty page cache','atec-cache-apcu'), '</a>';
	    	}
    	}
    	else { $wpc_tools->error('',esc_attr__('No page cache data available','atec-cache-apcu')); }
	}
}

echo '
</div></div>';

}}

?>