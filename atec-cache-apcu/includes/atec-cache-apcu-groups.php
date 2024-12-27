<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_apcu_groups { 

	function __construct($url, $nonce, $wpc_tools, $prefix) {

	$salt=get_option($prefix.'_settings',[])['salt']??'';
	$arr=array('PC salt'=>$salt);
	if (defined('WP_APCU_KEY_SALT')) $arr[]=array('APCU salt (*)'=>WP_APCU_KEY_SALT);
	atec_little_block_with_info('Persistent WP-APCu-'.__('Object-Cache','atec-cache-apcu'), $arr);

	echo '
	<div class="atec-g"><div>';

		if (!defined('WP_APCU_KEY_SALT')) define('WP_APCU_KEY_SALT','WHATEVER');
		$apcu_cache_info=class_exists('APCUIterator')?apcu_cache_info(true):false;
		if ($apcu_cache_info)
		{
			if (defined('WP_APCU_KEY_SALT')) 
			{
				$c=0; $total=0;
				if (iterator_count($apcu_it=new APCUIterator('/'.WP_APCU_KEY_SALT.'/'))!==0)
				{
					atec_table_header_tiny(['#',__('Key','atec-cache-apcu'),__('Hits','atec-cache-apcu'),__('Size','atec-cache-apcu'),__('Value','atec-cache-apcu')]);
					foreach ($apcu_it as $entry) 
					{
						$c++;
						echo '<tr>
								<td>', esc_attr($c), '</td>
								<td class="atec-anywrap">'; echo esc_attr(str_replace(WP_APCU_KEY_SALT,'*',$entry['key'])); echo '</td>
								<td>', esc_html($entry['num_hits']), '</td>
								<td class="atec-nowrap">', esc_html(size_format($entry['mem_size'])), '</td>
								<td class="atec-anywrap">', esc_html(htmlentities(substr(serialize($entry['value']),0,128))), '</td>
							</tr>';
						$total+=$entry['mem_size'];
					}
					atec_empty_tr();
					echo '<tr class="atec-table-tr-bold"><td>', esc_attr($c), '</td><td></td><td></td><td class="atec-nowrap">', esc_html(size_format($total)), '</td><td></td></tr>';
					atec_table_footer();
				}
				else { atec_error_msg(__('WP-APCu-Cache is empty','atec-cache-apcu')); echo '<br><br>'; }
				
				$c=0; $total=0;
				atec_little_block('Other persistent APCu-'.__('Object-Cache','atec-cache-apcu'));
				atec_table_header_tiny(['#',__('Key','atec-cache-apcu'),__('Hits','atec-cache-apcu'),__('Size','atec-cache-apcu'),__('Value','atec-cache-apcu')]);
				foreach ($apcu_it as $entry) 
				{
					if (str_starts_with($entry['key'],'atec_WPCA_')) continue;
					$c++;
					echo '<tr>
							<td class="atec-nowrap">', esc_attr($c), '</td>
							<td class="atec-anywrap">', esc_attr($entry['key']), '</td>
							<td class="atec-nowrap">', esc_html($entry['num_hits']), '</td>
							<td class="atec-nowrap">', esc_html(size_format($entry['mem_size'])), '</td>
							<td class="atec-anywrap">', esc_html(htmlentities(substr(serialize($entry['value']),0,128))), '</td>
						</tr>';
					$total+=$entry['mem_size'];
				}
				atec_empty_tr();
				echo '<tr class="atec-table-tr-bold"><td>', esc_attr($c), '</td><td></td><td></td><td class="atec-nowrap">', esc_html(size_format($total)), '</td><td></td></tr>';
				atec_table_footer();
			}

			$c=0; $total=0;
			if (iterator_count($apcu_it=new APCUIterator('/atec_WPCA_*_*/'))!==0)
			{
				atec_little_block(__('Page Cache','atec-cache-apcu'));
				atec_table_header_tiny(['#',__('Key','atec-cache-apcu'),__('Hits','atec-cache-apcu'),__('Size','atec-cache-apcu')]);
				foreach ($apcu_it as $entry) 
				{
					if (!str_starts_with($entry['key'],'atec_WPCA_')) continue;
					$c++;
					echo '<tr>
							<td class="atec-nowrap">', esc_attr($c), '</td>
							<td class="atec-anywrap atec-violet">', esc_attr($entry['key']), '</td>
							<td class="atec-nowrap">', esc_html($entry['num_hits']), '</td>
							<td class="atec-nowrap">', esc_html(size_format($entry['mem_size'])), '</td>
						</tr>';
                    $total+=$entry['mem_size'];
				}
				atec_empty_tr();
				echo '<tr class="atec-table-tr-bold"><td>', esc_attr($c), '</td><td colspan="3"></td><td class="atec-nowrap">', esc_html(size_format($total)), '</td></tr>';
				atec_table_footer();
			}
		}
		else $wpc_tools->error('APCu',__('cache data could NOT be retrieved','atec-cache-apcu'));
		
	echo '
	</div></div>';

}}

?>