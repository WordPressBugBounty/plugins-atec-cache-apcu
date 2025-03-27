<?php
if (!defined('ABSPATH')) { exit; }

class ATEC_wpc_tools
{
	public static function hitrate($hits,$misses)
	{
		$id1=uniqid();
		$id2=uniqid();
		echo '
		<div class="atec-db atec-border ac_percent_block">
			<div class="atec-dilb atec-fs-12">', esc_attr__('Hitrate','atec-cache-apcu'), '</div>
			<div class="atec-dilb  atec-float-right atec-fs-12">', esc_attr(round($hits,1)), '%</div>
			<br>
			<div class="ac_percent_div">
				<span id="atec_hitrate_'.esc_attr($id1).'" style="background-color:green;"></span>
				<span id="atec_hitrate_'.esc_attr($id2).'" style="background-color:red;"></span>
			</div>
		</div>';
		atec_reg_inline_script('wpx_anim_hitrate',
		'jQuery("#atec_hitrate_'.esc_attr($id1).'").animate({ width: "'.($hits).'%" }, 1000);
		jQuery("#atec_hitrate_'.esc_attr($id2).'").animate({ width: "'.($misses).'%" }, 1000);
		'); 		
	}

	public static function usage($percent)
	{
		$id1=uniqid();
		echo '
		<div class="atec-db atec-border ac_percent_block">
			<div class="atec-dilb atec-fs-12">', esc_attr__('Usage','atec-cache-apcu'), '</div>
			<div class="atec-dilb  atec-float-right atec-fs-12">', esc_attr(round($percent,1)), '%</div><br>
			<div class="ac_percent_div"><span id="atec_usage_'.esc_attr($id1).'" style="background-color:orange;"></span></div>
		</div>';
		atec_reg_inline_script('wpx_anim_usage','jQuery("#atec_usage_'.esc_attr($id1).'").animate({ width: "'.($percent).'%" }, 1000);'); 
	}
	
	public static function flushing_start($type)
	{
		echo 
		'<div class="atec-badge atec-db atec-mb-10 atec-bg-w6" style="padding: 5px 10px 5px 10px;">
			<div class="atec-dilb" style="width:20px; padding-right:5px;"><span id="atec_wpc_dash" class="dashicon-spin ', esc_attr(atec_dash_class('hourglass')), '"></span></div>
			<div class="atec-dilb">', esc_attr__('Flushing','atec-cache-apcu'), ' ', esc_attr(str_replace('_',' ',$type)), '&nbsp;...&nbsp;</div>';
			atec_loader_dots();
			atec_flush();
	}

	public static function flushing_end($result)
	{
		$str = $result?__('success','atec-cache-apcu'):__('failed','atec-cache-apcu');
			echo 
			'<span class="atec-', $result?'green':'red', '">', esc_attr($str), '</span>.
		</div>';
		atec_reg_inline_script('wpx_redirect','jQuery(".atec-loader-dots").remove(); jQuery("#atec_wpc_dash").removeClass("dashicon-spin dashicons-hourglass").addClass("dashicons-'.($result?'yes-alt':'dismiss').'");'); 
	}
}
?>