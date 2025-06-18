<?php
namespace ATEC_WPCA;
defined('ABSPATH') || exit;

use ATEC\TOOLS;

final class Groups {

private static function parse_alloptions($v1, $v2)
{
	foreach($v1 as $k=>$v) { if (!is_null($v) && gettype($v)=== 'array') $v1[$k]= serialize($v); }
	foreach($v2 as $k=>$v) { if (!is_null($v) && gettype($v)=== 'array') $v2[$k]= serialize($v); }
	foreach($v1 as $k=>$v)
	{
		if (isset($v2[$k]))
		{
			if ($v!== $v2[$k])
			{
				echo '<tr><td class="atec-nowrap">', esc_attr($k), '</td><td class="atec-anywrap">', esc_html($v), '</td></tr>';
			}
		}
	}
}

public static function init($una)
{

	if ($una->action=== 'delete')
		{ if (wp_cache_wpc_delete(ATEC_OC_KEY_SALT.':'.$una->id)) TOOLS::msg(true, 'Cache item removed'); }

	echo
	'<div class="atec-g">
		<div>';

			TOOLS::little_block('Local WP '.__('Object Cache', 'atec-cache-apcu').' vs. APCu '.__('Object Cache', 'atec-cache-apcu'));
			TOOLS::msg('warning', 'This page is for „PRO“ users debugging only');

			$wpc_arr = wp_cache_wpc_array();
			if (!empty($wpc_arr))
			{
				TOOLS::table_header(['#',__('Key', 'atec-cache-apcu'),__('Value', 'atec-cache-apcu'), 'APCu?', 'WP==APCu', '']);

				$c = 0; $total = 0;
				$alloptArr = $alloptAPCuArr = [];
				$search = ATEC_OC_KEY_SALT.':';
				ksort($wpc_arr);
				foreach($wpc_arr as $key=>$value)
				{
					$c++;
					$apcu = apcu_fetch($key, $success);
					$strippedKey = str_replace($search, '', $key);

					$eq = false;
					$a1 = maybe_serialize($value);
					if ($success)
					{
						$a2 = maybe_serialize($apcu);
						$eq = $a1=== $a2;
						$i2	= $eq?'yes-alt' : 'dismiss';
						$c2	= 'atec-'.($eq?'green' : 'red');
						if (str_contains($strippedKey, 'alloptions') && !$eq) { $alloptArr= $value; $alloptAPCuArr= $apcu; }
					}

					$i1	= $success?'yes-alt' : 'dismiss';
					$c1	= 'atec-'.($success?'green' : 'red');
					echo
					'<tr>
						<td>', esc_attr($c), '</td>
						<td class="atec-anywrap">', esc_attr(substr($strippedKey,0,64)), '</td>
						<td', $success && !$eq?' title="'.esc_html($a1).'&#013;&#013;'.esc_html($a2).'"' : '' , ' class="atec-anywrap">', esc_html(substr($a1,0,64)), '</td>
						<td><span class="', esc_attr(TOOLS::dash_class($i1, $c1)), '"></span></td>
						<td><span class="', ($success?esc_attr(TOOLS::dash_class($i2, $c2)):''), '"></span></td>';
						TOOLS::dash_button_td($una, 'delete', 'WP_Cache_Debug', 'trash', true, $strippedKey);
					echo '
					</tr>';
				}
				TOOLS::table_footer();

				if (!empty($alloptArr))
				{
					echo
					'<h4>„alloptions“</h4>
					<div class="atec-box-white atec-fit atec-small atec-anywrap">';
						TOOLS::table_header([__('Key', 'atec-cache-apcu'),__('Value', 'atec-cache-apcu')]);
							self::parse_alloptions($alloptArr, $alloptAPCuArr);
						TOOLS::table_footer();
					echo
					'</div>';
				}
			}
			else TOOLS::msg(false, __('WP APCu Cache is empty', 'atec-cache-apcu'));

	echo
	'</div>
</div>';

}

}
?>