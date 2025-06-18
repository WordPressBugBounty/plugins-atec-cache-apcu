<?php
defined('ABSPATH') || exit;

use ATEC\TOOLS;
use ATEC\WPC;

return function($una) 
{
	
	TOOLS::little_block_multi($una, 'APCu WP '.__('Object Cache', 'atec-cache-apcu'), ['flush' => '#trash Flush'], 'APCu');

	switch ($una->action)
	{
		case 'flush':
			WPC::flush_cache($una, [], 'WP');
			break;
			
		case 'delete':
			if ($una->id !== '') apcu_delete($una->id);
			break;
	}
	
	$apcu_it=new APCUIterator();
	$arr = iterator_to_array($apcu_it);
	array_multisort(array_column($arr, 'key'), SORT_ASC, $arr);

	if (defined('ATEC_OC_KEY_SALT'))
	{
		TOOLS::table_header([
			'#',
			__('Group', 'atec-cache-apcu'),
			__('Key', 'atec-cache-apcu'),
			__('Hits', 'atec-cache-apcu'),
			__('Type', 'atec-cache-apcu'),
			__('Size', 'atec-cache-apcu'),
			__('Value', 'atec-cache-apcu'),
			'']);
			
			$c = 0; $total = 0;
			foreach($arr as $entry)
			{
				if (strpos($entry['key'],ATEC_OC_KEY_SALT)===0)
				{
					$c++;
					$total+= $entry['mem_size'];
					[$salt, $group, $key] = explode(':', $entry['key'], 3);
					echo
					'<tr>';
						TOOLS::table_td($c);
						TOOLS::table_td($group);
						TOOLS::table_td($key, 'atec-anywrap');
						TOOLS::table_td($entry['num_hits'], 'atec-right');
						TOOLS::table_td(gettype($entry['value']), 'atec-right');
						TOOLS::table_td(size_format($entry['mem_size']), 'atec-nowrap atec-right');
						TOOLS::table_td(htmlentities(substr(serialize($entry['value']),0,64)), 'atec-anywrap');
						TOOLS::dash_button_td($una, 'delete', 'APCu', 'trash', true, $entry['key']);
					echo '
					</tr>';
				}
			}
			TOOLS::table_tr();
			TOOLS::table_tr([$c, '2@', TOOLS::size_format($total), '2@'], 'td', 'bold');
			
		TOOLS::table_footer();
	}
	else TOOLS::msg('info', 'APCu WP '.__('Object Cache', 'atec-cache-apcu').' '.__('is empty', 'atec-cache-apcu'));

	TOOLS::clear();
		
	TOOLS::little_block(__('Other persistent', 'atec-cache-apcu').' '.__('Items', 'atec-cache-apcu'));
	TOOLS::table_header([
		'#',
		__('Key', 'atec-cache-apcu'),
		__('Hits', 'atec-cache-apcu'),
		__('Size', 'atec-cache-apcu'),
		__('Value', 'atec-cache-apcu')]);
	
		$salt = defined('ATEC_OC_KEY_SALT') ? ATEC_OC_KEY_SALT : 'TEMP_KEY_SALT';
		$c=0; $total=0;
		foreach ((array) $arr as $entry)
		{
			if (strpos($entry['key'], $salt)===0 || strpos($entry['key'], 'atec_WPCA_')===0) continue;	// Skip OC & PC
			$c++;
			$total+= $entry['mem_size'];
			echo 
			'<tr>
				<td class="atec-nowrap">', esc_attr($c), '</td>
				<td class="atec-anywrap">', esc_attr($entry['key']), '</td>
				<td class="atec-nowrap atec-right">', esc_html($entry['num_hits']), '</td>';
				TOOLS::td_size_format($entry['mem_size']);
				echo
				'<td class="atec-anywrap">', esc_html(htmlentities(substr(serialize($entry['value']),0,128))), '</td>
			</tr>';
		}

		TOOLS::table_tr();
		if ($c===0) TOOLS::table_tr(['99@-/-']);
		else TOOLS::table_tr([$c, '2@', TOOLS::size_format($total), ''], 'td', 'bold');

	TOOLS::table_footer();
		
	TOOLS::clear();

	TOOLS::little_block(__('Page Cache', 'atec-cache-apcu'));
	TOOLS::table_header([
		'#',
		__('Key', 'atec-cache-apcu'),
		__('Hits', 'atec-cache-apcu'),
		__('Size', 'atec-cache-apcu')]);
		
		$c = 0;
		$total = 0;
		foreach ($arr as $entry)
		{
			if (strpos($entry['key'], 'atec_WPCA_')!==0) continue;	// Skip OC & PC
			$c++;
			echo 
			'<tr>
				<td class="atec-nowrap">', esc_attr($c), '</td>
				<td class="atec-anywrap atec-violet">', esc_attr($entry['key']), '</td>
				<td class="atec-nowrap atec-right">', esc_html($entry['num_hits']), '</td>';
				TOOLS::td_size_format($entry['mem_size']);
				echo
			'</tr>';
			$total+= $entry['mem_size'];
		}
		TOOLS::table_tr();
		TOOLS::table_tr([$c, '2@', TOOLS::size_format($total)], 'td', 'bold');
		
	TOOLS::table_footer();

}
?>