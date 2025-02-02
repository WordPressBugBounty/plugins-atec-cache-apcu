<?php
if (!defined('ABSPATH')) { exit(); }

class ATEC_wpc_tools
{
	public function hitrate($hits,$misses)
	{
		echo '
		<div class="atec-db atec-border atec-bg-w atec-mb-10" style="width:180px; padding: 3px 5px 5px 5px;">
			<div class="atec-dilb atec-fs-12">', esc_attr__('Hitrate','atec-cache-apcu'), '</div>
			<div class="atec-dilb atec-right atec-fs-12">', esc_attr(round($hits,1)), '%</div>
			<br>
			<div class="ac_percent_div">
				<span class="ac_percent" style="width:', esc_attr($hits), '%; background-color:green;"></span>
				<span class="ac_percent" style="width:', esc_attr($misses), '%; background-color:red;"></span>
			</div>
		</div>';
	}
	public function usage($percent)
	{
		echo '
		<div class="atec-db atec-border atec-bg-w atec-mb-10" style="width:180px; padding: 3px 5px 5px 5px;">
			<div class="atec-dilb atec-fs-12">', esc_attr__('Usage','atec-cache-apcu'), '</div>
			<div class="atec-dilb atec-right atec-fs-12">', esc_attr(round($percent,1)), '%</div>
			<br>
			<div class="ac_percent_div">
				<span class="ac_percent" style="width:', esc_attr($percent), '%; background-color:orange;"></span>
				<span class="ac_percent" style="width:', esc_attr(100-$percent), '%; background-color:white;"></span>
			</div>
		</div>';
	}
}
?>