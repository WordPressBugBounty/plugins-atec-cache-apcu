<?php
namespace ATEC_WPCA;
defined('ABSPATH') || exit;

use ATEC\ALIAS;
use ATEC\TOOLS;

final class Groups {

private static function debug_alloptions_integrity_check()
{
	$key      = 'wpbs_checkbox_test';
	$expected = time();
	$log      = [];

	update_option($key, $expected);
	$retrieved = get_option($key);

	// Delete runtime + persistent
	wp_cache_delete($key, 'options');
	wp_cache_delete('alloptions', 'options');

	$retrieved_after_flush = get_option($key);

	// Determine pass/fail
	$ok1 = ($retrieved == $expected);
	$ok2 = ($retrieved_after_flush == $expected); // allow loose check here

	TOOLS::div('box');

		TOOLS::p_title('Settings Cache Debug Test');
		TOOLS::table_header();
			ALIAS::tr(['Set', $expected . ' ✅']);
			ALIAS::tr(['Retrieved', $retrieved . ($ok1 ? ' ✅' : ' ❌')]);
			ALIAS::tr(['After flush', $retrieved_after_flush . ($ok2 ? ' ✅' : ' ❌')]);
		TOOLS::table_footer();

		if (!$ok1 || !$ok2)
		{
			$log[] = "\n❌ alloptions mismatch after flush";
			if (!$ok1)
				$log[] = "Mismatch before flush: got $retrieved";

			if (!$ok2)
				$log[] = "Mismatch after flush: got $retrieved_after_flush";

			if (defined('WP_DEBUG') && WP_DEBUG)
			{ 
				error_log('[OC DEBUG] alloptions test failed: ' . json_encode($log)); 		// phpcs:ignore
			}
		}

		echo '<pre class="atec-m-0">' . esc_html(trim(implode("\n", $log))) . '</pre>';

	TOOLS::div(-1);
}

private static function parse_alloptions($v1, $v2): bool
{
	$errors = false;
	foreach($v1 as $k=>$v) { if (!is_null($v) && gettype($v)=== 'array') $v1[$k]= serialize($v); }
	foreach($v2 as $k=>$v) { if (!is_null($v) && gettype($v)=== 'array') $v2[$k]= serialize($v); }
	foreach($v1 as $k=>$v)
	{
		if (isset($v2[$k]))
		{
			if ($v!== $v2[$k])
			{
				$errors = true;
				echo '<tr><td class="atec-nowrap">', esc_attr($k), '</td><td class="atec-anywrap">', esc_html($v), '</td></tr>';
			}
		}
	}
	return $errors;
}

public static function init($una)
{

	TOOLS::msg('warning', 'This page is for ‘PRO’ users debugging only');

	if ($una->action=== 'delete')
	{ 
		if (wp_cache_wpc_delete(ATEC_OC_KEY_SALT.':'.$una->id)) TOOLS::msg(true, 'Cache item removed'); 
	}

	$np_keys = array_keys(wp_cache_get_np_groups());

	self::debug_alloptions_integrity_check();
	TOOLS::clear();

	TOOLS::little_block('Local WP '.__('Object Cache', 'atec-cache-apcu').' vs. APCu '.__('Object Cache', 'atec-cache-apcu'));
	TOOLS::p_bold('Non persistent groups',implode(', ', $np_keys));

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
			$group = strpos($strippedKey, ':') !== false
				? substr($strippedKey, 0, strpos($strippedKey, ':'))
				: $strippedKey;

			$highlight = in_array($group, $np_keys, true)
			    ? '<span class="atec-violet">' . esc_html($group) . '</span>' . substr($strippedKey, strlen($group))
    			: $strippedKey;

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
				<td class="atec-anywrap">', wp_kses_post(substr($highlight,0,64)), '</td>
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
			'<h4>‘alloptions’ mismatch</h4>
			<div class="atec-box-white atec-fit atec-small atec-anywrap">';
				TOOLS::table_header([__('Key', 'atec-cache-apcu'),__('Value', 'atec-cache-apcu')]);
					$errors = self::parse_alloptions($alloptArr, $alloptAPCuArr);
					if (!$errors) ALIAS::tr(['999@No mismatch']);
				TOOLS::table_footer();
			echo
			'</div>';
		}
	}
	else TOOLS::msg(false, __('WP APCu Cache is empty', 'atec-cache-apcu'));

}

}
?>