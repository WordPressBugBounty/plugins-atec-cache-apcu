<?php
defined('ABSPATH') || exit;

use ATEC\ALIAS;
use ATEC\INIT;
use ATEC\TOOLS;
use ATEC\WPC;

return function($una, $stats) 
{
	$href = INIT::build_url($una, 'flush', 'Cache', ['type' => 'OC_Stats']);
	echo
	'<div class="atec-border-white atec-dilb atec-fit atec-vat">
	
		<h4>',
			'WP OC Cumulated Stats',
			'<a title="', esc_attr__('Reset statistics', 'atec-cache-apcu'), '" class=" atec-float-right atec-ml-20 button" style="margin-top: -5px;" ',
				'href="', esc_url($href), '">', wp_kses_post(WPC::dash_trash()),
			'</a>',
		'</h4>
		<hr>';

		$diff = time()-($ts = $stats['ts']??0);
		$dayFrac	= $diff/86400;
		TOOLS::table_header([], '', 'summary');
			ALIAS::tr(['Started:', TOOLS::gmdate($ts)]);
			ALIAS::tr(['Requests:', $stats['count']??0]);
			ALIAS::tr();
			$hits = $stats['hits']??0; $misses = $stats['misses']??0; $total = $hits+$misses;
			$sets = $stats['sets']??0;
			ALIAS::tr(['Set:', number_format($sets)]);
			ALIAS::tr(['Get:', number_format($hits+$misses)]);
			if ($dayFrac>1)
			{
				ALIAS::tr();
				ALIAS::tr(['Set:', number_format($sets/$dayFrac)]);
				ALIAS::tr(['Get:', number_format(($total)/$dayFrac)]);
			}
		TOOLS::table_footer();

		if ($total>0) WPC::hitrate($hits*100/$total, $misses*100/$total);

	echo
	'</div>';

}
?>