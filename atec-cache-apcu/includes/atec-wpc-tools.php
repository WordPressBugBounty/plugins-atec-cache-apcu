<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_wpc_tools
{
	public function enabled($enabled,$active=false): void { echo '<span style="color:', ($enabled?($active?'black':'green'):'red'), '" title="', ($enabled?esc_attr__('Enabled','atec-cache-apcu'):esc_attr__('Disabled','atec-cache-apcu')), '" class="', esc_attr(atec_dash_class($enabled?'yes-alt':'warning')), '"></span>'; }
	public function error($cache,$txt): void { echo '<p class="atec-mb-0 atec-red">', esc_attr($cache), $cache!==''?' ':'', esc_html($txt),'.</p>'; }
	public function success($cache,$txt): void { echo '<p class="atec-mb-0">', esc_attr($cache), $cache!==''?' ':'', esc_html($txt), '.&nbsp;<span class="', esc_attr(atec_dash_class('yes-alt')), '"></span></p>'; }
	public function p($txt): void { echo '<p class="atec-mb-0">', esc_html($txt), '.</p>'; }
	
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