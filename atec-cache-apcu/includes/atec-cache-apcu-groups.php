<?php
if (!defined('ABSPATH')) { exit; }

class ATEC_apcu_groups { 

function __construct($url, $nonce, $action, $prefix) {

$salt=get_option('atec_WPCA_settings',[])['salt']??'';
$arr=array('PC salt'=>$salt);

$wp_apcu_key_salt_exists = defined('WP_APCU_KEY_SALT');
if ($wp_apcu_key_salt_exists) $arr['APCU salt (*)']=WP_APCU_KEY_SALT;

if ($action==='deleteAll')
{
	echo '
	<div class="notice is-dismissible atec-mb-10">
		<p>', esc_attr__('Flushing','atec-cache-apcu'), ' APCu ', esc_attr__('Cache','atec-cache-apcu') ,' ... ';

		if ($wp_apcu_key_salt_exists && class_exists('APCUIterator')) 
		{
			$apcu_it=new APCUIterator('/'.WP_APCU_KEY_SALT.'/');
			if (iterator_count($apcu_it)!==0)
			{ foreach ($apcu_it as $entry) { apcu_delete($entry['key']); } }
		}
	
		echo '<span class="atec-green">', esc_attr__('successful','atec-cache-apcu'), '</span>.
		</p>
	</div>';
}
elseif ($action==='delete')
{
	if ($wp_apcu_key_salt_exists && ($id = atec_clean_request('id'))!=='' && ($salt = WP_APCU_KEY_SALT??'')!=='') apcu_delete($salt.':'.$id);
}

echo '
<div class="atec-g"><div>';

	if (class_exists('APCUIterator'))
	{
		$apcu_it=new APCUIterator();
		$arr = iterator_to_array($apcu_it);
		array_multisort(array_column($arr, 'key'), SORT_ASC,$arr);

		if ($wp_apcu_key_salt_exists) 
		{
			atec_little_block_with_button(__('Persistent','atec-cache-apcu').' '.__('Object Cache','atec-cache-apcu'),$url,$nonce,'deleteAll','APCu','',false,true,false);
			if (!empty($arr))
			{
				$search = WP_APCU_KEY_SALT.':';
				atec_table_header_tiny(['#',__('Key','atec-cache-apcu'),__('Hits','atec-cache-apcu'),__('Size','atec-cache-apcu'),__('Value','atec-cache-apcu'),'']);
				$c = 0; $total = 0;		
				foreach($arr as $entry) 
				{
					if (str_starts_with($entry['key'],WP_APCU_KEY_SALT))
					{
						$c++;
						$total+=$entry['mem_size'];
						$stripped = str_replace($search,'',$entry['key']);
						echo '<tr>
								<td>', esc_attr($c), '</td>
								<td class="atec-anywrap">'; echo esc_attr($stripped); echo '</td>
								<td class="atec-table-right">', esc_html($entry['num_hits']), '</td>
								<td class="atec-nowrap atec-table-right">', esc_html(size_format($entry['mem_size'])), '</td>
								<td class="atec-anywrap">', esc_html(htmlentities(substr(serialize($entry['value']),0,128))), '</td>';
								atec_create_button('delete&nav=APCu','trash',true,$url,$stripped,$nonce);
							echo '
							</tr>';
					}
				}
				atec_empty_tr();
				echo '<tr class="atec-table-tr-bold"><td>', esc_attr($c), '</td><td></td><td></td><td class="atec-nowrap">', esc_html(size_format($total)), '</td><td colspan="2"></td></tr>';
				atec_table_footer();
			}
			else { atec_error_msg(__('WP APCu Cache is empty','atec-cache-apcu')); echo '<br><br>'; }
		}
			
		atec_little_block(__('Other persistent','atec-cache-apcu').' APCu '.__('Cache','atec-cache-apcu'));
		atec_table_header_tiny(['#',__('Key','atec-cache-apcu'),__('Hits','atec-cache-apcu'),__('Size','atec-cache-apcu'),__('Value','atec-cache-apcu')]);
			$salt = $wp_apcu_key_salt_exists?WP_APCU_KEY_SALT:'TEMP_KEY_SALT';
			$c=0; $total=0;
			if (!empty($arr))
				foreach ($arr as $entry) 
				{
					if (str_starts_with($entry['key'],$salt)
					||
					str_starts_with($entry['key'],'atec_WPCA_')) continue;
					$c++;
					$total+=$entry['mem_size'];
					echo '<tr>
							<td class="atec-nowrap">', esc_attr($c), '</td>
							<td class="atec-anywrap">', esc_attr($entry['key']), '</td>
							<td class="atec-nowrap atec-table-right">', esc_html($entry['num_hits']), '</td>
							<td class="atec-nowrap atec-table-right">', esc_html(size_format($entry['mem_size'])), '</td>
							<td class="atec-anywrap">', esc_html(htmlentities(substr(serialize($entry['value']),0,128))), '</td>
						</tr>';
				}
			if ($c===0) echo '<tr><td colspan="999">-/-</td></tr>';
			else
			{
				atec_empty_tr();
				echo '<tr class="atec-table-tr-bold"><td>', esc_attr($c), '</td><td></td><td></td><td class="atec-nowrap">', esc_html(size_format($total)), '</td><td></td></tr>';
			}
		atec_table_footer();

		if (iterator_count($apcu_it=new APCUIterator('/atec_WPCA_*_*/'))!==0)
		{
			atec_little_block(__('Page Cache','atec-cache-apcu'));
			atec_table_header_tiny(['#',__('Key','atec-cache-apcu'),__('Hits','atec-cache-apcu'),__('Size','atec-cache-apcu')]);
				$arr = iterator_to_array($apcu_it);
				array_multisort(array_column($arr, 'key'), SORT_ASC,$arr);
				$c=0; $total=0;
				foreach ($arr as $entry) 
				{
					if (!str_starts_with($entry['key'],'atec_WPCA_')) continue;
					$c++;
					echo '<tr>
							<td class="atec-nowrap">', esc_attr($c), '</td>
							<td class="atec-anywrap atec-violet">', esc_attr($entry['key']), '</td>
							<td class="atec-nowrap atec-table-right">', esc_html($entry['num_hits']), '</td>
							<td class="atec-nowrap atec-table-right">', esc_html(size_format($entry['mem_size'])), '</td>
						</tr>';
					$total+=$entry['mem_size'];
				}
				atec_empty_tr();
				echo '<tr class="atec-table-tr-bold"><td>', esc_attr($c), '</td><td colspan="2"></td><td class="atec-nowrap atec-table-right">', esc_html(size_format($total)), '</td></tr>';
			atec_table_footer();
		}
		
	}
	else atec_error_msg('APCu '.__('cache data could NOT be retrieved','atec-cache-apcu'));
	
echo '
</div></div>';

}}

?>