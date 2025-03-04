<?php
if (!defined('ABSPATH')) { exit(); }

class ATEC_apcu_wpc_groups { 
	
private function atec_wpcu_parse_alloptions($v1,$v2)
{
	$arr = [];
	foreach($v1 as $k=>$v) { if (!is_null($v) && gettype($v)==='array') $v1[$k]=serialize($v); }
	foreach($v2 as $k=>$v) { if (!is_null($v) && gettype($v)==='array') $v2[$k]=serialize($v); }
	foreach($v1 as $k=>$v)
	{
		if (isset($v2[$k])) { if ($v!==$v2[$k]) $arr[]='Key: '.$k.' '.$v.' != '.$v2[$k]; }
		else $arr[]='Key: '.$k.' n/a';
	}
	return $arr;
}

function __construct($url, $nonce, $action, $prefix, $wpc_tools) {
	
if ($action==='delete') 
{ 
	$id = atec_clean_request('id'); 
	if (wp_cache_wpc_delete(WP_APCU_KEY_SALT.':'.$id)) atec_success_msg('Cache item removed'); 
}

echo '
<div class="atec-g"><div>';

		atec_little_block('WP '.__('Object Cache','atec-cache-apcu'));
		atec_warning_msg('This page is for „PRO“ users debugging only');
		
		$wpc_arr = wp_cache_wpc_array();
		if (!empty($wpc_arr))
		{	
			atec_table_header_tiny(['#',__('Key','atec-cache-apcu'),__('Value','atec-cache-apcu'),'APCu?','==','']);

			$c = 0; $total = 0; $allArr = [];
			$search = WP_APCU_KEY_SALT.':';
			ksort($wpc_arr);
			foreach($wpc_arr as $key=>$value) 
			{
				$c++;
				$apcu = apcu_fetch($key);
				$stripped = str_replace($search,'',$key);				
				$a1 = maybe_serialize($value);
				if ($apcu) 
				{
					$a2 = maybe_serialize($apcu);
					$eq = $a1===$a2;
					$i2	= $eq?'yes-alt':'dismiss';
					$c2	= 'atec-'.($eq?'green':'red');
					if (str_contains($stripped,'alloptions') && !$eq) $allArr=$this->atec_wpcu_parse_alloptions($value,$apcu);
				}
				$i1	= $key?'yes-alt':'dismiss';
				$c1	= 'atec-'.($key?'green':'red');

				echo 
				'<tr>
					<td>', esc_attr($c), '</td>
					<td class="atec-anywrap">', esc_attr(substr($stripped,0,64)), '</td>
					<td', !$eq?' title="'.esc_html($a1).'&#013;&#013;'.esc_html($a2).'"':'' ,' class="atec-anywrap">', esc_html(substr($a1,0,64)), '</td>
					<td><span class="', esc_attr(atec_dash_class($i1,$c1)), '"></span></td>
					<td><span class="', ($apcu?esc_attr(atec_dash_class($i2,$c2)):''), '"></span></td>';					
					atec_create_button('delete&nav=WP_Cache_Debug','trash',true,$url,$stripped,$nonce);
				echo '
				</tr>';
			}
			atec_table_footer();
			
			if (!empty($allArr))
			{
				echo
				'<h4>„alloptions“</h4><div class="atec-box-white atec-fit atec-small atec-anywrap">';
				foreach($allArr as $v) echo $v.'<br>';
				echo
				'</div>';
			}
		}
		else atec_error_msg(__('WP APCu Cache is empty','atec-cache-apcu'));
	
echo '
</div></div>';

}}

?>